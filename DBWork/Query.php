<?php

namespace DBWork;

use PDO;

abstract class Query
{
    protected $dbh;
    
    public $table;
    public $fields;
    public $query;

    public function __construct($dbh, $table, $fields)
    {
        $this->dbh    = $dbh;
        $this->table  = $table;
        $this->fields = $fields;
    }
}
