<?php

require_once '../models/Usuario.php';
require_once '../models/Auth.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $conn;
    private $clave_secreta;
    private $usuario;
    private $auth;

    public function __construct($db)
    {
        $this->conn = $db;
        $config = require('../config/config.php');
        $this->clave_secreta = $config['secret_key'];
        $this->usuario = new Usuario($this->conn);
        $this->auth = new Auth($this->conn);
    }

    private function responder($data, $codigo_http = 200)
    {
        http_response_code($codigo_http);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Generar el token JWT
    private function generarToken($usuario)
    {
        $config = require('../config/config.php');
        $issuedAt = time();
        $expirationTime = $issuedAt + $config['jwt_exp'];
        $payload = [
            'iss' => $config['jwt_issuer'],  // Emisor
            'aud' => $config['jwt_audience'],  // Audiencia
            'iat' => $issuedAt,  // Fecha de emisión
            'exp' => $expirationTime,  // Fecha de expiración
            'data' => [  // Información del usuario
                'id_usuario' => $usuario['id_usuario'],  // ID del usuario
                'id_rol' => $usuario['id_rol'],  // ID del rol del usuario
                'email' => $usuario['email'],  // Email del usuario
            ]
        ];

        // Codificamos el token con la clave secreta
        return JWT::encode($payload, $this->clave_secreta, 'HS256');
    }

    // Metodo para autenticar al usuario y generar el token JWT
    public function login($email, $clave)
    {
        try {
            $this->usuario->email = $email;
            $usuario_data = $this->usuario->obtenerPorEmail();

            if ($usuario_data && password_verify($clave, $usuario_data['clave'])) {
                if ($usuario_data['estado'] !== 'activo') {
                    return $this->responder(array("error" => "El usuario no está activo"), 401);
                }
                //Generar el access token
                $accessToken = $this->generarToken($usuario_data);
                // Generar el refresh token aleatorio de caracteres
                $refreshToken = bin2hex(random_bytes(50));
                // Guardar el refresh token en la base de datos
                $this->auth->guardarRefreshToken($usuario_data['id_usuario'], $refreshToken);

                //Devolvemos ambos tokens
                $this->responder([
                    "access_token" => $accessToken,
                    "refresh_token" => $refreshToken,
                ]);
            } else {
                return $this->responder(array("error" => "Credenciales inválidas"), 401);
            }
        } catch (Exception $e) {
            return $this->responder(array("error" => $e->getMessage()), 500);
        }
    }

    // Metodo para verificar el refresh_token y generar un nuevo access token
    public function refreshToken($refreshToken)
    {
        try {
            $usuario_data = $this->auth->obtenerPorRefreshToken($refreshToken);

            if ($usuario_data) {
                // Generar un nuevo access token
                $accessToken = $this->generarToken($usuario_data);
                $this->responder(['access_token' => $accessToken]);
            } else {
                return $this->responder(array("error" => "Refresh token inválido"), 401);
            }
        } catch (Exception $e) {
            return $this->responder(array("error" => $e->getMessage()), 500);
        }
    }

    // Metodo logout() para guardar el token en una lista negra
    public function logout()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            $this->responder(['error' => 'Token no proporcionado'], 400);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        // Decodificamos el token para obtener su fecha de expiración
        try {
            $decoded = JWT::decode($token, new Key($this->clave_secreta, 'HS256'));
            // Convertir el timestamp a una fecha legible
            $fecha_expiracion = date('Y-m-d H:i:s', $decoded->exp);
            // Guardar el token en la lista negra
            $this->auth->guardarEnBlacklist($token, $fecha_expiracion);
            // Eliminamos el refres token del usuario
            $this->auth->eliminarRefreshToken($decoded->data->id_usuario);
            $this->responder(['mensaje' => 'Cierre de sesión exitoso'], 200);
        } catch (Exception $e) {
            $this->responder(['error' => $e->getMessage()], 500);
        }
    }
}
