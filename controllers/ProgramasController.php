<?php
require_once '../models/Programa.php';

class ProgramasController
{
    private $conn;
    private $modeloPrograma;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->modeloPrograma = new Programa($db);
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
        $camposObligatorios = ['descripcion_programa', 'version_programa', 'licencia_programa', 'id_equipo'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        return true;
    }

    // Metodo para asignar datos a un programa
    private function asignarDatosPrograma($datos)
    {
        $this->modeloPrograma->descripcion_programa = $datos['descripcion_programa'];
        $this->modeloPrograma->version_programa = $datos['version_programa'];
        $this->modeloPrograma->licencia_programa = $datos['licencia_programa'];
        $this->modeloPrograma->id_equipo = $datos['id_equipo'];
        $this->modeloPrograma->foto_programa = $datos['foto_programa'] ?? null;
    }

    // Metodo para validar Ids numericos
    private function validarIdsNumericos($datos)
    {
        return is_numeric($datos['id_equipo']);
    }

    // Metodo controlador para obtener programa por ID
    public function obtenerPorId($id)
    {
        try {
            $programa = $this->modeloPrograma->obtenerPorId($id);
            if ($programa) {
                $this->responder($programa);
            } else {
                $this->responder(["error" => "Programa no encontrado."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener el programa: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener el programa."], 500);
        }
    }

    // Metodo controlador para obtener programas paginadas con filtro
    public function obtenerProgramas($page = 1, $limit = 10, $filtros = null)
    {
        try {
            $offset = ($page - 1) * $limit;
            $programas = $this->modeloPrograma->obtenerProgramas($limit, $offset, $filtros);
            if (!empty($programas)) {
                $this->responder($programas);
            } else {
                $this->responder(["error" => "No se encontraron programas."], 404);
            }
        } catch (Exception $e) {
            error_log("Error al obtener los programas: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar obtener los programas."], 500);
        }
    }

    // Metodo controlador para registra un programa
    public function registrar($datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos)) {
                $this->responder(["error" => "Faltan datos obligatorios o están vacíos."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloPrograma->verificarLicenciaExiste($datos['licencia_programa'])) {
                $this->responder(["error" => "La licencia del programa ya está registrado."], 400);
            }

            $this->asignarDatosPrograma($datos);
            if ($this->modeloPrograma->registrar()) {
                $this->responder(["mensaje" => "Programa registrado correctamente."], 201);
            } else {
                $this->responder(["error" => "Error al registrar el programa."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al registrar el programa: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar registrar el programa."], 500);
        }
    }

    // Metodo controlador para actualizar un programa
    public function actualizar($id, $datos)
    {
        try {
            if (!$this->validarDatosObligatorios($datos, true)) {
                $this->responder(["error" => "Faltan datos obligatorios."], 400);
            }
            if (!$this->validarIdsNumericos($datos)) {
                $this->responder(["error" => "Los IDs deben ser numéricos."], 400);
            }
            if ($this->modeloPrograma->verificarLicenciaExiste($datos['licencia_programa'], $id)) {
                $this->responder(["error" => "La licencia del programa ya está registrado."], 400);
            }

            $this->modeloPrograma->id_programa = $id;

            if (isset($datos['foto_programa'])) {
                $this->modeloPrograma->foto_programa = $datos['foto_programa'];
            }

            $this->asignarDatosPrograma($datos, true);
            if ($this->modeloPrograma->actualizar()) {
                $this->responder(["mensaje" => "Programa actualizado correctamente."]);
            } else {
                $this->responder(["error" => "Error al actualizar el programa."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar el programa: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar actualizar el programa."], 500);
        }
    }

    // Metodo para eliminar un programa
    public function eliminar($id)
    {
        try {
            $this->modeloPrograma->id_programa = $id;
            $fotoActual = $this->modeloPrograma->obtenerRutaFoto($id);

            if ($fotoActual && file_exists($fotoActual)) {
                unlink($fotoActual);
            }

            if ($this->modeloPrograma->eliminar()) {
                $this->responder(["mensaje" => "Programa eliminado correctamente."]);
            } else {
                $this->responder(["error" => "Error al eliminar el programa."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar el programa: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar eliminar el programa."], 500);
        }
    }

    // Método para cambiar el estado de un programa
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $this->modeloPrograma->id_programa = $id;
            $this->modeloPrograma->estado_programa = $nuevoEstado;
            if ($this->modeloPrograma->cambiarEstado()) {
                $this->responder(["mensaje" => "Estado del programa cambiado correctamente."]);
            } else {
                $this->responder(["error" => "Error al cambiar el estado del programa."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al cambiar el estado del programa: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar cambiar el estado del programa."], 500);
        }
    }

    // Metodo para subir foto de programa
    public function subirFotoPrograma($id, $foto)
    {
        try {
            if (!$this->modeloPrograma->existeProgramaPorId($id)) {
                $this->responder(["error" => "Programa no encontrado."], 404);
            }

            $fotoActual = $this->modeloPrograma->obtenerRutaFoto($id);

            $this->modeloPrograma->id_programa = $id;
            $nuevaRutaFoto = $this->subirFoto($foto);

            if ($nuevaRutaFoto) {
                if ($fotoActual && file_exists($fotoActual)) {
                    unlink($fotoActual);
                }

                $this->modeloPrograma->foto_programa = $nuevaRutaFoto;
                if ($this->modeloPrograma->actualizarFoto()) {
                    $this->responder(["mensaje" => "Foto de programa actualizado correctamente."]);
                } else {
                    $this->responder(["error" => "Error al actualizar la foto del programa."], 500);
                }
            } else {
                $this->responder(["error" => "Error al subir la foto."], 500);
            }
        } catch (Exception $e) {
            error_log("Error al subir la foto del programa: " . $e->getMessage());
            $this->responder(["error" => "Error del servidor al intentar subir la foto del programa."], 500);
        }
    }

    private function subirFoto($archivo)
    {
        $directorio = "../public/uploads/fotos_programas/"; // Ruta para guardar las fotos
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
