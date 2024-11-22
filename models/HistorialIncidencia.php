<?php

class HistorialIncidencia
{
    private $conn;
    private const TABLA = "historial_incidencias";

    public $id_historial;
    public $id_incidencia;
    public $id_usuario;
    public $estado_historial;
    public $fecha_cambio_historial;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un historial de incidencia por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_historial = :id_historial LIMIT 1";
        $params = [':id_historial' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener historiales de incidencias paginadas con filtros
    public function obtenerHistorialIncidencias($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar un nuevo historial de incidencias
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (id_incidencia, id_usuario, estado_historial) VALUES (:id_incidencia, :id_usuario, :estado_historial)";
        $params = [
            ':id_incidencia' => $this->id_incidencia,
            ':id_usuario' => $this->id_usuario,
            ':estado_historial' => $this->estado_historial,
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar un historial de incidencias
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET id_incidencia = :id_incidencia, id_usuario = :id_usuario, estado_historial = :estado_historial WHERE id_historial = :id_historial";
        $params = [
            ':id_historial' => $this->id_historial,
            ':id_incidencia' => $this->id_incidencia,
            ':id_usuario' => $this->id_usuario,
            ':estado_historial' => $this->estado_historial,
        ];
        $resultado = $this->ejecutarConsulta($query, $params);

        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un historial de incidencias
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_historial = :id_historial";
        $params = [':id_historial' => $this->id_historial];
        return $this->ejecutarConsulta($query, $params);
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
        if (!empty($filtros['fecha_cambio_historial'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_cambio_historial'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_cambio_historial'] . " 23:59:59";
            $whereClauses[] = "fecha_cambio_historial BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_cambio_historial BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_cambio_historial',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
            ]) && !empty($valor)) {
                $whereClauses[] = "$campo = :$campo";
                $params[":$campo"] = $valor;
            }
        }

        return ['clausulas' => $whereClauses, 'parametros' => $params];
    }
}
