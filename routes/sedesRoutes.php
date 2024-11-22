<?php
// Rutas para la gestión de sedes
if ($uri[4] === 'sedes') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $sedesController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['descripcion_sede', 'direccion_sede', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_sede', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_sede']), ARRAY_FILTER_USE_KEY);
                echo $sedesController->obtenerSedes($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $sedesController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $sedesController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de sede no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $sedesController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de sede no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
