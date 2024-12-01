<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$dbname = "arveaattfi";

try {
    // Establish a connection to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch the settings
    $stmt = $conn->query("SELECT duration FROM settings WHERE username = 'admin' AND password = 'admin'");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($settings) {
        // Return the settings as JSON
        echo json_encode([
            "status" => "success",
            "data" => $settings
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No settings found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
}
?>
