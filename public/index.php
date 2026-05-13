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
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
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
    <title>AnalizadorFirmas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/theme.css">
</head>
<!-- Elimine la etiqueta <style> de aca y lo pase a assets/css/theme.css para llevar un orden-->

<body>
    <div class="container">
        <a class="api-links" href="logout.php">Cerrar sesión</a>
        <h1>AnalizadorFirmas</h1>
        <p>Descubre qué hay dentro de tus archivos y si son seguros.</p>
        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="archivo" required>
                <br>
                <button class="btn-success" type="submit">Analizar archivo</button>
            </form>

            <?php if ($resultado): ?>
            <div class="resultado">
                <h3>✅ Archivo analizado correctamente</h3>

                <!-- Contenedor para controlar el espacio -->
                <div class="tabla-contenedor">
                    <table>
                        <tr>
                            <td>Nombre</td>
                            <td><?= htmlspecialchars($resultado['nombre']) ?></td>
                        </tr>
                        <tr>
                            <td>Tipo detectado</td>
                            <td><strong><?= htmlspecialchars($resultado['tipo']) ?></strong> (código
                                <?= $resultado['codigo'] ?>)</td>
                        </tr>
                        <tr>
                            <td>Hash MD5</td>
                            <td><code><?= $resultado['hash'] ?></code></td>
                        </tr>
                        <tr>
                            <td>Tamaño</td>
                            <td><?= $resultado['tamaño'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>


            <?php if ($error): ?>
            <div class="error">❌ Error: <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php
            $conexion = Conexion::getInstance();
            $repo = new ArchivoRepository($conexion);
            $archivos = $repo->obtenerTodos();
            ?> <h3> Historial de archivos</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <?php foreach ($archivos as $a): ?>
                <tbody>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= htmlspecialchars($a['nombre_original']) ?></td>
                        <td><?= $a['tipo_detectado'] ?></td>
                        <td><?= $a['tamaño'] ?></td>
                        <td><?= $a['fecha_subida'] ?></td>
                    </tr>
                </tbody>
                <?php endforeach; ?>
            </table>

            <div class="api-links">
                <strong class="endpoints-title">Endpoints API REST:</strong><br>
                <a class="api-links" href="api/tipos.php">GET /api/tipos</a>
                <a class="api-links" href="api/version.php">GET /api/version</a>
                <span style="color:#666">POST /api/analizar</span>
            </div>

        </div>
    </div>
</body>

</html>