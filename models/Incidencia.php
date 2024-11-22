<?php

class Incidencia
{
    private $conn;
    private const TABLA = "incidencias";

    public $id_incidencia;
    public $codigo_incidencia;
    public $id_usuario;
    public $id_equipo;
    public $id_tipo_incidencia;
    public $descripcion_incidencia;
    public $id_prioridad;
    public $id_estado;
    public $fecha_creacion_incidencia;
    public $fecha_modificacion_incidencia;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Método para obtener una incidencia por su ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_incidencia = :id_incidencia LIMIT 1";
        $params = [':id_incidencia' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener incidencias paginadas con filtros
    public function obtenerIncidencias($limit = 10, $offset = 0, $filtros = [])
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

    // Método para crear una nueva incidencia
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (codigo_incidencia, id_usuario, id_equipo, id_tipo_incidencia, descripcion_incidencia, id_prioridad, id_estado) VALUES (:codigo_incidencia, :id_usuario, :id_equipo, :id_tipo_incidencia, :descripcion_incidencia, :id_prioridad, :id_estado)";
        $params = [
            ':codigo_incidencia' => $this->codigo_incidencia,
            ':id_usuario' => $this->id_usuario,
            ':id_equipo' => $this->id_equipo,
            ':id_tipo_incidencia' => $this->id_tipo_incidencia,
            ':descripcion_incidencia' => $this->descripcion_incidencia,
            ':id_prioridad' => $this->id_prioridad,
            ':id_estado' => $this->id_estado
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Método para actualizar una incidencia existente
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET codigo_incidencia = :codigo_incidencia, id_usuario = :id_usuario, id_equipo = :id_equipo, id_tipo_incidencia = :id_tipo_incidencia, descripcion_incidencia = :descripcion_incidencia, id_prioridad = :id_prioridad, id_estado = :id_estado WHERE id_incidencia = :id_incidencia";
        $params = [
            ':id_incidencia' => $this->id_incidencia,
            ':codigo_incidencia' => $this->codigo_incidencia,
            ':id_usuario' => $this->id_usuario,
            ':id_equipo' => $this->id_equipo,
            ':id_tipo_incidencia' => $this->id_tipo_incidencia,
            ':descripcion_incidencia' => $this->descripcion_incidencia,
            ':id_prioridad' => $this->id_prioridad,
            ':id_estado' => $this->id_estado
        ];
        $resultado = $this->ejecutarConsulta($query, $params);

        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Método para eliminar una incidencia
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_incidencia = :id_incidencia";
        $params = [':id_incidencia' => $this->id_incidencia];
        return $this->ejecutarConsulta($query, $params);
    }

    // Verificar que el codigo de incidencia no exista
    public function verificarCodigoIncidenciaExiste($codigo_incidencia, $id_incidencia = null)
    {
        $query = "SELECT id_incidencia FROM " . self::TABLA . " WHERE codigo_incidencia = :codigo_incidencia";
        $params = [':codigo_incidencia' => $codigo_incidencia];

        if ($id_incidencia !== null) {
            $query .= " AND id_incidencia != :id_incidencia";
            $params[':id_incidencia'] = $id_incidencia;
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
        if (!empty($filtros['fecha_creacion_incidencia'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_incidencia'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_incidencia'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_incidencia BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_incidencia BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_incidencia'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_incidencia'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_incidencia'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_incidencia BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_incidencia BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_incidencia',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_incidencia',
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
