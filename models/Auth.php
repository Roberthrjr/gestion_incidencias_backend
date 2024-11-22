<?php

class Auth
{
    private $conn;
    private const TABLA_USUARIOS = "usuarios";
    private const TABLA_BLACKLIST = "blacklist_tokens";

    public $id_usuario;
    public $email;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Método para guardar el Refresh Token
    public function guardarRefreshToken($id_usuario, $refreshToken): bool
    {
        $query = "UPDATE " . self::TABLA_USUARIOS . " SET refresh_token = :refresh_token WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':refresh_token', $refreshToken);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        return $this->ejecutarConsulta($stmt, "Error al guardar el refresh token");
    }

    // Método para verificar si el Refresh Token proporcionado es válido
    public function obtenerPorRefreshToken($refreshToken)
    {
        $query = "SELECT * FROM " . self::TABLA_USUARIOS . " WHERE refresh_token = :refresh_token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':refresh_token', $refreshToken);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    // Método para guardar un token en la lista negra
    public function guardarEnBlacklist($token, $fecha_expiracion): bool
    {

        $query = "INSERT INTO " . self::TABLA_BLACKLIST . " (token, fecha_expiracion) VALUES (:token, :fecha_expiracion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':fecha_expiracion', $fecha_expiracion);
        return $this->ejecutarConsulta($stmt, "Error al guardar el token en la blacklist");
    }

    // Método para eliminar el Refresh Token
    public function eliminarRefreshToken($id_usuario): bool
    {
        $query = "UPDATE " . self::TABLA_USUARIOS . " SET refresh_token = NULL WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        return $this->ejecutarConsulta($stmt, "Error al eliminar el refresh token");
    }

    // Metodo privado para ejecutar consultas y manejar errores
    private function ejecutarConsulta($stmt, $errorMessage): bool
    {
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($errorMessage . ": " . $e->getMessage());
            return false;
        }
    }
}
