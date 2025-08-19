<?php
require 'db.php';
header('Content-Type: application/json');

$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin    = $_GET['fecha_fin'] ?? null;

try {
    if ($fecha_inicio && $fecha_fin) {
        // Rango de fechas
        $stmt = $pdo->prepare("
            SELECT * 
            FROM pagos 
            WHERE fecha BETWEEN ? AND ? 
              AND anulado = 0
            ORDER BY fecha DESC, id DESC
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);

    } elseif ($fecha_inicio) {
        // Solo una fecha
        $stmt = $pdo->prepare("
            SELECT * 
            FROM pagos 
            WHERE fecha = ? 
              AND anulado = 0
            ORDER BY id DESC
        ");
        $stmt->execute([$fecha_inicio]);

    } else {
        // Todos los registros
        $stmt = $pdo->query("
            SELECT * 
            FROM pagos 
            WHERE anulado = 0
            ORDER BY fecha DESC, id DESC
        ");
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
}
?>
