<?php
include 'db.php';

$nombre = $conexion->real_escape_string($_POST['nombre']);
$cantidad = (int) $_POST['cantidad'];
$descripcion = $conexion->real_escape_string($_POST['descripcion']);
$codigo_barras = $conexion->real_escape_string($_POST['codigo_barras']);

$fotoNombre = $_FILES['foto']['name'];
$fotoTmp = $_FILES['foto']['tmp_name'];
$rutaDestino = 'fotos/' . uniqid() . '_' . basename($fotoNombre);

if (!move_uploaded_file($fotoTmp, $rutaDestino)) {
    http_response_code(500);
    echo json_encode(["error" => "Error al subir la foto"]);
    exit;
}

$sql = "INSERT INTO productos (nombre, cantidad, descripcion, codigo_barras, foto)
        VALUES ('$nombre', $cantidad, '$descripcion', '$codigo_barras', '$rutaDestino')";

if ($conexion->query($sql)) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => $conexion->error]);
}
?>
