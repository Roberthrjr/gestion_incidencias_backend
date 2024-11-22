<?php

class Periferico
{
    private $conn;
    private const TABLA = "perifericos";

    public $id_periferico;
    public $codigo_patrimonial_periferico;
    public $numero_serie_periferico;
    public $descripcion_periferico;
    public $marca_periferico;
    public $modelo_periferico;
    public $id_equipo;
    public $foto_periferico;
    public $estado_periferico;
    public $fecha_creacion_periferico;
    public $fecha_modificacion_periferico;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un periferico por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_periferico = :id_periferico LIMIT 1";
        $params = [':id_periferico' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener perifericos paginados con filtros
    public function obtenerPerifericos($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar un nuevo periferico
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (codigo_patrimonial_periferico, numero_serie_periferico, descripcion_periferico, marca_periferico, modelo_periferico, id_equipo, foto_periferico) VALUES (:codigo_patrimonial_periferico, :numero_serie_periferico, :descripcion_periferico, :marca_periferico, :modelo_periferico, :id_equipo, :foto_periferico)";
        $params = [
            ':codigo_patrimonial_periferico' => $this->codigo_patrimonial_periferico,
            ':numero_serie_periferico' => $this->numero_serie_periferico,
            ':descripcion_periferico' => $this->descripcion_periferico,
            ':marca_periferico' => $this->marca_periferico,
            ':modelo_periferico' => $this->modelo_periferico,
            ':id_equipo' => $this->id_equipo,
            ':foto_periferico' => $this->foto_periferico
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar un periferico
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET codigo_patrimonial_periferico = :codigo_patrimonial_periferico, numero_serie_periferico = :numero_serie_periferico, descripcion_periferico = :descripcion_periferico, marca_periferico = :marca_periferico, modelo_periferico = :modelo_periferico, id_equipo = :id_equipo";

        $params = [
            ':id_periferico' => $this->id_periferico,
            ':codigo_patrimonial_periferico' => $this->codigo_patrimonial_periferico,
            ':numero_serie_periferico' => $this->numero_serie_periferico,
            ':descripcion_periferico' => $this->descripcion_periferico,
            ':marca_periferico' => $this->marca_periferico,
            ':modelo_periferico' => $this->modelo_periferico,
            ':id_equipo' => $this->id_equipo
        ];

        if (isset($this->foto_periferico)) {
            $query .= ", foto_periferico = :foto_periferico";
            $params[':foto_periferico'] = $this->foto_periferico;
        }

        $query .= " WHERE id_periferico = :id_periferico";
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un periferico
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_periferico = :id_periferico";
        $params = [':id_periferico' => $this->id_periferico];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar foto del periferico
    public function actualizarFoto()
    {
        $query = "UPDATE " . self::TABLA . " SET foto_periferico = :foto_periferico WHERE id_periferico = :id_periferico";
        $params = [
            ':foto_periferico' => $this->foto_periferico,
            ':id_periferico' => $this->id_periferico
        ];
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para obtener la ruta actual de la foto del periferico
    public function obtenerRutaFoto($id_periferico)
    {
        $query = "SELECT foto_periferico FROM " . self::TABLA . " WHERE id_periferico = :id_periferico";
        $params = [':id_periferico' => $id_periferico];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado ? $resultado['foto_periferico'] : null;
    }

    // Metodo para saber si existe periferico por ID
    public function existePerifericoPorId($id_periferico)
    {
        $query = "SELECT id_periferico FROM " . self::TABLA . " WHERE id_periferico = :id_periferico LIMIT 1";
        $params = [':id_periferico' => $id_periferico];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Metodo para cambiar el estado del periferico
    public function cambiarEstado()
    {
        $query = "UPDATE " . self::TABLA . " SET estado_periferico = :estado_periferico WHERE id_periferico = :id_periferico";
        $params = [
            ':estado_periferico' => $this->estado_periferico,
            ':id_periferico' => $this->id_periferico
        ];

        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para verificar si el codigo patrimonial ya existe
    public function verificarCodPatExiste($codigo_patrimonial_periferico, $id_periferico = null)
    {
        $query = "SELECT id_periferico FROM " . self::TABLA . " WHERE codigo_patrimonial_periferico = :codigo_patrimonial_periferico";
        $params = [':codigo_patrimonial_periferico' => $codigo_patrimonial_periferico];

        if ($id_periferico !== null) {
            $query .= " AND id_periferico != :id_periferico";
            $params[':id_periferico'] = $id_periferico;
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
        if (!empty($filtros['fecha_creacion_periferico'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_periferico'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_periferico'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_periferico BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_periferico BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_periferico'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_periferico'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_periferico'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_periferico BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_periferico BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_periferico',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_periferico',
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
