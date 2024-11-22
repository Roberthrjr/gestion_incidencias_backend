<?php
require_once '../models/Area.php';

class AreasController
{
    private $conn;
    private $modeloArea;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloArea = new Area($db);
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
        $camposObligatorios = ['descripcion_area', 'id_sede'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un area
    private function asignarDatosArea($datos)
    {
        $this->modeloArea->descripcion_area = $datos['descripcion_area'];
        $this->modeloArea->id_sede = $datos['id_sede'];
    }


    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_sede']);
    }

    // Metodo controlador para obtener area por ID
    public function obtenerPorId($id)
    {
        try {
            $area = $this->modeloArea->obtenerPorId($id);
            if ($area) {
                $this->responder($area);
            } else {
                $this->responder(["error" => "Area no encontrado"], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el area: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el area."], 500);
        }
    }

    // Metodo controlador para obtener areas paginados con filtro
    public function obtenerAreas($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $areas = $this->modeloArea->obtenerAreas($limit, $offset, $filtros);
            if (!empty($areas)) {
                $this->responder($areas);
            } else {
                $this->responder(["error" => "No se encontraron areas."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los areas: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los areas."], 500);
        }
    }

    // Metodo controlador para registra un area
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos"], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos"], 400);
            }

            $this->asignarDatosArea($datos);
            if ($this->modeloArea->registrar()) {
                $this->responder(["mensaje" => "Area registrada correctamente"], 201);
            } else {
                $this->responder(["error" => "Error al registrar el area"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar area: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el area."], 500);
        }
    }

    // Metodo controlador para actualizar un area
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios"], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos"], 400);
            }
            $this->modeloArea->id_area = $id;
            $this->asignarDatosArea($datos, true);
            if ($this->modeloArea->actualizar()) {
                $this->responder(["mensaje" => "Area actualizada correctamente"]);
            } else {
                $this->responder(["error" => "Error al actualizar el area"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar area: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el area."], 500);
        }
    }

    // Metodo para eliminar un area
    public function eliminar($id)
    {
        try {
            $this->modeloArea->id_area = $id;
            if ($this->modeloArea->eliminar()) {
                $this->responder(["mensaje" => "Area eliminada correctamente"]);
            } else {
                $this->responder(["error" => "Error al eliminar el area"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar area: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el area."], 500);
        }
    }
}
