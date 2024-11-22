<?php
// Rutas para la gestión de incidencias
if ($uri[4] === 'incidencias') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $incidenciasController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['codigo_incidencia', 'id_usuario', 'id_equipo', 'id_tipo_incidencia', 'descripcion_incidencia', 'id_prioridad', 'id_estado', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_incidencia', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_incidencia']), ARRAY_FILTER_USE_KEY);
                echo $incidenciasController->obtenerIncidencias($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $incidenciasController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $incidenciasController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de incidencia no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $incidenciasController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de incidencia no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
