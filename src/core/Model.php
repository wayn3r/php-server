<?php

namespace Core;

abstract class Model {
    private \Core\DBConnection $db;
    private string $notDataBaseFieldSymbol = '@';
    private string $className;
    protected bool $throwDataBaseError = true;
    protected string $MainDB;
    protected bool $global = false;
    protected string $table;
    protected array $props;
    /**
     * @var string[] $foreigns Son las propiedas que son de otras tablas
     */
    protected array $foreigns = [];
    /**
     * Verifica que el modelo sea válido
     *  @param bool $strict
     *  Determina si la validación es estricta, si TRUE todas las validaciones se evaluaran, si FALSE el $strict sera TRUE si no esta definido el campo "cod"
     * @param bool $cod_required
     * Determina si el codigo es requerido para pasar la validación o no
     *  @return array
     * Devuelve un array con los errores que encuentra en las propiedades del modelo,
     * si no hay errores devuelve un array vacio
     */
    public abstract function validate(bool $strict = false, bool $cod_required = false): array;
    public function __construct($fields = null) {
        // obteniendo el nombre del modelo para mostralo en los errores
        $this->className = get_class($this);
        // Asignando valores del modelo
        if (is_array($fields)) $this->bind($fields);
        // Conexion a la base de datos
        $this->db = DBConnection::getInstance();
        $this->MainDB = $this->db->MainDB;
    }
    public function bind(array $fields, string $prefix = '') {
        // Asignando valores de array al modelo
        foreach ($fields as $prop => $value) {
            if (!\Helpers\Tools::startsWith($prefix, $prop))
                continue;
            $prop = \Helpers\Tools::leftTrim($prefix, $prop);
            if (in_array($prop, $this->props))
                $prop = array_keys($this->props, $prop)[0];

            if (!property_exists($this, $prop))
                continue;
            $this->set($prop, $value);
        }
    }
    public function getFieldsString(string $prefix = '', bool $alias = false): string {
        $result = '';
        $asPrefix = !empty($prefix) ? $prefix . '.' : '';
        foreach ($this->props as $field) {
            if (substr($field, 0, 1) === $this->notDataBaseFieldSymbol) continue;
            $as = $alias ? " AS \"{$prefix}_{$field}\"" : '';
            $result .= <<<INPUT
                {$asPrefix}"{$field}"{$as},
                INPUT;
        }
        return rtrim($result, ',');
    }
    public function Query(string $SqlQuery, string ...$args): \Core\DBResponse {
        try {
            if ($this->global) {
                $SqlQuery = str_replace("\"{$this->MainDB}\".", '', $SqlQuery);
            }
            $response = $this->db->query($SqlQuery, ...$args);
            if ($this->throwDataBaseError && $response->error) {
                \Core\DBConnection::rollback();
                throw new \Exception($response->message);
            }
            return $response;
        } catch (\Exception $e) {
            \Core\DBConnection::rollback();
            throw $e;
        }
    }
    public function getTable(): string {
        return $this->table;
    }
    public function prop(string $prop): string {
        $this->validar_array_prop('Definala para poder usarla.', false, $prop);
        return $this->props[$prop];
    }
    public function getLastCod() {
        $sql = <<<INPUT
            SELECT "{$this->props['cod']}" FROM "{$this->MainDB}"."{$this->table}" ORDER BY "{$this->props['cod']}" DESC LIMIT 1
            INPUT;
        $result = $this->Query($sql);
        if ($row = odbc_fetch_array($result->result)) {
            return $row[$this->props['cod']];
        }
        return '0';
    }
    public function getNextCod() {
        return $this->getLastCod() + 1;
    }
    public function toArray(bool $dbfields = false): array {
        $data = [];
        foreach ($this->props as $prop => $dbfield) {
            if ($dbfields && substr($dbfield, 0, 1) === $this->notDataBaseFieldSymbol) continue;
            $key = $dbfields ? $dbfield : $prop;
            $key = ltrim($key, $this->notDataBaseFieldSymbol);
            if (isset($this->$prop))
                $data[$key] = $this->$prop;
        }
        return $data;
    }
    protected function set(string $prop, $value) {
        $type = $this->getPropType($prop);
        $prop = ltrim($prop, $this->notDataBaseFieldSymbol);

        switch ($type) {
            case 'float':
            case 'int':
                if (!is_numeric($value)) return;
                return $this->$prop = floatval($value);
            case 'string':
                if (empty($value) || !is_scalar($value)) return;
                $regex_date = '\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])';
                $regex_time = '([01]\d|2[0-4])(:[0-5]\d){2}';
                $regex_miliseconds = '\d{9}';
                $regex_date_time_miliseconds = '/^' . $regex_date . ' ' . $regex_time . '.' . $regex_miliseconds . '$/';
                // echo $regex_date_time_miliseconds;
                // echo $value;
                // exit;
                if (preg_match($regex_date_time_miliseconds, $value)) {
                    $value = explode('.', $value)[0];
                    $regex_empty_time = '[0]{2}(:00){2}';
                    $regex_date_empty_time = '/^' . $regex_date . ' ' . $regex_empty_time . '$/';
                    if (preg_match($regex_date_empty_time, $value)) {
                        $value = explode(' ', $value)[0];
                    }
                }
                return $this->$prop = utf8_encode($value);
            case 'DateTime':
                if (gettype($value) === 'object' && get_class($value) === 'DateTime')
                    return $this->$prop = $value;
                $value = explode('.', $value);
                $value = $value[0];
                return $this->$prop = new \DateTime($value);
            default:
                if (class_exists($type))
                    return $this->$prop = new $type($value);
            case 'none':
                if (gettype($value) === $type)
                    return $this->$prop = $value;
        }
    }
    /**
     * Separa de un string la propiedad del modelo que se encuentre dentro de parentesis
     * @param string $method
     * El string del metodo SQL con la propiedad
     * @return array 
     * Devueleve un array de dos posiciones en la primera la propiedad del modelo
     * en la segunda el string con metodo SQL.
     * 
     * Si no se encuentran parentesis devolvera un string vacio para la segunda posicion   
     */
    protected function getSqlMethod(string $method): array {
        if (!preg_match(
            '/(?<=(\w\())[^()]+(?=\)$)/i',
            $method,
            $method_params
        )) return [$method, ''];

        preg_match(
            '/(?<={)[^}]+(?=})/',
            $method_params[0],
            $prop
        );

        $prop = $prop[0] ?? $method_params[0];
        $method = str_replace($prop, '?', $method);
        return [$prop, $method];
    }
    /**
     * Esta funcion solo crea string para where con el formato sgte: Prefijo1.
     * "campo1" = 'valor1' AND Prefijo2."campo2" = 'valor2'...
     * La estructura del objeto de la variable objwhere debe ser la sgte:
     * ["campo"=>"valor"]
     */
    protected function where(array $objWhere, string $prefix = "", string $operador = 'AND') {
        $result = "";
        $variables = [];
        foreach ($objWhere as $property => $value) {
            // si el valor es un array se asumira que los campos dentro 
            // del array se separaran por OR
            if (is_array($value) && is_numeric($property)) {
                $result .= '(';
                [$where, $whereVariables] = $this->where($value, $prefix, 'OR');
                $result .= $where . " ) {$operador} ";
                $variables = array_merge($variables, $whereVariables);
                continue;
            }
            [$property, $method] = $this->getSqlMethod($property);
            $property = isset($this->props[$property]) ? $this->props[$property] : $property;
            $this->validar_array_prop('Definala para poder realizar consultas con ella.', false, $property);

            if (substr($property, 0, 1) === $this->notDataBaseFieldSymbol)
                throw new \Exception("Se esta tratando de realizar una consulta con una propiedad que no esta definida en la base de datos: [{$property}]");


            // valores nulos
            $null_values = ['null', 'not null', null];

            // si el valor es un array pero el indice no es numerico se asumira que
            // los valores dentro se separan por OR con la misma proriedad
            if (
                is_array($value)
                && count($value) > 0
                && !is_numeric($property)
            ) {
                foreach ($value as $comparador => $__value) {
                    $__value = $__value ?? 'null';
                    $result .= $this->setFieldString(
                        $property,
                        $prefix,
                        is_string($comparador)
                            ? $comparador
                            : '=',
                        'OR',
                        $method,
                        in_array(strtolower($__value), $null_values)
                    );
                    $variables[] = $__value;
                }

                $result = rtrim($result, 'OR ');
                $result .= " {$operador} ";
                continue;
            }

            $value = $value ?? 'null';

            $result .= $this->setFieldString(
                $property,
                $prefix,
                '=',
                $operador,
                $method,
                in_array(strtolower($value), $null_values)
            );
            $variables[] = $value;
        }
        if ($variables)
            $result = rtrim($result, $operador . ' ');
        return [$result, $variables];
    }
    /**
     * @param string $comparador '='|'<='|'>='|'<'|'>'|'is' 
     */
    private function setFieldString(
        string $field,
        string $prefix,
        string $comparador,
        string $operador,
        string $method = '',
        bool $noquote = false
    ) {
        $prefix = !empty($prefix) ? $prefix . '.' : '';
        $method = str_replace(['{', '}'], '', $method);
        if (!empty($method))
            $result = str_replace('?', $prefix . '"' . $field . '"', $method);
        else $result = <<<INPUT
            {$prefix}"{$field}"
        INPUT;
        if ($noquote)
            return $result .= " {$comparador} ? {$operador} ";
        return $result .= " {$comparador} '?' {$operador} ";
    }
    protected function getPropType(string $prop): string {
        if (!property_exists($this, $prop))
            throw new \Exception("La propiedad [{$prop}] no existe en el modelo [{$this->className}], no se pudo obtener el tipo de dato de [{$prop}]");

        $property = new \ReflectionProperty($this, $prop);
        if ($property->isPrivate())
            throw new \Exception("La propiedad [{$prop}] es privada para el modelo [{$this->className}], no se pudo obtener el tipo de dato de [{$prop}]");

        if (!$property->hasType())
            return 'none';
        return $property->getType()->getName();
    }
    private function getValuesString(): array {
        $valuesString = '';
        $valuesVariables = [];
        foreach ($this->props as $key => $prop) {
            if (substr($prop, 0, 1) === $this->notDataBaseFieldSymbol) continue;
            if (isset($this->$key)) {
                $valuesString .= "'?',";
                $valuesVariables[] = $this->$key;
            } else
                $valuesString .= " NULL,";
        }
        $valuesString = rtrim($valuesString, ',');
        return [$valuesString, $valuesVariables];
    }
    #region CRUD FUNCTIONS
    public function List(array $where = []): array {
        $this->validar_propiedad('Definala para realizar la petición', 'table');

        $fields = $this->getFieldsString();
        [$where, $whereVariables] = $this->where($where);
        $sql = <<<INPUT
            SELECT {$fields} FROM "{$this->MainDB}"."{$this->table}"
            INPUT;
        if (isset($where) && $where !== '') {
            $sql .= <<<INPUT
                 WHERE {$where}
                INPUT;
        }
        $result = $this->Query($sql, ...$whereVariables);
        $data = [];
        while ($row = odbc_fetch_array($result->result)) {
            $newModel = clone $this;
            $newModel->bind($row);
            $data[] = $newModel;
        }
        return $data;
    }
    public function find(string $key, string $value): bool {
        if (!isset($this->props[$key])) return false;

        $fields = $this->getFieldsString();
        $sql = <<<INPUT
            SELECT {$fields} FROM "{$this->MainDB}"."{$this->table}" WHERE "{$this->props[$key]}" = '?' LIMIT 1;
            INPUT;

        $result = $this->Query($sql, $value);
        if ($row = odbc_fetch_array($result->result)) {
            $this->bind($row);
            return true;
        }
        return false;
    }
    public function findFields(string $key, string $value, array $fields): bool {
        $this->validar_propiedad('Definala para realizar la petición', 'table');
        if (!isset($this->props[$key])) return false;

        $fieldsString = '';
        foreach ($fields as $prop) {
            if (isset($this->props[$prop]))
                $fieldsString .= <<<INPUT
                    "{$this->props[$prop]}",
                    INPUT;
        }
        $fieldsString = rtrim($fieldsString, ',');
        $sql = <<<INPUT
            SELECT {$fieldsString} FROM "{$this->MainDB}"."{$this->table}" WHERE "{$this->props[$key]}" = '?' LIMIT 1;
            INPUT;

        $result = $this->Query($sql, $value);
        if ($row = odbc_fetch_array($result->result)) {
            $this->bind($row);
            return true;
        }
        return false;
    }
    public function ListFields(array $fields, array $where = []): array {
        $this->validar_propiedad('Definala para realizar la petición', 'table');

        $fieldsString = '';
        foreach ($fields as $prop) {
            if (isset($this->props[$prop]))
                $fieldsString .= <<<INPUT
                    "{$this->props[$prop]}",
                    INPUT;
        }
        $fieldsString = rtrim($fieldsString, ',');
        [$where, $whereVariables] = $this->where($where);
        $sql = <<<INPUT
            SELECT {$fieldsString} FROM "{$this->MainDB}"."{$this->table}"
            INPUT;
        if (isset($where) && $where !== '') {
            $sql .= <<<INPUT
                WHERE {$where}
            INPUT;
        }
        $result = $this->Query($sql, ...$whereVariables);
        $data = [];
        while ($row = odbc_fetch_array($result->result)) {
            $newModel = clone $this;
            $newModel->bind($row);
            $data[] = $newModel;
        }
        return $data;
    }
    public function UpdateStatus() {
        $this->validar_propiedad('Definala para realizar la petición', 'table');
        $this->validar_array_prop('Definala para realizar la petición', true, 'cod', 'estado');

        $sql = <<<INPUT
            UPDATE "{$this->MainDB}"."{$this->table}" 
            SET "{$this->props['estado']}" = '?'
            WHERE "{$this->props['cod']}" = '?';
            INPUT;
        $result = $this->Query($sql, $this->estado, $this->cod);
        return $result;
    }
    public function Save() {
        $this->validar_propiedad('Definala para realizar la petición', 'table');
        $this->validar_array_prop('Definala para realizar la petición', false, 'cod');

        // Se busca el siguiente codigo para asignarlo al registro
        $this->cod = $this->getNextCod();
        [$valuesString, $data] = $this->getValuesString();
        $sql = <<<INPUT
            INSERT INTO "{$this->MainDB}"."{$this->table}" (
                {$this->getFieldsString()}
            ) VALUES (
                {$valuesString}
            )
            INPUT;
        $result = $this->Query($sql, ...$data);
        return $result;
    }
    public function Update() {
        $this->validar_propiedad('Definala para realizar la petición', 'table');
        $this->validar_array_prop('Definala para realizar la petición', true, 'cod');

        $sql = <<<INPUT
            UPDATE "{$this->MainDB}"."{$this->table}" SET
            INPUT;
        $variables = [];
        foreach ($this->props as $key => $prop) {
            if (substr($prop, 0, 1) === $this->notDataBaseFieldSymbol) continue;
            if (isset($this->$key)) {
                $sql .= <<<INPUT
                     "{$prop}" = '?',
                    INPUT;
                $variables[] = $this->$key;
            }
        }
        $sql = rtrim($sql, ',');
        $sql .= <<<INPUT
             WHERE "{$this->props['cod']}" = '?'
            INPUT;
        $variables[] = $this->cod;
        $result = $this->Query($sql, ...$variables);
        return $result;
    }
    public function findByFields(array $props) {
        [$where, $whereVariables] = $this->where($props);
        $fields = $this->getFieldsString();
        $sql = <<<INPUT
            SELECT {$fields} FROM "{$this->MainDB}"."{$this->table}" WHERE {$where} LIMIT 1;
            INPUT;
        $result = $this->Query($sql, ...$whereVariables);
        if ($row = odbc_fetch_array($result->result)) {
            $this->bind($row);
            return true;
        }
        return false;
    }
    public function Upsert(array $objwhere) {
        $this->validar_propiedad('Definala para realizar la petición', 'table');
        $this->validar_array_prop('Definala para realizar la petición', false, 'cod');

        if (count($objwhere) == 0) throw new \Exception("La variable where no tiene objetos para realizar la operacion, modelo [{$this->className}]");
        if (!$this->validateArray($objwhere)) throw new \Exception("La estructura del array no es correcta, es posible que contenga propiedades no definidas en el modelo [{$this->className}] o valores vacios");

        [$where, $whereVariables] = $this->where($objwhere);
        $clone = clone $this;
        if ($clone->findByFields($objwhere))
            $this->cod = $clone->cod;
        else $this->cod = $this->getNextCod();

        [$valuesString, $data] = $this->getValuesString();
        $sql = <<<INPUT
            UPSERT "{$this->MainDB}"."{$this->table}" (
            {$this->getFieldsString()}
            ) VALUES (
                {$valuesString}
             ) WHERE "{$this->props["cod"]}"=(SELECT "{$this->props["cod"]}" FROM "{$this->MainDB}"."{$this->table}" WHERE {$where});
            INPUT;
        $result = $this->Query($sql, ...array_merge($data, $whereVariables));
        return $result;
    }
    #endregion

