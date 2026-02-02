<?php
// Database configuration for consultation_system
// Update these values to match your environment or use environment variables in production
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'consultation_system');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Create and return a PDO instance configured for MySQL.
 * Uses exceptions for error handling and sets UTF8.
 *
 * @return PDO
 */
function getPDO()
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

?>
