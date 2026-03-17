<?php
/**
 * API Endpoint: Books (libros)
 * Handles GET, POST, PUT, DELETE for the libros table.
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
            $stmt = $conn->prepare(
                'SELECT l.id_libro, l.libro, l.id_autor, a.autor
                 FROM libros l
                 LEFT JOIN autores a ON l.id_autor = a.id_autor
                 WHERE l.id_libro = ?'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                sendError(404, 'Book not found.');
            }
            sendSuccess($row);
        }

        // Return all books with author name joined
        $result = $conn->query(
            'SELECT l.id_libro, l.libro, l.id_autor, a.autor
             FROM libros l
             LEFT JOIN autores a ON l.id_autor = a.id_autor
             ORDER BY l.id_libro ASC'
        );
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
        sendSuccess($books);
        break;

    // ── POST ─────────────────────────────────────────────────────────────────
    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        $title    = trim($body['libro']    ?? '');
        $authorId = (int) ($body['id_autor'] ?? 0);

        if ($title === '' || $authorId <= 0) {
            sendError(400, 'Fields "libro" and "id_autor" are required.');
        }

        $stmt = $conn->prepare('INSERT INTO libros (libro, id_autor) VALUES (?, ?)');
        $stmt->bind_param('si', $title, $authorId);

        if (!$stmt->execute()) {
            error_log('[DB] INSERT libros: ' . $stmt->error);
            sendError(500, 'Failed to create book.');
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        sendSuccess(['id_libro' => $newId], 'Book created successfully.');
        break;

    // ── PUT ──────────────────────────────────────────────────────────────────
    case 'PUT':
        $body = json_decode(file_get_contents('php://input'), true);
        $id       = (int) ($body['id_libro']  ?? 0);
        $title    = trim($body['libro']       ?? '');
        $authorId = (int) ($body['id_autor']  ?? 0);

        if ($id <= 0 || $title === '' || $authorId <= 0) {
            sendError(400, 'Fields "id_libro", "libro", and "id_autor" are required.');
        }

        $stmt = $conn->prepare('UPDATE libros SET libro = ?, id_autor = ? WHERE id_libro = ?');
        $stmt->bind_param('sii', $title, $authorId, $id);

        if (!$stmt->execute()) {
            error_log('[DB] UPDATE libros: ' . $stmt->error);
            sendError(500, 'Failed to update book.');
        }

        if ($stmt->affected_rows === 0) {
            sendError(404, 'Book not found or no changes made.');
        }

        $stmt->close();
        sendSuccess(null, 'Book updated successfully.');
        break;

    // ── DELETE ───────────────────────────────────────────────────────────────
    case 'DELETE':
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            sendError(400, 'A valid book ID is required.');
        }

        $stmt = $conn->prepare('DELETE FROM libros WHERE id_libro = ?');
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            error_log('[DB] DELETE libros: ' . $stmt->error);
            sendError(500, 'Failed to delete book.');
        }

        if ($stmt->affected_rows === 0) {
            sendError(404, 'Book not found.');
        }

        $stmt->close();
        sendSuccess(null, 'Book deleted successfully.');
        break;

    default:
        sendError(405, 'Method not allowed.');
}

$conn->close();
