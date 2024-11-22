<?php
require_once '../models/Usuario.php';

class UsuariosController
{
    private $conn;
    private $modeloUsuario;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloUsuario = new Usuario($db);
    }

    private function responder($data, $codigo_http = 200)
    {
        http_response_code($codigo_http);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Metodo para validar datos obligatorios
    private function validarDatosObligatorios($datos, $actualizar = false)
    {
        $camposObligatorios = ['nombres', 'apellidos', 'email', 'tipo_documento', 'numero_documento', 'id_area', 'id_cargo', 'id_rol'];
        if (!$actualizar) {
            $camposObligatorios[] = 'clave';
        }
        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un usuario
    private function asignarDatosUsuario($datos, $actualizar = false)
    {
        $this->modeloUsuario->nombres = $datos['nombres'];
        $this->modeloUsuario->apellidos = $datos['apellidos'];
        $this->modeloUsuario->email = $datos['email'];
        $this->modeloUsuario->telefono = $datos['telefono'] ?? null;
        $this->modeloUsuario->tipo_documento = $datos['tipo_documento'];
        $this->modeloUsuario->numero_documento = $datos['numero_documento'];
        $this->modeloUsuario->foto = $datos['foto'] ?? null;
        $this->modeloUsuario->id_area = $datos['id_area'];
        $this->modeloUsuario->id_cargo = $datos['id_cargo'];
        $this->modeloUsuario->id_rol = $datos['id_rol'];
        if (!$actualizar) {
            $this->modeloUsuario->clave = password_hash($datos['clave'], PASSWORD_BCRYPT);
            $this->modeloUsuario->estado = 'activo';
        }
    }


    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_area']) && is_numeric($datos['id_cargo']) && is_numeric($datos['id_rol']);
    }

    // Metodo controlador para obtener usuarios por ID
    public function obtenerPorId($id)
    {
        try {
            $usuario = $this->modeloUsuario->obtenerPorId($id);
            if ($usuario) {
                $this->responder($usuario);
            } else {
                $this->responder(["error" => "Usuario no encontrado"], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el usuario."], 500);
        }
    }

    // Metodo controlador para obtener usuarios paginados con filtro
    public function obtenerUsuarios($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $usuarios = $this->modeloUsuario->obtenerUsuarios($limit, $offset, $filtros);
            if (!empty($usuarios)) {
                $this->responder($usuarios);
            } else {
                $this->responder(["error" => "No se encontraron usuarios."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los usuarios: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los usuarios."], 500);
        }
    }

    // Metodo controlador para registra un usuario
    public function registrar($datos)
    {
        try {
            var_dump($datos);
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloUsuario->verificarEmailExiste($datos['email'])) {
                $this->responder(["error" => "El email ya está registrado."], 400);
            }
            if ($this->modeloUsuario->verificarNumeroDocumentoExiste($datos['numero_documento'])) {
                $this->responder(["error" => "El número de documento ya está registrado."], 400);
            }

            $this->asignarDatosUsuario($datos);
            if ($this->modeloUsuario->registrar()) {
                $this->responder(["mensaje" => "Usuario registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el usuario."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el usuario."], 500);
        }
    }

    // Metodo controlador para actualizar un usuario
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloUsuario->verificarEmailExiste($datos['email'], $id)) {
                $this->responder(["error" => "El email ya está registrado."], 400);
            }
            if ($this->modeloUsuario->verificarNumeroDocumentoExiste($datos['numero_documento'], $id)) {
                $this->responder(["error" => "El número de documento ya está registrado."], 400);
            }

            $this->modeloUsuario->id_usuario = $id;

            if (isset($datos['foto'])) {
                $this->modeloUsuario->foto = $datos['foto'];
            }

            $this->asignarDatosUsuario($datos, true);
            if ($this->modeloUsuario->actualizar()) {
                $this->responder(["mensaje" => "Usuario actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el usuario."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el usuario."], 500);
        }
    }

    // Metodo para eliminar un usuario
    public function eliminar($id)
    {
        try {
            $this->modeloUsuario->id_usuario = $id;
            $fotoActual = $this->modeloUsuario->obtenerRutaFoto($id);

            if ($fotoActual && file_exists($fotoActual)) {
                unlink($fotoActual);
            }

            if ($this->modeloUsuario->eliminar()) {
                $this->responder(["mensaje" => "Usuario eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el usuario."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el usuario."], 500);
        }
    }

    // Método para actualizar la clave del usuario
    public function actualizarClave($id, $datos)
    {
        try {
            // Verificar que los campos necesarios estén presentes
            if (empty($datos['clave_actual']) || empty($datos['nueva_clave']) || empty($datos['confirmar_clave'])) {
                $this->responder(["error" => "Faltan datos obligatorios para actualizar la clave."], 400);
            }

            // Verificar que la nueva clave coincida con la confirmación
            if ($datos['nueva_clave'] !== $datos['confirmar_clave']) {
                $this->responder(["error" => "La nueva clave y la confirmación no coinciden."], 400);
            }

            // Obtener el usuario actual para verificar la clave actual
            $usuario = $this->modeloUsuario->obtenerClavePorId($id);
            if (!$usuario || !password_verify($datos['clave_actual'], $usuario['clave'])) {
                $this->responder(["error" => "La clave actual no es correcta."], 400);
            }

            // Actualizar la clave encriptada
            $this->modeloUsuario->id_usuario = $id;
            $this->modeloUsuario->clave = password_hash($datos['nueva_clave'], PASSWORD_BCRYPT);

            // Intentar actualizar la clave en la base de datos
            if ($this->modeloUsuario->actualizarClave()) {
                $this->responder(["mensaje" => "Clave actualizada correctamente."], 200);
            } else {
                $this->responder(["error" => "Error al actualizar la clave."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la clave del usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la clave."], 500);
        }
    }

    // Método para cambiar el estado de un usuario
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $this->modeloUsuario->id_usuario = $id;
            $this->modeloUsuario->estado = $nuevoEstado;
            if ($this->modeloUsuario->cambiarEstado()) {
                $this->responder(["mensaje" => "Estado del usuario cambiado correctamente."]);
            } else {
                $this->responder(["error" => "Error al cambiar el estado del usuario."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al cambiar el estado del usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar cambiar el estado del usuario."], 500);
        }
    }

    // Metodo para subir foto de usuario
    public function subirFotoUsuario($id, $foto)
    {
        try {
            // Verificar que el usuario existe
            if (!$this->modeloUsuario->existeUsuarioPorId($id)) {
                $this->responder(["error" => "Usuario no encontrado."], 404);
            }

            // Recuperar la foto actual del usuario
            $fotoActual = $this->modeloUsuario->obtenerRutaFoto($id);

            // Subir la nueva foto
            $this->modeloUsuario->id_usuario = $id;
            $nuevaRutaFoto = $this->subirFoto($foto);

            if ($nuevaRutaFoto) {
                // Eliminar la foto anterior si existe
                if ($fotoActual && file_exists($fotoActual)) {
                    unlink($fotoActual);
                }

                $this->modeloUsuario->foto = $nuevaRutaFoto;
                if ($this->modeloUsuario->actualizarFoto()) {
                    $this->responder(["mensaje" => "Foto de usuario actualizada correctamente."]);
                } else {
                    $this->responder(["error" => "Error al actualizar la foto del usuario."], 500);
                }
            } else {
                $this->responder(["error" => "Error al subir la foto."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al subir la foto del usuario: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar subir la foto del usuario."], 500);
        }
    }

    private function subirFoto($archivo)
    {
        $directorio = "../public/uploads/fotos_usuarios/"; // Ruta para guardar las fotos
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true); // Crear el directorio si no existe
        }

        // Verificar si hay algún error en la subida del archivo
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ["error" => "Hubo un error al subir el archivo. Código de error: " . $archivo['error']];
        }

        // Validar tamaño máximo (2 MB en este caso)
        $tamanioMaximo = 2 * 1024 * 1024; // 2 MB
        if ($archivo['size'] > $tamanioMaximo) {
            return ["error" => "El archivo excede el tamaño máximo permitido de 2 MB."];
        }

        // Validar tipo de archivo (solo jpg, jpeg y png)
        $tipoArchivo = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($tipoArchivo, ['jpg', 'jpeg', 'png'])) {
            return ["error" => "Formato de archivo no permitido. Solo se aceptan jpg, jpeg y png."];
        }

        // Validar que el contenido sea realmente una imagen
        $infoImagen = getimagesize($archivo['tmp_name']);
        if ($infoImagen === false) {
            return ["error" => "El archivo no es una imagen válida."];
        }

        // Validar dimensiones de la imagen (opcional: ejemplo de 5000x5000 píxeles máx)
        $anchoMax = 5000;
        $altoMax = 5000;
        if ($infoImagen[0] > $anchoMax || $infoImagen[1] > $altoMax) {
            return ["error" => "La imagen excede las dimensiones máximas permitidas de 5000x5000 píxeles."];
        }

        // Generar un nombre de archivo único para evitar conflictos
        $nombreArchivo = uniqid() . "_" . basename($archivo['name']);
        $rutaCompleta = $directorio . $nombreArchivo;

        // Mover el archivo al directorio destino
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $rutaCompleta; // Retorna la ruta para guardar en la base de datos
        } else {
            return ["error" => "Error al mover el archivo al directorio de destino."];
        }
    }
}
