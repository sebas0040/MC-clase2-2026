<?php

$host = "localhost";
$user = "Galel";
$password = "Sebas#12";  
$database = "uden_db_clase2"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


$conn->set_charset("utf8");

?>
