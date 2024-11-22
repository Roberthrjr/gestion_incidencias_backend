<?php
require_once '../models/Sede.php';

class SedesController
{
    private $conn;
    private $modeloSede;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloSede = new Sede($db);
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
        $camposObligatorios = ['descripcion_sede', 'direccion_sede'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a una sede
    private function asignarDatosSede($datos)
    {
        $this->modeloSede->descripcion_sede = $datos['descripcion_sede'];
        $this->modeloSede->direccion_sede = $datos['direccion_sede'];
    }

    // Metodo controlador para obtener una sede por ID
    public function obtenerPorId($id)
    {
        try {
            $sede = $this->modeloSede->obtenerPorId($id);
            if ($sede) {
                $this->responder($sede);
            } else {
                $this->responder(["error" => "Sede no encontrada."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la sede: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la sede."], 500);
        }
    }

    // Metodo controlador para obtener sedes paginadas con filtro
    public function obtenerSedes($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $sedes = $this->modeloSede->obtenerSedes($limit, $offset, $filtros);
            if (!empty($sedes)) {
                $this->responder($sedes);
            } else {
                $this->responder(["error" => "No se encontraron las sedes."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las sedes: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las sedes."], 500);
        }
    }

    // Metodo controlador para registra una sede
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if ($this->modeloSede->verificarSedeExiste($datos['descripcion_sede'])) {
                $this->responder(["error" => "La sede ya existe."], 409);
            }

            $this->asignarDatosSede($datos);
            if ($this->modeloSede->registrar()) {
                $this->responder(["mensaje" => "Sede registrada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar la sede."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la sede: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la sede."], 500);
        }
    }

    // Metodo controlador para actualizar una sede
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if ($this->modeloSede->verificarSedeExiste($datos['descripcion_sede'], $id)) {
                $this->responder(["error" => "La sede ya existe."], 409);
            }

            $this->modeloSede->id_sede = $id;
            $this->asignarDatosSede($datos, true);
            if ($this->modeloSede->actualizar()) {
                $this->responder(["mensaje" => "Sede actualizada correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar la sede."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la sede: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la sede."], 500);
        }
    }

    // Metodo para eliminar una sede
    public function eliminar($id)
    {
        try {
            $this->modeloSede->id_sede = $id;
            if ($this->modeloSede->eliminar()) {
                $this->responder(["mensaje" => "Sede eliminada correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar la sede."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la sede: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la sede."], 500);
        }
    }
}
