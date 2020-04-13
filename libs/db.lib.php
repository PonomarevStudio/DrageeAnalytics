<?php

define('MYSQL_SERVER', $_ENV['MYSQL_SERVER']);
define('MYSQL_USER', $_ENV['MYSQL_USER']);
define('MYSQL_PASSWORD', $_ENV['MYSQL_PASS']);
define('MYSQL_DB', $_ENV['MYSQL_DB']);

$MYSQL_CONNECTION = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
$GLOBALS['MYSQL_CONNECTION'] = $MYSQL_CONNECTION;

mysqli_query($MYSQL_CONNECTION, "set character_set_connection=utf8;");
mysqli_query($MYSQL_CONNECTION, "set character_set_client=utf8;");
mysqli_query($MYSQL_CONNECTION, "set character_set_results=utf8;");

if (!function_exists('dbg')) {
    function dbg($message = false)
    {
        error_log($message);
        return false;
    }
}

class DB
{

    public static $VERSION = '1.1';

    public static function select($table_name, $fields, $where = "", $order = "", $up = true, $limit = "")
    {
        global $MYSQL_CONNECTION;
        for ($i = 0; $i < count($fields); $i++) {
            if ((strpos($fields[$i], "(") === false) && ($fields[$i] != "*")) $fields[$i] = "`" . $fields[$i] . "`";
        }
        $fields = implode(",", $fields);
        if (!$order) $order = "";
        else {
            if ($order != "RAND()") {
                $order = "ORDER BY `$order`";
                if (!$up) $order .= " DESC";
            } else $order = "ORDER BY $order";
        }
        if ($limit) $limit = "LIMIT $limit";
        if ($where) $query = "SELECT $fields FROM $table_name WHERE $where $order $limit";
        else $query = "SELECT $fields FROM $table_name $order $limit";
        $res = mysqli_query($MYSQL_CONNECTION, $query) or dbg('ERROR IN QUERY [SELECT, ' . $table_name . ', ' . $where . '] RESPONSE: ' . mysqli_error($MYSQL_CONNECTION));
        return $res;
    }

    public static function update($table_name, $upd_fields, $where)
    {
        global $MYSQL_CONNECTION;
        $query = "UPDATE $table_name SET ";
        foreach ($upd_fields as $field => $value) $query .= "`$field` = '" . addslashes($value) . "',";
        $query = substr($query, 0, -1);
        if ($where) {
            $query .= " WHERE $where";
            $res = mysqli_query($MYSQL_CONNECTION, $query) or dbg('ERROR IN QUERY [UPDATE, ' . $table_name . ', ' . $where . '] RESPONSE: ' . mysqli_error($MYSQL_CONNECTION));
            return $res;
        } else return false;
    }

    public static function insert($table_name, $new_value)
    {
        global $MYSQL_CONNECTION;
        $table_name = $table_name;
        $query = "INSERT INTO $table_name (";
        foreach ($new_value as $field => $value) $query .= "`" . $field . "`,";
        $query = substr($query, 0, -1);
        $query .= ") VALUES (";
        foreach ($new_value as $value) $query .= "'" . addslashes($value) . "',";
        $query = substr($query, 0, -1);
        $query .= ")";
        $res = mysqli_query($MYSQL_CONNECTION, $query) or dbg('ERROR IN QUERY [INSERT, ' . $table_name . '] RESPONSE: ' . mysqli_error($MYSQL_CONNECTION));
        return $res;
    }

    public static function delete($table_name, $where = "")
    {
        global $MYSQL_CONNECTION;
        if ($where) {
            $query = "DELETE FROM $table_name WHERE $where";
            $res = mysqli_query($MYSQL_CONNECTION, $query) or dbg('ERROR IN QUERY [DELETE, ' . $table_name . ', ' . $where . '] RESPONSE: ' . mysqli_error($MYSQL_CONNECTION));
            return $res;
        } else return false;
    }

    public static function query($query)
    {
        global $MYSQL_CONNECTION;
        $res = mysqli_query($MYSQL_CONNECTION, $query) or dbg('ERROR IN QUERY [' . $query . '] RESPONSE: ' . mysqli_error($MYSQL_CONNECTION));
        return $res;
    }

    public static function mysql_exit()
    {
        global $MYSQL_CONNECTION;
        mysqli_close($MYSQL_CONNECTION);
    }
}