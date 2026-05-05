<?php
/**
 * Endpoint: GET /api/tipos.php
 *
 * Lista todos los tipos de archivo soportados por la DLL.
 * Consulta ObtenerTotalTiposSoportados() y ObtenerNombreTipo()
 * directamente desde la librería en ensamblador.
 *
 * Respuesta JSON:
 * {
 *   "total": 9,
 *   "tipos": [
 *     {"codigo": 1, "nombre": "JPEG"},
 *     {"codigo": 2, "nombre": "PNG"},
 *     ...
 *   ]
 * }
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use GET."]);
    exit;
}

require_once __DIR__ . '/../../src/Service/MotorFirmas.php';

try {
    // Singleton: la DLL ya está cargada si fue llamada antes, si no se carga aquí
    $motor = MotorFirmas::getInstance();

    $total = $motor->totalTipos();
    $tipos = [];

    for ($i = 1; $i <= $total; $i++) {
        $tipos[] = [
            "codigo" => $i,
            "nombre" => $motor->obtenerNombreTipo($i)
        ];
    }

    echo json_encode([
        "total" => $total,
        "tipos" => $tipos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
