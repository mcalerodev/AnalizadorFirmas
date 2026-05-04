<?php

class StorageManager {

    public function guardarArchivo($archivo) {

        $extensionesPermitidas = [
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'ico',
            'pdf','zip', 'rar', '7z', 'tar', 'gz','mp3', 'mp4', 'wav', 'avi'
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Extensión no permitida");
        }

        // Tamaño
        $maxSize = 5 * 1024 * 1024;

        if ($archivo['size'] > $maxSize) {
            throw new Exception("Archivo demasiado grande (máx 5MB)");
        }

        // Carpeta
        $directorio = __DIR__ . '/../uploads/';

        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        // Nombre seguro
        $nombreSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['name']);
        $nombreUnico = uniqid() . "_" . $nombreSeguro;

        $rutaFinal = $directorio . $nombreUnico;

        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            return $rutaFinal;
        }

        throw new Exception("Error al guardar el archivo");
    }
}