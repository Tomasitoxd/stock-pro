<?php
header('Content-Type: application/json');
require 'db.php'; // Usa $conexion (mysqli)

// Parámetros recibidos
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin    = $_GET['fecha_fin'] ?? null;

if (!$fecha_inicio || !$fecha_fin) {
    // Si no hay fechas, buscar el último y penúltimo cierre
    $sql = "SELECT fecha, hora FROM cierres_caja ORDER BY fecha DESC, hora DESC LIMIT 2";
    $cierres = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);

    if (count($cierres) == 0) {
        echo json_encode([
            'ingresos' => 0,
            'gastos'   => 0,
            'neto'     => 0,
            'error'    => 'No hay cierres de caja registrados.'
        ]);
        exit;
    }

    // Último cierre
    $fecha_inicio = $cierres[0]['fecha'];
    $hora_inicio  = $cierres[0]['hora'];

    // Penúltimo cierre o inicio del día si no hay anterior
    if (isset($cierres[1])) {
        $fecha_fin = $cierres[1]['fecha'];
        $hora_fin  = $cierres[1]['hora'];
    } else {
        $fecha_fin = $fecha_inicio;
        $hora_fin  = '23:59:59';
    }

    // Ajustar para rango correcto (del penúltimo al último)
    // Si tienes lógica inversa en cierres, intercambia fechas
    if (strtotime("$fecha_inicio $hora_inicio") > strtotime("$fecha_fin $hora_fin")) {
        $tmpFecha = $fecha_inicio;
        $tmpHora  = $hora_inicio;
        $fecha_inicio = $fecha_fin;
        $hora_inicio  = $hora_fin;
        $fecha_fin    = $tmpFecha;
        $hora_fin     = $tmpHora;
    }
} else {
    // Si hay fechas, se usa todo el día completo
    $hora_inicio = '00:00:00';
    $hora_fin    = '23:59:59';
}

// Consultar ingresos en rango
$stmt_ingresos = $conexion->prepare("
    SELECT SUM(monto) AS total_ingresos 
    FROM pagos 
    WHERE anulado = 0
      AND (fecha > ? OR (fecha = ? AND hora >= ?))
      AND (fecha < ? OR (fecha = ? AND hora <= ?))
");
$stmt_ingresos->bind_param("ssssss", $fecha_inicio, $fecha_inicio, $hora_inicio, $fecha_fin, $fecha_fin, $hora_fin);
$stmt_ingresos->execute();
$total_ingresos = $stmt_ingresos->get_result()->fetch_assoc()['total_ingresos'] ?? 0;

// Consultar gastos en rango
$stmt_gastos = $conexion->prepare("
    SELECT SUM(monto) AS total_gastos 
    FROM gastos 
    WHERE (fecha > ? OR (fecha = ? AND hora >= ?))
      AND (fecha < ? OR (fecha = ? AND hora <= ?))
");
$stmt_gastos->bind_param("ssssss", $fecha_inicio, $fecha_inicio, $hora_inicio, $fecha_fin, $fecha_fin, $hora_fin);
$stmt_gastos->execute();
$total_gastos = $stmt_gastos->get_result()->fetch_assoc()['total_gastos'] ?? 0;

// Calcular neto
$neto = ($total_ingresos ?: 0) - ($total_gastos ?: 0);

// Respuesta JSON
echo json_encode([
    'ingresos' => floatval($total_ingresos),
    'gastos'   => floatval($total_gastos),
    'neto'     => floatval($neto),
    'desde'    => "$fecha_inicio $hora_inicio",
    'hasta'    => "$fecha_fin $hora_fin"
]);
?>
