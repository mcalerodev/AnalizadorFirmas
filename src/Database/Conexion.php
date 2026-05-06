<?php

/**
 * Clase Conexion — Patrón Singleton
 *
 * Garantiza que exista UNA SOLA instancia de PDO en toda la aplicación.
 * Cualquier archivo que necesite la BD llama: Conexion::getInstance()
 */
class Conexion
{

    // Única instancia (estática)
    private static $instancia = null;

    // Configuración
    private static $host = "localhost";
    private static $db   = "analizador_firmas";
    private static $user = "root";
    private static $pass = "19801021";

    // Constructor privado: nadie puede hacer "new Conexion()" desde afuera
    private function __construct() {}

    /**
     * Devuelve la única instancia PDO.
     * Si no existe, la crea por primera vez.
     */
    public static function getInstance()
    {
        if (self::$instancia === null) {
            try {
                self::$instancia = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8",
                    self::$user,
                    self::$pass
                );
                self::$instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instancia->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
            }
        }
        return self::$instancia;
    }

    // Evitar clonación y deserialización del Singleton
    private function __clone() {}
    public function __wakeup() {}
}
