<?php

class ArchivoRepository {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // =========================
    // GUARDAR
    // =========================
    public function guardar($datos) {

        $sql = "INSERT INTO archivos_analizados 
        (nombre_original, tipo_detectado, hash_md5, tamaño, usuario_id) 
        VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            $datos['nombre_original'],
            $datos['tipo_detectado'],
            $datos['hash_md5'],
            $datos['tamaño'],
            $datos['usuario_id']
        ]);
    }
}