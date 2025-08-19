<?php
session_start();
include 'db.php';

$usuario = $_POST['usuario'] ?? '';
$clave   = $_POST['clave'] ?? '';

$sql = "SELECT * FROM empleados WHERE usuario = ?";
$stmt = $conexion->prepare($sql);  // 🔹 USAMOS $conexion
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $empleado = $result->fetch_assoc();
  if (password_verify($clave, $empleado['clave'])) {
    $_SESSION['usuario'] = $empleado['usuario'];
    $_SESSION['nombre']  = $empleado['nombre_completo'];
    echo json_encode(["success" => true, "nombre" => $empleado['nombre_completo']]);
  } else {
    echo json_encode(["success" => false, "mensaje" => "Contraseña incorrecta"]);
  }
} else {
  echo json_encode(["success" => false, "mensaje" => "Usuario no encontrado"]);
}
?>
