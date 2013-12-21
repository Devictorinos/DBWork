<?php

namespace DBWork;

use PDO;

abstract class Query
{
    protected $dbh;
    
    public $table;
    public $fields;
    public $query;
    public $alias;

    public function __construct($dbh, $table, $alias, $fields)
    {
        $this->dbh    = $dbh;
        $this->table  = $table;
        $this->alias  = $alias;
        $this->fields = $fields;

    }
}
