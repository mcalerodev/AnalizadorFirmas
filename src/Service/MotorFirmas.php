<?php

/**
 * Clase MotorFirmas — Patrón Singleton con modo dual (FFI / EXE fallback)
 *
 * Intenta cargar la DLL con PHP FFI (x64).
 * Si la DLL es x86 y PHP es x64, cae automáticamente al modo EXE
 * usando motor_firmas.exe como proceso externo.
 *
 * Modo FFI : comunicación directa con la DLL — más rápido.
 * Modo EXE : llama al ejecutable externo con shell_exec — compatible siempre.
 */
class MotorFirmas
{
    private static $instancia = null;

    private $ffi      = null;   // null cuando se usa modo EXE
    private $modoExe  = false;  // true cuando FFI no está disponible

    // Mapa de tipos usado en modo EXE (sin DLL)
    private static $tiposMap = [
        1 => "JPEG",
        2 => "PNG",
        3 => "PDF",
        4 => "ZIP",
        5 => "GIF",
        6 => "BMP",
        7 => "EXE",
        8 => "ELF",
        9 => "MP3"
    ];

    private function __construct()
    {
        $rutaDll = realpath(__DIR__ . '/../../engine/motor_firmas.dll');
        $rutaExe = realpath(__DIR__ . '/../../engine/motor_firmas.exe');

        // Intentar cargar FFI (fallará si hay conflicto de arquitectura x86/x64)
       if (class_exists('FFI') && $rutaDll !== false && file_exists($rutaDll)) {
    try {
        $this->ffi = FFI::cdef("
            int AnalizarFirma(unsigned char* buffer, int size);
            char* ObtenerNombreTipo(int tipo);
            int VerificarFirmaEspecifica(unsigned char* buffer, int size, int tipo);
            int ObtenerVersionLibreria();
            int ObtenerTotalTiposSoportados();
        ", $rutaDll);
    } catch (\Throwable $e) {
        $this->ffi     = null;
        $this->modoExe = true;
    }
} elseif ($rutaExe !== false && file_exists($rutaExe)) {
    $this->modoExe = true;
} else {
    throw new \Exception("No se encontró motor_firmas.dll ni motor_firmas.exe.");
}
    }

    /**
     * Devuelve la única instancia de MotorFirmas.
     * La DLL se carga solo la primera vez que se llama.
     */
    public static function getInstance()
    {
        if (self::$instancia === null) {
            self::$instancia = new MotorFirmas();
        }
        return self::$instancia;
    }

    // Evitar clonación del Singleton
    private function __clone() {}

    /** Indica si está operando con EXE en lugar de DLL */
    public function esModoExe()
    {
        return $this->modoExe;
    }

    public function analizarArchivo($ruta)
    {
        if (!file_exists($ruta)) {
            throw new \Exception("Archivo no existe: $ruta");
        }

        $contenido = file_get_contents($ruta);

        if (strlen($contenido) < 4) {
            throw new \Exception("Archivo muy pequeño para analizar");
        }

        // ── Modo EXE ──────────────────────────────────────────────────────
        if ($this->modoExe) {
            //lo cambie temporalmente porque me dio error al hacer pruebas, pero lo volvi a poner originalmente
            $rutaExe = realpath(__DIR__ . '/../../engine/motor_firmas.exe');
            $output  = shell_exec(escapeshellarg($rutaExe) . ' ' . escapeshellarg($ruta));
            return intval(trim($output));
        }

        // ── Modo FFI (DLL x64) ────────────────────────────────────────────
        $len    = strlen($contenido);
        $buffer = $this->ffi->new("unsigned char[$len]", false);
        FFI::memcpy($buffer, $contenido, $len);
        return $this->ffi->AnalizarFirma($buffer, $len);
    }

    public function obtenerNombreTipo($tipo)
    {
        // Modo EXE: usar mapa local
        if ($this->modoExe) {
            return isset(self::$tiposMap[$tipo]) ? self::$tiposMap[$tipo] : "DESCONOCIDO";
        }

        // Modo FFI
        $ptr = $this->ffi->ObtenerNombreTipo($tipo);
        if ($ptr === null) {
            return "DESCONOCIDO";
        }
        return FFI::string($ptr);
    }

    public function verificarTipoEspecifico($ruta, $tipo)
    {
        if ($this->modoExe) {
            // En modo EXE comparamos el resultado del análisis
            return ($this->analizarArchivo($ruta) === $tipo) ? 1 : 0;
        }

        $contenido = file_get_contents($ruta);
        $len       = strlen($contenido);
        $buffer    = $this->ffi->new("unsigned char[$len]", false);
        FFI::memcpy($buffer, $contenido, $len);
        return $this->ffi->VerificarFirmaEspecifica($buffer, $len, $tipo);
    }

    public function version()
    {
        if ($this->modoExe) {
            return 1; // versión por defecto en modo EXE
        }
        return $this->ffi->ObtenerVersionLibreria();
    }

    public function totalTipos()
    {
        if ($this->modoExe) {
            return count(self::$tiposMap);
        }
        return $this->ffi->ObtenerTotalTiposSoportados();
    }
}
