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

    /**
     * Array maestro de tipos de archivo.
     * ⚠ Los códigos (claves) deben coincidir con los EQU definidos en engine/firmas.inc.
     *
     * Formato: código => ['nombre' => string, 'extensiones' => string[]]
     * Todos los demás mapas y arreglos del sistema se derivan de este.
     */
    private static $TIPOS = [
        0  => ['nombre' => 'DESCONOCIDO',    'extensiones' => []],
        1  => ['nombre' => 'JPEG',           'extensiones' => ['jpg', 'jpeg']],
        2  => ['nombre' => 'PNG',            'extensiones' => ['png']],
        3  => ['nombre' => 'GIF',            'extensiones' => ['gif']],
        4  => ['nombre' => 'BMP',            'extensiones' => ['bmp']],
        5  => ['nombre' => 'PDF',            'extensiones' => ['pdf']],
        6  => ['nombre' => 'ZIP',            'extensiones' => ['zip']],
        7  => ['nombre' => 'DOCX/XLSX/PPTX', 'extensiones' => ['docx', 'xlsx', 'pptx']],
        8  => ['nombre' => 'EXE (PE)',        'extensiones' => ['exe']],
        9  => ['nombre' => 'ELF',            'extensiones' => ['elf']],
        10 => ['nombre' => 'MP3',            'extensiones' => ['mp3']],
        11 => ['nombre' => 'MP4',            'extensiones' => ['mp4']],
        12 => ['nombre' => 'RAR',            'extensiones' => ['rar']],
        13 => ['nombre' => '7Z',             'extensiones' => ['7z']],
        14 => ['nombre' => 'WAV',            'extensiones' => ['wav']],
        15 => ['nombre' => 'AVI',            'extensiones' => ['avi']],
        16 => ['nombre' => 'WEBP',           'extensiones' => ['webp']],
        17 => ['nombre' => 'ICO',            'extensiones' => ['ico']],
        18 => ['nombre' => 'TAR',            'extensiones' => ['tar']],
        19 => ['nombre' => 'GZIP',           'extensiones' => ['gz']],
        20 => ['nombre' => 'JAVA CLASS',     'extensiones' => ['class']],
    ];

    /**
     * Devuelve la lista plana de todas las extensiones permitidas.
     * Fuente única: self::$TIPOS
     */
    public static function getExtensionesPermitidas(): array
    {
        $exts = [];
        foreach (self::$TIPOS as $tipo) {
            foreach ($tipo['extensiones'] as $ext) {
                $exts[] = $ext;
            }
        }
        return $exts;
    }

    /**
     * Devuelve un mapa invertido: extensión => código de tipo.
     * Fuente única: self::$TIPOS
     */
    public static function getMapaExtensiones(): array
    {
        $mapa = [];
        foreach (self::$TIPOS as $codigo => $tipo) {
            foreach ($tipo['extensiones'] as $ext) {
                $mapa[$ext] = $codigo;
            }
        }
        return $mapa;
    }

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
            error_log("Salida del EXE: " . trim($output) . "\n");
        } else {
            // ── Modo DLL / FFI ─────────────────────────────
            $len = strlen($contenido);
            $buffer = $this->ffi->new("unsigned char[$len]", false);

            FFI::memcpy($buffer, $contenido, $len);

            $resultado = $this->ffi->AnalizarFirma($buffer, $len);
            error_log("Resultado del FFI: $resultado\n");
        }

        // Si DLL/EXE falla → detectar por extensión
        if ($resultado < 0) {

            $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

            error_log("Advertencia: No se pudo analizar el archivo con DLL/EXE. Intentando por extensión: $extension\n");

            // Mapa derivado del array maestro self::$TIPOS
            $mapa = self::getMapaExtensiones();

            return $mapa[$extension] ?? 0;
        }

        return $resultado;
    }

    public function obtenerNombreTipo($tipo)
    {
        // Nombre derivado del array maestro self::$TIPOS
        return self::$TIPOS[$tipo]['nombre'] ?? self::$TIPOS[0]['nombre'];
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
            // Total derivado del array maestro (excluye DESCONOCIDO, código 0)
            return count(self::$TIPOS) - 1;
        }
        return $this->ffi->ObtenerTotalTiposSoportados();
    }
}
