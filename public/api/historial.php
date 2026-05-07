<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../src/Database/Conexion.php';
require_once __DIR__ . '/../../src/Repository/ArchivoRepository.php';

try {
    // ArchivoRepository resuelve la conexión internamente (Singleton)
    $repo = new ArchivoRepository();

    // 🔍 Filtros opcionales
    $tipo  = $_GET['tipo']  ?? null;
    $fecha = $_GET['fecha'] ?? null;

    $data = $repo->obtenerHistorial($tipo, $fecha);

    echo json_encode([
        "status" => "success",
        "data"   => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
