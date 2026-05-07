<?php

require_once __DIR__ . '/../Storage/StorageManager.php';

class ArchivoRepository {

    private $conexion;
    private $storage;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->storage = new StorageManager();
    }

    // REGISTRAR AUDITORÍA
    public function registrarAuditoria($usuario, $accion, $archivoId, $descripcion) {

        $sql = "INSERT INTO auditoria (usuario, accion, archivo_id, descripcion)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario, $accion, $archivoId, $descripcion]);
    }

    // GUARDAR
    public function guardar($datos)
    {

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

        $id = $this->conexion->lastInsertId();

        // Auditoría
        $this->registrarAuditoria(
            $_SESSION['correo'] ?? 'sistema',
            'SUBIR_ARCHIVO',
            $id,
            'Archivo subido: ' . $datos['nombre_original']
        );
    }

    // OBTENER TODOS
    public function obtenerTodos()
    {

        $sql = "SELECT * FROM archivos_analizados ORDER BY fecha_subida DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER POR ID
    public function obtenerPorId($id)
    {

        $sql = "SELECT * FROM archivos_analizados WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ACTUALIZAR
    public function actualizar($id, $datos)
    {

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

        // Auditoría
        $this->registrarAuditoria(
            $_SESSION['correo'] ?? 'sistema',
            'ACTUALIZAR_ARCHIVO',
            $id,
            'Archivo actualizado'
       );
    }

    // ELIMINAR (CORREGIDO)
    public function eliminar($id) {

        // 1. Obtener archivo
        $archivo = $this->obtenerPorId($id);

        if (!$archivo) {
            throw new Exception("Archivo no encontrado");
        }

        // 2. Eliminar archivo físico 
        if (isset($archivo['ruta'])) {
            $this->storage->eliminarArchivo($archivo['ruta']);
        }

        // 3. Eliminar de BD
        $sql = "DELETE FROM archivos_analizados WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);

        // 4. Auditoría
        $this->registrarAuditoria(
            $_SESSION['correo'] ?? 'sistema',
            'ELIMINAR_ARCHIVO',
            $id,
            'Archivo eliminado'
        );
    }

    // FILTROS

    public function filtrarPorTipo($tipo) {

        $sql = "SELECT * FROM archivos_analizados WHERE tipo_detectado = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$tipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrarPorFecha($fechaInicio, $fechaFin) {

        $sql = "SELECT * FROM archivos_analizados WHERE fecha_subida BETWEEN ? AND ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$fechaInicio, $fechaFin]);

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
    public function buscarPorHash($hash)
    {
        $sql = "SELECT * FROM archivos_analizados WHERE hash_md5 = :hash LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':hash', $hash);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerHistorial($tipo = null, $fecha = null)
    {
        $sql = "SELECT * FROM archivos_analizados WHERE 1=1";

        if ($tipo) {
            $sql .= " AND tipo_detectado = :tipo";
        }

        if ($fecha) {
            $sql .= " AND DATE(fecha_subida) = :fecha";
        }

        // ORDER BY debe ir ANTES de prepare()
        $sql .= " ORDER BY fecha_subida DESC";

        $stmt = $this->conexion->prepare($sql);

        if ($tipo) {
            $stmt->bindParam(':tipo', $tipo);
        }

        if ($fecha) {
            $stmt->bindParam(':fecha', $fecha);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}