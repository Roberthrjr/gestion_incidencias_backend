<?php
// Rutas para la gestión de usuarios
if ($uri[4] === 'usuarios') {
    $userInfo = verificarPermisosAdmin($tokenHelper); // Verificar permisos

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $usuariosController->obtenerPorId($id);
            } else {
                $filtros = array_filter($_GET, fn($key) => in_array($key, ['nombres', 'apellidos', 'email', 'tipo_documento', 'numero_documento', 'id_area', 'id_cargo', 'id_rol', 'estado', 'fecha_creacion_desde', 'fecha_creacion_hasta', 'fecha_creacion', 'fecha_modificacion_desde', 'fecha_modificacion_hasta', 'fecha_modificacion']), ARRAY_FILTER_USE_KEY);
                echo $usuariosController->obtenerUsuarios($page, $limit, $filtros);
            }
            break;

        case 'POST':
            if (isset($uri[5]) && $uri[5] === 'foto' && isset($uri[6])) {
                $id = (int)$uri[6];
                $foto = $_FILES['foto'] ?? null;
                if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
                    echo $usuariosController->subirFotoUsuario($id, $foto);
                } else {
                    responder(["error" => "Archivo de foto no proporcionado o es inválido"], 400);
                }
            } else {
                $datos = json_decode(file_get_contents("php://input"), true);
                echo $usuariosController->registrar($datos);
            }
            break;

        case 'PUT':
            $datos = json_decode(file_get_contents("php://input"), true);
            if (isset($uri[5])) {
                $accion = $uri[5];
                if ($accion === 'clave' && isset($uri[6])) {
                    $id = (int)$uri[6];
                    echo $usuariosController->actualizarClave($id, $datos);
                } elseif ($accion === 'estado' && isset($uri[6])) {
                    $id = (int)$uri[6];
                    if (isset($datos['nuevo_estado'])) {
                        echo $usuariosController->cambiarEstado($id, $datos['nuevo_estado']);
                    } else {
                        responder(["error" => "El nuevo estado es obligatorio"], 400);
                    }
                } else {
                    $id = (int)$uri[5];
                    echo $usuariosController->actualizar($id, $datos);
                }
            } else {
                responder(["error" => "ID de usuario no proporcionado"], 400);
            }
            break;

        case 'DELETE':
            if (isset($uri[5])) {
                $id = (int)$uri[5];
                echo $usuariosController->eliminar($id);
            } else {
                responder(["error" => "ID de usuario no proporcionado"], 400);
            }
            break;

        default:
            responder(["error" => "Método no permitido"], 405);
            break;
    }
}
