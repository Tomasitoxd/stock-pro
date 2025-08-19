<?php
header('Content-Type: application/json');
require 'db.php'; // asegúrate que aquí se crea $conexion

$concepto = $_POST['concepto'] ?? '';
$monto = floatval($_POST['monto'] ?? 0);
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$hora = date('H:i:s');

if ($concepto === '' || $monto <= 0) {
    echo json_encode(['success'=>false,'error'=>'Datos inválidos']);
    exit;
}

$sql = "INSERT INTO gastos (concepto, monto, fecha, hora) VALUES (?,?,?,?)";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode(['success'=>false,'error'=>'Error en prepare: '.$conexion->error]);
    exit;
}

$stmt->bind_param("sdss", $concepto, $monto, $fecha, $hora);
$ok = $stmt->execute();

echo json_encode(['success'=>$ok, 'error'=>$ok ? '' : $stmt->error]);
