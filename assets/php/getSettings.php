<?php
require_once 'connection.php';

try {
    // Query to fetch the settings
    $stmt = $db->query("SELECT `height`, `volume`, `duration` FROM `settings` WHERE `id` = 1");

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
