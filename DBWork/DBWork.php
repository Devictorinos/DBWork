<?php

/**
 * Nice DB Work class.
 *
 * @package Nice DB Work
 * @author Viktor Lubchuk <viktorlubchuk@gmail.com>
 */

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

            if (strpos($field, "(")) {

                preg_match("/\(.*?(.*)\)/", $field, $matches);
                $argm = $matches[1];

                $field = preg_replace("/$argm/", "`$table`.$argm", $field);
            
                return "$field";

            } else {

                return "`$table`.$field";

            }

        }, $fields);

        return $fields;
    }


    private function tableClause($table)
    {
        $table = "`$table`";
        return $table;
    }


    /**
    * method select, public access is assumed
    */
    public function select($table, $alias, $fields = null)
    {
        $fields = $this->fieldClause($alias, $fields);

        return new Select($this->dbh, $table, $alias, $fields);
        
    }


    /**
    * method update, public access is assumed
    */
    public function update($table)
    {
        $table = $this->tableClause($table, $alias = null, $fields = null);

        return new Update($this->dbh, $table, $alias, $fields);
    }


    /**
    * method insert, public access is assumed
    */
    public function insert($table, $alias = null, $fields = null)
    {
        $table = $this->tableClause($table);
        return new Insert($this->dbh, $table, $alias, $fields);
    }


    /**
    * method delete, public access is assumed
    */
    public function delete($table, $alias = null, $fields = null)
    {
        $table  = $this->tableClause($table);
        return new Delete($this->dbh, $table, $alias, $fields);
    }


    /**
    * method truncate, public access is assumed
    */
    public function truncate($table, $alias = null, $fields = null)
    {
        $table = $this->tableClause($table);
        return new Truncate($this->dbh, $table, $alias, $fields);
    }

    // public function select($table, $alias, $fields)
    // {
    //     $fields = $this->fieldClause($fields, $alias);
            
    //      return new SelectQuery($this->dbh, $table, $alias, $fields);
        
    // }

    // public function transaction(callable $callback)
    // {
    //     $this->dbh->beginTransaction();
        
    //     try {
    //         call_user_func($callback);

    //     } catch (Exception $e) {

    //         Log::error($e);
    //         $this->dbh->rollBack();

    //     }
    //     $this->dbh->commit();
    // }
}
