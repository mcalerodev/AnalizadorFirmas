; ============================================================================
; motor_firmas.asm - Motor de Análisis de Firmas de Archivos
; ============================================================================
; Este módulo implementa las funciones principales para detectar el tipo
; de archivo basándose en sus firmas mágicas (magic numbers).
;
; Autor: Proyecto Analizador de Firmas
; Fecha: Abril 2026
; Plataforma: Windows x86 (32-bit)
; ============================================================================

.386
.MODEL FLAT, STDCALL
OPTION CASEMAP:NONE

; ----------------------------------------------------------------------------
; Includes
; ----------------------------------------------------------------------------
INCLUDE firmas.inc

; ----------------------------------------------------------------------------
; Prototipos de Windows API (para DLL)
; ----------------------------------------------------------------------------
; No se necesitan APIs de Windows para las funciones de análisis puro

; ============================================================================
; SECCIÓN DE CÓDIGO
; ============================================================================
.CODE

; ============================================================================
; DllMain - Punto de entrada de la DLL
; ============================================================================
; Parámetros:
;   hInstance   - Handle de la instancia de la DLL
;   fdwReason   - Razón de la llamada
;   lpReserved  - Reservado
; Retorno:
;   EAX = TRUE (1) si éxito
; ============================================================================
DllMain PROC hInstance:DWORD, fdwReason:DWORD, lpReserved:DWORD
    mov     eax, 1                  ; Retornar TRUE
    ret
DllMain ENDP

; ============================================================================
; AnalizarFirma - Función principal de análisis de firma
; ============================================================================
; Descripción:
;   Analiza los primeros bytes de un buffer para determinar el tipo de archivo.
;
; Parámetros:
;   pBuffer     - Puntero al buffer con los datos del archivo (mínimo 16 bytes)
;   dwTamanio   - Tamaño del buffer en bytes
;
; Retorno:
;   EAX = Código del tipo de archivo (ver firmas.inc para códigos)
;         0 = Tipo desconocido
;         Valores negativos = Código de error
;
; Registros modificados: EAX, ECX, EDX (preserva EBX, ESI, EDI, EBP)
; ============================================================================
AnalizarFirma PROC USES ebx esi edi, pBuffer:DWORD, dwTamanio:DWORD
    LOCAL resultado:DWORD
    
    ; Inicializar resultado como desconocido
    mov     resultado, TIPO_DESCONOCIDO
    
    ; Validar parámetros
    mov     eax, pBuffer
    test    eax, eax
    jz      @ErrorBufferNulo
    
    mov     eax, dwTamanio
    cmp     eax, MIN_BYTES_FIRMA
    jb      @ErrorTamanio
    
    ; Cargar puntero al buffer
    mov     esi, pBuffer
    
    ; ========================================================================
    ; VERIFICACIÓN DE FIRMAS (ordenadas por frecuencia de uso)
    ; ========================================================================
    
    ; --- Verificar JPEG (FF D8 FF) ---
    movzx   eax, BYTE PTR [esi]
    cmp     al, 0FFh
    jne     @NoJPEG
    movzx   eax, BYTE PTR [esi+1]
    cmp     al, 0D8h
    jne     @NoJPEG
    movzx   eax, BYTE PTR [esi+2]
    cmp     al, 0FFh
    jne     @NoJPEG
    mov     resultado, TIPO_JPEG
    jmp     @Fin
    
@NoJPEG:
    ; --- Verificar PNG (89 50 4E 47 0D 0A 1A 0A) ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 474E5089h          ; Primeros 4 bytes de PNG (little endian)
    jne     @NoPNG
    mov     eax, DWORD PTR [esi+4]
    cmp     eax, 0A1A0A0Dh          ; Siguientes 4 bytes de PNG
    jne     @NoPNG
    mov     resultado, TIPO_PNG
    jmp     @Fin
    
@NoPNG:
    ; --- Verificar PDF (%PDF = 25 50 44 46) ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 46444025h          ; "%PDF" en little endian
    jne     @NoPDF
    mov     resultado, TIPO_PDF
    jmp     @Fin
    
