; ============================================================================
; motor_firmas.asm - Motor de Análisis de Firmas de Archivos (x64)
; ============================================================================
; Este módulo implementa las funciones principales para detectar el tipo
; de archivo basándose en sus firmas mágicas (magic numbers).
;
; Autor: Proyecto Analizador de Firmas
; Fecha: Abril 2026
; Plataforma: Windows x64 (64-bit)
; ============================================================================

OPTION CASEMAP:NONE

; ----------------------------------------------------------------------------
; Includes
; ----------------------------------------------------------------------------
INCLUDE firmas.inc

; ============================================================================
; SECCIÓN DE CÓDIGO
; ============================================================================
.CODE

; ============================================================================
; DllMain - Punto de entrada de la DLL
; ============================================================================
; Parámetros:
;   hInstance   - Handle de la instancia de la DLL (QWORD en 64-bit)
;   fdwReason   - Razón de la llamada (DWORD)
;   lpReserved  - Reservado (QWORD)
; Retorno:
;   EAX = TRUE (1) si éxito
; ============================================================================
DllMain PROC hInstance:QWORD, fdwReason:DWORD, lpReserved:QWORD
    mov     eax, 1                  ; Retornar TRUE
    ret
DllMain ENDP

; ============================================================================
; AnalizarFirma - Función principal de análisis de firma
; ============================================================================
; Parámetros:
;   pBuffer     - Puntero al buffer con datos (mínimo 16 bytes) -> AHORA ES QWORD
;   dwTamanio   - Tamaño del buffer en bytes -> DWORD
; Retorno:
;   EAX = Código del tipo de archivo (ver firmas.inc para códigos)
; Registros modificados: RAX, RCX, RDX (preserva RBX, RSI, RDI, RBP)
; ============================================================================
AnalizarFirma PROC USES rbx rsi rdi, pBuffer:QWORD, dwTamanio:DWORD
    LOCAL resultado:DWORD
    
    ; Inicializar resultado como desconocido
    mov     resultado, TIPO_DESCONOCIDO
    
    ; Validar parámetros usando registros de 64 bits
    mov     rax, pBuffer
    test    rax, rax
    jz      @ErrorBufferNulo
    
    mov     eax, dwTamanio
    cmp     eax, MIN_BYTES_FIRMA
    jb      @ErrorTamanio
    
    ; Cargar puntero al buffer en registro de 64 bits (RSI)
    mov     rsi, pBuffer
    
    ; ========================================================================
    ; VERIFICACIÓN DE FIRMAS (ordenadas por frecuencia de uso)
    ; ========================================================================
    
    ; --- Verificar JPEG (FF D8 FF) ---
    movzx   eax, BYTE PTR [rsi]
    cmp     al, 0FFh
    jne     @NoJPEG
    movzx   eax, BYTE PTR [rsi+1]
    cmp     al, 0D8h
    jne     @NoJPEG
    movzx   eax, BYTE PTR [rsi+2]
    cmp     al, 0FFh
    jne     @NoJPEG
    mov     resultado, TIPO_JPEG
    jmp     @Fin
    
@NoJPEG:
    ; --- Verificar PNG (89 50 4E 47 0D 0A 1A 0A) ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 474E5089h          
    jne     @NoPNG
    mov     eax, DWORD PTR [rsi+4]
    cmp     eax, 0A1A0A0Dh          
    jne     @NoPNG
    mov     resultado, TIPO_PNG
    jmp     @Fin
    
@NoPNG:
    ; --- Verificar PDF (%PDF = 25 50 44 46) ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 46444025h          
    jne     @NoPDF
    mov     resultado, TIPO_PDF
    jmp     @Fin
    
@NoPDF:
    ; --- Verificar ZIP/DOCX/XLSX/PPTX ---
    movzx   eax, WORD PTR [rsi]
    cmp     ax, 4B50h               
    jne     @NoZIP
    movzx   eax, WORD PTR [rsi+2]
    cmp     ax, 0403h               
    je      @EsZIP
    cmp     ax, 0605h               
    je      @EsZIP
    cmp     ax, 0807h               
    je      @EsZIP
    jmp     @NoZIP
    
