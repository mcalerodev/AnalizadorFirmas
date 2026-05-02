<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CARGAR DEPENDENCIAS
require_once __DIR__ . '/src/conexion.php';
require_once __DIR__ . '/src/StorageManager.php';
require_once __DIR__ . '/src/ArchivoRepository.php';

// INSTANCIAS
$storage = new StorageManager();        
$repo = new ArchivoRepository($conexion); 

// CUANDO SE ENVÍA EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // 1. GUARDAR ARCHIVO
        $ruta = $storage->guardarArchivo($_FILES['archivo']);

        // 2. ANALIZAR CON EXE
        $output = shell_exec(__DIR__ . "/motor_firmas.exe " . escapeshellarg($ruta));
        $tipoCodigo = intval($output);

        // 3. MAPEAR NOMBRE (SIN ?? porque PHP 5.6)
        $tipos = array(
            1 => "JPEG",
            2 => "PNG",
            3 => "PDF",
            4 => "ZIP",
            5 => "GIF",
            6 => "BMP",
            7 => "EXE",
            8 => "ELF",
            9 => "MP3"
        );

        if (isset($tipos[$tipoCodigo])) {
            $tipoNombre = $tipos[$tipoCodigo];
        } else {
            $tipoNombre = "DESCONOCIDO";
        }

        // 4. HASH
        $hash = md5_file($ruta);

        // 5. DATOS BD
        $datos = array(
            'nombre_original' => $_FILES['archivo']['name'],
            'tipo_detectado' => $tipoNombre,
            'hash_md5' => $hash,
            'tamaño' => $_FILES['archivo']['size'],
            'usuario_id' => null
        );

        // 6. GUARDAR
        $repo->guardar($datos);

        echo "Archivo guardado en la DB. Tipo detectado: " . $tipoNombre;

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="archivo" required>
    <button type="submit">Subir archivo</button>
</form>