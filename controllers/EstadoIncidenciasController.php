<?php
require_once '../models/EstadoIncidencia.php';

class EstadoIncidenciasController
{
    private $conn;
    private $modeloEstadoIncidencia;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloEstadoIncidencia = new EstadoIncidencia($db);
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
        $camposObligatorios = ['descripcion_estado'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un estado de incidencia
    private function asignarDatosEstadoIncidencia($datos)
    {
        $this->modeloEstadoIncidencia->descripcion_estado = $datos['descripcion_estado'];
    }

    // Metodo controlador para obtener estado de incidencia por ID
    public function obtenerPorId($id)
    {
        try {
            $estadoIncidencia = $this->modeloEstadoIncidencia->obtenerPorId($id);
            if ($estadoIncidencia) {
                $this->responder($estadoIncidencia);
            } else {
                $this->responder(["error" => "Estado de incidencia no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el estado de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el estado de incidencia."], 500);
        }
    }

    // Metodo controlador para obtener los estados de incidencia paginadas con filtro
    public function obtenerEstadosIncidencias($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $estadosIncidencias = $this->modeloEstadoIncidencia->obtenerEstadosIncidencias($limit, $offset, $filtros);
            if (!empty($estadosIncidencias)) {
                $this->responder($estadosIncidencias);
            } else {
                $this->responder(["error" => "No se encontraron estados de incidencia."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los estados de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los estados de incidencia."], 500);
        }
    }

    // Metodo controlador para registra un estado de incidencia
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if ($this->modeloEstadoIncidencia->verificarDescripcionEstadoExiste($datos['descripcion_estado'])) {
                $this->responder(["error" => "El estado de incidencia ya existe."], 409);
            }

            $this->asignarDatosEstadoIncidencia($datos);
            if ($this->modeloEstadoIncidencia->registrar()) {
                $this->responder(["mensaje" => "Estado de incidencia registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el estado de incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el estado de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el estado de incidencia."], 500);
        }
    }

    // Metodo controlador para actualizar un estado de incidencia
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if ($this->modeloEstadoIncidencia->verificarDescripcionEstadoExiste($datos['descripcion_estado'], $id)) {
                $this->responder(["error" => "El estado de incidencia ya existe."], 409);
            }

            $this->modeloEstadoIncidencia->id_estado = $id;
            $this->asignarDatosEstadoIncidencia($datos, true);
            if ($this->modeloEstadoIncidencia->actualizar()) {
                $this->responder(["mensaje" => "Estado de incidencia actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el estado de incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el estado de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el estado de incidencia."], 500);
        }
    }

    // Metodo para eliminar un estado de incidencia
    public function eliminar($id)
    {
        try {
            $this->modeloEstadoIncidencia->id_estado = $id;
            if ($this->modeloEstadoIncidencia->eliminar()) {
                $this->responder(["mensaje" => "Estado de incidencia eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el estado de incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el estado de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el estado de incidencia."], 500);
        }
    }
}
