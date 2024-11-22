<?php
require_once '../models/HistorialIncidencia.php';

class HistorialIncidenciasController
{
    private $conn;
    private $modeloHistorialIncidencia;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloHistorialIncidencia = new HistorialIncidencia($db);
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
        $camposObligatorios = ['id_incidencia', 'id_usuario', 'estado_historial'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un historial de incidencia
    private function asignarDatosHistorialIndicencia($datos)
    {
        $this->modeloHistorialIncidencia->id_incidencia = $datos['id_incidencia'];
        $this->modeloHistorialIncidencia->id_usuario = $datos['id_usuario'];
        $this->modeloHistorialIncidencia->estado_historial = $datos['estado_historial'];
    }

    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_incidencia']) && is_numeric($datos['id_usuario']);
    }

    // Metodo controlador para obtener un historial de incidencia por ID
    public function obtenerPorId($id)
    {
        try {
            $historialIndiencia = $this->modeloHistorialIncidencia->obtenerPorId($id);
            if ($historialIndiencia) {
                $this->responder($historialIndiencia);
            } else {
                $this->responder(["error" => "Historial de incidencia no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el historial de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el historial de incidencia."], 500);
        }
    }

    // Metodo controlador para obtener historiales de incidencias paginados con filtro
    public function obtenerHistorialIncidencias($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $historialIndiencias = $this->modeloHistorialIncidencia->obtenerHistorialIncidencias($limit, $offset, $filtros);
            if (!empty($historialIndiencias)) {
                $this->responder($historialIndiencias);
            } else {
                $this->responder(["error" => "No se encontraron historiales de incidencias."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los historiales de incidencias: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los historiales de incidencias."], 500);
        }
    }

    // Metodo controlador para registra un historial de incidencia
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }

            $this->asignarDatosHistorialIndicencia($datos);
            if ($this->modeloHistorialIncidencia->registrar()) {
                $this->responder(["mensaje" => "Historial de incidencia registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el historial de incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el historial de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el historial de incidencia."], 500);
        }
    }

    // Metodo controlador para actualizar un historial de incidencia
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }

            $this->modeloHistorialIncidencia->id_historial = $id;
            $this->asignarDatosHistorialIndicencia($datos, true);
            if ($this->modeloHistorialIncidencia->actualizar()) {
                $this->responder(["mensaje" => "Historial de incidencia actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el historial de incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el historial de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el historial de incidencia."], 500);
        }
    }

    // Metodo para eliminar un historial de incidencia
    public function eliminar($id)
    {
        try {
            $this->modeloHistorialIncidencia->id_historial = $id;
            if ($this->modeloHistorialIncidencia->eliminar()) {
                $this->responder(["mensaje" => "Historial de incidencia eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el historial de incidencia."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el historial de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el historial de incidencia."], 500);
        }
    }
}
