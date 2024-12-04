<?php
require_once 'connection.php';

try {
    // Check if the required POST parameters are set
    if (isset($_POST['height']) && isset($_POST['volume']) && isset($_POST['duration'])) {
        // Get the input values
        $height = $_POST['height'];
        $volume = $_POST['volume'];
        $duration = $_POST['duration'];

        // Prepare an update statement
        $stmt = $db->prepare("UPDATE `settings` SET `height` = :height, `volume` = :volume, `duration` = :duration WHERE `id` = 1");
        
        // Bind the parameters
        $stmt->bindParam(':height', $height, PDO::PARAM_INT);
        $stmt->bindParam(':volume', $volume, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Settings updated successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update settings"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid input"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
}
?>