@EsZIP:
    mov     resultado, TIPO_ZIP
    jmp     @Fin
    
@NoZIP:
    ; --- Verificar GIF ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 38464947h          
    jne     @NoGIF
    mov     resultado, TIPO_GIF
    jmp     @Fin
    
@NoGIF:
    ; --- Verificar BMP ---
    movzx   eax, WORD PTR [rsi]
    cmp     ax, 4D42h               
    jne     @NoBMP
    mov     resultado, TIPO_BMP
    jmp     @Fin
    
@NoBMP:
    ; --- Verificar EXE/PE ---
    movzx   eax, WORD PTR [rsi]
    cmp     ax, 5A4Dh               
    jne     @NoEXE
    mov     resultado, TIPO_EXE
    jmp     @Fin
    
@NoEXE:
    ; --- Verificar ELF ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 464C457Fh          
    jne     @NoELF
    mov     resultado, TIPO_ELF
    jmp     @Fin
    
@NoELF:
    ; --- Verificar MP3 con ID3 ---
    movzx   eax, BYTE PTR [rsi]
    cmp     al, 49h                 
    jne     @NoID3
    movzx   eax, BYTE PTR [rsi+1]
    cmp     al, 44h                 
    jne     @NoID3
    movzx   eax, BYTE PTR [rsi+2]
    cmp     al, 33h                 
    jne     @NoID3
    mov     resultado, TIPO_MP3
    jmp     @Fin
    
@NoID3:
    ; --- Verificar MP3 Frame Sync ---
    movzx   eax, BYTE PTR [rsi]
    cmp     al, 0FFh
    jne     @NoMP3Sync
    movzx   eax, BYTE PTR [rsi+1]
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
    ; --- Verificar RIFF ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 46464952h          
    jne     @NoRIFF
    
    cmp     dwTamanio, 12
    jb      @RIFFDesconocido
    
    mov     eax, DWORD PTR [rsi+8]
    cmp     eax, 45564157h          
    je      @EsWAV
    cmp     eax, 20495641h          
    je      @EsAVI
    cmp     eax, 50424557h          
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
    mov     resultado, TIPO_DESCONOCIDO
    jmp     @Fin
    
@NoRIFF:
    ; --- Verificar MP4/MOV ---
    cmp     dwTamanio, 8
    jb      @NoMP4
    mov     eax, DWORD PTR [rsi+4]
    cmp     eax, 70797466h          
    jne     @NoMP4
    mov     resultado, TIPO_MP4
    jmp     @Fin
    
@NoMP4:
    ; --- Verificar RAR ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 21726152h          
    jne     @NoRAR
    movzx   eax, WORD PTR [rsi+4]
    cmp     ax, 071Ah               
    jne     @NoRAR
    mov     resultado, TIPO_RAR
    jmp     @Fin
    
@NoRAR:
    ; --- Verificar 7-Zip ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 0AFBC7A37h         
    jne     @No7Z
    movzx   eax, WORD PTR [rsi+4]
    cmp     ax, 1C27h
    jne     @No7Z
    mov     resultado, TIPO_7Z
    jmp     @Fin
    
@No7Z:
    ; --- Verificar ICO ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 00010000h
    jne     @NoICO
    mov     resultado, TIPO_ICO
    jmp     @Fin
    
@NoICO:
    ; --- Verificar GZIP ---
    movzx   eax, WORD PTR [rsi]
    cmp     ax, 8B1Fh
    jne     @NoGZIP
    mov     resultado, TIPO_GZIP
    jmp     @Fin
    
@NoGZIP:
    ; --- Verificar Java Class ---
    mov     eax, DWORD PTR [rsi]
    cmp     eax, 0BEBAFECAh         
    jne     @NoClass
    mov     resultado, TIPO_CLASS
    jmp     @Fin
    
