<?php
// Rutas para la gestión de historiales de incidencias
if ($uri[4] === 'historiales') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $historialIncidenciasController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['id_incidencia', 'id_usuario', 'estado_historial', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_cambio_historial']), ARRAY_FILTER_USE_KEY);
                echo $historialIncidenciasController->obtenerHistorialIncidencias($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $historialIncidenciasController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $historialIncidenciasController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de historial de incidencia no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $historialIncidenciasController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de historial de incidencia no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
