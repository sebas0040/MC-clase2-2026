<?php 
include("config/conectdb.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Libros</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h2>📚 Lista de Libros</h2>

    <!-- BUSCAR -->
    <form id="searchForm" class="search-form">
        <input type="text" id="buscarInput" placeholder="Buscar por código, libro o autor...">
        <button type="submit">Buscar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Libro</th>
                <th>Autor</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody id="tablaLibros">
        </tbody>
    </table>

    <button class="btn-add" id="addBtn">+ Agregar Libro</button>
</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modal-title"></h3>

        <input type="text" id="libroInput" placeholder="Libro">
        <input type="text" id="autorInput" placeholder="Autor">

        <div class="modal-buttons">
            <button class="cancel-btn">Cancelar</button>
            <button class="confirm-btn" id="deleteConfirm">Eliminar</button>
            <button class="save-btn" id="saveBtn">Guardar</button>
        </div>
    </div>
</div>

<!-- IMPORTANTE: conectar JS externo -->
<script src="js/app.js"></script>

</body>
</html>