@NoPDF:
    ; --- Verificar ZIP/DOCX/XLSX/PPTX (50 4B 03 04 o variantes) ---
    movzx   eax, WORD PTR [esi]
    cmp     ax, 4B50h               ; "PK" en little endian
    jne     @NoZIP
    ; Es un archivo PK, verificar si es ZIP normal o vacío
    movzx   eax, WORD PTR [esi+2]
    cmp     ax, 0403h               ; ZIP normal
    je      @EsZIP
    cmp     ax, 0605h               ; ZIP vacío
    je      @EsZIP
    cmp     ax, 0807h               ; ZIP spanned
    je      @EsZIP
    jmp     @NoZIP
    
@EsZIP:
    ; Podría ser DOCX/XLSX/PPTX (necesitaría verificar contenido interno)
    ; Por ahora, marcamos como ZIP
    mov     resultado, TIPO_ZIP
    jmp     @Fin
    
@NoZIP:
    ; --- Verificar GIF (47 49 46 38) ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 38464947h          ; "GIF8" en little endian
    jne     @NoGIF
    mov     resultado, TIPO_GIF
    jmp     @Fin
    
@NoGIF:
    ; --- Verificar BMP (42 4D = "BM") ---
    movzx   eax, WORD PTR [esi]
    cmp     ax, 4D42h               ; "BM" en little endian
    jne     @NoBMP
    mov     resultado, TIPO_BMP
    jmp     @Fin
    
@NoBMP:
    ; --- Verificar EXE/PE (4D 5A = "MZ") ---
    movzx   eax, WORD PTR [esi]
    cmp     ax, 5A4Dh               ; "MZ" en little endian
    jne     @NoEXE
    mov     resultado, TIPO_EXE
    jmp     @Fin
    
@NoEXE:
    ; --- Verificar ELF (7F 45 4C 46 = ".ELF") ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 464C457Fh          ; ".ELF" en little endian
    jne     @NoELF
    mov     resultado, TIPO_ELF
    jmp     @Fin
    
@NoELF:
    ; --- Verificar MP3 con ID3 (49 44 33 = "ID3") ---
    movzx   eax, BYTE PTR [esi]
    cmp     al, 49h                 ; 'I'
    jne     @NoID3
    movzx   eax, BYTE PTR [esi+1]
    cmp     al, 44h                 ; 'D'
    jne     @NoID3
    movzx   eax, BYTE PTR [esi+2]
    cmp     al, 33h                 ; '3'
    jne     @NoID3
    mov     resultado, TIPO_MP3
    jmp     @Fin
    
@NoID3:
    ; --- Verificar MP3 Frame Sync (FF FB, FF FA, FF F3, FF F2) ---
    movzx   eax, BYTE PTR [esi]
    cmp     al, 0FFh
    jne     @NoMP3Sync
    movzx   eax, BYTE PTR [esi+1]
    cmp     al, 0FBh
    je      @EsMP3
    cmp     al, 0FAh
    je      @EsMP3
    cmp     al, 0F3h
    je      @EsMP3
    cmp     al, 0F2h
    je      @EsMP3
    jmp     @NoMP3Sync
    
@EsMP3:
    mov     resultado, TIPO_MP3
    jmp     @Fin
    
@NoMP3Sync:
    ; --- Verificar RIFF (52 49 46 46 = "RIFF") para WAV/AVI/WebP ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 46464952h          ; "RIFF" en little endian
    jne     @NoRIFF
    
    ; Verificar tamaño suficiente para leer offset 8
    cmp     dwTamanio, 12
    jb      @RIFFDesconocido
    
    ; Verificar subtipo en offset 8
    mov     eax, DWORD PTR [esi+8]
    cmp     eax, 45564157h          ; "WAVE" en little endian
    je      @EsWAV
    cmp     eax, 20495641h          ; "AVI " en little endian
    je      @EsAVI
    cmp     eax, 50424557h          ; "WEBP" en little endian
    je      @EsWEBP
    jmp     @RIFFDesconocido
    
@EsWAV:
    mov     resultado, TIPO_WAV
    jmp     @Fin
    
@EsAVI:
    mov     resultado, TIPO_AVI
    jmp     @Fin
    
