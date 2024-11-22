<?php
require_once '../models/Subcategoria.php';

class SubcategoriasController
{
    private $conn;
    private $modeloSubcategoria;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloSubcategoria = new Subcategoria($db);
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
        $camposObligatorios = ['descripcion_subcategoria', 'id_categoria'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a una subcategoria
    private function asignarDatosSubcategoria($datos)
    {
        $this->modeloSubcategoria->descripcion_subcategoria = $datos['descripcion_subcategoria'];
        $this->modeloSubcategoria->id_categoria = $datos['id_categoria'];
    }


    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_categoria']);
    }

    // Metodo controlador para obtener una subcategoria por ID
    public function obtenerPorId($id)
    {
        try {
            $subcategoria = $this->modeloSubcategoria->obtenerPorId($id);
            if ($subcategoria) {
                $this->responder($subcategoria);
            } else {
                $this->responder(["error" => "Subcategoria no encontrada."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener la subcategoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener la subcategoria."], 500);
        }
    }

    // Metodo controlador para obtener subcategorias paginadas con filtro
    public function obtenerSubcategorias($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $subcategorias = $this->modeloSubcategoria->obtenerSubcategorias($limit, $offset, $filtros);
            if (!empty($subcategorias)) {
                $this->responder($subcategorias);
            } else {
                $this->responder(["error" => "No se encontraron las subcategorias."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener las subcategorias: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener las subcategorias."], 500);
        }
    }

    // Metodo controlador para registra una subcategoria
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }

            $this->asignarDatosSubcategoria($datos);
            if ($this->modeloSubcategoria->registrar()) {
                $this->responder(["mensaje" => "Subcategoria registrada correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar la subcategoria."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar la subcategoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar la subcategoria."], 500);
        }
    }

    // Metodo controlador para actualizar una subcategoria
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }

            $this->modeloSubcategoria->id_subcategoria = $id;
            $this->asignarDatosSubcategoria($datos, true);
            if ($this->modeloSubcategoria->actualizar()) {
                $this->responder(["mensaje" => "Subcategoria actualizada correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar la subcategoria."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar la subcategoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar la subcategoria."], 500);
        }
    }

    // Metodo para eliminar una subcategoria
    public function eliminar($id)
    {
        try {
            $this->modeloSubcategoria->id_subcategoria = $id;
            if ($this->modeloSubcategoria->eliminar()) {
                $this->responder(["mensaje" => "Subcategoria eliminada correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar la subcategoria."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar la subcategoria: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar la subcategoria."], 500);
        }
    }
}
