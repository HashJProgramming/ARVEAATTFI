<?php
// Allow all CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "arveaattfi";

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if distance is received
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distance'])) {
        $distance = $_POST['distance'];

        // Prepare SQL statement to insert data
        $stmt = $conn->prepare("INSERT INTO distances (distance, timestamp) VALUES (:distance, NOW())");
        $stmt->bindParam(':distance', $distance, PDO::PARAM_STR); // Bind as a string (you can also use PDO::PARAM_INT if you want)

        if ($stmt->execute()) {
            echo "Distance stored successfully.";
        } else {
            echo "Error storing distance.";
        }
    } else {
        echo "Invalid request.";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>