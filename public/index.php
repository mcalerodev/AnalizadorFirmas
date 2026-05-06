<?php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        $conexion = Conexion::getInstance();
        $repo     = new ArchivoRepository($conexion);

        $repo->guardar([
            'nombre_original' => $_FILES['archivo']['name'],
            'tipo_detectado'  => $tipoNombre,
            'hash_md5'        => $hash,
            'tamaño'          => $_FILES['archivo']['size'],
            'usuario_id'      => null,
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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>AnalizadorFirmas — Prueba</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
        }

        .resultado {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            padding: 16px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .error {
            background: #ffebee;
            border: 1px solid #f44336;
            padding: 16px;
            border-radius: 6px;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        td:first-child {
            font-weight: bold;
            width: 160px;
        }

        button {
            background: #1976d2;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type=file] {
            margin: 12px 0;
        }

        .api-links {
            margin-top: 30px;
            font-size: 0.9em;
        }

        .api-links a {
            display: inline-block;
            margin-right: 12px;
            color: #1976d2;
        }
    </style>
</head>

<body>
    <h2>🔍 AnalizadorFirmas — Prueba de Integración</h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="archivo" required>
        <br>
        <button type="submit">Analizar archivo</button>
    </form>

    <?php if ($resultado): ?>
        <div class="resultado">
            <h3>✅ Archivo analizado correctamente</h3>
            <table>
                <tr>
                    <td>Nombre</td>
                    <td><?= htmlspecialchars($resultado['nombre']) ?></td>
                </tr>
                <tr>
                    <td>Tipo detectado</td>
                    <td><strong><?= htmlspecialchars($resultado['tipo']) ?></strong> (código <?= $resultado['codigo'] ?>)
                    </td>
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
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">❌ Error: <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
        <?php
$conexion = Conexion::getInstance();
$repo = new ArchivoRepository($conexion);
$archivos = $repo->obtenerTodos();
?>

<h3> Historial de archivos</h3>

<table border="1" cellpadding="8">
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

    <div class="api-links">
        <strong>Endpoints API REST:</strong><br>
        <a href="api/tipos.php">GET /api/tipos</a>
        <a href="api/version.php">GET /api/version</a>
        <span style="color:#666">POST /api/analizar</span>
    </div>
</body>

</html>