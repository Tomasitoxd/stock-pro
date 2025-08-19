<?php
include 'db.php';      // Debe exponer $conexion (mysqli)

header('Content-Type: application/json; charset=utf-8');

// --------------------------------------------------------------------
// ¿PIDIERON UN PRODUCTO CONCRETO?  ej:  fetch_stock.php?id=5
// --------------------------------------------------------------------
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Prepared statement  → evita inyección SQL
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $res = $stmt->get_result();
    echo json_encode($res->fetch_assoc() ?: []);   // Si no existe, devuelve {}
    exit;                                          // <-- Importante
}

// --------------------------------------------------------------------
// SIN id → DEVUELVE TODA LA TABLA
// --------------------------------------------------------------------
$resultado = $conexion->query("SELECT * FROM productos");
$productos = [];

while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}

echo json_encode($productos);
?>

