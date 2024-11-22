<?php
// Rutas para la gestión de valoraciones
if ($uri[4] === 'valoraciones') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $valoracionesController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['id_incidencia', 'id_usuario', 'calificacion', 'comentario_valoracion', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_valoracion']), ARRAY_FILTER_USE_KEY);
                echo $valoracionesController->obtenerValoraciones($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $valoracionesController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $valoracionesController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de valoracion no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $valoracionesController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de valoracion no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
