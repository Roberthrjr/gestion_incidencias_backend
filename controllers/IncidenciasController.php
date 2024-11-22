<?php
require_once '../models/Incidencia.php';

class IncidenciasController
{
    private $conn;
    private $modeloIncidencia;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloIncidencia = new Incidencia($db);
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
        $camposObligatorios = ['codigo_incidencia', 'id_usuario', 'id_equipo', 'id_tipo_incidencia', 'descripcion_incidencia', 'id_prioridad', 'id_estado'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a una incidencia
    private function asignarDatosIncidencia($datos)
    {
        $this->modeloIncidencia->codigo_incidencia = $datos['codigo_incidencia'];
        $this->modeloIncidencia->id_usuario = $datos['id_usuario'];
        $this->modeloIncidencia->id_equipo = $datos['id_equipo'];
        $this->modeloIncidencia->id_tipo_incidencia = $datos['id_tipo_incidencia'];
        $this->modeloIncidencia->descripcion_incidencia = $datos['descripcion_incidencia'];
        $this->modeloIncidencia->id_prioridad = $datos['id_prioridad'];
        $this->modeloIncidencia->id_estado = $datos['id_estado'];
    }

    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_usuario']) && is_numeric($datos['id_equipo']) && is_numeric($datos['id_tipo_incidencia']) && is_numeric($datos['id_prioridad']) && is_numeric($datos['id_estado']);
    }

    // Obtener una incidencia por su ID
    public function obtenerPorId($id)
    {
        try {
            $incidencia = $this->modeloIncidencia->obtenerPorId($id);
            if ($incidencia) {
                $this->responder($incidencia);
            } else {
                $this->responder(["error" => "Incidencia no encontrada."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la incidencia."], 500);
        }
    }

    // Metodo controlador para obtener incidencias paginadas con filtro
    public function obtenerIncidencias($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $incidencias = $this->modeloIncidencia->obtenerIncidencias($limit, $offset, $filtros);
            if (!empty($incidencias)) {
                $this->responder($incidencias);
            } else {
                $this->responder(["error" => "No se encontraron las incidencias."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las incidencias: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las incidencias."], 500);
        }
    }

    // Metodo controlador para registra una incidencia
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloIncidencia->verificarCodigoIncidenciaExiste($datos['codigo_incidencia'])) {
                $this->responder(["error" => "El codigo de incidencia ya está registrado."], 400);
            }

            $this->asignarDatosIncidencia($datos);
            if ($this->modeloIncidencia->registrar()) {
                $this->responder(["mensaje" => "Incidencia registrada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar la incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la incidencia."], 500);
        }
    }

    // Metodo controlador para actualizar una incidencia
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloIncidencia->verificarCodigoIncidenciaExiste($datos['codigo_incidencia'], $id)) {
                $this->responder(["error" => "El codigo de incidencia ya está registrado."], 400);
            }

            $this->modeloIncidencia->id_incidencia = $id;
            $this->asignarDatosIncidencia($datos);
            if ($this->modeloIncidencia->actualizar()) {
                $this->responder(["mensaje" => "Incidencia actualizada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al actualizar la incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la incidencia."], 500);
        }
    }

    // Metodo para eliminar una incidencia
    public function eliminar($id)
    {
        try {
            $this->modeloIncidencia->id_incidencia = $id;
            if ($this->modeloIncidencia->eliminar()) {
                $this->responder(["mensaje" => "Incidencia eliminada correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar la incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la incidencia."], 500);
        }
    }
}
