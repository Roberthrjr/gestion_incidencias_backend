<?php

class Red
{
    private $conn;
    private const TABLA = "configuracion_red";

    public $id_red;
    public $tipo_conexion;
    public $direccion_ip;
    public $grupo_trabajo;
    public $id_equipo;
    public $fecha_creacion_red;
    public $fecha_modificacion_red;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener una configuracion de red por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_red = :id_red LIMIT 1";
        $params = [':id_red' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener configuraciones de red paginadas con filtros
    public function obtenerRedes($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar una nueva configuracion de red
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (tipo_conexion, direccion_ip, grupo_trabajo, id_equipo) VALUES (:tipo_conexion, :direccion_ip, :grupo_trabajo, :id_equipo)";
        $params = [
            ':tipo_conexion' => $this->tipo_conexion,
            ':direccion_ip' => $this->direccion_ip,
            ':grupo_trabajo' => $this->grupo_trabajo,
            ':id_equipo' => $this->id_equipo
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar una configuracion de red
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET tipo_conexion = :tipo_conexion, direccion_ip = :direccion_ip, grupo_trabajo = :grupo_trabajo, id_equipo = :id_equipo WHERE id_red = :id_red";
        $params = [
            ':id_red' => $this->id_red,
            ':tipo_conexion' => $this->tipo_conexion,
            ':direccion_ip' => $this->direccion_ip,
            ':grupo_trabajo' => $this->grupo_trabajo,
            ':id_equipo' => $this->id_equipo
        ];

        $resultado = $this->ejecutarConsulta($query, $params);

        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar una configuracion de red
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_red = :id_red";
        $params = [':id_red' => $this->id_red];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para verificar que la ip no exista en otro equipo
    public function verificarIpExiste($direccion_ip, $id_red = null)
    {
        $query = "SELECT id_red FROM " . self::TABLA . " WHERE direccion_ip = :direccion_ip";
        $params = [':direccion_ip' => $direccion_ip];

        // Si se proporciona un ID de red, lo excluimos de la verificación
        if ($id_red !== null) {
            $query .= " AND id_red != :id_red";
            $params[':id_red'] = $id_red;
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
        if (!empty($filtros['fecha_creacion_red'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_red'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_red'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_red BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_red BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_red'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_red'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_red'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_red BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_red BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_red',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_red',
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
