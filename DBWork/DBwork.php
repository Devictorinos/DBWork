<?php


namespace DBwork;

use PDO;
use PDOExeption;

class DBWork
{

    private $dbh;
    private $config;
    private $valid;

    public function __construct()
    {

        $this->config = new Config();
        $this->valid = new CheckValid();

        $dbc = 'mysql:host=' . $this->config->host . ';dbname=' . $this->config->dbName;
        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

        try {
            $this->dbh = new PDO($dbc.';charset=utf8', $this->config->userName, $this->config->password, $options);

        } catch (PDOException $e) {

            echo $e->getMessage();
        }
    }



    private function fieldClause($fields)
    {

        // var_dump($alias);
        // var_dump($fields);

        // if(is_array($fields)) {
        //  $alias = $alias;
        //  $fields = array_map(function($field) use ($alias) {
        //      return" $alias.`$field`";
        //  },$fields);

        // }

        $fields = implode(",", $fields);
        return $fields;

    }

    public function select($table, $alias, $fields)
    {
        $fields = $this->fieldClause($fields, $alias);
            
         return new SelectQuery($this->dbh, $table, $alias, $fields);
        
    }
}
