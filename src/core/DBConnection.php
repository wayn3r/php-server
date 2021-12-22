<?php

namespace Core;

class DBConnection {
    private static \Core\DBConnection $instance;
    public $conexion;
    public string $MainDB;


    private function __construct() {
        $this->connect();
    }
    public function __destruct() {
        $this->disconnect();
    }
    public static function getInstance(): self {
        self::$instance ??= new \Core\DBConnection();
        return self::$instance;
    }
    public static function autocommit(bool $autocommit = false) {
        self::getInstance();
        odbc_autocommit(self::$instance->conexion, $autocommit);
    }
    public static function commit() {
        self::getInstance();
        odbc_commit(self::$instance->conexion);
    }
    public static function rollback() {
        self::getInstance();
        odbc_rollback(self::$instance->conexion);
    }
    public function query(string $sqlQuery, string ...$args): \Core\DBResponse {
        $sqlQuery = $this->prepareSQL($sqlQuery, ...$args);
        $sqlQuery = utf8_decode($sqlQuery);
        try {
            $request = new \Core\DBResponse(
                odbc_exec($this->conexion, $sqlQuery),
                false
            );
            if ($request->result !== false) {
                return $request;
            }

            $request->error = true;
            $request->message = odbc_errormsg();
            return $request;
        } catch (\Exception $e) {
            \Core\DBConnection::rollback();
            throw new \Exception("Ha ocurrido un error al intentar consultar la base de datos \n" . $e->getMessage());
        }
    }

    private function preventSQLInjection(string $param): string {
        $param = str_replace(["'", '"', '`', '$', '%', '#', '&'], '', $param);
        return $param;
    }
    private function prepareSQL(string $sql, string ...$args): string {
        // Se buscan los parametros(?) que esten en el SQL
        preg_match_all('/(\?)(:[1-9]\d*)*/i', $sql, $params);
        $params = $params[0]; //el resultado siempre esta en la primera posicion
        // se divide el SQL por cada parametro encontrado(?)
        $sqlArray = preg_split('/(\?)(:[1-9]\d*)*/i', $sql);
        // Asignando un numero a los parametros(?) que no lo tienen
        $position = 1;
        foreach ($params as $index => $param) {
            if ($param !== '?') continue;
            while (in_array($param . ':' . $position, $params))
                $position++;
            $params[$index] = $param . ':' . $position;
        }
        // obteniendo un array con los parametros(?) como indice y la cantidad de veces que se repite como valir
        $sqlArgsCount = count(array_count_values($params));
        $argsCount = count($args);
        if ($argsCount < $sqlArgsCount) {
            throw new \Exception("Se han definido [{$sqlArgsCount}] argumentos en la consulta, y se solo se han pasado [{$argsCount}] argumento(s)");
        }
        foreach ($params as $index => $param) {
            $_position = \Helpers\Tools::leftTrim('?:', $param) - 1;
            $sqlArray[$index] .= $this->preventSQLInjection($args[$_position]);
        }
        return implode('', $sqlArray);
    }
    private function dbconfig() {
        return json_decode(file_get_contents('Core/database.json'));
    }

    private function connect() {
        $config = $this->dbconfig();

        $this->MainDB = $config->database;
        $this->conexion = odbc_connect($config->odbc, $config->user, $config->password);
    }

    private function disconnect() {
        odbc_close($this->conexion);
    }
}
