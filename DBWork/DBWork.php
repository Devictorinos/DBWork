<?php


namespace DBWork;

use PDO;
use Exeption;

class DBWork
{

    private $dbh;

    public function __construct($host, $database, $username, $password)
    {

        $dbc = 'mysql:host=' . $host . ';dbname=' . $database;
        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

        try {
            $this->dbh = new PDO($dbc.';charset=utf8', $username, $password, $options);

        } catch (Exception $e) {
            Log::error($e);
        }
    }

    private function fieldClause($table, $fields)
    {
        if (is_null($fields)) {
            return array();
        }

        if (is_string($fields)) {
            $fields = array($fields);
        }

        $fields = array_map(function ($field) use ($table) {
            return "$table.$field";
        }, $fields);

        return $fields;
    }

    public function select($table, $fields = null)
    {
        $fields = $this->fieldClause($table, $fields);
            
        return new Select($this->dbh, $table, $fields);
        
    }

    // public function select($table, $alias, $fields)
    // {
    //     $fields = $this->fieldClause($fields, $alias);
            
    //      return new SelectQuery($this->dbh, $table, $alias, $fields);
        
    // }

    public function transaction(callable $callback)
    {
        $this->dbh->beginTransaction();

        try {

            call_user_func($callback);

        } catch (Exception $e) {

            Log::error($e);
            $this->dbh->rollBack();

        }

        $this->dbh->commit();
    }
}
