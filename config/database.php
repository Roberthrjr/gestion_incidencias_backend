<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        // Cargar los valores desde el archivo .env
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    // Método para obtener la conexión
    public function getConnection()
    {
        $this->conn = null;

        try {
            // Crear una nueva conexión PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");  // Aseguramos que la codificación sea UTF-8
        } catch (PDOException $exception) {
            // Mostrar mensaje de error si no se puede conectar
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
