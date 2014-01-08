<?php

/**
 * Nice DB Work class.
 *
 * @package Nice DB Work
 * @author Viktor Lubchuk <viktorlubchuk@gmail.com>
 *
 * @abstract
 */

namespace DBWork;

use PDO;

abstract class Query
{
    protected $dbh;
    public $table;
    public $fields;
    public $alias;

    public function __construct($dbh, $table, $alias, $fields)
    {
        $this->dbh    = $dbh;
        $this->table  = $table;
        $this->alias  = $alias;
        $this->fields = $fields;

    }
}
