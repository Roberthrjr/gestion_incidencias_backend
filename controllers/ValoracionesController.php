<?php
require_once '../models/Valoracion.php';

class ValoracionesController
{
    private $conn;
    private $modeloValoracion;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloValoracion = new Valoracion($db);
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
        $camposObligatorios = ['id_incidencia', 'id_usuario', 'calificacion', 'comentario_valoracion'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un valoracion
    private function asignarDatosValoracion($datos)
    {
        $this->modeloValoracion->id_incidencia = $datos['id_incidencia'];
        $this->modeloValoracion->id_usuario = $datos['id_usuario'];
        $this->modeloValoracion->calificacion = $datos['calificacion'];
        $this->modeloValoracion->comentario_valoracion = $datos['comentario_valoracion'];
    }

    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_incidencia']) && is_numeric($datos['id_usuario']);
    }

    // Metodo controlador para obtener una valoracion por ID
    public function obtenerPorId($id)
    {
        try {
            $valoracion = $this->modeloValoracion->obtenerPorId($id);
            if ($valoracion) {
                $this->responder($valoracion);
            } else {
                $this->responder(["error" => "Valoracion no encontrada."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la valoración: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la valoración."], 500);
        }
    }

    // Metodo controlador para obtener las valoraciones paginadas con filtro
    public function obtenerValoraciones($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $valoraciones = $this->modeloValoracion->obtenerValoraciones($limit, $offset, $filtros);
            if (!empty($valoraciones)) {
                $this->responder($valoraciones);
            } else {
                $this->responder(["error" => "No se encontraron las valoraciones."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las valoraciones: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las valoraciones."], 500);
        }
    }

    // Metodo controlador para registra una valoracion
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloValoracion->verificarValoracionExiste($datos['id_incidencia'])) {
                $this->responder(["error" => "La incidencia ya tiene una valoración registrada."], 400);
            }

            $this->asignarDatosValoracion($datos);
            if ($this->modeloValoracion->registrar()) {
                $this->responder(["mensaje" => "Valoración registrada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar la valoración."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la valoración: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la valoración."], 500);
        }
    }

    // Metodo controlador para actualizar una valoracion
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloValoracion->verificarValoracionExiste($datos['id_incidencia'], $id)) {
                $this->responder(["error" => "La incidencia ya tiene una valoración registrada."], 400);
            }
            $this->modeloValoracion->id_valoracion = $id;

            $this->asignarDatosValoracion($datos, true);
            if ($this->modeloValoracion->actualizar()) {
                $this->responder(["mensaje" => "Valoración actualizada correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar la valoración."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la valoración: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la valoración."], 500);
        }
    }

    // Metodo para eliminar una valoración
    public function eliminar($id)
    {
        try {
            $this->modeloValoracion->id_valoracion = $id;
            if ($this->modeloValoracion->eliminar()) {
                $this->responder(["mensaje" => "Valoración eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar la valoración."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la valoración: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la valoración."], 500);
        }
    }
}