@EsWEBP:
    mov     resultado, TIPO_WEBP
    jmp     @Fin
    
@RIFFDesconocido:
    ; RIFF pero subtipo desconocido
    mov     resultado, TIPO_DESCONOCIDO
    jmp     @Fin
    
@NoRIFF:
    ; --- Verificar MP4/MOV (ftyp en offset 4) ---
    cmp     dwTamanio, 8
    jb      @NoMP4
    mov     eax, DWORD PTR [esi+4]
    cmp     eax, 70797466h          ; "ftyp" en little endian
    jne     @NoMP4
    mov     resultado, TIPO_MP4
    jmp     @Fin
    
@NoMP4:
    ; --- Verificar RAR (52 61 72 21 1A 07 = "Rar!..") ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 21726152h          ; "Rar!" en little endian
    jne     @NoRAR
    movzx   eax, WORD PTR [esi+4]
    cmp     ax, 071Ah               ; Siguientes 2 bytes
    jne     @NoRAR
    mov     resultado, TIPO_RAR
    jmp     @Fin
    
@NoRAR:
    ; --- Verificar 7-Zip (37 7A BC AF 27 1C) ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 0AFBC7A37h         ; Primeros 4 bytes de 7z
    jne     @No7Z
    movzx   eax, WORD PTR [esi+4]
    cmp     ax, 1C27h
    jne     @No7Z
    mov     resultado, TIPO_7Z
    jmp     @Fin
    
@No7Z:
    ; --- Verificar ICO (00 00 01 00) ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 00010000h
    jne     @NoICO
    mov     resultado, TIPO_ICO
    jmp     @Fin
    
@NoICO:
    ; --- Verificar GZIP (1F 8B) ---
    movzx   eax, WORD PTR [esi]
    cmp     ax, 8B1Fh
    jne     @NoGZIP
    mov     resultado, TIPO_GZIP
    jmp     @Fin
    
@NoGZIP:
    ; --- Verificar Java Class (CA FE BA BE) ---
    mov     eax, DWORD PTR [esi]
    cmp     eax, 0BEBAFECAh         ; "CAFEBABE" en little endian
    jne     @NoClass
    mov     resultado, TIPO_CLASS
    jmp     @Fin
    
@NoClass:
    ; --- Verificar TAR (ustar en offset 257) ---
    cmp     dwTamanio, 262          ; Necesitamos al menos 262 bytes
    jb      @NoTAR
    mov     eax, DWORD PTR [esi+257]
    cmp     eax, 61747375h          ; "usta" en little endian
    jne     @NoTAR
    movzx   eax, BYTE PTR [esi+261]
    cmp     al, 72h                 ; 'r'
    jne     @NoTAR
    mov     resultado, TIPO_TAR
    jmp     @Fin
    
@NoTAR:
    ; Tipo no reconocido
    mov     resultado, TIPO_DESCONOCIDO
    jmp     @Fin
    
@ErrorBufferNulo:
    mov     resultado, ERROR_BUFFER_NULO
    jmp     @Fin
    
@ErrorTamanio:
    mov     resultado, ERROR_TAMANIO_INV
    
@Fin:
    mov     eax, resultado
    ret
AnalizarFirma ENDP

; ============================================================================
; ObtenerNombreTipo - Obtiene el nombre descriptivo del tipo de archivo
; ============================================================================
; Descripción:
;   Devuelve un puntero a una cadena con el nombre del tipo de archivo.
;
; Parámetros:
;   dwTipo      - Código del tipo de archivo (retornado por AnalizarFirma)
;
; Retorno:
;   EAX = Puntero a cadena ASCIIZ con el nombre del tipo
;         NULL si el código es inválido
;
; Registros modificados: EAX
; ============================================================================
ObtenerNombreTipo PROC dwTipo:DWORD
    ; Validar rango del tipo
    mov     eax, dwTipo
    cmp     eax, 0
    jl      @TipoInvalido           ; Códigos de error son negativos
    cmp     eax, MAX_FIRMAS
    jae     @TipoInvalido
    
    ; Calcular offset en la tabla de nombres
    shl     eax, 2                  ; Multiplicar por 4 (tamaño de DWORD)
    add     eax, OFFSET TablaNombresTipos
    mov     eax, [eax]              ; Obtener puntero al nombre
    ret
    
