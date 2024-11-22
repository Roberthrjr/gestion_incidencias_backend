<?php
// Rutas para la gestión de programas
if ($uri[4] === 'programas') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $programasController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['descripcion_programa', 'version_programa', 'licencia_programa', 'id_equipo', 'estado_programa', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion_programa', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion_programa']), ARRAY_FILTER_USE_KEY);
                echo $programasController->obtenerProgramas($page, $limit, $filtros);
            }
            break;

        case 'POST':
            if (isset($uri[5]) && $uri[5] === 'foto' && isset($uri[6])) {
                $id = (int)$uri[6];
                $foto = $_FILES['foto_programa'] ?? null;
                if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
                    echo $programasController->subirFotoPrograma($id, $foto);
                } else {
                    responder(["mensaje" => "Archivo de foto no proporcionado o es inválido."], 400);
                }
            } else {
                $datos = json_decode(file_get_contents("php://input"), true);
                echo $programasController->registrar($datos);
            }
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $accion = $uri[5];
                if ($accion === 'estado' && isset($uri[6])) {
                    $id = (int)$uri[6];
                    if (isset($datos['nuevo_estado'])) {
                        echo $programasController->cambiarEstado($id, $datos['nuevo_estado']);
                    } else {
                        responder(["mensaje" => "El nuevo estado es obligatorio."], 400);
                    }
                } else {
                    $id = (int)$uri[5];
                    echo $programasController->actualizar($id, $datos);
                }
            } else {
                responder(["mensaje" => "ID de programa no proporcionado."], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $programasController->eliminar($id);
            } else {
                responder(["mensaje" => "ID de programa no proporcionado."], 400);
            }
            break;

        default:
            responder(["mensaje" => "Método no permitido."], 405);
            break;
    }
}
