<?php

namespace DBWork;

use PDO;

abstract class Query
{
    protected $dbh;
    
    public $table;
    public $fields;

    public function __construct($dbh, $table, $fields)
    {
        $this->dbh    = $dbh;
        $this->table  = $table;
        $this->fields = $fields;
    }

    //Select Select Method
    public function getAll($debug = false)
    {
        return $this->runSQL($debug)->fetchAll(PDO::FETCH_ASSOC);
    }

    //select One Row Method
    public function getOne($debug = false)
    {
        return $this->runSQL($debug)->fetch(PDO::FETCH_ASSOC);
    }
}
