<?php
require_once '../models/Cargo.php';

class CargosController
{
    private $conn;
    private $modeloCargo;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloCargo = new Cargo($db);
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
        $camposObligatorios = ['descripcion_cargo'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un cargo
    private function asignarDatosCargo($datos)
    {
        $this->modeloCargo->descripcion_cargo = $datos['descripcion_cargo'];
    }

    // Metodo controlador para obtener cargo por ID
    public function obtenerPorId($id)
    {
        try {
            $cargo = $this->modeloCargo->obtenerPorId($id);
            if ($cargo) {
                $this->responder($cargo);
            } else {
                $this->responder(["error" => "Cargo no encontrado"], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el cargo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el cargo."], 500);
        }
    }

    // Metodo controlador para obtener cargos paginados con filtro
    public function obtenerCargos($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $cargos = $this->modeloCargo->obtenerCargos($limit, $offset, $filtros);
            if (!empty($cargos)) {
                $this->responder($cargos);
            } else {
                $this->responder(["error" => "No se encontraron cargos."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los cargos: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los cargos."], 500);
        }
    }

    // Metodo controlador para registra un cargo
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos"], 400);
            }
            if ($this->modeloCargo->verificarDescripcionCargoExiste($datos['descripcion_cargo'])) {
                $this->responder(["error" => "El cargo ya existe"], 409);
            }

            $this->asignarDatosCargo($datos);
            if ($this->modeloCargo->registrar()) {
                $this->responder(["mensaje" => "Cargo registrado correctamente"], 201);
            } else {
                $this->responder(["error" => "Error al registrar el cargo"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el cargo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el cargo."], 500);
        }
    }

    // Metodo controlador para actualizar un cargo
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios"], 400);
            }
            if ($this->modeloCargo->verificarDescripcionCargoExiste($datos['descripcion_cargo'], $id)) {
                $this->responder(["error" => "El cargo ya existe"], 409);
            }

            $this->modeloCargo->id_cargo = $id;
            $this->asignarDatosCargo($datos, true);
            if ($this->modeloCargo->actualizar()) {
                $this->responder(["mensaje" => "Cargo actualizado correctamente"]);
            } else {
                $this->responder(["error" => "Error al actualizar el cargo"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el cargo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el cargo."], 500);
        }
    }

    // Metodo para eliminar un cargo
    public function eliminar($id)
    {
        try {
            $this->modeloCargo->id_cargo = $id;
            if ($this->modeloCargo->eliminar()) {
                $this->responder(["mensaje" => "Cargo eliminado correctamente"]);
            } else {
                $this->responder(["error" => "Error al eliminar el cargo"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el cargo: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el cargo."], 500);
        }
    }
}
