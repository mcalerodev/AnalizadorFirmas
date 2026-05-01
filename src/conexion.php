<?php

// Configuración de la base de datos
$host = "localhost";
$db   = "analizador_firmas";
$user = "root";
$pass = "19801021";

// Crear conexión con PDO
try {
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Configuración importante
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}