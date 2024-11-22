<?php
// Rutas para la gestión de estados de incidencias
if ($uri[4] === 'estados') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $estadoIncidenciasController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['descripcion_estado', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_estado', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_estado']), ARRAY_FILTER_USE_KEY);
                echo $estadoIncidenciasController->obtenerEstadosIncidencias($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $estadoIncidenciasController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $estadoIncidenciasController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de estado de incidencia no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $estadoIncidenciasController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de estado de incidencia no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
