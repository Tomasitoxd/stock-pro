<?php
require 'db.php';

$id       = $_POST['id']        ?? 0;
$password = $_POST['password'] ?? '';

$clave_correcta = '1234';          // cámbiala si querés

if ($password !== $clave_correcta) {
    echo json_encode(["success"=>false,"error"=>"Contraseña incorrecta"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE pagos SET anulado = 1 WHERE id = ?");
$ok   = $stmt->execute([$id]);

echo json_encode(["success"=>$ok]);
?>
