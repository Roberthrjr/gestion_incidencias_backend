<?php

class Prioridad
{
    private $conn;
    private const TABLA = "prioridades";

    public $id_prioridad;
    public $descripcion_prioridad;
    public $fecha_creacion_prioridad;
    public $fecha_modificacion_prioridad;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Metodo para obtener una prioridad por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_prioridad = :id_prioridad";
        $params = [':id_prioridad' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener prioridades paginadas con filtros
    public function obtenerPrioridades($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar una prioridad
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (descripcion_prioridad) VALUES (:descripcion_prioridad)";
        $params = [':descripcion_prioridad' => $this->descripcion_prioridad];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar una prioridad
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET descripcion_prioridad = :descripcion_prioridad WHERE id_prioridad = :id_prioridad";
        $params = [
            ':id_prioridad' => $this->id_prioridad,
            ':descripcion_prioridad' => $this->descripcion_prioridad
        ];
        $resultado = $this->ejecutarConsulta($query, $params);

        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar una prioridad
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_prioridad = :id_prioridad";
        $params = [':id_prioridad' => $this->id_prioridad];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para verificar si la prioridad ya existe
    public function verificarDescripcionPrioridadExiste($descripcion_prioridad, $id_prioridad = null)
    {
        $query = "SELECT id_prioridad FROM " . self::TABLA . " WHERE descripcion_prioridad = :descripcion_prioridad";
        $params = [':descripcion_prioridad' => $descripcion_prioridad];

        if ($id_prioridad !== null) {
            $query .= " AND id_prioridad != :id_prioridad";
            $params[':id_prioridad'] = $id_prioridad;
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
        if (!empty($filtros['fecha_creacion_prioridad'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_prioridad'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_prioridad'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_prioridad BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_prioridad BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_prioridad'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_prioridad'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_prioridad'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_prioridad BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_prioridad BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_prioridad',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_prioridad',
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
