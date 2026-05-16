<?php

require_once __DIR__ . '/../Service/MotorFirmas.php';

class StorageManager
{

    private $directorio;

    public function __construct()
    {
        $this->directorio = __DIR__ . '/../../storage/';

        if (!file_exists($this->directorio)) {
            mkdir($this->directorio, 0777, true);
        }
    }

    // GUARDAR ARCHIVO
    public function guardarArchivo($archivo)
    {

        // ✔ EXTENSIONES PERMITIDAS — derivadas del array maestro MotorFirmas::$TIPOS
        $extensionesPermitidas = MotorFirmas::getExtensionesPermitidas();

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Extensión no permitida");
        }

        // ✔ VALIDAR TAMAÑO (5MB)
        $maxSize = 5 * 1024 * 1024;

        if ($archivo['size'] > $maxSize) {
            throw new Exception("Archivo demasiado grande (máx 5MB)");
        }

        // ✔ NOMBRE SEGURO
        $nombreSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['name']);
        $nombreUnico = uniqid() . "_" . $nombreSeguro;

        $rutaFinal = $this->directorio . $nombreUnico;

        // ✔ MOVER ARCHIVO
        if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            return $rutaFinal;
        }

        throw new Exception("Error al guardar el archivo");
    }

    // ✔ ELIMINAR ARCHIVO FÍSICO
    public function eliminarArchivo($ruta)
    {

        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }
}
