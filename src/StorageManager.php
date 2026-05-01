<?php

class StorageManager {

    private $directorio = __DIR__ . '/../uploads/';

    public function guardarArchivo($archivo) {

        // Crear carpeta si no existe
        if (!file_exists($this->directorio)) {
            mkdir($this->directorio, 0777, true);
        }

        // Generar nombre único
        $nombreUnico = uniqid() . "_" . basename($archivo['name']);

        $rutaCompleta = $this->directorio . $nombreUnico;

        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $rutaCompleta;
        } else {
            throw new Exception("Error al guardar el archivo");
        }
    }
}