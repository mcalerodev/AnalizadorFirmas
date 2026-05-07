<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===============================
// CARGAR DEPENDENCIAS
// ===============================
require_once __DIR__ . '/src/conexion.php';
require_once __DIR__ . '/src/StorageManager.php';
require_once __DIR__ . '/src/ArchivoRepository.php';

// ===============================
// INSTANCIAS
// ===============================
$storage = new StorageManager();
$repo = new ArchivoRepository($conexion);

// ===============================
//  ELIMINAR ARCHIVO (PRUEBA)
// ===============================
if (isset($_GET['eliminar'])) {
    try {
        $repo->eliminar($_GET['eliminar']);
        echo "Archivo eliminado correctamente";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// ===============================
// SUBIR ARCHIVO
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // 1. GUARDAR ARCHIVO EN DISCO
        $ruta = $storage->guardarArchivo($_FILES['archivo']);

        // 2. CALCULAR HASH
        $hash = md5_file($ruta);

        // 3. PREPARAR DATOS
        $datos = [
            'nombre_original' => $_FILES['archivo']['name'],
            'tipo_detectado' => 'DESCONOCIDO',
            'hash_md5' => $hash,
            'tamaño' => $_FILES['archivo']['size'],
            'usuario_id' => null,
            'ruta' => $ruta
        ];

        // 4. GUARDAR EN BD
        $repo->guardar($datos);

        echo "Archivo guardado en la DB";

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!-- ===============================
FORMULARIO DE SUBIDA
================================ -->
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="archivo" required>
    <button type="submit">Subir archivo</button>
</form>