<?php

class ArchivoRepository {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // =========================
    // REGISTRAR AUDITORÍA
    // =========================
    public function registrarAuditoria($usuario, $accion, $archivoId, $descripcion) {

        $sql = "INSERT INTO auditoria (usuario, accion, archivo_id, descripcion)  VALUES (?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario, $accion, $archivoId, $descripcion]);
    }

    // =========================
    // GUARDAR ARCHIVO
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

        // Obtener ID del archivo recién insertado
        $id = $this->conexion->lastInsertId();

        // Auditoría
        $this->registrarAuditoria(
            'sistema',
            'SUBIR_ARCHIVO',
            $id,
            'Archivo subido: ' . $datos['nombre_original']
        );
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
                SET nombre_original = ?, tipo_detectado = ?, hash_md5 = ?, tamaño = ?, usuario_id = ?, ruta = ? WHERE id = ?";

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
    // ELIMINAR (SEGURA + AUDITORÍA)
    // =========================
    public function eliminar($id) {

        // Obtener archivo
        $archivo = $this->obtenerPorId($id);

        if (!$archivo) {
            throw new Exception("Archivo no encontrado");
        }

        $ruta = $archivo['ruta'];

        // Eliminar archivo físico
        if (file_exists($ruta)) {
            unlink($ruta);
        }

        // Eliminar de BD
        $sql = "DELETE FROM archivos_analizados WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);

        // Auditoría
        $this->registrarAuditoria(
            'sistema',
            'ELIMINAR_ARCHIVO',
            $id,
            'Archivo eliminado del sistema'
        );
    }

    // =========================
    // FILTROS
    // =========================

    public function filtrarPorTipo($tipo) {

        $sql = "SELECT * FROM archivos_analizados WHERE tipo_detectado = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$tipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrarPorFecha($inicio, $fin) {

        $sql = "SELECT * FROM archivos_analizados 
                WHERE fecha_subida BETWEEN ? AND ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$inicio, $fin]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrarPorUsuario($usuarioId) {

        $sql = "SELECT * FROM archivos_analizados WHERE usuario_id = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorNombre($nombre) {

        $sql = "SELECT * FROM archivos_analizados WHERE nombre_original LIKE ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(["%$nombre%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}