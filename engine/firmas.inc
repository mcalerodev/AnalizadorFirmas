; ============================================================================
; firmas.inc - Definición de Firmas Mágicas de Archivos
; ============================================================================
; Este archivo contiene las firmas (magic numbers) de diferentes tipos de
; archivos para su detección automática.
;
; Autor: Proyecto Analizador de Firmas
; Fecha: Abril 2026
; ============================================================================

; ----------------------------------------------------------------------------
; Códigos de tipo de archivo (valores de retorno)
; ----------------------------------------------------------------------------
TIPO_DESCONOCIDO    EQU 0       ; Tipo de archivo no reconocido
TIPO_JPEG           EQU 1       ; Imagen JPEG/JPG
TIPO_PNG            EQU 2       ; Imagen PNG
TIPO_GIF            EQU 3       ; Imagen GIF
TIPO_BMP            EQU 4       ; Imagen BMP
TIPO_PDF            EQU 5       ; Documento PDF
TIPO_ZIP            EQU 6       ; Archivo ZIP
TIPO_DOCX           EQU 7       ; Documento Word (DOCX/XLSX/PPTX - basado en ZIP)
TIPO_EXE            EQU 8       ; Ejecutable Windows PE
TIPO_ELF            EQU 9       ; Ejecutable Linux ELF
TIPO_MP3            EQU 10      ; Audio MP3
TIPO_MP4            EQU 11      ; Video MP4
TIPO_RAR            EQU 12      ; Archivo RAR
TIPO_7Z             EQU 13      ; Archivo 7-Zip
TIPO_WAV            EQU 14      ; Audio WAV
TIPO_AVI            EQU 15      ; Video AVI
TIPO_WEBP           EQU 16      ; Imagen WebP
TIPO_ICO            EQU 17      ; Icono Windows
TIPO_TAR            EQU 18      ; Archivo TAR
TIPO_GZIP           EQU 19      ; Archivo GZip
TIPO_CLASS          EQU 20      ; Java Class

; ----------------------------------------------------------------------------
; Códigos de error
; ----------------------------------------------------------------------------
ERROR_NINGUNO       EQU 0       ; Sin error
ERROR_BUFFER_NULO   EQU -1      ; Buffer es NULL
ERROR_TAMANIO_INV   EQU -2      ; Tamaño inválido (muy pequeño)
ERROR_INTERNO       EQU -3      ; Error interno

; ----------------------------------------------------------------------------
; Constantes de tamaño
; ----------------------------------------------------------------------------
MIN_BYTES_FIRMA     EQU 16      ; Mínimo de bytes necesarios para análisis
MAX_FIRMAS          EQU 21      ; Número total de tipos de firma

; ----------------------------------------------------------------------------
; Estructura de firma de archivo
; Cada firma tiene: longitud (1 byte) + offset (1 byte) + bytes de firma
; ----------------------------------------------------------------------------

.DATA

; Nombres de tipos de archivo (para función de obtener nombre)
szDesconocido       BYTE "DESCONOCIDO", 0
szJPEG              BYTE "JPEG", 0
szPNG               BYTE "PNG", 0
szGIF               BYTE "GIF", 0
szBMP               BYTE "BMP", 0
szPDF               BYTE "PDF", 0
szZIP               BYTE "ZIP", 0
szDOCX              BYTE "DOCX/XLSX/PPTX", 0
szEXE               BYTE "EXE (PE)", 0
szELF               BYTE "ELF", 0
szMP3               BYTE "MP3", 0
szMP4               BYTE "MP4", 0
szRAR               BYTE "RAR", 0
sz7Z                BYTE "7Z", 0
szWAV               BYTE "WAV", 0
szAVI               BYTE "AVI", 0
szWEBP              BYTE "WEBP", 0
szICO               BYTE "ICO", 0
szTAR               BYTE "TAR", 0
szGZIP              BYTE "GZIP", 0
szCLASS             BYTE "JAVA CLASS", 0

; Tabla de punteros a nombres de tipos
TablaNombresTipos   DWORD OFFSET szDesconocido      ; 0
                    DWORD OFFSET szJPEG             ; 1
                    DWORD OFFSET szPNG              ; 2
                    DWORD OFFSET szGIF              ; 3
                    DWORD OFFSET szBMP              ; 4
                    DWORD OFFSET szPDF              ; 5
                    DWORD OFFSET szZIP              ; 6
                    DWORD OFFSET szDOCX             ; 7
                    DWORD OFFSET szEXE              ; 8
                    DWORD OFFSET szELF              ; 9
                    DWORD OFFSET szMP3              ; 10
                    DWORD OFFSET szMP4              ; 11
                    DWORD OFFSET szRAR              ; 12
                    DWORD OFFSET sz7Z               ; 13
                    DWORD OFFSET szWAV              ; 14
                    DWORD OFFSET szAVI              ; 15
                    DWORD OFFSET szWEBP             ; 16
                    DWORD OFFSET szICO              ; 17
                    DWORD OFFSET szTAR              ; 18
                    DWORD OFFSET szGZIP             ; 19
                    DWORD OFFSET szCLASS            ; 20

