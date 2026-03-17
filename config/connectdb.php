<?php
/**
 * Database Connection Configuration
 * MC-CLASE2-2026 | Library Management System
 */

// Capture any accidental PHP warnings/notices so they don't corrupt JSON output
ob_start();

// Show errors in the PHP error log only, never in the HTTP response body
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
error_reporting(E_ALL);

define('DB_HOST',    'localhost');
define('DB_USER',    'Galel');
define('DB_PASS',    'Sebas#12');
define('DB_NAME',    'uden_db_clase2');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a secure MySQLi connection.
 * Throws RuntimeException on failure (credentials never exposed to client).
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
 * Sends a JSON error response and terminates execution.
 */
function sendError($code, $message) {
    ob_end_clean(); // discard any buffered HTML warnings
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Sends a JSON success response and terminates execution.
 */
function sendSuccess($data = null, $message = 'OK') {
    ob_end_clean(); // discard any buffered HTML warnings
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}
