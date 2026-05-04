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
        (nombre_original, tipo_detectado, hash_md5, tamaño, usuario_id, ruta) 
        VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            $datos['nombre_original'],
            $datos['tipo_detectado'],
            $datos['hash_md5'],
            $datos['tamaño'],
            $datos['usuario_id'],
            $datos['ruta']
        ]);
    }

    // =========================
    // OBTENER TODOS
    // =========================
    public function obtenerTodos() {

        $sql = "SELECT * FROM archivos_analizados ORDER BY fecha_subida DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // OBTENER POR ID
    // =========================
    public function obtenerPorId($id) {

        $sql = "SELECT * FROM archivos_analizados WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================
    // ACTUALIZAR
    // =========================
    public function actualizar($id, $datos) {

        $sql = "UPDATE archivos_analizados 
                SET nombre_original = ?, tipo_detectado = ?, hash_md5 = ?, tamaño = ?, usuario_id = ?, ruta = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            $datos['nombre_original'],
            $datos['tipo_detectado'],
            $datos['hash_md5'],
            $datos['tamaño'],
            $datos['usuario_id'],
            $datos['ruta'],
            $id
        ]);
    }

    // =========================
    // ELIMINAR (solo BD por ahora)
    // =========================
    public function eliminar($id) {

        $sql = "DELETE FROM archivos_analizados WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
    }

    // =========================
    // FILTRAR POR TIPO
    // =========================
    public function filtrarPorTipo($tipo) {

        $sql = "SELECT * FROM archivos_analizados WHERE tipo_detectado = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$tipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // FILTRAR POR FECHA
    // =========================
    public function filtrarPorFecha($fechaInicio, $fechaFin) {

        $sql = "SELECT * FROM archivos_analizados 
                WHERE fecha_subida BETWEEN ? AND ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$fechaInicio, $fechaFin]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // FILTRAR POR USUARIO
    // =========================
    public function filtrarPorUsuario($usuarioId) {

        $sql = "SELECT * FROM archivos_analizados WHERE usuario_id = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // BUSCAR POR NOMBRE
    // =========================
    public function buscarPorNombre($nombre) {

        $sql = "SELECT * FROM archivos_analizados 
                WHERE nombre_original LIKE ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(["%$nombre%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}