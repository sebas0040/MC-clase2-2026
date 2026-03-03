<?php
header("Content-Type: application/json; charset=UTF-8");
include("../config/conectdb.php");

/*
|--------------------------------------------------------------------------
| RESPUESTA UNIFORME
|--------------------------------------------------------------------------
*/
function response($success, $message = "", $data = null) {
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| OBTENER ACCIÓN
|--------------------------------------------------------------------------
*/
$action = $_GET['action'] ?? '';

if (!$action) {
    response(false, "No se especificó ninguna acción");
}

/*
|--------------------------------------------------------------------------
| ROUTER PRINCIPAL
|--------------------------------------------------------------------------
*/
switch ($action) {

    case 'add':
        agregar($conn);
        break;

    case 'edit':
        editar($conn);
        break;

    case 'delete':
        eliminar($conn);
        break;

    case 'search':
        buscar($conn);
        break;

    default:
        response(false, "Acción no válida");
}

/*
|--------------------------------------------------------------------------
| FUNCIONES
|--------------------------------------------------------------------------
*/

function agregar($conn) {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(false, "Método no permitido");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['libro']) || empty($data['autor'])) {
        response(false, "Todos los campos son obligatorios");
    }

    $libro = trim($data['libro']);
    $autor = trim($data['autor']);

    if ($libro === "" || $autor === "") {
        response(false, "Datos inválidos");
    }

    $stmt = $conn->prepare("INSERT INTO libros (libro, autor) VALUES (?, ?)");
    $stmt->bind_param("ss", $libro, $autor);

    if ($stmt->execute()) {
        response(true, "Libro agregado correctamente", [
            "id_libro" => $stmt->insert_id,
            "libro" => $libro,
            "autor" => $autor
        ]);
    }

    response(false, "Error al agregar libro");
}

function editar($conn) {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(false, "Método no permitido");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id_libro']) || empty($data['libro']) || empty($data['autor'])) {
        response(false, "Datos incompletos");
    }

    $id = intval($data['id_libro']);
    $libro = trim($data['libro']);
    $autor = trim($data['autor']);

    if ($id <= 0 || $libro === "" || $autor === "") {
        response(false, "Datos inválidos");
    }

    $stmt = $conn->prepare("UPDATE libros SET libro = ?, autor = ? WHERE id_libro = ?");
    $stmt->bind_param("ssi", $libro, $autor, $id);

    if ($stmt->execute()) {
        response(true, "Libro actualizado correctamente");
    }

    response(false, "Error al actualizar libro");
}

function eliminar($conn) {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(false, "Método no permitido");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id_libro'])) {
        response(false, "ID no recibido");
    }

    $id = intval($data['id_libro']);

    if ($id <= 0) {
        response(false, "ID inválido");
    }

    $stmt = $conn->prepare("DELETE FROM libros WHERE id_libro = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        response(true, "Libro eliminado correctamente");
    }

    response(false, "Error al eliminar libro");
}

function buscar($conn) {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        response(false, "Método no permitido");
    }

    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";

    $sql = "SELECT * FROM libros";
    $params = [];
    $types = "";

    if ($buscar !== "") {
        $sql .= " WHERE libro LIKE ? OR autor LIKE ? OR id_libro LIKE ?";
        $buscarLike = "%" . $buscar . "%";
        $params = [$buscarLike, $buscarLike, $buscarLike];
        $types = "sss";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $libros = [];

    while ($row = $result->fetch_assoc()) {
        $libros[] = $row;
    }

    response(true, "Resultados obtenidos", $libros);
}