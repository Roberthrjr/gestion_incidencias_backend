<?php
require_once '../models/TipoIncidencia.php';

class TipoIncidenciasController
{
    private $conn;
    private $modeloTipoIncidencia;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloTipoIncidencia = new TipoIncidencia($db);
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
        $camposObligatorios = ['descripcion_tipo_incidencia'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un tipo de incidencia
    private function asignarDatosTipoIncidencia($datos)
    {
        $this->modeloTipoIncidencia->descripcion_tipo_incidencia = $datos['descripcion_tipo_incidencia'];
    }

    // Metodo controlador para obtener tipo de incidencia por ID
    public function obtenerPorId($id)
    {
        try {
            $incidencia = $this->modeloTipoIncidencia->obtenerPorId($id);
            if ($incidencia) {
                $this->responder($incidencia);
            } else {
                $this->responder(["error" => "Tipo de incidencia no encontrada"], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el tipo de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el tipo de incidencia."], 500);
        }
    }

    // Metodo controlador para obtener tipos de incidencias paginados con filtro
    public function obtenerTiposIncidencias($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $incidencias = $this->modeloTipoIncidencia->obtenerTiposIncidencias($limit, $offset, $filtros);
            if (!empty($incidencias)) {
                $this->responder($incidencias);
            } else {
                $this->responder(["error" => "No se encontraron los tipos de incidencias."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los tipos de incidencias: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los tipos de incidencias."], 500);
        }
    }

    // Metodo controlador para registra un tipo de incidencia
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos"], 400);
            }
            if ($this->modeloTipoIncidencia->verificarTipoIncidenciaExiste($datos['descripcion_tipo_incidencia'])) {
                $this->responder(["error" => "El tipo de incidencia ya existe"], 409);
            }

            $this->asignarDatosTipoIncidencia($datos);
            if ($this->modeloTipoIncidencia->registrar()) {
                $this->responder(["mensaje" => "Tipo de incidencia registrado correctamente"], 201);
            } else {
                $this->responder(["error" => "Error al registrar el tipo de incidencia"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el tipo de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el  tipo de incidencia."], 500);
        }
    }

    // Metodo controlador para actualizar un tipo de incidencia
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios"], 400);
            }
            if ($this->modeloTipoIncidencia->verificarTipoIncidenciaExiste($datos['descripcion_tipo_incidencia'], $id)) {
                $this->responder(["error" => "El tipo de incidencia ya existe"], 409);
            }

            $this->modeloTipoIncidencia->id_tipo_incidencia = $id;
            $this->asignarDatosTipoIncidencia($datos, true);
            if ($this->modeloTipoIncidencia->actualizar()) {
                $this->responder(["mensaje" => "Tipo de incidencia actualizado correctamente"]);
            } else {
                $this->responder(["error" => "Error al actualizar el tipo de incidencia"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el tipo de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el  tipo de incidencia."], 500);
        }
    }

    // Metodo para eliminar un tipo de incidencia
    public function eliminar($id)
    {
        try {
            $this->modeloTipoIncidencia->id_tipo_incidencia = $id;
            if ($this->modeloTipoIncidencia->eliminar()) {
                $this->responder(["mensaje" => "Tipo de incidencia eliminado correctamente"]);
            } else {
                $this->responder(["error" => "Error al eliminar el tipo de incidencia"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el tipo de incidencia: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el tipo de incidencia."], 500);
        }
    }
}
