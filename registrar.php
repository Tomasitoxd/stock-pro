<?php
include 'db.php';

$usuario = $_POST['usuario'];
$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
$nombre = $_POST['nombre_completo'];

$sql = "INSERT INTO empleados (usuario, clave, nombre_completo) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $usuario, $clave, $nombre);

if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "mensaje" => "Ese usuario ya existe"]);
}
?>
