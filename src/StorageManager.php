<?php

class StorageManager {

    // GUARDAR ARCHIVO
    public function guardarArchivo($archivo) {

        // EXTENSIONES PERMITIDAS
        $extensionesPermitidas = [
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'ico',
            'pdf','zip', 'rar', '7z', 'tar', 'gz','mp3', 'mp4', 'wav', 'avi'
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Extensión no permitida");
        }

        //  VALIDAR TAMAÑO (ejem...5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($archivo['size'] > $maxSize) {
            throw new Exception("Archivo demasiado grande (máx 5MB)");
        }

        // CREAR CARPETA SI NO EXISTE
        $directorio = __DIR__ . '/../uploads/';

        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        // NOMBRE ÚNICO
        $nombreUnico = uniqid() . "_" . basename($archivo['name']);

        $rutaFinal = $directorio . $nombreUnico;

        // 5. MOVER ARCHIVO
        if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            return $rutaFinal;
        }

        throw new Exception("Error al guardar el archivo");
    }
}