@NoClass:
    ; --- Verificar TAR ---
    cmp     dwTamanio, 262          
    jb      @NoTAR
    mov     eax, DWORD PTR [rsi+257]
    cmp     eax, 61747375h          
    jne     @NoTAR
    movzx   eax, BYTE PTR [rsi+261]
    cmp     al, 72h                 
    jne     @NoTAR
    mov     resultado, TIPO_TAR
    jmp     @Fin
    
@NoTAR:
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
; Retorno en x64: RAX = Puntero de 64 bits a la cadena ASCIIZ
; ============================================================================
ObtenerNombreTipo PROC dwTipo:DWORD
    ; Validar rango del tipo
    mov     eax, dwTipo
    cmp     eax, 0
    jl      @TipoInvalido           
    cmp     eax, MAX_FIRMAS
    jae     @TipoInvalido
    
    ; Extender EAX a RAX de 64 bits para matemáticas de punteros
    movsxd  rax, dwTipo
    
    ; Calcular offset en la tabla de nombres
    ; En x64 los punteros son de 8 bytes, así que multiplicamos por 8 (shl rax, 3)
    shl     rax, 3                  
    lea     rcx, TablaNombresTipos  ; Cargar dirección base en RCX
    add     rax, rcx                ; Sumar base + desplazamiento
    mov     rax, [rax]              ; Obtener el puntero QWORD final a la cadena
    ret
    
@TipoInvalido:
    xor     rax, rax                ; Retornar NULL (0) en RAX
    ret
ObtenerNombreTipo ENDP

; ============================================================================
; VerificarFirmaEspecifica - Verifica si coincide con un tipo específico
; NOTA: Reescrito sin INVOKE para usar la convención de llamadas FastCall x64
; ============================================================================
VerificarFirmaEspecifica PROC pBuffer:QWORD, dwTamanio:DWORD, dwTipo:DWORD
    LOCAL tipoDetectado:DWORD
    
    ; Preparar parámetros en registros requeridos por x64 para llamar a AnalizarFirma
    ; Param 1 (RCX) = pBuffer (QWORD)
    ; Param 2 (RDX) = dwTamanio (DWORD)
    mov     rcx, pBuffer
    mov     edx, dwTamanio
    
    ; Reservar "Shadow Space" (32 bytes) obligatorio en x64 antes de un CALL
    sub     rsp, 32
    call    AnalizarFirma
    ; Restaurar la pila
    add     rsp, 32
    
    ; Verificar errores
    test    eax, eax
    js      @RetornarError          
    
    ; Comparar con el tipo solicitado
    mov     tipoDetectado, eax
    mov     eax, dwTipo
    cmp     eax, tipoDetectado
    jne     @NoCoincide
    
    mov     eax, 1
    ret
    
@NoCoincide:
    xor     eax, eax                
    ret
    
@RetornarError:
    ret
VerificarFirmaEspecifica ENDP

; ============================================================================
; ObtenerVersionLibreria
; ============================================================================
ObtenerVersionLibreria PROC
    mov     eax, 00010000h          
    ret
ObtenerVersionLibreria ENDP

; ============================================================================
; ObtenerTotalTiposSoportados
; ============================================================================
ObtenerTotalTiposSoportados PROC
    mov     eax, MAX_FIRMAS
    ret
ObtenerTotalTiposSoportados ENDP

; ============================================================================
; CompararBytes - Función auxiliar para comparar bytes
; Parámetros son QWORD para soportar punteros de 64 bits
; ============================================================================
CompararBytes PROC USES rsi rdi, pBuffer1:QWORD, pBuffer2:QWORD, dwLongitud:DWORD
    mov     rsi, pBuffer1
    mov     rdi, pBuffer2
    mov     ecx, dwLongitud
    
    ; Validar parámetros de 64 bits
    test    rsi, rsi
    jz      @Diferentes
    test    rdi, rdi
    jz      @Diferentes
    test    ecx, ecx
    jz      @Iguales                
    
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
