<?php

namespace DBWork;

use PDO;
use PDOException;
use Exception;

class Update extends Query
{
    public $where     = array();
    public $params    = array();
    public $subjects  = array();
    public $keysVals  = array();
    public $sql;


    public function set($fields)
    {

        $this->keysVals = array_merge($this->keysVals, $fields);
        return $this;
    }
   

    private function buildSQL()
    {
        $validFields = array();

        //Getting auto increment field from data base
        
        $_field = $this->dbh->prepare("SHOW INDEX FROM `people` WHERE key_name = 'PRIMARY'");
        $_field->execute();
        $autoInc_field = $_field->fetch(PDO::FETCH_ASSOC);
        $autoInc_field = $autoInc_field['Column_name'];
      
        //Checking if field are exists in  array fields to update
        if (array_key_exists(''.$autoInc_field.'', $this->keysVals)) {
             
             $where = "`$autoInc_field`";
             $keyVal = intval($this->keysVals[''.$autoInc_field.'']);
             unset($this->keysVals[''.$autoInc_field.'']);

        } else {
            throw new Exception("Error : There is no primary key field in array. Check your Fields array", 1);
            exit();
            
        }

        $keys   = array_keys($this->keysVals);
        $values = array_values($this->keysVals);

        //Adding where value to array values
        array_push($values, $keyVal);

        //setting where array
        $this->where = "WHERE $where = ?";

        $sql = array();

        $sql[] = "UPDATE $this->table";
       
        $keys = array_map(function ($field) {
            return "`$field` = ?";
        }, $keys);


        if (!empty($keys)) {
            
            $sql[] = "SET ".implode(",", $keys)."";
        } else {
            throw new Exception("Error there is no Keys Provided!!", 1);
            exit();
        }

        $sql[] = $this->where;

        $sql = implode("\n", $sql);
        $this->sql = $sql;
        $this->params = $values;

        var_dump($this->params);
        return array($this->sql, $this->params);


    }

    public function runSQL($debug = false)
    {
        $sql;
        $params = array();

        list($sql, $params) = $this->buildSQL();

        if ($debug) {
            Log::query($sql, $params);
        }

        $this->dbh->beginTransaction();

        try {
            
            $query = $this->dbh->prepare($sql);

            foreach ($params as $key => $val) {
             
                $type = is_null($val)    ? PDO::PARAM_NULL : PDO::PARAM_STR;
                $type = is_bool($val)    ? PDO::PARAM_BOOL : PDO::PARAM_STR;
                $type = is_integer($val) ? PDO::PARAM_INT  : PDO::PARAM_STR;

                $query->bindValue($key+1, $val, $type);
                
            }

            $query->execute();

            $this->dbh->commit();
        
            return true;
            
        } catch (PDOException $e) {
             Log::error($e);
             $this->dbh->rollBack();

        }
    }
}
