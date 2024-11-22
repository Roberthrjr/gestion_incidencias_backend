<?php
require_once '../models/Componente.php';

class ComponentesController
{
    private $conn;
    private $modeloComponente;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloComponente = new Componente($db);
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
        $camposObligatorios = ['numero_serie_componente', 'descripcion_componente', 'marca_componente', 'modelo_componente', 'id_equipo'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un componente
    private function asignarDatosComponente($datos)
    {
        $this->modeloComponente->numero_serie_componente = $datos['numero_serie_componente'];
        $this->modeloComponente->descripcion_componente = $datos['descripcion_componente'];
        $this->modeloComponente->marca_componente = $datos['marca_componente'];
        $this->modeloComponente->modelo_componente = $datos['modelo_componente'];
        $this->modeloComponente->id_equipo = $datos['id_equipo'];
        $this->modeloComponente->foto_componente = $datos['foto_componente'] ?? null;
    }

    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_equipo']);
    }

    // Metodo controlador para obtener componente por ID
    public function obtenerPorId($id)
    {
        try {
            $componente = $this->modeloComponente->obtenerPorId($id);
            if ($componente) {
                $this->responder($componente);
            } else {
                $this->responder(["error" => "Componente no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el componente: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el componente."], 500);
        }
    }

    // Metodo controlador para obtener componentes paginados con filtro
    public function obtenerComponentes($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $componentes = $this->modeloComponente->obtenerComponentes($limit, $offset, $filtros);
            if (!empty($componentes)) {
                $this->responder($componentes);
            } else {
                $this->responder(["error" => "No se encontraron componentes."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los componentes: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los componentes."], 500);
        }
    }

    // Metodo controlador para registra un componente
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloComponente->verificarNumeroSerieComponenteExiste($datos['numero_serie_componente'])) {
                $this->responder(["error" => "El numero de serie del componente ya está registrado."], 400);
            }

            $this->asignarDatosComponente($datos);
            if ($this->modeloComponente->registrar()) {
                $this->responder(["mensaje" => "Componente registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el componente."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el componente: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el componente."], 500);
        }
    }

    // Metodo controlador para actualizar un componente
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloComponente->verificarNumeroSerieComponenteExiste($datos['numero_serie_componente'], $id)) {
                $this->responder(["error" => "El numero de serie del componente ya está registrado."], 400);
            }

            $this->modeloComponente->id_componente = $id;

            if (isset($datos['foto_componente'])) {
                $this->modeloComponente->foto_componente = $datos['foto_componente'];
            }

            $this->asignarDatosComponente($datos, true);
            if ($this->modeloComponente->actualizar()) {
                $this->responder(["mensaje" => "Componente actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el componente."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el componente: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el componente."], 500);
        }
    }

    // Metodo para eliminar un componente
    public function eliminar($id)
    {
        try {
            $this->modeloComponente->id_componente = $id;
            $fotoActual = $this->modeloComponente->obtenerRutaFoto($id);

            if ($fotoActual && file_exists($fotoActual)) {
                unlink($fotoActual);
            }

            if ($this->modeloComponente->eliminar()) {
                $this->responder(["mensaje" => "Componente eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el componente."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el componente: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el componente."], 500);
        }
    }

    // Método para cambiar el estado de un componente
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $this->modeloComponente->id_componente = $id;
            $this->modeloComponente->estado_componente = $nuevoEstado;
            if ($this->modeloComponente->cambiarEstado()) {
                $this->responder(["mensaje" => "Estado del componente cambiado correctamente."]);
            } else {
                $this->responder(["error" => "Error al cambiar el estado del componente."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al cambiar el estado del componente: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar cambiar el estado del componente."], 500);
        }
    }

    // Metodo para subir foto de componente
    public function subirFotoComponente($id, $foto)
    {
        try {
            // Verificar que el componente existe
            if (!$this->modeloComponente->existeComponentePorId($id)) {
                $this->responder(["error" => "Componente no encontrado."], 404);
            }

            // Recuperar la foto actual del equipo
            $fotoActual = $this->modeloComponente->obtenerRutaFoto($id);

            // Subir la nueva foto
            $this->modeloComponente->id_componente = $id;
            $nuevaRutaFoto = $this->subirFoto($foto);

            if ($nuevaRutaFoto) {
                // Eliminar la foto anterior si existe
                if ($fotoActual && file_exists($fotoActual)) {
                    unlink($fotoActual);
                }

                $this->modeloComponente->foto_componente = $nuevaRutaFoto;
                if ($this->modeloComponente->actualizarFoto()) {
                    $this->responder(["mensaje" => "Foto de componente actualizado correctamente."]);
                } else {
                    $this->responder(["error" => "Error al actualizar la foto del componente."], 500);
                }
            } else {
                $this->responder(["error" => "Error al subir la foto."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al subir la foto del componente: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar subir la foto del componente."], 500);
        }
    }

    private function subirFoto($archivo)
    {
        $directorio = "../public/uploads/fotos_componentes/"; // Ruta para guardar las fotos
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true); // Crear el directorio si no existe
        }

        // Verificar si hay algún error en la subida del archivo
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ["error" => "Hubo un error al subir el archivo. Código de error: " . $archivo['error']];
        }

        // Validar tamaño máximo (2 MB en este caso)
        $tamanioMaximo = 2 * 1024 * 1024; // 2 MB
        if ($archivo['size'] > $tamanioMaximo) {
            return ["error" => "El archivo excede el tamaño máximo permitido de 2 MB."];
        }

        // Validar tipo de archivo (solo jpg, jpeg y png)
        $tipoArchivo = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($tipoArchivo, ['jpg', 'jpeg', 'png'])) {
            return ["error" => "Formato de archivo no permitido. Solo se aceptan jpg, jpeg y png."];
        }

        // Validar que el contenido sea realmente una imagen
        $infoImagen = getimagesize($archivo['tmp_name']);
        if ($infoImagen === false) {
            return ["error" => "El archivo no es una imagen válida."];
        }

        // Validar dimensiones de la imagen (opcional: ejemplo de 5000x5000 píxeles máx)
        $anchoMax = 5000;
        $altoMax = 5000;
        if ($infoImagen[0] > $anchoMax || $infoImagen[1] > $altoMax) {
            return ["error" => "La imagen excede las dimensiones máximas permitidas de 5000x5000 píxeles."];
        }

        // Generar un nombre de archivo único para evitar conflictos
        $nombreArchivo = uniqid() . "_" . basename($archivo['name']);
        $rutaCompleta = $directorio . $nombreArchivo;

        // Mover el archivo al directorio destino
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $rutaCompleta; // Retorna la ruta para guardar en la base de datos
        } else {
            return ["error" => "Error al mover el archivo al directorio de destino."];
        }
    }
}
