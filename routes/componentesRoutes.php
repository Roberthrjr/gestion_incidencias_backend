<?php
// Rutas para la gestión de componentes
if ($uri[4] === 'componentes') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $componentesController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['numero_serie_componente', 'descripcion_componente', 'marca_componente', 'modelo_componente', 'id_equipo', 'estado_componente', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_componente', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_componente']), ARRAY_FILTER_USE_KEY);
                echo $componentesController->obtenerComponentes($page, $limit, $filtros);
            }
            break;

        case 'POST':
            if (isset($uri[5]) && $uri[5] === 'foto' && isset($uri[6])) {
                $id = (int)$uri[6];
                $foto = $_FILES['foto_componente'] ?? null;
                if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
                    echo $componentesController->subirFotoComponente($id, $foto);
                } else {
                    responder(["mensaje" => "Archivo de foto no proporcionado o es inválido."], 400);
                }
            } else {
                $datos = json_decode(file_get_contents("php://input"), true);
                echo $componentesController->registrar($datos);
            }
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $accion = $uri[5];
                if ($accion === 'estado' && isset($uri[6])) {
                    $id = (int)$uri[6];
                    if (isset($datos['nuevo_estado'])) {
                        echo $componentesController->cambiarEstado($id, $datos['nuevo_estado']);
                    } else {
                        responder(["mensaje" => "El nuevo estado es obligatorio."], 400);
                    }
                } else {
                    $id = (int)$uri[5];
                    echo $componentesController->actualizar($id, $datos);
                }
            } else {
                responder(["mensaje" => "ID de componente no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $componentesController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de componente no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
