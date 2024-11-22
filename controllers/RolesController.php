<?php
require_once '../models/Rol.php';

class RolesController
{
    private $conn;
    private $modeloRol;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloRol = new Rol($db);
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
        $camposObligatorios = ['descripcion_rol'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a una rol
    private function asignarDatosRol($datos)
    {
        $this->modeloRol->descripcion_rol = $datos['descripcion_rol'];
    }

    // Metodo controlador para obtener un rol por ID
    public function obtenerPorId($id)
    {
        try {
            $rol = $this->modeloRol->obtenerPorId($id);
            if ($rol) {
                $this->responder($rol);
            } else {
                $this->responder(["error" => "Rol no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el rol: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el rol."], 500);
        }
    }

    // Metodo controlador para obtener roles paginadas con filtro
    public function obtenerRoles($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $roles = $this->modeloRol->obtenerRoles($limit, $offset, $filtros);
            if (!empty($roles)) {
                $this->responder($roles);
            } else {
                $this->responder(["error" => "No se encontraron los roles."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los roles: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los roles."], 500);
        }
    }

    // Metodo controlador para registra un rol
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if ($this->modeloRol->verificarRolExiste($datos['descripcion_rol'])) {
                $this->responder(["error" => "La descripcion del rol ya está registrado."], 400);
            }

            $this->asignarDatosRol($datos);
            if ($this->modeloRol->registrar()) {
                $this->responder(["mensaje" => "Rol registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el rol."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el rol: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el rol."], 500);
        }
    }

    // Metodo controlador para actualizar un rol
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if ($this->modeloRol->verificarRolExiste($datos['descripcion_rol'], $id)) {
                $this->responder(["error" => "La descripcion del rol ya está registrado."], 400);
            }
            $this->modeloRol->id_rol = $id;

            $this->asignarDatosRol($datos, true);
            if ($this->modeloRol->actualizar()) {
                $this->responder(["mensaje" => "Rol actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el rol."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el rol: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el rol."], 500);
        }
    }

    // Metodo para eliminar un rol
    public function eliminar($id)
    {
        try {
            $this->modeloRol->id_rol = $id;
            if ($this->modeloRol->eliminar()) {
                $this->responder(["mensaje" => "Rol eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el rol."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el rol: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el rol."], 500);
        }
    }
}
