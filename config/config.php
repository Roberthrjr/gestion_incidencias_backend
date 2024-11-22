<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar las variables de entorno desde el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'secret_key' => $_ENV['SECRET_KEY'] ?? 'wY1y;/u/UakSvbRo[#,.$_*6zAxadaTw',  // Clave secreta desde .env o un valor por defecto
    'jwt_issuer' => $_ENV['JWT_ISSUER'] ?? 'http://localhost',  // Emisor del JWT desde .env o un valor por defecto
    'jwt_audience' => $_ENV['JWT_AUDIENCE'] ?? 'http://localhost',  // Audiencia del JWT desde .env o un valor por defecto
    'jwt_exp' => $_ENV['JWT_EXP'] ?? 28800  // Tiempo de expiración del token desde .env o un valor por defecto
];
