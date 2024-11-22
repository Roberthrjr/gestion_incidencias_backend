<?php

class Componente
{
    private $conn;
    private const TABLA = "componentes";

    public $id_componente;
    public $numero_serie_componente;
    public $descripcion_componente;
    public $marca_componente;
    public $modelo_componente;
    public $id_equipo;
    public $foto_componente;
    public $estado_componente;
    public $fecha_creacion_componente;
    public $fecha_modificacion_componente;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un componente por ID
    public function obtenerPorId($id_componente)
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE id_componente = :id_componente LIMIT 1";
        $params = [':id_componente' => $id_componente];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener componentes paginadas con filtros
    public function obtenerComponentes($limit = 10, $offset = 0, $filtros = [])
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

    // Metodo para registrar un nuevo componente
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " (numero_serie_componente, descripcion_componente, marca_componente, modelo_componente, id_equipo, foto_componente) VALUES (:numero_serie_componente, :descripcion_componente, :marca_componente, :modelo_componente, :id_equipo, :foto_componente)";
        $params = [
            ':numero_serie_componente' => $this->numero_serie_componente,
            ':descripcion_componente' => $this->descripcion_componente,
            ':marca_componente' => $this->marca_componente,
            ':modelo_componente' => $this->modelo_componente,
            ':id_equipo' => $this->id_equipo,
            ':foto_componente' => $this->foto_componente,
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualzar un componente
    public function actualizar()
    {
        $this->sanitizarDatos();

        $query = "UPDATE " . self::TABLA . " SET 
                numero_serie_componente = :numero_serie_componente, 
                descripcion_componente = :descripcion_componente, 
                marca_componente = :marca_componente, 
                modelo_componente = :modelo_componente, 
                id_equipo = :id_equipo";

        $params = [
            ':id_componente' => $this->id_componente,
            ':numero_serie_componente' => $this->numero_serie_componente,
            ':descripcion_componente' => $this->descripcion_componente,
            ':marca_componente' => $this->marca_componente,
            ':modelo_componente' => $this->modelo_componente,
            ':id_equipo' => $this->id_equipo
        ];

        if (isset($this->foto_componente)) {
            $query .= ", foto_componente = :foto_componente";
            $params[':foto_componente'] = $this->foto_componente;
        }

        $query .= " WHERE id_componente = :id_componente";
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un componente
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_componente = :id_componente";
        $params = [':id_componente' => $this->id_componente];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar foto del componente
    public function actualizarFoto()
    {
        $query = "UPDATE " . self::TABLA . " SET foto_componente = :foto_componente WHERE id_componente = :id_componente";
        $params = [
            ':foto_componente' => $this->foto_componente,
            ':id_componente' => $this->id_componente
        ];
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para obtener la ruta actual de la foto del componente
    public function obtenerRutaFoto($id_componente)
    {
        $query = "SELECT foto_componente FROM " . self::TABLA . " WHERE id_componente = :id_componente";
        $params = [':id_componente' => $id_componente];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado ? $resultado['foto_componente'] : null;
    }

    // Metodo para saber si existe componente por ID
    public function existeComponentePorId($id_componente)
    {
        $query = "SELECT id_componente FROM " . self::TABLA . " WHERE id_componente = :id_componente LIMIT 1";
        $params = [':id_componente' => $id_componente];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Metodo para cambiar el estado del componente
    public function cambiarEstado()
    {
        $query = "UPDATE " . self::TABLA . " SET estado_componente = :estado_componente WHERE id_componente = :id_componente";
        $params = [
            ':estado_componente' => $this->estado_componente,
            ':id_componente' => $this->id_componente
        ];

        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para verificar si el numero de serie de componente ya existe
    public function verificarNumeroSerieComponenteExiste($numero_serie_componente, $id_componente = null)
    {
        $query = "SELECT id_componente FROM " . self::TABLA . " WHERE numero_serie_componente = :numero_serie_componente";
        $params = [':numero_serie_componente' => $numero_serie_componente];

        if ($id_componente !== null) {
            $query .= " AND id_componente != :id_componente";
            $params[':id_componente'] = $id_componente;
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
        if (!empty($filtros['fecha_creacion_componente'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion_componente'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion_componente'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion_componente BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion_componente BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion_componente'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion_componente'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion_componente'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion_componente BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion_componente BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion_componente',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion_componente',
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
