<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CARGAR DEPENDENCIAS
require_once __DIR__ . '/src/conexion.php';           // Conexión a la BD
require_once __DIR__ . '/src/StorageManager.php';     // Manejo de archivos físicos
require_once __DIR__ . '/src/ArchivoRepository.php';  // CRUD de BD


// INSTANCIAS
$storage = new StorageManager();        // Maneja guardado de archivos
$repo = new ArchivoRepository($conexion); // Maneja base de datos



// CUANDO SE ENVÍA EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // 1. GUARDAR ARCHIVO EN DISCO
        // Guarda el archivo en /uploads y devuelve la ruta
        $ruta = $storage->guardarArchivo($_FILES['archivo']);

        // 2. CALCULAR HASH 
        // Sirve para identificar archivos únicos
        $hash = md5_file($ruta);

        // 3. PREPARAR DATOS PARA BD
        $datos = [
            'nombre_original' => $_FILES['archivo']['name'], // nombre original
            'tipo_detectado' => 'DESCONOCIDO',              // luego se llenará*
            'hash_md5' => $hash,                            // hash del archivo
            'tamaño' => $_FILES['archivo']['size'],         // tamaño en bytes
            'usuario_id' => null                            // luego será login**
        ];

        // 4. GUARDAR EN BASE DE DATOS
        $repo->guardar($datos);
        echo "Archivo guardadó en la DB";

    } catch (Exception $e) {

        // MANEJO DE ERRORES
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