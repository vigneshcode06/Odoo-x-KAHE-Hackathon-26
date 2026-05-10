<?php
// Database configuration
// When using Docker, the DB_HOST is usually the service name 'db'
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'traveloop';
$user = getenv('DB_USER') ?: 'traveloop';
$pass = getenv('DB_PASS') ?: 'secret';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Return JSON error if API, otherwise show plain text error (but avoid showing sensitive info)
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Database connection failed"]);
        exit;
    } else {
        die("Database connection failed. Please try again later.");
    }
}
