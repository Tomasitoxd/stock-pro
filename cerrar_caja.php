<?php
include 'db.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaHoy = date('Y-m-d');

// 🔹 Verificar si ya se cerró hoy
$check = $conexion->prepare("SELECT id FROM cierres_caja WHERE fecha = ?");
$check->bind_param("s", $fechaHoy);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "El cierre de caja ya fue realizado hoy."]);
    exit;
}
$check->close();

// 🔹 Calcular totales del día completo
$stmtPagos = $conexion->prepare("
  SELECT SUM(monto) AS total_pagos 
  FROM pagos 
  WHERE DATE(fecha) = ? AND anulado = 0
");
$stmtPagos->bind_param("s", $fechaHoy);
$stmtPagos->execute();
$totalPagos = $stmtPagos->get_result()->fetch_assoc()['total_pagos'] ?? 0;

$stmtGastos = $conexion->prepare("
  SELECT SUM(monto) AS total_gastos 
  FROM gastos 
  WHERE DATE(fecha) = ?
");
$stmtGastos->bind_param("s", $fechaHoy);
$stmtGastos->execute();
$totalGastos = $stmtGastos->get_result()->fetch_assoc()['total_gastos'] ?? 0;

// 🔹 Calcular saldo
$total = $totalPagos - $totalGastos;

// 🔹 Si no hubo movimientos, no cerrar
if ($totalPagos == 0 && $totalGastos == 0) {
    echo json_encode(["success" => false, "error" => "No hay movimientos para cerrar hoy."]);
    exit;
}

// 🔹 Insertar cierre
$horaAhora = date('H:i:s');
$insert = $conexion->prepare("INSERT INTO cierres_caja (fecha, hora, total, total_pagos, total_gastos) VALUES (?, ?, ?, ?, ?)");
$insert->bind_param("ssddd", $fechaHoy, $horaAhora, $total, $totalPagos, $totalGastos);

if ($insert->execute()) {
    echo json_encode([
        "success" => true,
        "total" => $total,
        "pagos" => $totalPagos,
        "gastos" => $totalGastos
    ]);
} else {
    echo json_encode(["success" => false, "error" => $conexion->error]);
}
?>
