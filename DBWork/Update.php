<?php

namespace DBWork;

use PDO;
use PDOException;

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



    // private function chekField(array $fields, array $DBfields)
    // {
        
    //     $result = array_diff($fields, $DBfields);
    //     $res = $result == true ? false : true;
    //     return $res;
    // }


    private function buildSQL()
    {
        $validFields = array();
        $keys   = array_keys($this->keysVals);
        $values = array_values($this->keysVals);

       // //Checking if all fields are exists in Data Base
       //  $_fields = $this->dbh->prepare("DESCRIBE $this->table");
       //  $_fields->execute();
       //  $table_fields = $_fields->fetchAll(PDO::FETCH_COLUMN);

       //  $invalidFields = $this->chekField($keys, $table_fields);//IF RETURNING FALSE ITS MEAN ALL FIELDS ARE EXISTS IN DATA BASE

       //  var_dump($invalidFields);

        //Getting auto increment field from data base
        $_field = $this->dbh->prepare("SHOW columns FROM $this->table WHERE extra LIKE '%auto_increment%'");
        $_field->execute();
        $autoInc_field = $_field->fetchAll(PDO::FETCH_COLUMN);

        //Checking if autoincremet field exists in array fields
        $auto_inc = array_intersect($autoInc_field, $keys);
        if (!empty($auto_inc)) {
            $id = array_shift($auto_inc);
        }

        //Unsetting auto increment field and his value from array and inserting his value to a variable
        for ($i=0; $i < count($keys); $i++) {

            if ($keys[$i] === $id) {
                unset($keys[$i]);
                $val = $values[$i];
                unset($values[$i]);
            }
        }


        //setting where array
        $whereArray = [$id => $val];
        $this->where = "WHERE `$id` = $val";

        //Adding where value to array values
      //  array_push($values, $val);
        $sql = array();


        $sql[] = "UPDATE $this->table";
       
        $keys = array_map(function ($field) {
            return "`$field` = '?'";
        }, $keys);

        // var_dump($keys);
        // var_dump($values);

        // var_dump($auto_inc);

        if (!empty($keys)) {
            
            $sql[] = "SET ".implode(",", $keys)."";
        } else {
            throw new Exception("Error there is no Keys Provided!!", 1);
            
        }

        $sql[] = $this->where;

        $sql = implode("\n", $sql);
        $this->sql = $sql;
        $this->params = $values;

       // Log::query($this->sql, $this->params);
        // var_dump($fields);
        // var_dump($params);
        return array($this->sql, $this->params);


    }

    public function runSQL($debug = false)
    {
        $sql;
        $params = array();
        list($sql, $params) = $this->buildSQL();

        // var_dump($sql);
         //var_dump($params);

        if ($debug) {
            Log::query($sql, $params);
        }

       // $this->dbh->beginTransaction();

        try {
            
            $query = $this->dbh->prepare($sql);
            var_dump($sql);
            foreach ($params as $key => $val) {

                // $type = is_null($val)    ? PDO::PARAM_NULL : PDO::PARAM_STR;
                // $type = is_bool($val)    ? PDO::PARAM_BOOL : PDO::PARAM_STR;
                // $type = is_integer($val) ? PDO::PARAM_INT  : PDO::PARAM_STR;

                // $query->bindValue($key+1, $val, $type);
               // $query->execute(array $params);
            }

            //$query->execute(array($params));
             var_dump($query);
           // $this->dbh->commit();
            if ($query) {
                echo "Updated";
            } else {
                echo "not Updated";
            }
            
        } catch (PDOException $e) {
             Log::error($e);
             //$this->dbh->rollBack();

        }
    }
}
