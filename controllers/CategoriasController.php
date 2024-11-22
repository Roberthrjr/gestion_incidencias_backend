<?php
require_once '../models/Categoria.php';

class CategoriasController
{
    private $conn;
    private $modeloCategoria;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloCategoria = new Categoria($db);
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
        $camposObligatorios = ['descripcion_categoria'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un categoria
    private function asignarDatosCategoria($datos)
    {
        $this->modeloCategoria->descripcion_categoria = $datos['descripcion_categoria'];
    }

    // Metodo controlador para obtener categoria por ID
    public function obtenerPorId($id)
    {
        try {
            $categoria = $this->modeloCategoria->obtenerPorId($id);
            if ($categoria) {
                $this->responder($categoria);
            } else {
                $this->responder(["error" => "Categoria no encontrada"], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la categoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la categoria."], 500);
        }
    }

    // Metodo controlador para obtener categorias paginados con filtro
    public function obtenerCategorias($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $categorias = $this->modeloCategoria->obtenerCategorias($limit, $offset, $filtros);
            if (!empty($categorias)) {
                $this->responder($categorias);
            } else {
                $this->responder(["error" => "No se encontraron categorias."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las categorias: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las categorias."], 500);
        }
    }

    // Metodo controlador para registra una categoria
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos"], 400);
            }
            if ($this->modeloCategoria->verificarDescripcionCategoriaExiste($datos['descripcion_categoria'])) {
                $this->responder(["error" => "La categoria ya existe"], 409);
            }

            $this->asignarDatosCategoria($datos);
            if ($this->modeloCategoria->registrar()) {
                $this->responder(["mensaje" => "Categoria registrada correctamente"], 201);
            } else {
                $this->responder(["error" => "Error al registrar la categoria"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la categoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la categoria."], 500);
        }
    }

    // Metodo controlador para actualizar una categoria
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios"], 400);
            }
            if ($this->modeloCategoria->verificarDescripcionCategoriaExiste($datos['descripcion_categoria'], $id)) {
                $this->responder(["error" => "La categoria ya existe"], 409);
            }

            $this->modeloCategoria->id_categoria = $id;
            $this->asignarDatosCategoria($datos, true);
            if ($this->modeloCategoria->actualizar()) {
                $this->responder(["mensaje" => "Categoria actualizada correctamente"]);
            } else {
                $this->responder(["error" => "Error al actualizar la categoria"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la categoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la categoria."], 500);
        }
    }

    // Metodo para eliminar una categoria
    public function eliminar($id)
    {
        try {
            $this->modeloCategoria->id_categoria = $id;
            if ($this->modeloCategoria->eliminar()) {
                $this->responder(["mensaje" => "Categoria eliminada correctamente"]);
            } else {
                $this->responder(["error" => "Error al eliminar la categoria"], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la categoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la categoria."], 500);
        }
    }
}
