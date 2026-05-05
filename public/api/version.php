<?php
/**
 * Endpoint: GET /api/version.php
 *
 * Retorna la versión de la librería DLL (motor_firmas).
 *
 * Respuesta JSON:
 * {
 *   "version": 1,
 *   "libreria": "motor_firmas.dll"
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
    $motor = MotorFirmas::getInstance();

    echo json_encode([
        "version"  => $motor->version(),
        "libreria" => "motor_firmas.dll"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
