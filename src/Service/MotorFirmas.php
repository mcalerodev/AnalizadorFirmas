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
    // ⚠ Los códigos deben coincidir con los EQU definidos en engine/firmas.inc
    private static $tiposMap = [
        0  => "DESCONOCIDO",
        1  => "JPEG",
        2  => "PNG",
        3  => "GIF",
        4  => "BMP",
        5  => "PDF",
        6  => "ZIP",
        7  => "DOCX/XLSX/PPTX",
        8  => "EXE (PE)",
        9  => "ELF",
        10 => "MP3",
        11 => "MP4",
        12 => "RAR",
        13 => "7Z",
        14 => "WAV",
        15 => "AVI",
        16 => "WEBP",
        17 => "ICO",
        18 => "TAR",
        19 => "GZIP",
        20 => "JAVA CLASS"
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

    // ── Modo EXE ─────────────────────────────────────
    if ($this->modoExe) {
        $rutaExe = realpath(__DIR__ . '/../../engine/motor_firmas.exe');
        $output = shell_exec(escapeshellarg($rutaExe) . ' ' . escapeshellarg($ruta));

        $resultado = intval(trim($output));

    } else {

        // ── Modo DLL / FFI ─────────────────────────────
        $len = strlen($contenido);
        $buffer = $this->ffi->new("unsigned char[$len]", false);

        FFI::memcpy($buffer, $contenido, $len);

        $resultado = $this->ffi->AnalizarFirma($buffer, $len);
    }

    // Si DLL/EXE falla → detectar por extensión
    if ($resultado < 0) {

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        $mapa = [
            'jpg'  => 1,
            'jpeg' => 1,
            'png'  => 2,
            'pdf'  => 3,
            'zip'  => 4,
            'gif'  => 5,
            'bmp'  => 6,
            'exe'  => 7,
            'mp3'  => 9
        ];

        return $mapa[$extension] ?? 0;
    }

    return $resultado;
}

   public function obtenerNombreTipo($tipo)
{
    $tipos = [
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

    return $tipos[$tipo] ?? "DESCONOCIDO";
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
