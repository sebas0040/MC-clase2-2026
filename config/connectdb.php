<?php
/**
 * Database Connection Configuration
 * MC-CLASE2-2026 | Library Management System
 */

// Captura warnings para que no contaminen el JSON
ob_start();

ini_set('display_errors', '0');
ini_set('log_errors',     '1');
error_reporting(E_ALL);

// En Docker, el host es el nombre del servicio definido en docker-compose.yml
// En XAMPP local sería 'localhost' — cambia según tu entorno
define('DB_HOST',    getenv('DB_HOST')     ?: 'db');
define('DB_USER',    getenv('DB_USER')     ?: 'Galel');
define('DB_PASS',    getenv('DB_PASSWORD') ?: 'Sebas#12');
define('DB_NAME',    getenv('DB_NAME')     ?: 'uden_db_clase2');
define('DB_CHARSET', 'utf8mb4');

/**
 * Devuelve una conexión MySQLi segura.
 */
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        error_log('[DB] Connection failed: ' . $conn->connect_error);
        throw new RuntimeException('Database connection unavailable. Please try again later.');
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * Respuesta JSON de error.
 */
function sendError($code, $message) {
    ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Respuesta JSON de éxito.
 */
function sendSuccess($data = null, $message = 'OK') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}