; ============================================================================
; FIRMAS MÁGICAS DE ARCHIVOS
; Formato: longitud de firma, offset desde inicio, bytes de firma...
; ============================================================================

; JPEG: FF D8 FF (primeros 3 bytes)
FIRMA_JPEG          BYTE 3, 0, 0FFh, 0D8h, 0FFh

; PNG: 89 50 4E 47 0D 0A 1A 0A (8 bytes)
FIRMA_PNG           BYTE 8, 0, 89h, 50h, 4Eh, 47h, 0Dh, 0Ah, 1Ah, 0Ah

; GIF87a: 47 49 46 38 37 61
FIRMA_GIF87         BYTE 6, 0, 47h, 49h, 46h, 38h, 37h, 61h

; GIF89a: 47 49 46 38 39 61
FIRMA_GIF89         BYTE 6, 0, 47h, 49h, 46h, 38h, 39h, 61h

; BMP: 42 4D (BM)
FIRMA_BMP           BYTE 2, 0, 42h, 4Dh

; PDF: 25 50 44 46 (%PDF)
FIRMA_PDF           BYTE 4, 0, 25h, 50h, 44h, 46h

; ZIP/DOCX/XLSX/PPTX: 50 4B 03 04 (PK..)
FIRMA_ZIP           BYTE 4, 0, 50h, 4Bh, 03h, 04h

; ZIP vacío: 50 4B 05 06
FIRMA_ZIP_EMPTY     BYTE 4, 0, 50h, 4Bh, 05h, 06h

; ZIP Spanned: 50 4B 07 08
FIRMA_ZIP_SPAN      BYTE 4, 0, 50h, 4Bh, 07h, 08h

; EXE (PE): 4D 5A (MZ)
FIRMA_EXE           BYTE 2, 0, 4Dh, 5Ah

; ELF: 7F 45 4C 46 (.ELF)
FIRMA_ELF           BYTE 4, 0, 7Fh, 45h, 4Ch, 46h

; MP3 con ID3v2: 49 44 33 (ID3)
FIRMA_MP3_ID3       BYTE 3, 0, 49h, 44h, 33h

; MP3 Frame sync: FF FB, FF FA, FF F3, FF F2
FIRMA_MP3_SYNC1     BYTE 2, 0, 0FFh, 0FBh
FIRMA_MP3_SYNC2     BYTE 2, 0, 0FFh, 0FAh
FIRMA_MP3_SYNC3     BYTE 2, 0, 0FFh, 0F3h
FIRMA_MP3_SYNC4     BYTE 2, 0, 0FFh, 0F2h

; MP4/M4A/MOV: ftyp en offset 4
FIRMA_MP4           BYTE 4, 4, 66h, 74h, 79h, 70h  ; "ftyp" en offset 4

; RAR 1.5+: 52 61 72 21 1A 07 00 (Rar!...)
FIRMA_RAR           BYTE 7, 0, 52h, 61h, 72h, 21h, 1Ah, 07h, 00h

; RAR 5.0+: 52 61 72 21 1A 07 01 00
FIRMA_RAR5          BYTE 8, 0, 52h, 61h, 72h, 21h, 1Ah, 07h, 01h, 00h

; 7-Zip: 37 7A BC AF 27 1C
FIRMA_7Z            BYTE 6, 0, 37h, 7Ah, 0BCh, 0AFh, 27h, 1Ch

; WAV: 52 49 46 46 ... 57 41 56 45 (RIFF....WAVE)
FIRMA_RIFF          BYTE 4, 0, 52h, 49h, 46h, 46h  ; "RIFF"
FIRMA_WAVE          BYTE 4, 8, 57h, 41h, 56h, 45h  ; "WAVE" en offset 8

; AVI: 52 49 46 46 ... 41 56 49 20 (RIFF....AVI )
FIRMA_AVI           BYTE 4, 8, 41h, 56h, 49h, 20h  ; "AVI " en offset 8

; WebP: 52 49 46 46 ... 57 45 42 50 (RIFF....WEBP)
FIRMA_WEBP          BYTE 4, 8, 57h, 45h, 42h, 50h  ; "WEBP" en offset 8

; ICO: 00 00 01 00
FIRMA_ICO           BYTE 4, 0, 00h, 00h, 01h, 00h

; TAR (ustar magic en offset 257)
; Nota: El offset 257 requiere WORD en lugar de BYTE
FIRMA_TAR           BYTE 5
                    WORD 257           ; offset
                    BYTE 75h, 73h, 74h, 61h, 72h  ; "ustar"

; GZIP: 1F 8B
FIRMA_GZIP          BYTE 2, 0, 1Fh, 8Bh

; Java Class: CA FE BA BE
FIRMA_CLASS         BYTE 4, 0, 0CAh, 0FEh, 0BAh, 0BEh

; ============================================================================
; FIN DE DEFINICIONES DE FIRMAS
; ============================================================================
