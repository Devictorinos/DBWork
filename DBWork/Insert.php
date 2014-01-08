<?php

/**
 * Nice DB Work class.
 *
 * @package Nice DB Work
 * @author Viktor Lubchuk <viktorlubchuk@gmail.com>
 */

namespace DBWork;

use PDO;

use PDOException;

use Exception;

class Insert extends Query
{
    public $where    = array();
    public $params   = array();
    public $fields   = array();
    public $values   = array();
    public $sql      = array();

    /**
    * method fieldsValues, public access is assumed
    */    
    public function fieldsValues(array $subjects)
    {

        //setting fields to insert
        $fields = array_keys($subjects);
        $this->fields = array_map(function ($field) {
            return "`$field`";
        }, $fields);
        $this->fields = implode(",", $this->fields);


        //setting params and values to insert
        $this->params = array_values($subjects);

        $this->values = array_map(function ($val) {
            return "?";
        }, $this->params);

        $this->values = implode(",", $this->values);

        return $this;
    }

    private function buildSQL()
    {
        $this->sql[] = "INSERT INTO $this->table ($this->fields)"."\n";
        $this->sql[] = "VALUES ($this->values)";
        $this->sql = implode(" ", $this->sql);
        return $this->sql;
    }

    /**
    * method runSQL, public access is assumed
    */
    public function runSQL($debug = false)
    {
        $sql = $this->buildSQL();
        
        if ($debug) {
            Log::query($sql, $this->params);
        }

        $this->dbh->beginTransaction();
        try {

            $query = $this->dbh->prepare($sql);

            foreach ($this->params as $key => $param) {
                
                $type = is_null($param)    ? PDO::PARAM_NULL : PDO::PARAM_STR;
                $type = is_bool($param)    ? PDO::PARAM_BOOL : PDO::PARAM_STR;
                $type = is_integer($param) ? PDO::PARAM_INT  : PDO::PARAM_STR;

                $query->bindValue($key+1, $param, $type);
            }

            $query->execute();

            $this->dbh->commit();

            if ($query) {
                return true;
            } else {
                return false;
            }
           
        } catch (PDOException $e) {
            Log::error($e);
            $this->dbh->rollBack();
        }
       
    }
}
