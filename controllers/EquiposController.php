<?php
require_once '../models/Equipo.php';

class EquiposController
{
    private $conn;
    private $modeloEquipo;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloEquipo = new Equipo($db);
    }

    private function responder($data, $codigo_http = 200)
    {
        http_response_code($codigo_http);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Metodo para validar datos obligatorios
    private function validarDatosObligatorios($datos)
    {
        $camposObligatorios = ['codigo_patrimonial_equipo', 'nombre_equipo', 'marca_equipo', 'modelo_equipo', 'id_area', 'id_subcategoria'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un equipo
    private function asignarDatosEquipo($datos)
    {
        $this->modeloEquipo->codigo_patrimonial_equipo = $datos['codigo_patrimonial_equipo'];
        $this->modeloEquipo->nombre_equipo = $datos['nombre_equipo'];
        $this->modeloEquipo->marca_equipo = $datos['marca_equipo'];
        $this->modeloEquipo->modelo_equipo = $datos['modelo_equipo'];
        $this->modeloEquipo->id_area = $datos['id_area'];
        $this->modeloEquipo->id_subcategoria = $datos['id_subcategoria'];
        $this->modeloEquipo->foto_equipo = $datos['foto_equipo'] ?? null;
    }


    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_area']) && is_numeric($datos['id_subcategoria']);
    }

    // Metodo controlador para obtener equipo por ID
    public function obtenerPorId($id)
    {
        try {
            $equipo = $this->modeloEquipo->obtenerPorId($id);
            if ($equipo) {
                $this->responder($equipo);
            } else {
                $this->responder(["error" => "Equipo no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el equipo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el equipo."], 500);
        }
    }

    // Metodo controlador para obtener equipos paginados con filtro
    public function obtenerEquipos($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $equipos = $this->modeloEquipo->obtenerEquipos($limit, $offset, $filtros);
            if (!empty($equipos)) {
                $this->responder($equipos);
            } else {
                $this->responder(["error" => "No se encontraron los equipos."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los equipos: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los equipos."], 500);
        }
    }

    // Metodo controlador para registra un equipo
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloEquipo->verificarCodPatExiste($datos['codigo_patrimonial_equipo'])) {
                $this->responder(["error" => "El código patrimonial ya está registrado."], 400);
            }

            $this->asignarDatosEquipo($datos);
            if ($this->modeloEquipo->registrar()) {
                $this->responder(["mensaje" => "Equipo registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el equipo."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el equipo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el equipo."], 500);
        }
    }

    // Metodo controlador para actualizar un equipo
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloEquipo->verificarCodPatExiste($datos['codigo_patrimonial_equipo'], $id)) {
                $this->responder(["error" => "El código patrimonial ya está registrado."], 400);
            }
            $this->modeloEquipo->id_equipo = $id;

            if (isset($datos['foto_equipo'])) {
                $this->modeloEquipo->foto_equipo = $datos['foto_equipo'];
            }

            $this->asignarDatosEquipo($datos, true);
            if ($this->modeloEquipo->actualizar()) {
                $this->responder(["mensaje" => "Equipo actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el equipo."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el equipo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el equipo."], 500);
        }
    }

    // Metodo para eliminar un equipo
    public function eliminar($id)
    {
        try {
            $this->modeloEquipo->id_equipo = $id;
            $fotoActual = $this->modeloEquipo->obtenerRutaFoto($id);

            if ($fotoActual && file_exists($fotoActual)) {
                unlink($fotoActual);
            }
            if ($this->modeloEquipo->eliminar()) {
                $this->responder(["mensaje" => "Equipo eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el equipo."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el equipo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el equipo."], 500);
        }
    }

    // Metodo para cambiar el estado de un equipo
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $this->modeloEquipo->id_equipo = $id;
            $this->modeloEquipo->estado_equipo = $nuevoEstado;
            if ($this->modeloEquipo->cambiarEstado()) {
                $this->responder(["mensaje" => "Estado del equipo cambiado correctamente."]);
            } else {
                $this->responder(["error" => "Error al cambiar el estado del equipo."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al cambiar el estado del equipo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar cambiar el estado del equipo."], 500);
        }
    }

    // Metodo para subir foto de equipo
    public function subirFotoEquipo($id, $foto)
    {
        try {
            // Verificar que el equipo existe
            if (!$this->modeloEquipo->existeEquipoPorId($id)) {
                $this->responder(["error" => "Equipo no encontrado."], 404);
            }

            // Recuperar la foto actual del equipo
            $fotoActual = $this->modeloEquipo->obtenerRutaFoto($id);

            // Subir la nueva foto
            $this->modeloEquipo->id_equipo = $id;
            $nuevaRutaFoto = $this->subirFoto($foto);

            if ($nuevaRutaFoto) {
                // Eliminar la foto anterior si existe
                if ($fotoActual && file_exists($fotoActual)) {
                    unlink($fotoActual);
                }

                $this->modeloEquipo->foto_equipo = $nuevaRutaFoto;
                if ($this->modeloEquipo->actualizarFoto()) {
                    $this->responder(["mensaje" => "Foto de equipo actualizado correctamente."]);
                } else {
                    $this->responder(["error" => "Error al actualizar la foto del equipo."], 500);
                }
            } else {
                $this->responder(["error" => "Error al subir la foto."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al subir la foto del equipo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar subir la foto del equipo."], 500);
        }
    }

    private function subirFoto($archivo)
    {
        $directorio = "../public/uploads/fotos_equipos/"; // Ruta para guardar las fotos
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
