<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
    require_once('ssp.class.php');
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "arveaattfi";

    $db = new PDO("mysql:host=$host", $username, $password);
    $query = "CREATE DATABASE IF NOT EXISTS $database";

    try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec($query);
        $db->exec("USE $database");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `distances` (
                `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,    
                `distance` double NOT NULL,
                `timestamp` datetime DEFAULT current_timestamp()
            );
        ");
        
        $db->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `username` varchar(255) NOT NULL,
                `password` varchar(255) NOT NULL,
                `height` int(11) NOT NULL,
                `volume` int(11) NOT NULL,
                `duration` int(11) NOT NULL
            );
        ");

        $db->beginTransaction();

        $stmt = $db->prepare("SELECT COUNT(*) FROM `settings` WHERE `username` = 'admin'");
        $stmt->execute();
        $userExists = $stmt->fetchColumn();
        
        if (!$userExists) {
            $stmt = $db->prepare("INSERT INTO `settings` (`username`, `password`, `height`, `volume`, `duration`) VALUES (:username, :password, 30, 16000, 60)");
            $stmt->bindValue(':username', 'admin');
            $stmt->bindValue(':password', '$2y$10$WgL2d2fzi6IiGiTfXvdBluTLlMroU8zBtIcRut7SzOB6j9i/LbA4K');
            $stmt->execute();
        }
        
        $db->commit();

    } catch(PDOException $e) {
        die("Error creating database: " . $e->getMessage());
    }
?>