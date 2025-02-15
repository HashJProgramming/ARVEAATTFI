<?php
require_once 'connection.php';

try {
    
    // Query to fetch the settings
    $stmt = $db->query("SELECT * FROM `settings` WHERE `id` = 1");

    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        // Extract the container height and volume from settings
        $containerHeight = $settings['height'];
        $containerVolume = $settings['volume'];
        $baseArea = $containerVolume / $containerHeight;

        // DB table to use
        $table = 'distances';
        // Table's primary key
        $primaryKey = 'id';
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array(
                    'db' => 'distance',  
                    'dt' => 1,
                    'formatter' => function($d, $row) {
                        return number_format($row['distance'], 0, '.', '') . ' cm';
                    }
                ),
            array(
                'db' => 'id',  
                'dt' => 2, // We'll use this column to return waterVolume
                'formatter' => function($d, $row) use ($containerHeight, $baseArea) {
                    $waterLevel = $containerHeight - $row['distance']; // Water level in cm
                    $waterVolume = $waterLevel * $baseArea;            // Volume in cm³
                    return number_format(($waterVolume / 1000), 2, '.', ''). ' L';
                }
            ),
            array(
                'db' => 'id',  
                'dt' => 3, // We'll use this column to return waterPercentage
                'formatter' => function($d, $row) use ($containerHeight, $containerVolume, $baseArea) {
                    $waterLevel = $containerHeight - $row['distance']; // Water level in cm
                    $waterVolume = $waterLevel * $baseArea;            // Volume in cm³
                    $waterPercentage = ($waterVolume / $containerVolume) * 100; // Percentage
                    return number_format($waterPercentage, 2, '.', ''). '%';
                }
            ),
            array( 'db' => 'timestamp',   'dt' => 4 )
        );

        // SQL server connection information
        $sql_details = array(
            'user' => 'root',
            'pass' => '',
            'db'   => 'arveaattfi',
            'host' => 'localhost'
        );

        echo json_encode(
            SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns)
        );
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