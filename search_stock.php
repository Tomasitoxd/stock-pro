<?php
include 'db.php';

$q = $_GET['q'];
$q = $conexion->real_escape_string($q);

$resultado = $conexion->query("SELECT * FROM productos WHERE nombre LIKE '%$q%' OR id LIKE '%$q%'");

$productos = [];

while ($fila = $resultado->fetch_assoc()) {
  $productos[] = $fila;
}

echo json_encode($productos);
?>
