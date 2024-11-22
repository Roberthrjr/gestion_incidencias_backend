<?php

class Usuario
{
    private $conn;
    private const TABLA = "usuarios";

    public $id_usuario;
    public $email;
    public $nombres;
    public $apellidos;
    public $telefono;
    public $tipo_documento;
    public $numero_documento;
    public $clave;
    public $estado;
    public $foto;
    public $id_area;
    public $id_cargo;
    public $id_rol;
    public $fecha_creacion;
    public $fecha_modificacion;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metodo para obtener un usuario por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT 
                id_usuario, 
                nombres, 
                apellidos, 
                email, 
                telefono, 
                tipo_documento, 
                numero_documento, 
                estado, 
                foto, 
                id_area, 
                id_cargo, 
                id_rol, 
                fecha_creacion, 
                fecha_modificacion 
              FROM " . self::TABLA . " WHERE id_usuario = :id_usuario LIMIT 1";
        $params = [':id_usuario' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener solo la clave del usuario por ID
    public function obtenerClavePorId($id)
    {
        $query = "SELECT clave FROM " . self::TABLA . " WHERE id_usuario = :id_usuario LIMIT 1";
        $params = [':id_usuario' => $id];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para obtener usuarios paginados con filtros
    public function obtenerUsuarios($limit = 10, $offset = 0, $filtros = [])
    {
        $query = "SELECT 
                id_usuario, 
                nombres, 
                apellidos, 
                email, 
                telefono, 
                tipo_documento, 
                numero_documento, 
                estado, 
                foto, 
                id_area, 
                id_cargo, 
                id_rol, 
                fecha_creacion, 
                fecha_modificacion 
              FROM " . self::TABLA;

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


    // Metodo para crear un usuario
    public function registrar()
    {
        $this->sanitizarDatos();
        $query = "INSERT INTO " . self::TABLA . " 
            (nombres, apellidos, email, telefono, tipo_documento, numero_documento, clave, estado, foto, id_area, id_cargo, id_rol) 
            VALUES 
            (:nombres, :apellidos, :email, :telefono, :tipo_documento, :numero_documento, :clave, :estado, :foto, :id_area, :id_cargo, :id_rol)";

        $params = [
            ':nombres' => $this->nombres,
            ':apellidos' => $this->apellidos,
            ':email' => $this->email,
            ':telefono' => $this->telefono,
            ':tipo_documento' => $this->tipo_documento,
            ':numero_documento' => $this->numero_documento,
            ':clave' => $this->clave,
            ':estado' => $this->estado,
            ':foto' => $this->foto,
            ':id_area' => $this->id_area,
            ':id_cargo' => $this->id_cargo,
            ':id_rol' => $this->id_rol
        ];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar un usuario
    public function actualizar()
    {
        $this->sanitizarDatos();

        $query = "UPDATE " . self::TABLA . " SET nombres = :nombres, apellidos = :apellidos, email = :email, telefono = :telefono, tipo_documento = :tipo_documento, numero_documento = :numero_documento, id_area = :id_area, id_cargo = :id_cargo, id_rol = :id_rol";

        $params = [
            ':id_usuario' => $this->id_usuario,
            ':nombres' => $this->nombres,
            ':apellidos' => $this->apellidos,
            ':email' => $this->email,
            ':telefono' => $this->telefono,
            ':tipo_documento' => $this->tipo_documento,
            ':numero_documento' => $this->numero_documento,
            ':id_area' => $this->id_area,
            ':id_cargo' => $this->id_cargo,
            ':id_rol' => $this->id_rol
        ];

        if (isset($this->foto)) {
            $query .= ", foto = :foto";
            $params[':foto'] = $this->foto;
        }

        $query .= " WHERE id_usuario = :id_usuario";
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para eliminar un usuario
    public function eliminar()
    {
        $query = "DELETE FROM " . self::TABLA . " WHERE id_usuario = :id_usuario";
        $params = [':id_usuario' => $this->id_usuario];
        return $this->ejecutarConsulta($query, $params);
    }

    // Metodo para actualizar foto del usuario
    public function actualizarFoto()
    {
        $query = "UPDATE " . self::TABLA . " SET foto = :foto WHERE id_usuario = :id_usuario";
        $params = [
            ':foto' => $this->foto,
            ':id_usuario' => $this->id_usuario
        ];
        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para obtener la ruta actual de la foto del usuario
    public function obtenerRutaFoto($id_usuario)
    {
        $query = "SELECT foto FROM " . self::TABLA . " WHERE id_usuario = :id_usuario";
        $params = [':id_usuario' => $id_usuario];
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado ? $resultado['foto'] : null;
    }

    // Metodo para actualizar la clave del usuario
    public function actualizarClave()
    {
        try {
            $query = "UPDATE " . self::TABLA . " SET clave = :clave WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':clave', $this->clave);
            $stmt->bindParam(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al actualizar la clave: " . $e->getMessage());
            return false;
        }
    }

    // Metodo para cambiar el estado del usuario
    public function cambiarEstado()
    {
        $query = "UPDATE " . self::TABLA . " SET estado = :estado WHERE id_usuario = :id_usuario";
        $params = [
            ':estado' => $this->estado,
            ':id_usuario' => $this->id_usuario
        ];

        $resultado = $this->ejecutarConsulta($query, $params);
        return $resultado !== false && $this->conn->errorCode() === '00000';
    }

    // Metodo para verificar si el email ya existe
    public function verificarEmailExiste($email, $id_usuario = null)
    {
        $query = "SELECT id_usuario FROM " . self::TABLA . " WHERE email = :email";
        $params = [':email' => $email];

        // Si se proporciona un ID de usuario, lo excluimos de la verificación
        if ($id_usuario !== null) {
            $query .= " AND id_usuario != :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }

        $query .= " LIMIT 1";
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Metodo para verificar si el numero de documento ya existe
    public function verificarNumeroDocumentoExiste($numero_documento, $id_usuario = null)
    {
        $query = "SELECT id_usuario FROM " . self::TABLA . " WHERE numero_documento = :numero_documento";
        $params = [':numero_documento' => $numero_documento];

        // Si se proporciona un ID de usuario, lo excluimos de la verificación
        if ($id_usuario !== null) {
            $query .= " AND id_usuario != :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }

        $query .= " LIMIT 1";
        $resultado = $this->ejecutarConsulta($query, $params, true);
        return $resultado !== false;
    }

    // Metodo buscar un usuario por su email
    public function obtenerPorEmail()
    {
        $query = "SELECT * FROM " . self::TABLA . " WHERE email = :email LIMIT 1";
        $params = [':email' => $this->email];
        return $this->ejecutarConsulta($query, $params, true);
    }

    // Metodo para saber si existe usuario por ID
    public function existeUsuarioPorId($id)
    {
        $query = "SELECT id_usuario FROM " . self::TABLA . " WHERE id_usuario = :id_usuario LIMIT 1";
        $params = [':id_usuario' => $id];
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
                if ($key === 'email') {
                    $this->$key = filter_var($value, FILTER_SANITIZE_EMAIL);
                } else {
                    $this->$key = htmlspecialchars(strip_tags($value));
                }
            }
        }
    }

    private function construirClausulasWhere($filtros)
    {
        $whereClauses = [];
        $params = [];

        // Filtrar por fecha de creación
        if (!empty($filtros['fecha_creacion'])) {
            $filtros['fecha_inicio_creacion'] = $filtros['fecha_creacion'] . " 00:00:00";
            $filtros['fecha_fin_creacion'] = $filtros['fecha_creacion'] . " 23:59:59";
            $whereClauses[] = "fecha_creacion BETWEEN :fecha_inicio_creacion AND :fecha_fin_creacion";
            $params[':fecha_inicio_creacion'] = $filtros['fecha_inicio_creacion'];
            $params[':fecha_fin_creacion'] = $filtros['fecha_fin_creacion'];
        } elseif (!empty($filtros['fecha_creacion_desde']) && !empty($filtros['fecha_creacion_hasta'])) {
            $whereClauses[] = "fecha_creacion BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta";
            $params[':fecha_creacion_desde'] = $filtros['fecha_creacion_desde'];
            $params[':fecha_creacion_hasta'] = $filtros['fecha_creacion_hasta'];
        }

        // Filtrar por fecha de modificación
        if (!empty($filtros['fecha_modificacion'])) {
            $filtros['fecha_inicio_modificacion'] = $filtros['fecha_modificacion'] . " 00:00:00";
            $filtros['fecha_fin_modificacion'] = $filtros['fecha_modificacion'] . " 23:59:59";
            $whereClauses[] = "fecha_modificacion BETWEEN :fecha_inicio_modificacion AND :fecha_fin_modificacion";
            $params[':fecha_inicio_modificacion'] = $filtros['fecha_inicio_modificacion'];
            $params[':fecha_fin_modificacion'] = $filtros['fecha_fin_modificacion'];
        } elseif (!empty($filtros['fecha_modificacion_desde']) && !empty($filtros['fecha_modificacion_hasta'])) {
            $whereClauses[] = "fecha_modificacion BETWEEN :fecha_modificacion_desde AND :fecha_modificacion_hasta";
            $params[':fecha_modificacion_desde'] = $filtros['fecha_modificacion_desde'];
            $params[':fecha_modificacion_hasta'] = $filtros['fecha_modificacion_hasta'];
        }

        // Otros filtros dinámicos
        foreach ($filtros as $campo => $valor) {
            if (!in_array($campo, [
                'fecha_creacion',
                'fecha_creacion_desde',
                'fecha_creacion_hasta',
                'fecha_inicio_creacion',
                'fecha_fin_creacion',
                'fecha_modificacion',
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
