#include <ESP8266WiFi.h>
#include <Arduino.h>
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>

const char *ssid = "Project"; // AP SSID
const int relayPins[] = {D0, D1, D2, D3, D4}; // Relays connected to D0-D4
const int trigPin = D6;       // Ultrasonic sensor Trigger
const int echoPin = D5;       // Ultrasonic sensor Echo
const int soilMoisturePin = A0; // Soil moisture sensor connected to A0
bool manualOverride = false; // Flag to check if manual control is active

unsigned long duration = 0;

DNSServer dnsServer;
IPAddress apIP(10, 0, 0, 1);
ESP8266WebServer webServer(80);

// Add CORS headers to the response
void addCORSHeaders() {
  webServer.sendHeader("Access-Control-Allow-Origin", "*");
  webServer.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  webServer.sendHeader("Access-Control-Allow-Headers", "Content-Type");
}

// Function to measure distance using ultrasonic sensor
long measureDistance() {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);

  long duration = pulseIn(echoPin, HIGH); // Measure the time taken for the echo
  long distance = duration * 0.034 / 2;  // Convert to distance in cm
  return distance;
}

// Turn specific relay ON
void handleOn() {
  addCORSHeaders();
  int relayIndex = webServer.arg("relay").toInt();
  if (relayIndex >= 0 && relayIndex < 5) {
    digitalWrite(relayPins[relayIndex], LOW); // Turn relay ON
    manualOverride = true; // Enable manual override
    webServer.send(200, "text/plain", "Relay " + String(relayIndex) + " is ON");
  } else {
    webServer.send(400, "text/plain", "Invalid relay index");
  }
}

// Turn specific relay OFF
void handleOff() {
  addCORSHeaders();
  int relayIndex = webServer.arg("relay").toInt();
  if (relayIndex >= 0 && relayIndex < 5) {
    digitalWrite(relayPins[relayIndex], HIGH); // Turn relay OFF
    manualOverride = true; // Enable manual override
    webServer.send(200, "text/plain", "Relay " + String(relayIndex) + " is OFF");
  } else {
    webServer.send(400, "text/plain", "Invalid relay index");
  }
}

// Measure distance and return result
void handleDistance() {
  addCORSHeaders();
  long distance = measureDistance();
  webServer.send(200, "text/plain", String(distance));
}

// Measure soil moisture and return result
void handleSoilMoisture() {
  addCORSHeaders();
  int sensorAnalog = analogRead(soilMoisturePin);
  float moisturePercentage = 100 - ((sensorAnalog / 1023.0) * 100); // Adjust for 10-bit ADC
  webServer.send(200, "text/plain", "Soil Moisture: " + String(moisturePercentage, 2) + "%");
}

// Handle preflight (OPTIONS) requests
void handleOptions() {
  addCORSHeaders();
  webServer.send(204); // No Content
}

// Function to send distance data to the server
void sendDistanceToServer(long distance) {
 // if (WiFi.status() == WL_CONNECTED) { // Ensure WiFi is connected
    WiFiClient client; // Create a WiFiClient instance
    HTTPClient http;
    String serverPath = "http://10.0.0.100/ARVEAATTFI/assets/php/storeDistance.php";
    
    // Start connection and send HTTP POST request
    http.begin(client, serverPath); // Pass the WiFiClient instance and the URL
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    // Prepare POST data
    String postData = "distance=" + String(distance);
    int httpResponseCode = http.POST(postData);
    
    if (httpResponseCode > 0) {
      Serial.println("Data sent to server successfully");
      Serial.println("Response: " + http.getString());
    } else {
      Serial.println("Error in sending POST request: " + String(httpResponseCode));
    }
    
    http.end(); // Close connection
  // } else {
  //   Serial.println("WiFi not connected");
  // }
}


void fetchDuration() {
  // if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    String serverPath = "http://10.0.0.100/ARVEAATTFI/assets/php/getDuration.php";

    http.begin(client, serverPath);
    int httpResponseCode = http.GET();

    if (httpResponseCode > 0) {
      String response = http.getString();
      // Parse JSON response
      int startIndex = response.indexOf("\"duration\":") + 11;
      int endIndex = response.indexOf("}", startIndex);
      String durationStr = response.substring(startIndex, endIndex);
      duration = durationStr.toInt() * 1000;  // Convert to milliseconds
      Serial.println("Duration fetched: " + String(duration) + " ms");
    } else {
      Serial.println("Error fetching duration: " + String(httpResponseCode));
    }
    http.end();
  // } else {
  //   Serial.println("WiFi not connected");
  // }
}

void setup() {
  Serial.begin(9600);

  // Relay setup
  for (int i = 0; i < 5; i++) {
    pinMode(relayPins[i], OUTPUT);
    digitalWrite(relayPins[i], HIGH); // Relay OFF by default
  }

  // Ultrasonic sensor setup
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  // Configure Access Point
  IPAddress gateway(10, 0, 0, 1);
  IPAddress subnet(255, 255, 255, 0);
  WiFi.softAPConfig(apIP, gateway, subnet);
  WiFi.softAP(ssid);

  // Start DNS server
  dnsServer.start(53, "*", apIP);

  // Configure web server routes
  webServer.on("/on", handleOn);
  webServer.on("/off", handleOff);
  webServer.on("/distance", handleDistance);
  webServer.on("/soilmoisture", handleSoilMoisture);
  webServer.on("/options", HTTP_OPTIONS, handleOptions); // Handle OPTIONS requests

  // Start web server
  webServer.begin();
  Serial.println("HTTP server started on 10.0.0.1");
  fetchDuration();
}

void loop() {
  dnsServer.processNextRequest();
  webServer.handleClient();
  
  long distance = measureDistance(); // Measure distance
  static unsigned long lastSendTime = 0; // Tracks the last time data was sent
  unsigned long currentTime = millis();

  static unsigned long lastSendTime_relay = 0; // Tracks the last time data was sent
  unsigned long currentTime_relay = millis();

  // If distance is less than or equal to 5 cm, turn all relays ON
  if (distance <= 5) {
    for (int i = 0; i < 5; i++) {
      digitalWrite(relayPins[i], LOW); // Turn all relays ON
    }
    manualOverride = false; // Disable manual override when distance condition is met
  } else if (!manualOverride) {
    // If manual control is not active and distance is greater than 5 cm, turn all relays OFF
    for (int i = 0; i < 5; i++) {
      digitalWrite(relayPins[i], HIGH); // Turn all relays OFF
    }
  }

  // Handle sending the measured distance to the server only every 5 minutes
  if (currentTime - lastSendTime >= 300000) { // Check if 5 minues have passed
    lastSendTime = currentTime; // Update the last send time
    // Only call sendDistanceToServer here, ensuring it doesn't block other processes
    sendDistanceToServer(distance);
  }

  if (currentTime_relay - lastSendTime_relay >= duration) {
    fetchDuration();
    lastSendTime_relay = currentTime_relay; // Update the last send time
    digitalWrite(relayPins[0], LOW);
    delay(10000);
    digitalWrite(relayPins[0], HIGH);
  }

  // Other functions can continue executing without delay
}
