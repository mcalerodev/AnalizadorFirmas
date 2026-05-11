# Guía de Arquitectura  
Analizador de Firmas de Archivos

---

## 1. Descripción General

El sistema "Analizador de Firmas de Archivos" es una aplicación web diseñada para verificar la autenticidad de los archivos mediante el análisis de sus firmas binarias.

A diferencia de los métodos tradicionales basados en extensiones, este sistema analiza el contenido real del archivo para determinar su tipo verdadero, permitiendo detectar archivos maliciosos o disfrazados.

---

## 2. Arquitectura General del Sistema

El sistema sigue una arquitectura de tres capas claramente definidas:

- Frontend (Interfaz de usuario)
- Backend (Lógica de aplicación en PHP)
- Motor de análisis (Lenguaje ensamblador)

Esta separación permite modularidad, escalabilidad y facilidad de mantenimiento.

---

## 3. Flujo de Funcionamiento

1. El usuario accede a la interfaz web.
2. Selecciona un archivo desde su dispositivo.
3. El archivo es enviado al servidor mediante PHP.
4. El backend lee los primeros bytes del archivo.
5. Se invoca la librería en ensamblador (motor_firmas.dll) mediante FFI.
6. El motor compara los bytes con una base de firmas conocidas.
7. Se determina el tipo real del archivo.
8. El backend procesa el resultado.
9. La interfaz muestra al usuario:
   - Tipo declarado (extensión del archivo)
   - Tipo real detectado
   - Advertencia en caso de discrepancia

---

## 4. Componentes del Sistema

### 4.1 Frontend (Interfaz de Usuario)

Responsable de la interacción directa con el usuario.

Funciones principales:
- Permitir la selección y subida de archivos
- Mostrar información del archivo seleccionado
- Presentar los resultados del análisis
- Proporcionar retroalimentación visual al usuario

Características:
- Diseño visual basado en un tema oscuro
- Uso de componentes reutilizables como botones, tarjetas y alertas
- Interfaz centrada en una tarjeta principal para mejorar la claridad visual
- Uso de iconografía para facilitar la interpretación de la información

Se utilizan iconos para representar tipos de archivo, acciones y estados del sistema, mejorando la comprensión visual y la experiencia del usuario.

---

### 4.2 Backend (PHP)

Encargado de la lógica de negocio del sistema.

Funciones principales:
- Recepción y validación de archivos
- Lectura de los primeros bytes del archivo
- Comunicación con el motor de análisis
- Procesamiento de resultados
- Envío de respuestas al frontend

El backend actúa como intermediario entre la interfaz de usuario y el motor de análisis en ensamblador.

---

### 4.3 Motor de Análisis (Assembly)

Componente de alto rendimiento encargado del análisis de firmas.

Funciones:
- Comparar los bytes iniciales del archivo con firmas conocidas
- Identificar el tipo real del archivo
- Retornar un código o identificador del tipo detectado

Este componente permite realizar análisis rápidos y eficientes a bajo nivel.

---

## 5. Integración mediante FFI

El sistema utiliza FFI (Foreign Function Interface) para conectar PHP con la librería en ensamblador.

Esto permite:
- Invocar funciones nativas desde PHP
- Obtener resultados de alto rendimiento
- Mantener una separación clara entre la lógica de aplicación y el análisis de bajo nivel

---

## 6. Tipos de Archivo Soportados

El sistema reconoce múltiples tipos de archivo mediante el análisis de firmas binarias, incluyendo:

- Documentos (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT)
- Imágenes (JPG, PNG, GIF, BMP)
- Archivos comprimidos (ZIP, RAR, 7Z)
- Archivos multimedia (MP3, MP4)
- Archivos ejecutables (EXE, DLL)
- Archivos web (HTML, XML)

Además, se contempla un tipo adicional para archivos desconocidos cuando no coinciden con ninguna firma registrada.

Para cada tipo de archivo se ha definido un icono representativo, seleccionado de manera consistente en estilo, tamaño y color, con el objetivo de mantener uniformidad visual en la interfaz.

---

## 7. Diseño de Interfaz de Usuario

El diseño de la interfaz se basa en principios de claridad, consistencia y simplicidad.

Elementos principales:
- Fondo oscuro para reducir fatiga visual
- Tipografía clara y legible
- Uso de una tarjeta central para agrupar la información
- Botones diferenciados por color según su función:
  - Azul para acciones principales
  - Verde para acciones positivas
  - Rojo para acciones destructivas

Se ha definido un sistema de componentes reutilizables que incluye botones, tarjetas, mensajes y alertas.

---

## 8. Experiencia de Usuario

Se han aplicado principios de experiencia de usuario para mejorar la interacción:

- Mensajes claros y comprensibles para el usuario
- Retroalimentación inmediata después de cada acción
- Indicadores visuales de estado (éxito, error, advertencia)
- Uso de iconos para reforzar la información textual

Ejemplo de mejora aplicada:
En lugar de mostrar mensajes técnicos, se utilizan mensajes descriptivos como:
“El archivo no coincide con su extensión y puede ser peligroso”.

---

## 9. Accesibilidad

El sistema considera buenas prácticas de accesibilidad:

- Contraste adecuado entre fondo y texto
- Uso de etiquetas en elementos de formulario
- Indicadores visuales de enfoque para navegación con teclado
- Uso combinado de iconos y texto para facilitar la comprensión

Estas medidas permiten que el sistema sea más usable para diferentes tipos de usuarios.

---

## 10. Seguridad

El sistema incorpora medidas básicas de seguridad:

- Validación de archivos en el servidor
- No ejecución de archivos subidos por el usuario
- Identificación de archivos potencialmente maliciosos
- Detección de discrepancias entre extensión y contenido

Esto permite reducir riesgos asociados a archivos engañosos o peligrosos.

---

## 11. Diagrama de Flujo Simplificado

Usuario → Interfaz Web → Backend (PHP) → Motor de Análisis (Assembly) → Resultado → Usuario

---

## 12. Conclusión

La arquitectura del sistema permite una separación clara de responsabilidades, combinando la eficiencia del lenguaje ensamblador con la accesibilidad de una aplicación web.

El uso de una interfaz intuitiva, junto con un motor de análisis eficiente, proporciona una solución práctica para la detección de archivos sospechosos y mejora la seguridad en el manejo de archivos.