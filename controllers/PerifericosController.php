<?php
require_once '../models/Periferico.php';

class PerifericosController
{
    private $conn;
    private $modeloPeriferico;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloPeriferico = new Periferico($db);
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
        $camposObligatorios = ['codigo_patrimonial_periferico', 'numero_serie_periferico', 'descripcion_periferico', 'marca_periferico', 'modelo_periferico', 'id_equipo'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un periferico
    private function asignarDatosPeriferico($datos)
    {
        $this->modeloPeriferico->codigo_patrimonial_periferico = $datos['codigo_patrimonial_periferico'];
        $this->modeloPeriferico->numero_serie_periferico = $datos['numero_serie_periferico'];
        $this->modeloPeriferico->descripcion_periferico = $datos['descripcion_periferico'];
        $this->modeloPeriferico->marca_periferico = $datos['marca_periferico'];
        $this->modeloPeriferico->modelo_periferico = $datos['modelo_periferico'];
        $this->modeloPeriferico->id_equipo = $datos['id_equipo'];
        $this->modeloPeriferico->foto_periferico = $datos['foto_periferico'] ?? null;
    }


    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_equipo']);
    }

    // Metodo controlador para obtener perifericos por ID
    public function obtenerPorId($id)
    {
        try {
            $periferico = $this->modeloPeriferico->obtenerPorId($id);
            if ($periferico) {
                $this->responder($periferico);
            } else {
                $this->responder(["error" => "Periferico no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el periferico: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el periferico."], 500);
        }
    }

    // Metodo controlador para obtener perifericos paginados con filtro
    public function obtenerPerifericos($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $perifericos = $this->modeloPeriferico->obtenerPerifericos($limit, $offset, $filtros);
            if (!empty($perifericos)) {
                $this->responder($perifericos);
            } else {
                $this->responder(["error" => "No se encontraron perifericos."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los perifericos: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los perifericos."], 500);
        }
    }

    // Metodo controlador para registra un periferico
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloPeriferico->verificarCodPatExiste($datos['codigo_patrimonial_periferico'])) {
                $this->responder(["error" => "El código patrimonial ya está registrado."], 400);
            }

            $this->asignarDatosPeriferico($datos);
            if ($this->modeloPeriferico->registrar()) {
                $this->responder(["mensaje" => "Periferico registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el periferico."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el periferico: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el periferico."], 500);
        }
    }

    // Metodo controlador para actualizar un periferico
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloPeriferico->verificarCodPatExiste($datos['codigo_patrimonial_periferico'], $id)) {
                $this->responder(["error" => "El código patrimonial ya está registrado."], 400);
            }
            $this->modeloPeriferico->id_periferico = $id;

            if (isset($datos['foto_periferico'])) {
                $this->modeloPeriferico->foto_periferico = $datos['foto_periferico'];
            }

            $this->asignarDatosPeriferico($datos, true);
            if ($this->modeloPeriferico->actualizar()) {
                $this->responder(["mensaje" => "Periferico actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el periferico."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el periferico: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el periferico."], 500);
        }
    }

    // Metodo para eliminar un periferico
    public function eliminar($id)
    {
        try {
            $this->modeloPeriferico->id_periferico = $id;
            $fotoActual = $this->modeloPeriferico->obtenerRutaFoto($id);

            if ($fotoActual && file_exists($fotoActual)) {
                unlink($fotoActual);
            }
            if ($this->modeloPeriferico->eliminar()) {
                $this->responder(["mensaje" => "Periferico eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el periferico."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el periferico: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el periferico."], 500);
        }
    }

    // Metodo para cambiar el estado de un periferico
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $this->modeloPeriferico->id_periferico = $id;
            $this->modeloPeriferico->estado_periferico = $nuevoEstado;
            if ($this->modeloPeriferico->cambiarEstado()) {
                $this->responder(["mensaje" => "Estado del periferico cambiado correctamente."]);
            } else {
                $this->responder(["error" => "Error al cambiar el estado del periferico."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al cambiar el estado del periferico: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar cambiar el estado del periferico."], 500);
        }
    }

    // Metodo para subir foto de periferico
    public function subirFotoPeriferico($id, $foto)
    {
        try {
            if (!$this->modeloPeriferico->existePerifericoPorId($id)) {
                $this->responder(["error" => "Periferico no encontrado."], 404);
            }

            $fotoActual = $this->modeloPeriferico->obtenerRutaFoto($id);

            $this->modeloPeriferico->id_periferico = $id;
            $nuevaRutaFoto = $this->subirFoto($foto);

            if ($nuevaRutaFoto) {
                if ($fotoActual && file_exists($fotoActual)) {
                    unlink($fotoActual);
                }

                $this->modeloPeriferico->foto_periferico = $nuevaRutaFoto;
                if ($this->modeloPeriferico->actualizarFoto()) {
                    $this->responder(["mensaje" => "Foto de periferico actualizado correctamente."]);
                } else {
                    $this->responder(["error" => "Error al actualizar la foto del periferico."], 500);
                }
            } else {
                $this->responder(["error" => "Error al subir la foto."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al subir la foto del periferico: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar subir la foto del periferico."], 500);
        }
    }

    private function subirFoto($archivo)
    {
        $directorio = "../public/uploads/fotos_perifericos/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ["error" => "Hubo un error al subir el archivo. Código de error: " . $archivo['error']];
        }

        $tamanioMaximo = 2 * 1024 * 1024; // 2 MB
        if ($archivo['size'] > $tamanioMaximo) {
            return ["error" => "El archivo excede el tamaño máximo permitido de 2 MB."];
        }

        $tipoArchivo = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($tipoArchivo, ['jpg', 'jpeg', 'png'])) {
            return ["error" => "Formato de archivo no permitido. Solo se aceptan jpg, jpeg y png."];
        }

        $infoImagen = getimagesize($archivo['tmp_name']);
        if ($infoImagen === false) {
            return ["error" => "El archivo no es una imagen válida."];
        }

        $anchoMax = 5000;
        $altoMax = 5000;
        if ($infoImagen[0] > $anchoMax || $infoImagen[1] > $altoMax) {
            return ["error" => "La imagen excede las dimensiones máximas permitidas de 5000x5000 píxeles."];
        }

        $nombreArchivo = uniqid() . "_" . basename($archivo['name']);
        $rutaCompleta = $directorio . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $rutaCompleta;
        } else {
            return ["error" => "Error al mover el archivo al directorio de destino."];
        }
    }
}
