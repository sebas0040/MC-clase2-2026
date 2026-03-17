<?php
/**
 * API Endpoint: Authors (autores)
 * Handles GET, POST, PUT, DELETE for the autores table.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'connectdb.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getConnection();
} catch (RuntimeException $e) {
    sendError(503, $e->getMessage());
}

switch ($method) {

    // ── GET ──────────────────────────────────────────────────────────────────
    case 'GET':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if ($id) {
            $stmt = $conn->prepare('SELECT id_autor, autor FROM autores WHERE id_autor = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                sendError(404, 'Author not found.');
            }
            sendSuccess($row);
        }

        $result = $conn->query('SELECT id_autor, autor FROM autores ORDER BY id_autor ASC');
        $authors = [];
        while ($row = $result->fetch_assoc()) {
            $authors[] = $row;
        }
        sendSuccess($authors);
        break;

    // ── POST ─────────────────────────────────────────────────────────────────
    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        $name = trim($body['autor'] ?? '');

        if ($name === '') {
            sendError(400, 'Field "autor" is required.');
        }

        $stmt = $conn->prepare('INSERT INTO autores (autor) VALUES (?)');
        $stmt->bind_param('s', $name);

        if (!$stmt->execute()) {
            error_log('[DB] INSERT autores: ' . $stmt->error);
            sendError(500, 'Failed to create author.');
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        sendSuccess(['id_autor' => $newId], 'Author created successfully.');
        break;

    // ── PUT ──────────────────────────────────────────────────────────────────
    case 'PUT':
        $body = json_decode(file_get_contents('php://input'), true);
        $id   = (int) ($body['id_autor'] ?? 0);
        $name = trim($body['autor']      ?? '');

        if ($id <= 0 || $name === '') {
            sendError(400, 'Fields "id_autor" and "autor" are required.');
        }

        $stmt = $conn->prepare('UPDATE autores SET autor = ? WHERE id_autor = ?');
        $stmt->bind_param('si', $name, $id);

        if (!$stmt->execute()) {
            error_log('[DB] UPDATE autores: ' . $stmt->error);
            sendError(500, 'Failed to update author.');
        }

        if ($stmt->affected_rows === 0) {
            sendError(404, 'Author not found or no changes made.');
        }

        $stmt->close();
        sendSuccess(null, 'Author updated successfully.');
        break;

    // ── DELETE ───────────────────────────────────────────────────────────────
    case 'DELETE':
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            sendError(400, 'A valid author ID is required.');
        }

        // Prevent orphan books
        $check = $conn->prepare('SELECT COUNT(*) AS total FROM libros WHERE id_autor = ?');
        $check->bind_param('i', $id);
        $check->execute();
        $count = $check->get_result()->fetch_assoc()['total'];
        $check->close();

        if ($count > 0) {
            sendError(409, "Cannot delete: this author has {$count} associated book(s). Reassign or delete them first.");
        }

        $stmt = $conn->prepare('DELETE FROM autores WHERE id_autor = ?');
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            error_log('[DB] DELETE autores: ' . $stmt->error);
            sendError(500, 'Failed to delete author.');
        }

        if ($stmt->affected_rows === 0) {
            sendError(404, 'Author not found.');
        }

        $stmt->close();
        sendSuccess(null, 'Author deleted successfully.');
        break;

    default:
        sendError(405, 'Method not allowed.');
}

$conn->close();
