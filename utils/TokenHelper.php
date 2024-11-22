<?php

require_once '../vendor/autoload.php';  // JWT Library
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenHelper
{
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para verificar el token JWT
    public function verificarToken()
    {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            // Extraemos el token de la cabecera Authization
            $token = str_replace('Bearer ', '', $headers['Authorization']);

            // Verificar si el token esta en la lista negra
            if ($this->estaEnBlacklist($token)) {
                echo json_encode(["error" => "Token inválido. Inicia sesión nuevamente"], JSON_UNESCAPED_UNICODE);
                exit;
            }
            try {
                $config = require('../config/config.php');
                $clave_secreta = $config['secret_key'];
                // Decodificar el token
                $decoded = JWT::decode($token, new Key($clave_secreta, 'HS256'));

                return array(
                    'id_usuario' => $decoded->data->id_usuario,
                    'id_rol' => $decoded->data->id_rol
                );
            } catch (Exception $e) {
                echo json_encode(["error" => "Acceso denegado: token inválido"], JSON_UNESCAPED_UNICODE);
                http_response_code(401);
                exit;
            }
        } else {
            echo json_encode(["error" => "Acceso denegado: token no proporcionado"], JSON_UNESCAPED_UNICODE);
            http_response_code(401);
            exit;
        }
    }

    private function estaEnBlacklist($token)
    {
        $query = "SELECT id FROM blacklist_tokens WHERE token = :token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        // Retornar true si el token esta en la lista negra
        return $stmt->rowCount() > 0;
    }
}
