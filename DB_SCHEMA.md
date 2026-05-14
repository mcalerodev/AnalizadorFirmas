# DB_SCHEMA.md

# Documentación de Base de Datos
## Proyecto: Analizador de Firmas de Archivos

---

## Descripción General

La base de datos `analizador_firmas` fue diseñada para almacenar información relacionada con el análisis de firmas digitales de archivos.

El sistema permite registrar usuarios, almacenar archivos analizados y mantener un historial de auditoría de acciones realizadas dentro de la aplicación.

La base de datos está compuesta por las siguientes tablas:

- `usuario`
- `archivos_analizados`
- `auditoria`

---

# Estructura de la Base de Datos

## Base de Datos

```sql
CREATE DATABASE IF NOT EXISTS analizador_firmas;
USE analizador_firmas;
```

---

# Tabla: usuario

## Descripción

La tabla `usuario` almacena la información de autenticación de los usuarios registrados en el sistema.

## Estructura

| Campo | Tipo de Dato | Restricciones | Descripción |
|-------|---------------|----------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Identificador único del usuario |
| correo | VARCHAR(100) | NOT NULL, UNIQUE | Correo electrónico del usuario |
| clave | VARCHAR(255) | NOT NULL | Contraseña cifrada del usuario |

## Script SQL

```sql
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL
);
```

---

# Tabla: archivos_analizados

## Descripción

La tabla `archivos_analizados` almacena la información de los archivos procesados por el sistema de análisis de firmas.

Cada registro contiene información relacionada con:
- nombre original del archivo
- tipo detectado
- hash MD5
- ubicación física del archivo
- fecha de carga
- tamaño
- usuario asociado

## Estructura

| Campo | Tipo de Dato | Restricciones | Descripción |
|-------|---------------|----------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Identificador único del archivo |
| nombre_original | VARCHAR(255) | NOT NULL | Nombre original del archivo cargado |
| tipo_detectado | VARCHAR(50) | NOT NULL | Tipo de archivo detectado por el motor |
| hash_md5 | CHAR(32) | NOT NULL | Hash MD5 generado para el archivo |
| ruta | VARCHAR(255) | NOT NULL | Ruta de almacenamiento del archivo |
| fecha_subida | DATETIME | DEFAULT CURRENT_TIMESTAMP | Fecha y hora de carga |
| usuario_id | INT | FOREIGN KEY, NULL | Usuario asociado al archivo |
| tamaño | INT | NOT NULL | Tamaño del archivo en bytes |

## Relaciones

| Tabla Relacionada | Tipo de Relación |
|-------------------|------------------|
| usuario | Muchos a Uno (N:1) |

## Clave Foránea

```sql
FOREIGN KEY (usuario_id) REFERENCES usuario(id)
```

## Script SQL

```sql
CREATE TABLE archivos_analizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_original VARCHAR(255) NOT NULL,
    tipo_detectado VARCHAR(50) NOT NULL,
    hash_md5 CHAR(32) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NULL,
    tamaño INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);
```

---

# Tabla: auditoria

## Descripción

La tabla `auditoria` registra las acciones realizadas dentro del sistema con fines de monitoreo y seguimiento.

Permite almacenar:
- usuario responsable
- acción ejecutada
- archivo relacionado
- descripción adicional
- fecha del evento

## Estructura

| Campo | Tipo de Dato | Restricciones | Descripción |
|-------|---------------|----------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Identificador del registro |
| usuario | VARCHAR(100) | DEFAULT 'sistema' | Usuario que ejecutó la acción |
| accion | VARCHAR(100) | NOT NULL | Acción realizada |
| archivo_id | INT | NULL | Archivo relacionado |
| descripcion | TEXT | NULL | Descripción detallada |
| fecha | DATETIME | DEFAULT CURRENT_TIMESTAMP | Fecha y hora del evento |

## Script SQL

```sql
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) DEFAULT 'sistema',
    accion VARCHAR(100) NOT NULL,
    archivo_id INT NULL,
    descripcion TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

# Índices

## Descripción

Se implementaron índices para optimizar búsquedas frecuentes y mejorar el rendimiento de consultas relacionadas con:
- tipo de archivo
- fecha de subida
- usuario asociado
- hash MD5

## Script SQL

```sql
CREATE INDEX idx_tipo
ON archivos_analizados(tipo_detectado);

CREATE INDEX idx_fecha
ON archivos_analizados(fecha_subida);

CREATE INDEX idx_usuario
ON archivos_analizados(usuario_id);

CREATE INDEX idx_hash
ON archivos_analizados(hash_md5);
```

---

# Relaciones del Sistema

| Tabla Principal | Tabla Relacionada | Relación |
|-----------------|-------------------|-----------|
| usuario | archivos_analizados | 1:N |

---

# Observaciones

- La contraseña del usuario se almacena utilizando un campo `VARCHAR(255)` para permitir el uso de algoritmos de cifrado seguros.
- El hash MD5 permite identificar archivos duplicados o verificar integridad.
- La tabla `auditoria` facilita el monitoreo de actividades dentro del sistema.
- Los índices mejoran el desempeño en consultas frecuentes realizadas por el sistema de análisis.

---