<?php
include 'db.php';

$resultado = $conexion->query("SELECT * FROM cierres_caja ORDER BY id DESC");
$cierres = [];

while ($fila = $resultado->fetch_assoc()) {
    $cierres[] = $fila;
}

echo json_encode($cierres);
?>
