<?php
// Rutas para autenticación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri[4] === 'login') {
    $datos = json_decode(file_get_contents("php://input"), true);
    if (isset($datos['email'], $datos['clave'])) {
        echo $authController->login($datos['email'], $datos['clave']);
    } else {
        responder(["error" => "Email y contraseña son obligatorios"], 400);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri[4] === 'token' && $uri[5] === 'refresh') {
    $datos = json_decode(file_get_contents("php://input"), true);
    if (isset($datos['refresh_token'])) {
        echo $authController->refreshToken($datos['refresh_token']);
    } else {
        responder(["error" => "El refresh token es obligatorio"], 400);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri[4] === 'logout') {
    echo $authController->logout();
}
