<?php
header('Content-Type: application/json');
require 'db.php'; // aquí debe crearse $conexion (mysqli)

$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;

if ($fecha_inicio && $fecha_fin) {
    // Rango de fechas
    $stmt = $conexion->prepare("
        SELECT id, concepto, monto, fecha, hora 
        FROM gastos 
        WHERE fecha BETWEEN ? AND ? 
        ORDER BY id DESC
    ");
    if (!$stmt) {
        echo json_encode(['error' => 'Error en prepare: ' . $conexion->error]);
        exit;
    }
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $res = $stmt->get_result();
} elseif ($fecha_inicio) {
    // Solo una fecha
    $stmt = $conexion->prepare("
        SELECT id, concepto, monto, fecha, hora 
        FROM gastos 
        WHERE fecha = ? 
        ORDER BY id DESC
    ");
    if (!$stmt) {
        echo json_encode(['error' => 'Error en prepare: ' . $conexion->error]);
        exit;
    }
    $stmt->bind_param("s", $fecha_inicio);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    // Sin filtro de fecha
    $res = $conexion->query("
        SELECT id, concepto, monto, fecha, hora 
        FROM gastos 
        ORDER BY id DESC
    ");
    if (!$res) {
        echo json_encode(['error' => 'Error en query: ' . $conexion->error]);
        exit;
    }
}

// Convertir a array
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

echo json_encode($rows);
?>
