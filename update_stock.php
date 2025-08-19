<?php
// update_stock.php
// ---------------------------------------------------
// 1) CONEXIÓN
require 'db.php';        // Debe exponer $pdo (PDO)

// 2) ENTRADAS (POST + FILES)
$id          = (int) ($_POST['id']           ?? 0);
$nombre      = trim($_POST['nombre']        ?? '');
$cantidad    = (int) ($_POST['cantidad']    ?? 0);
$descripcion = trim($_POST['descripcion']   ?? '');
$codigo      = trim($_POST['codigo_barras'] ?? '');

// Validación básica
if (!$id || $nombre === '' || $codigo === '') {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
    exit;
}

// 3) ¿VIENE UNA NUEVA FOTO?
$nuevaFoto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && $_FILES['foto']['size'] > 0) {
    // Elegí tu carpeta de uploads (creá la carpeta con permisos 755)
    $dirUploads = 'img/';
    if (!is_dir($dirUploads)) mkdir($dirUploads, 0755, true);

    $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $file = uniqid('prod_') . '.' . $ext;
    $dest = $dirUploads . $file;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
        $nuevaFoto = $dest;  // Guardamos la ruta relativa
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo mover la imagen']);
        exit;
    }
}

try {
    // 4) QUERY
    if ($nuevaFoto) {
        $sql = "UPDATE productos
                   SET nombre = :nombre,
                       cantidad = :cantidad,
                       descripcion = :descripcion,
                       codigo_barras = :codigo,
                       foto = :foto
                 WHERE id = :id";
        $params = [
            ':nombre'      => $nombre,
            ':cantidad'    => $cantidad,
            ':descripcion' => $descripcion,
            ':codigo'      => $codigo,
            ':foto'        => $nuevaFoto,
            ':id'          => $id
        ];
    } else {
        // Sin cambiar la imagen
        $sql = "UPDATE productos
                   SET nombre = :nombre,
                       cantidad = :cantidad,
                       descripcion = :descripcion,
                       codigo_barras = :codigo
                 WHERE id = :id";
        $params = [
            ':nombre'      => $nombre,
            ':cantidad'    => $cantidad,
            ':descripcion' => $descripcion,
            ':codigo'      => $codigo,
            ':id'          => $id
        ];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
