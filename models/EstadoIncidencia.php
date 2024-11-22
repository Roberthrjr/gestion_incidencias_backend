<?php

class EstadoIncidencia
{
    private $conn;
    private const TABLA = "estados_incidencias";

    public $id_estado;
    public $descripcion_estado;
    public $fecha_creacion_estado;
    public $fecha_modificacion_estado;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un estado de incidencia por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_estado = :id_estado LIMIT 1";
        $params = [':id_estado' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener estados de incidencias paginadas con filtros
    public function obtenerEstadosIncidencias($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar un nuevo estado de incidencia
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (descripcion_estado) VALUES (:descripcion_estado)";
        $params = [
            ':descripcion_estado' => $this->descripcion_estado
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar un estado de incidencia
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET descripcion_estado = :descripcion_estado WHERE id_estado = :id_estado";
        $params = [
            ':id_estado' => $this->id_estado,
            ':descripcion_estado' => $this->descripcion_estado
        ];
        $resultado = $this->ejecutarConsulta($query, $params);

        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un estado de incidencia
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_estado = :id_estado";
        $params = [':id_estado' => $this->id_estado];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para verificar que no exista una descripcion de estado
    public function verificarDescripcionEstadoExiste($descripcion_estado, $id_estado = null)
    {
        $query = "SELECT id_estado FROM " . self::TABLA . " WHERE descripcion_estado = :descripcion_estado";
        $params = [':descripcion_estado' => $descripcion_estado];

        if ($id_estado !== null) {
            $query .= " AND id_estado != :id_estado";
            $params[':id_estado'] = $id_estado;
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
        if (!empty($filtros['fecha_creacion_estado'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_estado'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_estado'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_estado BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_estado BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_estado'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_estado'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_estado'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_estado BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_estado BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_estado',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_estado',
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
