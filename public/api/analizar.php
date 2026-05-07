<?php

/**
 * Endpoint: POST /api/analizar.php
 *
 * Recibe un archivo mediante multipart/form-data,
 * lo guarda con StorageManager, lo analiza con la DLL
 * a través de MotorFirmas (FFI) y guarda el resultado en la BD.
 *
 * Respuesta JSON:
 * {
 *   "tipo_codigo": 2,
 *   "tipo_nombre": "PNG",
 *   "hash_md5": "abc123...",
 *   "nombre_original": "foto.png",
 *   "tamaño": 204800
 * }
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use POST."]);
    exit;
}

// Verificar que se envió un archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "No se recibió ningún archivo válido."]);
    exit;
}

// Cargar dependencias (Conexion.php la carga ArchivoRepository internamente)
require_once __DIR__ . '/../../src/Service/MotorFirmas.php';
require_once __DIR__ . '/../../src/Storage/StorageManager.php';
require_once __DIR__ . '/../../src/Repository/ArchivoRepository.php';

try {
    // ArchivoRepository resuelve la conexión internamente (Singleton)
    $repo = new ArchivoRepository();

    // 1. Calcular hash MD5 del archivo temporal ANTES de moverlo
    $rutaTmp = $_FILES['archivo']['tmp_name'];
    $hash    = md5_file($rutaTmp);

    // 2. CACHÉ: si ya fue analizado antes, devolver resultado guardado
    $existente = $repo->buscarPorHash($hash);
    if ($existente) {
        $repo->registrarAuditoria(
            'sistema',
            'analizar_cache',
            $existente['id'],
            'Resultado devuelto desde caché para: ' . $_FILES['archivo']['name']
        );

        http_response_code(200);
        echo json_encode([
            "tipo_codigo"     => 0,
            "tipo_nombre"     => $existente['tipo_detectado'],
            "hash_md5"        => $hash,
            "nombre_original" => $_FILES['archivo']['name'],
            "tamaño"          => $_FILES['archivo']['size'],
            "cache"           => true
        ]);
        exit;
    }

    // 3. Guardar archivo en disco (StorageManager valida extensión y tamaño)
    $storage     = new StorageManager();
    $rutaArchivo = $storage->guardarArchivo($_FILES['archivo']);

    // 4. Analizar con el motor (FFI o EXE fallback — Singleton)
    $motor      = MotorFirmas::getInstance();
    $tipoCodigo = $motor->analizarArchivo($rutaArchivo);
    $tipoNombre = $motor->obtenerNombreTipo($tipoCodigo);

    // 5. Persistir en BD
    $repo->guardar([
        'nombre_original' => $_FILES['archivo']['name'],
        'tipo_detectado'  => $tipoNombre,
        'hash_md5'        => $hash,
        'tamaño'          => $_FILES['archivo']['size'],
        'usuario_id'      => null
    ]);

    // 6. Registrar en auditoría
    $ultimo = $repo->buscarPorHash($hash);
    $repo->registrarAuditoria(
        'sistema',
        'analizar_nuevo',
        $ultimo['id'] ?? null,
        'Archivo analizado: ' . $_FILES['archivo']['name'] . ' → ' . $tipoNombre
    );

    // 7. Responder con JSON
    http_response_code(200);
    echo json_encode([
        "tipo_codigo"     => $tipoCodigo,
        "tipo_nombre"     => $tipoNombre,
        "hash_md5"        => $hash,
        "nombre_original" => $_FILES['archivo']['name'],
        "tamaño"          => $_FILES['archivo']['size'],
        "cache"           => false
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}