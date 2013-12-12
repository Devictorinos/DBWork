<?php

namespace DBWork;

use PDO;
use PDOException;

class Update extends Query
{
    public $where     = array();
    public $params    = array();
    public $subjects  = array();
    public $keysVals      = array();


    public function set($fields)
    {

        $this->keysVals = array_merge($this->keysVals, $fields);
        return $this;
    }


    public function buildSQL()
    {
        $sql = array();

        $sql[] = "UPDATE $this->table";
        $keys   = array_keys($this->keysVals);
        $values = array_values($this->keysVals);

       
        $keys = array_map(function ($field) {
            return "`$field` = ?";
        }, $keys);

        if (!empty($keys)) {
            
            $sql[] = "SET ".implode(",", $keys)."";
        } else {
            throw new Exception("Error there is no Keys Provided!!", 1);
            
        }

        $sql = implode("\n", $sql);

        Log::query($sql, $values);
        // var_dump($fields);
        // var_dump($params);

    }
}
