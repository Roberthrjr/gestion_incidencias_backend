<?php
// Rutas para la gestión de cargos
if ($uri[4] === 'cargos') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $cargosController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['descripcion_cargo',  'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_cargo', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_cargo']), ARRAY_FILTER_USE_KEY);
                echo $cargosController->obtenerCargos($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $cargosController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $cargosController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de cargo no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $cargosController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de cargo no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
