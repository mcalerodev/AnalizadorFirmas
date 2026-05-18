<?php

/**
 * Clase MotorFirmas — Patrón Singleton con soporte para AnalizadorFirmas.dll (x64)
 */
class MotorFirmas
{
    private static $instancia = null;

    private $ffi      = null;
    private $modoExe  = false;

    /**
     * Códigos de tipo coincidentes con engine/firmas.inc
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
        8  => ['nombre' => 'EXE (PE)',       'extensiones' => ['exe']],
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

    public static function getExtensionesPermitidas(): array
    {
        $exts = [];
        foreach (self::$TIPOS as $tipo) {
            $exts = array_merge($exts, $tipo['extensiones']);
        }
        return $exts;
    }

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
        // 1. ACTUALIZACIÓN: Nombre de la DLL generado por Visual Studio
        $rutaDll = realpath(__DIR__ . '/../../engine/AnalizadorFirmas.dll');
        $rutaExe = realpath(__DIR__ . '/../../engine/motor_firmas.exe');

        if (class_exists('FFI') && $rutaDll !== false && file_exists($rutaDll)) {
            try {
                // 2. FIRMAS ACTUALIZADAS: Coincidiendo con el .def y el código x64 de ASM
                $this->ffi = FFI::cdef("
                    int AnalizarFirma(const unsigned char* pBuffer, int dwTamanio);
                    const char* ObtenerNombreTipo(int dwTipo);
                    int VerificarFirmaEspecifica(const unsigned char* pBuffer, int dwTamanio, int dwTipo);
                    int ObtenerVersionLibreria();
                    int ObtenerTotalTiposSoportados();
                ", $rutaDll);
            } catch (\Throwable $e) {
                error_log("Fallo al cargar FFI: " . $e->getMessage());
                $this->ffi     = null;
                $this->modoExe = true;
            }
        } elseif ($rutaExe !== false && file_exists($rutaExe)) {
            $this->modoExe = true;
        } else {
            throw new \Exception("No se encontró AnalizadorFirmas.dll ni motor_firmas.exe.");
        }
    }

    public static function getInstance()
    {
        if (self::$instancia === null) {
            self::$instancia = new MotorFirmas();
        }
        return self::$instancia;
    }

    private function __clone() {}

    public function analizarArchivo($ruta)
    {
        if (!file_exists($ruta)) {
            throw new \Exception("Archivo no existe: $ruta");
        }

        $tamanioReal = filesize($ruta);
        if ($tamanioReal < 16) { // Mínimo definido en MIN_BYTES_FIRMA (firmas.inc)
            throw new \Exception("Archivo muy pequeño para analizar");
        }

        // ====================================================================
        // MEJORA 1: LECTURA OPTIMIZADA DE 1024 BYTES (AHORRO DE MEMORIA)
        // ====================================================================
        $bytesALeer = min($tamanioReal, 1024);
        $manejador = fopen($ruta, "rb");
        if (!$manejador) {
            throw new \Exception("No se pudo abrir el archivo para lectura.");
        }
        $cabecera = fread($manejador, $bytesALeer);
        fclose($manejador);

        if ($this->modoExe) {
            $rutaExe = realpath(__DIR__ . '/../../engine/motor_firmas.exe');
            $output = shell_exec(escapeshellarg($rutaExe) . ' ' . escapeshellarg($ruta));
            $resultado = intval(trim($output));
        } else {
            // Usamos un buffer de C para evitar problemas de punteros en x64
            // y solo pasamos los 1024 bytes leídos
            $buffer = FFI::new("unsigned char[$bytesALeer]", false);
            FFI::memcpy($buffer, $cabecera, $bytesALeer);

            $resultado = $this->ffi->AnalizarFirma($buffer, $bytesALeer);
            unset($buffer);
        }

        // ====================================================================
        // MEJORA 2: VALIDACIÓN EXTRA PARA ARCHIVOS OFFICE DENTRO DE ZIP
        // ====================================================================
        if ($resultado === 6) { // Si el motor detectó "ZIP"
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($ruta) === TRUE) {
                    // Verificamos si contiene estructuras XML propias de Office
                    if (
                        $zip->locateName('xl/workbook.xml') !== false ||
                        $zip->locateName('word/document.xml') !== false ||
                        $zip->locateName('ppt/presentation.xml') !== false
                    ) {

                        $resultado = 7; // Reasignamos al código DOCX/XLSX/PPTX
                        error_log("ZipArchive detectó estructura de Office en $ruta. Reasignando a Tipo 7.");
                    }
                    $zip->close();
                }
            } else {
                error_log("Advertencia: ZipArchive no está habilitado en PHP.");
            }
        }

        error_log("Análisis de $ruta → Tipo Final: $resultado");

        if ($resultado <= 0) {
            $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
            $mapa = self::getMapaExtensiones();
            error_log("Archivo no reconocido por la DLL, intentando por extensión: $extension → " . ($mapa[$extension] ?? 'desconocido'));
            return $mapa[$extension] ?? 0;
        }

        return $resultado;
    }

    public function obtenerNombreTipo($tipo)
    {
        return self::$TIPOS[$tipo]['nombre'] ?? self::$TIPOS[0]['nombre'];
    }

    public function verificarTipoEspecifico($ruta, $tipo)
    {
        // En lugar de hacer una nueva lectura de archivo y llamar a VerificarFirmaEspecifica en la DLL,
        // reutilizamos analizarArchivo() para asegurarnos de que la lectura optimizada de 1024 bytes
        // y la corrección de los archivos Office (ZIP a 7) se aplique siempre correctamente.
        return ($this->analizarArchivo($ruta) === $tipo) ? 1 : 0;
    }

    public function version()
    {
        if ($this->modoExe) return 0x00010000;
        return $this->ffi->ObtenerVersionLibreria();
    }

    public function totalTipos()
    {
        if ($this->modoExe) return count(self::$TIPOS) - 1;
        return $this->ffi->ObtenerTotalTiposSoportados();
    }
}