    #region Validación generales
    public function validateArray(array $array): bool {
        foreach ($array as $key => $value) {
            if (!(isset($this->props[$key]) || in_array($key, $this->props)) || !isset($value)) {
                return false;
            }
        }
        return true;
    }

    private function validar_propiedad(string $error_message, string ...$propiedades) {
        foreach ($propiedades as $propiedad) {
            if (
                !isset($this->$propiedad)
                || empty($this->$propiedad)
            ) {
                throw new \Exception("La propiedad [{$propiedad}] no esta definida para el modelo [{$this->className}]" . (!empty($error_message) ? '. ' . $error_message : ''));
            }
        }
    }
    private function validar_array_prop(string $error_message, bool $force_value, string ...$propiedades) {
        foreach ($propiedades as $propiedad) {
            if (
                (!isset($this->props[$propiedad])
                    || empty($this->props[$propiedad]))
                && !in_array($propiedad, $this->props)
            ) {
                throw new \Exception("La propiedad [{$propiedad}] no esta definida en los props del modelo [{$this->className}]" . (!empty($error_message) ? '. ' . $error_message : ''));
            }
            if (
                ($force_value && !isset($this->$propiedad))
                || ($force_value && empty($this->$propiedad))
            ) {
                throw new \Exception("La propiedad [{$propiedad}] no esta inicializada en el modelo [{$this->className}]" . (!empty($error_message) ? '. ' . $error_message : ''));
            }
        }
    }
    #endregion


    #region Metodos para el dataset aun no se donde poner esto 
    /**
     * Este metodo busca los modelos que esten relacionados al modelo actual
     *  
     * 
     * @param array $models - Son todos los modelos que esten relacionados directamente con el modelo actual
     * El modelo relacionado debe tener una variable declarada preferiblemente en el dataset, 
     * y el nombre de la variable del modelo debe ser 
     */
    public function populate($models = []) {
        $models = $models ?: $this->foreigns;
        $cache = \Core\Cache::getInstance();
        foreach ($models as $model) {
            $prop = \Helpers\Tools::uncapitalize($model);
            $modelClass = $this->getPropType($model);
            if (
                isset($this->$prop)
                && !isset($this->$model)
            ) {
                $key = $this->className . $prop . $this->$prop;
                $this->$model = $cache->use(
                    $key,
                    function () use ($modelClass, $prop) {
                        $data = new $modelClass;
                        $data->find('cod', $this->$prop);
                        return $data;
                    }
                );
            }
        }
    }
}
