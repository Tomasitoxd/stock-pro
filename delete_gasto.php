<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

// 🔹 1. Validar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado (sin sesión)']);
    exit;
}

// 🔹 2. Recibir datos
$id = intval($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

if ($id <= 0 || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// 🔹 3. Verificar contraseña del usuario actual
$sql = "SELECT clave FROM empleados WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    exit;
}

$user = $res->fetch_assoc();
if (!password_verify($password, $user['clave'])) {
    echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
    exit;
}

// 🔹 4. Eliminar gasto
$sqlDel = "DELETE FROM gastos WHERE id = ?";
$stmtDel = $conexion->prepare($sqlDel);
if (!$stmtDel) {
    echo json_encode(['success' => false, 'error' => 'Error en prepare: ' . $conexion->error]);
    exit;
}
$stmtDel->bind_param("i", $id);
$ok = $stmtDel->execute();

// 🔹 5. Respuesta final
if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmtDel->error]);
}
