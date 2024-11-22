<?php

// Manejo de la solicitud preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Establecer el encabezado para que todas las respuestas sean JSON
header('Content-Type: application/json');

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar las variables de entorno desde el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../utils/TokenHelper.php';

require_once '../controllers/AreasController.php';
require_once '../controllers/CargosController.php';
require_once '../controllers/CategoriasController.php';
require_once '../controllers/ComponentesController.php';
require_once '../controllers/RedesController.php';
require_once '../controllers/EquiposController.php';
require_once '../controllers/EstadoIncidenciasController.php';
require_once '../controllers/HistorialIncidenciasController.php';
require_once '../controllers/IncidenciasController.php';
require_once '../controllers/PerifericosController.php';
require_once '../controllers/PrioridadesController.php';
require_once '../controllers/ProgramasController.php';
require_once '../controllers/RolesController.php';
require_once '../controllers/SedesController.php';
require_once '../controllers/SubcategoriasController.php';
require_once '../controllers/TipoIncidenciasController.php';
require_once '../controllers/UsuariosController.php';
require_once '../controllers/ValoracionesController.php';

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar los controladores
$authController = new AuthController($db);
$tokenHelper = new TokenHelper($db);
$areasController = new AreasController($db);
$cargosController = new CargosController($db);
$categoriasController = new CategoriasController($db);
$componentesController = new ComponentesController($db);
$redesController = new RedesController($db);
$equiposController = new EquiposController($db);
$estadoIncidenciasController = new EstadoIncidenciasController($db);
$historialIncidenciasController = new HistorialIncidenciasController($db);
$incidenciasController = new IncidenciasController($db);
$perifericosController = new PerifericosController($db);
$prioridadesController = new PrioridadesController($db);
$programasController = new ProgramasController($db);
$rolesController = new RolesController($db);
$sedesController = new SedesController($db);
$subcategoriasController = new SubcategoriasController($db);
$tipoIncidenciasController = new TipoIncidenciasController($db);
$usuariosController = new UsuariosController($db);
$valoracionesController = new ValoracionesController($db);


// Obtener la URI solicitada
$uri = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Función para responder con JSON
function responder($data, $codigo_http = 200)
{
    http_response_code($codigo_http);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Función para verificar permisos de administrador
function verificarPermisosAdmin($tokenHelper)
{
    $userInfo = $tokenHelper->verificarToken();
    if ($userInfo['id_rol'] !== 1) {
        responder(["error" => "Acceso no autorizado: Solo los administradores pueden acceder a esta ruta"], 403);
    }
    return $userInfo;
}

// Funcion para verificar permisos de tecnico
function verificarPermisosTecnico($tokenHelper)
{
    $userInfo = $tokenHelper->verificarToken();
    if ($userInfo['id_rol'] !== 2) {
        responder(["error" => "Acceso no autorizado: Solo los tecnicos pueden acceder a esta ruta"], 403);
    }
    return $userInfo;
}

// Funcion para verificar permiso de usuario
function verificarPermisosUsuario($tokenHelper)
{
    $userInfo = $tokenHelper->verificarToken();
    if ($userInfo['id_rol'] !== 3) {
        responder(["error" => "Acceso no autorizado: Solo los usuarios pueden acceder a esta ruta"], 403);
    }
    return $userInfo;
}

// Incluir archivos de rutas
require_once 'authRoutes.php';
require_once 'areasRoutes.php';
require_once 'cargosRoutes.php';
require_once 'categoriasRoutes.php';
require_once 'componentesRoutes.php';
require_once 'redesRoutes.php';
require_once 'equiposRoutes.php';
require_once 'estadoIncidenciasRoutes.php';
require_once 'historialIndicenciasRoutes.php';
require_once 'incidenciasRoutes.php';
require_once 'perifericosRoutes.php';
require_once 'prioridadesRoutes.php';
require_once 'programasRoutes.php';
require_once 'rolesRoutes.php';
require_once 'sedesRoutes.php';
require_once 'subcategoriasRoutes.php';
require_once 'tiposIncidenciasRoutes.php';
require_once 'usuariosRoutes.php';
require_once 'valoracionesRoutes.php';

// Responder con un error 404 si la ruta no coincide con ninguna de las anteriores
responder(["error" => "Ruta no encontrada"], 404);
