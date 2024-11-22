<?php
// Rutas para la gestión de categorias
if ($uri[4] === 'categorias') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $categoriasController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['descripcion_categoria',  'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_categoria', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_categoria']), ARRAY_FILTER_USE_KEY);
                echo $categoriasController->obtenerCategorias($page, $limit, $filtros);
            }
            break;

        case 'POST':
            $datos = json_decode(file_get_contents("php://input"), true);
            echo $categoriasController->registrar($datos);
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $categoriasController->actualizar($id, $datos);
            } else {
                responder(["mensaje" => "ID de categoria no proporcionada."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $categoriasController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de categoria no proporcionada."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
