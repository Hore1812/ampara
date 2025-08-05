<?php

namespace Ampara;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    // The constructor is private to prevent initiation with 'new'.
    private function __construct() {}

    // The clone method is private to prevent cloning of the instance.
    private function __clone() {}

    /**
     * Returns the single instance of the PDO connection.
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Configuration is hardcoded here for simplicity, but could be moved to a config file.
            $host = 'localhost';
            $db   = 'u505676278_inet_ampara';
            $user = 'root';
            $pass = ''; // As seen in conexion.php

            try {
                self::$instance = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec("SET time_zone = '-05:00';");
            } catch (PDOException $e) {
                // In a real app, you'd log this error.
                // For now, we'll just die with a generic message.
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
