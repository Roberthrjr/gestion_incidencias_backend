<?php

class Programa
{
    private $conn;
    private const TABLA = "programas";

    public $id_programa;
    public $descripcion_programa;
    public $version_programa;
    public $licencia_programa;
    public $id_equipo;
    public $foto_programa;
    public $estado_programa;
    public $fecha_creacion_programa;
    public $fecha_modificacion_programa;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un programa por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_programa = :id_programa LIMIT 1";
        $params = [':id_programa' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener programas paginadas con filtros
    public function obtenerProgramas($limit = 10, $offset = 0, $filtros = [])
    {
        $query = "SELECT * FROM " . self::TABLA;

        $resultadoFiltros = $this->construirClausulasWhere($filtros);
        $whereClauses = $resultadoFiltros['clausulas'];
        $params = $resultadoFiltros['parametros'];

        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int)$limit;
        $params[':offset'] = (int)$offset;

        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para registrar un programa
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (descripcion_programa, version_programa, licencia_programa, id_equipo, foto_programa) VALUES (:descripcion_programa, :version_programa, :licencia_programa, :id_equipo, :foto_programa)";
        $params = [
            ':descripcion_programa' => $this->descripcion_programa,
            ':version_programa' => $this->version_programa,
            ':licencia_programa' => $this->licencia_programa,
            ':id_equipo' => $this->id_equipo,
            ':foto_programa' => $this->foto_programa
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar un programa
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET descripcion_programa = :descripcion_programa, version_programa = :version_programa, licencia_programa = :licencia_programa, id_equipo = :id_equipo";

        $params = [
            ':id_programa' => $this->id_programa,
            ':descripcion_programa' => $this->descripcion_programa,
            ':version_programa' => $this->version_programa,
            ':licencia_programa' => $this->licencia_programa,
            ':id_equipo' => $this->id_equipo
        ];

        if (isset($this->foto_programa)) {
            $query .= ", foto_programa = :foto_programa";
            $params[':foto_programa'] = $this->foto_programa;
        }

        $query .= " WHERE id_programa = :id_programa";
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un programa
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_programa = :id_programa";
        $params = [':id_programa' => $this->id_programa];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar foto del programa
    public function actualizarFoto()
    {
        $query = "UPDATE " . self::TABLA . " SET foto_programa = :foto_programa WHERE id_programa = :id_programa";
        $params = [
            ':foto_programa' => $this->foto_programa,
            ':id_programa' => $this->id_programa
        ];
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para obtener la ruta actual de la foto del programa
    public function obtenerRutaFoto($id_programa)
    {
        $query = "SELECT foto_programa FROM " . self::TABLA . " WHERE id_programa = :id_programa";
        $params = [':id_programa' => $id_programa];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado ? $resultado['foto_programa'] : null;
    }

    // Metodo para saber si existe programa por ID
    public function existeProgramaPorId($id_programa)
    {
        $query = "SELECT id_programa FROM " . self::TABLA . " WHERE id_programa = :id_programa LIMIT 1";
        $params = [':id_programa' => $id_programa];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Metodo para cambiar el estado del programa
    public function cambiarEstado()
    {
        $query = "UPDATE " . self::TABLA . " SET estado_programa = :estado_programa WHERE id_programa = :id_programa";
        $params = [
            ':estado_programa' => $this->estado_programa,
            ':id_programa' => $this->id_programa
        ];

        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para verificar si la licencia del programa ya existe
    public function verificarLicenciaExiste($licencia_programa, $id_programa = null)
    {
        $query = "SELECT id_programa FROM " . self::TABLA . " WHERE licencia_programa = :licencia_programa";
        $params = [':licencia_programa' => $licencia_programa];

        if ($id_programa !== null) {
            $query .= " AND id_programa != :id_programa";
            $params[':id_programa'] = $id_programa;
        }

        $query .= " LIMIT 1";
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Método privado para ejecutar consultas y manejar errores
    private function ejecutarConsulta($query, $params = [], $single = false)
    {
        try {
            $stmt = $this->conn->prepare($query);
            $this->enlazarParametros($stmt, $params);
            $stmt->execute();
            // Verificar el tipo de operación (INSERT, UPDATE, DELETE) basándonos en la consulta
            $tipoOperacion = strtoupper(substr(trim($query), 0, 6));

            if (in_array($tipoOperacion, ['INSERT', 'UPDATE', 'DELETE'])) {
                return $stmt->rowCount() > 0;
            }
            return $single ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en la consulta: " . $e->getMessage());
            return false;
        }
    }

    // Método privado para enlazar parámetros
    private function enlazarParametros($stmt, $params)
    {
        foreach ($params as $key => $value) {
            $tipo = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $tipo);
        }
    }

    // Método para sanitizar los datos
    private function sanitizarDatos()
    {
        foreach (get_object_vars($this) as $key => $value) {
            if (is_string($value)) {
                $this->$key = htmlspecialchars(strip_tags($value));
            }
        }
    }

    private function construirClausulasWhere($filtros)
    {
        $whereClauses = [];
        $params = [];

        // Filtrar por fecha de creación
        if (!empty($filtros['fecha_creacion_programa'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_programa'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_programa'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_programa BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_programa BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_programa'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_programa'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_programa'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_programa BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_programa BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_programa',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_programa',
                'fecha_modificacion_desde',
                'fecha_modificacion_hasta',
                'fecha_inicio_modificacion',
                'fecha_fin_modificacion'
            ]) && !empty($valor)) {
                $whereClauses[] = "$campo = :$campo";
                $params[":$campo"] = $valor;
            }
        }

        return ['clausulas' => $whereClauses, 'parametros' => $params];
    }
}
