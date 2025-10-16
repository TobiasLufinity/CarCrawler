<?php

echo "Installing Database" . PHP_EOL;

$config = require __DIR__ . "/config.php";

$db = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

$dbName = $config['db_name'];
$db->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->select_db($dbName);


$createUrls = "
CREATE TABLE IF NOT EXISTS urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255) NOT NULL UNIQUE,
    crawled_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
) ENGINE=InnoDB;
";

if ($db->query($createUrls) === TRUE) {
    echo "Table 'car_url' created or already exists.\n";
} else {
    echo "Error creating table: " . $db->error . "\n";
}

// Create tables if they don't exist
$createCars = "
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url_id INT NOT NULL,
    brand VARCHAR(255) NULL,
    model VARCHAR(255)  NULL,
    description LONGTEXT NULL,
    registration VARCHAR(255) UNIQUE NULL,
    price DECIMAL(10,2)  NULL,
    year INT NULL,
    mileage INT NULL,
    fuel VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (url_id) REFERENCES urls(id)
    FULLTEXT idx_fulltext (brand, model, description, registration, fuel);
) ENGINE=InnoDB;
";

if ($db->query($createCars) === TRUE) {
    echo "Table 'cars' created or already exists.\n";
} else {
    echo "Error creating table: " . $db->error . "\n";
}


$db->close();

echo "Intallation complete!" . PHP_EOL;