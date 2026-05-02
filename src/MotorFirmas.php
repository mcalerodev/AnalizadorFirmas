<?php

class MotorFirmas
{
    private $ffi;

    public function __construct()
    {
        // Cargar la DLL
        $this->ffi = FFI::cdef("
            int AnalizarFirma(unsigned char* buffer, int size);
            char* ObtenerNombreTipo(int tipo);
            int VerificarFirmaEspecifica(unsigned char* buffer, int size, int tipo);
            int ObtenerVersionLibreria();
            int ObtenerTotalTiposSoportados();
        ", __DIR__ . '/../motor_firmas.dll');
    }

    public function analizarArchivo($ruta)
    {
        if (!file_exists($ruta)) {
            throw new Exception("Archivo no existe");
        }

        $contenido = file_get_contents($ruta);

        if (strlen($contenido) < 16) {
            throw new Exception("Archivo muy pequeño");
        }

        // Crear buffer en C
        $buffer = FFI::new("unsigned char[" . strlen($contenido) . "]", false);
        FFI::memcpy($buffer, $contenido, strlen($contenido));

        $tipo = $this->ffi->AnalizarFirma($buffer, strlen($contenido));

        return $tipo;
    }

    public function obtenerNombreTipo($tipo)
    {
        $ptr = $this->ffi->ObtenerNombreTipo($tipo);

        if ($ptr == null) {
            return "DESCONOCIDO";
        }

        return FFI::string($ptr);
    }

    public function verificarTipoEspecifico($ruta, $tipo)
    {
        $contenido = file_get_contents($ruta);

        $buffer = FFI::new("unsigned char[" . strlen($contenido) . "]", false);
        FFI::memcpy($buffer, $contenido, strlen($contenido));

        return $this->ffi->VerificarFirmaEspecifica($buffer, strlen($contenido), $tipo);
    }

    public function version()
    {
        return $this->ffi->ObtenerVersionLibreria();
    }
}