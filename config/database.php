<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'career_copilot');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die("Database connection failed. Please check XAMPP/MySQL settings: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}
?>