<?php
include 'db.php';

$q = $conexion->real_escape_string($_GET['q']);
$resultado = $conexion->query("
  SELECT * FROM pagos 
  WHERE nombre_cliente LIKE '%$q%' OR concepto LIKE '%$q%' 
  ORDER BY fecha DESC
");

$pagos = [];

while ($fila = $resultado->fetch_assoc()) {
  $pagos[] = $fila;
}

echo json_encode($pagos);
?>
