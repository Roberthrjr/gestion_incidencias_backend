<?php
require_once '../models/Prioridad.php';

class PrioridadesController
{
    private $conn;
    private $modeloPrioridad;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloPrioridad = new Prioridad($db);
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
        $camposObligatorios = ['descripcion_prioridad'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a una prioridad
    private function asignarDatosPrioridad($datos)
    {
        $this->modeloPrioridad->descripcion_prioridad = $datos['descripcion_prioridad'];
    }

    // Metodo controlador para obtener una prioridad por ID
    public function obtenerPorId($id)
    {
        try {
            $prioridad = $this->modeloPrioridad->obtenerPorId($id);
            if ($prioridad) {
                $this->responder($prioridad);
            } else {
                $this->responder(["error" => "Prioridad no encontrada."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la prioridad: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la prioridad."], 500);
        }
    }

    // Metodo controlador para obtener prioridades paginadas con filtro
    public function obtenerPrioridades($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $prioridades = $this->modeloPrioridad->obtenerPrioridades($limit, $offset, $filtros);
            if (!empty($prioridades)) {
                $this->responder($prioridades);
            } else {
                $this->responder(["error" => "No se encontraron las prioridades."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las prioridades: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las prioridades."], 500);
        }
    }

    // Metodo controlador para registra una prioridad
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if ($this->modeloPrioridad->verificarDescripcionPrioridadExiste($datos['descripcion_prioridad'])) {
                $this->responder(["error" => "La descripcion de la prioridad ya está registrada."], 400);
            }

            $this->asignarDatosPrioridad($datos);
            if ($this->modeloPrioridad->registrar()) {
                $this->responder(["mensaje" => "Prioridad registrada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar la prioridad."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la prioridad: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la prioridad."], 500);
        }
    }

    // Metodo controlador para actualizar una prioridad
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if ($this->modeloPrioridad->verificarDescripcionPrioridadExiste($datos['descripcion_prioridad'], $id)) {
                $this->responder(["error" => "La descripcion de la prioridad ya está registrada."], 400);
            }
            $this->modeloPrioridad->id_prioridad = $id;

            $this->asignarDatosPrioridad($datos, true);
            if ($this->modeloPrioridad->actualizar()) {
                $this->responder(["mensaje" => "Prioridad actualizada correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar la prioridad."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la prioridad: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la prioridad."], 500);
        }
    }

    // Metodo para eliminar una prioridad
    public function eliminar($id)
    {
        try {
            $this->modeloPrioridad->id_prioridad = $id;
            if ($this->modeloPrioridad->eliminar()) {
                $this->responder(["mensaje" => "Prioridad eliminada correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar la prioridad."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la prioridad: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la prioridad."], 500);
        }
    }
}
