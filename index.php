<?php 
include("config/conectdb.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Libros</title>
<link rel="stylesheet" href="styles.css">
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
        <!-- Se llena dinámicamente -->
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

<script>
const tabla = document.getElementById("tablaLibros");
const modal = document.getElementById("modal");
const modalTitle = document.getElementById("modal-title");
const libroInput = document.getElementById("libroInput");
const autorInput = document.getElementById("autorInput");
const saveBtn = document.getElementById("saveBtn");
const deleteConfirm = document.getElementById("deleteConfirm");

let currentId = null;
let currentAction = null;

/* =========================
   FUNCIÓN PARA CARGAR LIBROS
========================= */
async function cargarLibros(busqueda = "") {
    const response = await fetch(`api/api.php?action=search&buscar=${encodeURIComponent(busqueda)}`);
    const data = await response.json();

    tabla.innerHTML = "";

    if(data.success && data.data.length > 0){
        data.data.forEach(libro => {
            tabla.innerHTML += `
                <tr>
                    <td>${libro.id_libro}</td>
                    <td>${libro.libro}</td>
                    <td>${libro.autor}</td>
                    <td><button onclick="abrirEditar(${libro.id_libro}, '${libro.libro}', '${libro.autor}')">✏️</button></td>
                    <td><button onclick="abrirEliminar(${libro.id_libro})">🗑️</button></td>
                </tr>
            `;
        });
    } else {
        tabla.innerHTML = "<tr><td colspan='5'>No hay resultados</td></tr>";
    }
}

/* =========================
   FETCH AGREGAR
========================= */
async function agregarLibro(libro, autor){
    const response = await fetch("api/api.php?action=add", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({libro, autor})
    });

    const data = await response.json();
    alert(data.message);

    if(data.success){
        cerrarModal();
        cargarLibros();
    }
}

/* =========================
   FETCH EDITAR
========================= */
async function editarLibro(id, libro, autor){
    const response = await fetch("api/api.php?action=edit", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id_libro:id, libro, autor})
    });

    const data = await response.json();
    alert(data.message);

    if(data.success){
        cerrarModal();
        cargarLibros();
    }
}

/* =========================
   FETCH ELIMINAR
========================= */
async function eliminarLibro(id){
    const response = await fetch("api/api.php?action=delete", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id_libro:id})
    });

    const data = await response.json();
    alert(data.message);

    if(data.success){
        cerrarModal();
        cargarLibros();
    }
}

/* =========================
   BUSCAR
========================= */
document.getElementById("searchForm").addEventListener("submit", e => {
    e.preventDefault();
    const texto = document.getElementById("buscarInput").value;
    cargarLibros(texto);
});

/* =========================
   CONTROL MODAL
========================= */
document.getElementById("addBtn").addEventListener("click", () => {
    currentAction = "add";
    modalTitle.textContent = "Agregar Libro";
    libroInput.style.display = "block";
    autorInput.style.display = "block";
    deleteConfirm.style.display = "none";
    saveBtn.style.display = "inline-block";
    libroInput.value = "";
    autorInput.value = "";
    modal.style.display = "block";
});

function abrirEditar(id, libro, autor){
    currentAction = "edit";
    currentId = id;
    modalTitle.textContent = "Editar Libro";
    libroInput.value = libro;
    autorInput.value = autor;
    libroInput.style.display = "block";
    autorInput.style.display = "block";
    deleteConfirm.style.display = "none";
    saveBtn.style.display = "inline-block";
    modal.style.display = "block";
}

function abrirEliminar(id){
    currentAction = "delete";
    currentId = id;
    modalTitle.textContent = "Eliminar Libro";
    libroInput.style.display = "none";
    autorInput.style.display = "none";
    saveBtn.style.display = "none";
    deleteConfirm.style.display = "inline-block";
    modal.style.display = "block";
}

saveBtn.addEventListener("click", () => {
    const libro = libroInput.value.trim();
    const autor = autorInput.value.trim();

    if(!libro || !autor){
        alert("Complete todos los campos");
        return;
    }

    if(currentAction === "add"){
        agregarLibro(libro, autor);
    }

    if(currentAction === "edit"){
        editarLibro(currentId, libro, autor);
    }
});

deleteConfirm.addEventListener("click", () => {
    eliminarLibro(currentId);
});

function cerrarModal(){
    modal.style.display = "none";
}

document.querySelector(".close").onclick = cerrarModal;
document.querySelector(".cancel-btn").onclick = cerrarModal;
window.onclick = e => { if(e.target == modal) cerrarModal(); };

/* =========================
   CARGA INICIAL
========================= */
cargarLibros();

</script>

</body>
</html>