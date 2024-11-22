<?php

class Equipo
{
    private $conn;
    private const TABLA = "equipos";

    public $id_equipo;
    public $codigo_patrimonial_equipo;
    public $nombre_equipo;
    public $marca_equipo;
    public $modelo_equipo;
    public $foto_equipo;
    public $id_area;
    public $id_subcategoria;
    public $estado_equipo;
    public $fecha_creacion_equipo;
    public $fecha_modificacion_equipo;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un equipo por ID
    public function obtenerPorId($id_equipo)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_equipo = :id_equipo LIMIT 1";
        $params = [':id_equipo' => $id_equipo];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener equipos paginados con filtros
    public function obtenerEquipos($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar un nuevo equipo
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (codigo_patrimonial_equipo, nombre_equipo, marca_equipo, modelo_equipo, foto_equipo, id_area, id_subcategoria) VALUES (:codigo_patrimonial_equipo, :nombre_equipo, :marca_equipo, :modelo_equipo, :foto_equipo, :id_area, :id_subcategoria)";
        $params = [
            ':codigo_patrimonial_equipo' => $this->codigo_patrimonial_equipo,
            ':nombre_equipo' => $this->nombre_equipo,
            ':marca_equipo' => $this->marca_equipo,
            ':modelo_equipo' => $this->modelo_equipo,
            ':foto_equipo' => $this->foto_equipo,
            ':id_area' => $this->id_area,
            ':id_subcategoria' => $this->id_subcategoria
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar un equipo
    public function actualizar()
    {
        $this->sanitizarDatos();
        $query = "UPDATE " . self::TABLA . " SET codigo_patrimonial_equipo = :codigo_patrimonial_equipo, nombre_equipo = :nombre_equipo, marca_equipo = :marca_equipo, modelo_equipo = :modelo_equipo, id_area = :id_area, id_subcategoria = :id_subcategoria";

        $params = [
            ':id_equipo' => $this->id_equipo,
            ':codigo_patrimonial_equipo' => $this->codigo_patrimonial_equipo,
            ':nombre_equipo' => $this->nombre_equipo,
            ':marca_equipo' => $this->marca_equipo,
            ':modelo_equipo' => $this->modelo_equipo,
            ':id_area' => $this->id_area,
            ':id_subcategoria' => $this->id_subcategoria,
        ];

        if (isset($this->foto_equipo)) {
            $query .= ", foto_equipo = :foto_equipo";
            $params[':foto_equipo'] = $this->foto_equipo;
        }

        $query .= " WHERE id_equipo = :id_equipo";
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un equipo
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_equipo = :id_equipo";
        $params = [':id_equipo' => $this->id_equipo];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar foto del equipo
    public function actualizarFoto()
    {
        $query = "UPDATE " . self::TABLA . " SET foto_equipo = :foto_equipo WHERE id_equipo = :id_equipo";
        $params = [
            ':foto_equipo' => $this->foto_equipo,
            ':id_equipo' => $this->id_equipo
        ];
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para obtener la ruta actual de la foto del equipo
    public function obtenerRutaFoto($id_equipo)
    {
        $query = "SELECT foto_equipo FROM " . self::TABLA . " WHERE id_equipo = :id_equipo";
        $params = [':id_equipo' => $id_equipo];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado ? $resultado['foto_equipo'] : null;
    }

    // Metodo para saber si existe equipo por ID
    public function existeEquipoPorId($id_equipo)
    {
        $query = "SELECT id_equipo FROM " . self::TABLA . " WHERE id_equipo = :id_equipo LIMIT 1";
        $params = [':id_equipo' => $id_equipo];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Metodo para cambiar el estado del equipo
    public function cambiarEstado()
    {
        $query = "UPDATE " . self::TABLA . " SET estado_equipo = :estado_equipo WHERE id_equipo = :id_equipo";
        $params = [
            ':estado_equipo' => $this->estado_equipo,
            ':id_equipo' => $this->id_equipo
        ];

        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para verificar si el codigo patrimonial ya existe
    public function verificarCodPatExiste($codigo_patrimonial_equipo, $id_equipo = null)
    {
        $query = "SELECT id_periferico FROM " . self::TABLA . " WHERE codigo_patrimonial_equipo = :codigo_patrimonial_equipo";
        $params = [':codigo_patrimonial_equipo' => $codigo_patrimonial_equipo];

        if ($id_equipo !== null) {
            $query .= " AND id_equipo != :id_equipo";
            $params[':id_equipo'] = $id_equipo;
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
        if (!empty($filtros['fecha_creacion_equipo'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_equipo'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_equipo'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_equipo BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_equipo BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_equipo'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_equipo'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_equipo'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_equipo BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_equipo BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_equipo',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_equipo',
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
