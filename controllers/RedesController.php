<?php
require_once '../models/Red.php';

class RedesController
{
    private $conn;
    private $modeloRed;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloRed = new Red($db);
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
        $camposObligatorios = ['tipo_conexion', 'direccion_ip', 'grupo_trabajo', 'id_equipo'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a una configuracion de red
    private function asignarDatosRed($datos)
    {
        $this->modeloRed->tipo_conexion = $datos['tipo_conexion'];
        $this->modeloRed->direccion_ip = $datos['direccion_ip'];
        $this->modeloRed->grupo_trabajo = $datos['grupo_trabajo'];
        $this->modeloRed->id_equipo = $datos['id_equipo'];
    }


    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_equipo']);
    }

    // Metodo controlador para obtener una configuracion de red por ID
    public function obtenerPorId($id)
    {
        try {
            $red = $this->modeloRed->obtenerPorId($id);
            if ($red) {
                $this->responder($red);
            } else {
                $this->responder(["error" => "Configuracion de red no encontrada."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la configuracion de red: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la configuracion de red."], 500);
        }
    }

    // Metodo controlador para obtener configuraciones de red paginadas con filtro
    public function obtenerRedes($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $redes = $this->modeloRed->obtenerRedes($limit, $offset, $filtros);
            if (!empty($redes)) {
                $this->responder($redes);
            } else {
                $this->responder(["error" => "No se encontraron las configuraciones de red."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las configuraciones de red: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las configuraciones de red."], 500);
        }
    }

    // Metodo controlador para registra una configuracion de red
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloRed->verificarIpExiste($datos['direccion_ip'])) {
                $this->responder(["error" => "La direccion IP ya existe."], 409);
            }

            $this->asignarDatosRed($datos);
            if ($this->modeloRed->registrar()) {
                $this->responder(["mensaje" => "Configuracion de red registrada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar la configuracion de red."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la configuracion de red: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la configuracion de red."], 500);
        }
    }

    // Metodo controlador para actualizar una configuracion de red
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloRed->verificarIpExiste($datos['direccion_ip'], $id)) {
                $this->responder(["error" => "La direccion IP ya existe."], 409);
            }

            $this->modeloRed->id_red = $id;
            $this->asignarDatosRed($datos, true);
            if ($this->modeloRed->actualizar()) {
                $this->responder(["mensaje" => "Configuracion de red actualizada correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar la configuracion de red."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la configuracion de red: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la configuracion de red."], 500);
        }
    }

    // Metodo para eliminar una configuracion de red
    public function eliminar($id)
    {
        try {
            $this->modeloRed->id_red = $id;
            if ($this->modeloRed->eliminar()) {
                $this->responder(["mensaje" => "Configuracion de red eliminada correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar la configuracion de red."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la configuracion de red: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la configuracion de red."], 500);
        }
    }
}
