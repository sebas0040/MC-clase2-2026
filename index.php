<?php 
include("config/conectdb.php");

$sql = "SELECT * FROM libros";
$result = $conn->query($sql);
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

    <form method="GET" action="" class="search-form">
        <input type="text" name="buscar" placeholder="Buscar por código, libro o autor..."
            value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
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
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr data-id='{$row['id_libro']}' data-libro='{$row['libro']}' data-autor='{$row['autor']}'>";
                echo "<td>" . $row['id_libro'] . "</td>";
                echo "<td>" . $row['libro'] . "</td>";
                echo "<td>" . $row['autor'] . "</td>";
                echo "<td><button class='icon edit-btn'><img src='img/lapiz.png' class='icon'></button></td>";
                echo "<td><button class='icon delete-btn'><img src='img/bote.png' class='icon'></button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No hay registros</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <button class="btn-add" id="addBtn">+ Agregar Libro</button>
</div>

<!-- Modal Reutilizable -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modal-title">Modal</h3>
        <div id="modal-body">
            <input type="text" id="libroInput" placeholder="Libro">
            <input type="text" id="autorInput" placeholder="Autor">
        </div>
        <div class="modal-buttons">
            <button class="cancel-btn">Cancelar</button>
            <a href="#" class="confirm-btn">Eliminar</a>
            <button class="save-btn">Guardar</button>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modal-title');
const libroInput = document.getElementById('libroInput');
const autorInput = document.getElementById('autorInput');
const cancelBtn = modal.querySelector('.cancel-btn');
const saveBtn = modal.querySelector('.save-btn');
const confirmBtn = modal.querySelector('.confirm-btn');
const closeBtn = modal.querySelector('.close');

function openModal(type, row = null) {
    modal.style.display = 'block';
    libroInput.style.display = autorInput.style.display = 'block';
    saveBtn.style.display = confirmBtn.style.display = 'none';

    if(type === 'add') {
        modalTitle.textContent = 'Agregar Libro';
        libroInput.value = '';
        autorInput.value = '';
        saveBtn.style.display = 'inline-block';
    }
    if(type === 'edit') {
        modalTitle.textContent = 'Editar Libro';
        libroInput.value = row.dataset.libro;
        autorInput.value = row.dataset.autor;
        saveBtn.style.display = 'inline-block';
    }
    if(type === 'delete') {
        modalTitle.textContent = 'Eliminar Libro';
        libroInput.style.display = autorInput.style.display = 'none';
        confirmBtn.style.display = 'inline-block';
        confirmBtn.href = `eliminar.php?id_libro=${row.dataset.id}`;
    }
}

// Eventos
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => openModal('edit', btn.closest('tr')));
});
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => openModal('delete', btn.closest('tr')));
});
document.getElementById('addBtn').addEventListener('click', () => openModal('add'));

cancelBtn.addEventListener('click', () => modal.style.display = 'none');
closeBtn.addEventListener('click', () => modal.style.display = 'none');
window.addEventListener('click', e => { if(e.target == modal) modal.style.display='none'; });

saveBtn.addEventListener('click', () => {
    const libro = libroInput.value.trim();
    const autor = autorInput.value.trim();
    if(libro && autor) {
        alert(`Aquí se guardaría el libro: ${libro}, autor: ${autor}`);
        modal.style.display = 'none';
        // Aquí puedes hacer un POST usando fetch/AJAX para guardar en DB
    } else {
        alert('Complete ambos campos');
    }
});
</script>

</body>
</html>