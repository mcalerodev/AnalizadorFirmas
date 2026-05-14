<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { echo json_encode(['ok'=>false,'error'=>'Método no permitido']); exit; }

require_once __DIR__ . '/../../src/Database/Conexion.php';
require_once __DIR__ . '/../../src/Repository/ArchivoRepository.php';

try {
    $body = json_decode(file_get_contents('php://input'), true);
    $id   = isset($body['id']) ? (int)$body['id'] : 0;

    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido']);
        exit;
    }

    $repo = new ArchivoRepository(Conexion::getInstance());
    $repo->eliminar($id);

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
