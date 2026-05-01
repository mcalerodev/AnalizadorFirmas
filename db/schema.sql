CREATE DATABASE IF NOT EXISTS analizador_firmas;
USE analizador_firmas;

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL
);

CREATE TABLE archivos_analizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_original VARCHAR(255) NOT NULL,
    tipo_detectado VARCHAR(50) NOT NULL,
    hash_md5 CHAR(32) NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NULL,
    tamaño INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);

CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) DEFAULT 'sistema',
    accion VARCHAR(100) NOT NULL,
    archivo_id INT NULL,
    descripcion TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

SHOW TABLES;