@TipoInvalido:
    xor     eax, eax                ; Retornar NULL
    ret
ObtenerNombreTipo ENDP

; ============================================================================
; VerificarFirmaEspecifica - Verifica si el archivo coincide con un tipo específico
; ============================================================================
; Descripción:
;   Verifica si los datos del buffer coinciden con un tipo de archivo específico.
;
; Parámetros:
;   pBuffer     - Puntero al buffer con los datos
;   dwTamanio   - Tamaño del buffer
;   dwTipo      - Tipo de archivo a verificar
;
; Retorno:
;   EAX = 1 si coincide, 0 si no coincide
;         Valores negativos = Código de error
;
; Registros modificados: EAX
; ============================================================================
VerificarFirmaEspecifica PROC pBuffer:DWORD, dwTamanio:DWORD, dwTipo:DWORD
    LOCAL tipoDetectado:DWORD
    
    ; Usar AnalizarFirma para detectar el tipo
    INVOKE  AnalizarFirma, pBuffer, dwTamanio
    
    ; Verificar errores
    test    eax, eax
    js      @RetornarError          ; Si es negativo, es un error
    
    ; Comparar con el tipo solicitado
    mov     tipoDetectado, eax
    mov     eax, dwTipo
    cmp     eax, tipoDetectado
    jne     @NoCoincide
    
    ; Coincide
    mov     eax, 1
    ret
    
@NoCoincide:
    xor     eax, eax                ; Retornar 0
    ret
    
@RetornarError:
    ; EAX ya contiene el código de error
    ret
VerificarFirmaEspecifica ENDP

; ============================================================================
; ObtenerVersionLibreria - Obtiene la versión de la librería
; ============================================================================
; Descripción:
;   Devuelve el número de versión de la librería.
;
; Parámetros: Ninguno
;
; Retorno:
;   EAX = Número de versión (formato: 0xMMMMmmmm donde MMMM=mayor, mmmm=menor)
;         Ejemplo: 0x00010000 = versión 1.0
;
; Registros modificados: EAX
; ============================================================================
ObtenerVersionLibreria PROC
    mov     eax, 00010000h          ; Versión 1.0
    ret
ObtenerVersionLibreria ENDP

; ============================================================================
; ObtenerTotalTiposSoportados - Obtiene el número total de tipos soportados
; ============================================================================
; Descripción:
;   Devuelve la cantidad de tipos de archivo que la librería puede detectar.
;
; Parámetros: Ninguno
;
; Retorno:
;   EAX = Número de tipos soportados
;
; Registros modificados: EAX
; ============================================================================
ObtenerTotalTiposSoportados PROC
    mov     eax, MAX_FIRMAS
    ret
ObtenerTotalTiposSoportados ENDP

; ============================================================================
; CompararBytes - Función auxiliar para comparar bytes
; ============================================================================
; Descripción:
;   Compara dos secuencias de bytes.
;
; Parámetros:
;   pBuffer1    - Puntero al primer buffer
;   pBuffer2    - Puntero al segundo buffer
;   dwLongitud  - Número de bytes a comparar
;
; Retorno:
;   EAX = 1 si son iguales, 0 si son diferentes
;
; Registros modificados: EAX, ECX
; ============================================================================
CompararBytes PROC USES esi edi, pBuffer1:DWORD, pBuffer2:DWORD, dwLongitud:DWORD
    mov     esi, pBuffer1
    mov     edi, pBuffer2
    mov     ecx, dwLongitud
    
    ; Validar parámetros
    test    esi, esi
    jz      @Diferentes
    test    edi, edi
    jz      @Diferentes
    test    ecx, ecx
    jz      @Iguales                ; 0 bytes = iguales
    
    ; Comparar byte por byte
    cld
    repe    cmpsb
    jne     @Diferentes
    
@Iguales:
    mov     eax, 1
    ret
    
@Diferentes:
    xor     eax, eax
    ret
CompararBytes ENDP

; ============================================================================
; FIN DEL MÓDULO
; ============================================================================
END
