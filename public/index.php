<?php

session_start();

/**
 * test.php — Interfaz de prueba (formulario web)
 *
 * Usa los mismos Singletons y clases que la API REST.
 * Ya NO usa shell_exec ni motor_firmas.exe — ahora llama
 * directamente a la DLL a través de MotorFirmas (FFI).
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar dependencias
require_once __DIR__ . '/../src/Database/Conexion.php';
require_once __DIR__ . '/../src/Service/MotorFirmas.php';
require_once __DIR__ . '/../src/Storage/StorageManager.php';
require_once __DIR__ . '/../src/Repository/ArchivoRepository.php';

$resultado = null;
$error     = null;
$rutaArchivo = null;

$conexion = Conexion::getInstance();
$repo = new ArchivoRepository($conexion);

// ELIMINAR ARCHIVO
if(isset($_GET['eliminar'])){

    $repo->eliminar($_GET['eliminar']);

    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (!isset($_FILES['archivo'])) {
            throw new Exception("Debe seleccionar un archivo");
        }

        // 1. Guardar archivo con validación (StorageManager)
        $storage     = new StorageManager();
        $rutaArchivo = $storage->guardarArchivo($_FILES['archivo']);

        // 2. Analizar con la DLL via FFI — Singleton (DLL se carga una sola vez)
        $motor      = MotorFirmas::getInstance();
        $tipoCodigo = $motor->analizarArchivo($rutaArchivo);
        $tipoNombre = $motor->obtenerNombreTipo($tipoCodigo);

        // 3. Hash MD5
        $hash = md5_file($rutaArchivo);

        // 4. Guardar en BD — Singleton (una sola instancia PDO)
        $repo->guardar([
            'nombre_original' => $_FILES['archivo']['name'],
            'tipo_detectado'  => $tipoNombre,
            'hash_md5'        => $hash,
            'tamaño'          => $_FILES['archivo']['size'],
            'usuario_id'      => $_SESSION['usuario_id'] ?? null,
            'ruta'            => $rutaArchivo
        ]);

        $resultado = [
            'nombre'  => $_FILES['archivo']['name'],
            'tipo'    => $tipoNombre,
            'codigo'  => $tipoCodigo,
            'hash'    => $hash,
            'tamaño'  => number_format($_FILES['archivo']['size'] / 1024, 2) . ' KB'
        ];

    } catch (Exception $e) {

        $error = $e->getMessage();

    }
}

// Historial
$archivos = $repo->obtenerTodos();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Analizador de Archivos</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
   
</head>

<body>

<div class="container">

    <div class="top-bar">

        <h1> Analizador de Archivos</h1>

        <a href="logout.php" class="logout">
            Cerrar sesión
        </a>

    </div>

    <div class="upload-card">

        <form method="POST" enctype="multipart/form-data" id="uploadForm">

            <div class="drop-area" id="dropArea">

                <p><strong>Arrastra y suelta un archivo aquí</strong></p>

                <p>o selecciona un archivo manualmente</p>

                <input type="file" name="archivo" id="fileInput" required>

            </div>

            <button type="submit">
                Analizar archivo
            </button>

            <div class="progress-container" id="progressContainer">

                <div class="progress-bar" id="progressBar">
                    0%
                </div>

            </div>

        </form>

        <?php if ($resultado): ?>

            <?php

                $extension = pathinfo($resultado['nombre'], PATHINFO_EXTENSION);

                $colorFondo = "#f5f5f5";

                switch(strtolower($extension)){

                    case 'pdf':
                        $colorFondo = "#ffebee";
                    break;

                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                        $colorFondo = "#e3f2fd";
                    break;

                    case 'doc':
                    case 'docx':
                        $colorFondo = "#e8f5e9";
                    break;

                    case 'zip':
                    case 'rar':
                        $colorFondo = "#fff8e1";
                    break;
                }

            ?>

            <div class="resultado" style="background:<?= $colorFondo ?>;">

                <h2>Resultado del análisis</h2>

                <table>

                    <tr>
                        <th>Dato</th>
                        <th>Información</th>
                    </tr>

                    <tr>
                        <td>Nombre del archivo</td>
                        <td><?= htmlspecialchars($resultado['nombre']) ?></td>
                    </tr>

                    <tr>
                        <td>Tipo detectado</td>
                        <td>
                            <strong><?= htmlspecialchars($resultado['tipo']) ?></strong>
                            (Código <?= $resultado['codigo'] ?>)
                        </td>
                    </tr>

                    <tr>
                        <td>Extensión detectada</td>
                        <td>.<?= $extension ?></td>
                    </tr>

                    <tr>
                        <td>Hora de análisis</td>
                        <td><?= date("H:i:s") ?></td>
                    </tr>

                    <tr>
                        <td>Hash MD5</td>
                        <td><?= $resultado['hash'] ?></td>
                    </tr>

                    <tr>
                        <td>Tamaño</td>
                        <td><?= $resultado['tamaño'] ?></td>
                    </tr>

                </table>

                <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">

                    <!-- Descargar -->
                    <a href="<?= $rutaArchivo ?>" download="<?= htmlspecialchars($resultado['nombre']) ?>">
                        <button type="button">Descargar</button>
                    </a>

                    <!-- Eliminar (último archivo analizado) -->
                    <a href="index.php?eliminar=<?= $archivos[0]['id'] ?? '' ?>">
                        <button type="button" style="background:#d32f2f;">
                            Eliminar
                        </button>
                    </a>

                    <!-- Analizar otro -->
                    <a href="index.php">
                        <button type="button" style="background:#388e3c;">
                            Analizar otro
                        </button>
                    </a>

                </div>

            </div>

        <?php endif; ?>

        <?php if ($error): ?>

            <div class="error">

                ❌ <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>

    </div>

    <div class="history">

        <h2>Historial de archivos</h2>

        <table>

            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Tamaño</th>
                <th>Fecha</th>
            </tr>

            <?php foreach ($archivos as $a): ?>

                <tr>

                    <td><?= $a['id'] ?></td>

                    <td><?= htmlspecialchars($a['nombre_original']) ?></td>

                    <td><?= $a['tipo_detectado'] ?></td>

                    <td><?= $a['tamaño'] ?></td>

                    <td><?= $a['fecha_subida'] ?></td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

    <div class="api-links">

        <strong>Endpoints API REST:</strong>

        <br><br>

        <a href="api/tipos.php">GET /api/tipos</a>

        <a href="api/version.php">GET /api/version</a>

        <span>POST /api/analizar</span>

    </div>

</div>

<script>

    const dropArea = document.getElementById("dropArea");
    const fileInput = document.getElementById("fileInput");

    dropArea.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropArea.classList.add("dragover");
    });

    dropArea.addEventListener("dragleave", () => {
        dropArea.classList.remove("dragover");
    });

    dropArea.addEventListener("drop", (e) => {
        e.preventDefault();
        dropArea.classList.remove("dragover");

        const files = e.dataTransfer.files;
        fileInput.files = files;
    });

</script>

</body>
</html>
