<?php

class StorageManager {

    private $directorio;

    public function __construct() {
        $this->directorio = __DIR__ . '/../../uploads/';
        
        if (!file_exists($this->directorio)) {
            mkdir($this->directorio, 0777, true);
        }
    }

    // GUARDAR ARCHIVO
    public function guardarArchivo($archivo) {

        $nombreSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['name']);
        $nombreUnico = uniqid() . "_" . $nombreSeguro;

        $rutaFinal = $this->directorio . $nombreUnico;

        if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            return $rutaFinal;
        }

        throw new Exception("Error al guardar el archivo");
    }

    //  ELIMINAR ARCHIVO FÍSICO 
    public function eliminarArchivo($ruta) {

        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }
}