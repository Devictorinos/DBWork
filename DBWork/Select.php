<?php

namespace DBWork;

use PDO;

use PDOException;

class Select extends Query
{
    public $joins = array();

    public $where  = array();
    public $params = array();
    public $groupBy;

    public $order  = array();
    public $limit  = null;


    //WHERE CLAUSE
    public function where($field, $operation, $subject)
    {
        $this->where[]  = "$this->table.$field $operation ?";
        $this->params[] = $subject;
        return $this;
    }

    //WHERE IN
    public function whereIn($field, array $list)
    {
        $fList  =  $this->inClause($list);
        $this->where[] = "$this->table.$field IN  $fList";
        $this->params  = array_merge($this->params, $list);
        return $this;

    }

    //WHERE BETWEEN
    public function whereBetween($field, $a, $b)
    {
        $this->where[]  = " $($this->table.$field BETWEEN ? AND ?) ";
        $this->params[] = $a;
        $this->params[] = $b;
        return $this;
    }

    //JOIN
    public function join(Select $table, $on)
    {
        return $this->innerJoin($table, $on);
    }

    //INNER JOIN
    public function innerJoin(Select $table, $on)
    {
        $this->joins[] = array("subject"=>$table, "on"=>$on, "join"=>"INNER JOIN");
        return $this;
    }

    //INNER JOIN
    public function leftJoin(Select $table, $on)
    {
        $this->joins[] = array("subject"=>$table, "on"=>$on, "join"=>"LEFT JOIN");
        return $this;
    }

    //INNER JOIN
    public function rightJoin(Select $table, $on)
    {
        $this->joins[] = array("subject"=>$table, "on"=>$on, "join"=>"RIGHT JOIN");
        return $this;
    }

    //INNER JOIN
    public function outerJoin(Select $table, $on)
    {
        $this->joins[] = array("subject"=>$table, "on"=>$on, "join"=>"OUTER JOIN");
        return $this;
    }

    // //GROUP BY
    // public function groupBy($field)
    // {
    //     $this->groupBy = " $field ";
    //     return $this;
    // }

    //ORDER BY
    public function orderBy($field, $asc = true)
    {
        if ($asc) {
            $this->order[] = "$this->table.$field ASC";
        } else {
            $this->order[] = "$this->table.$field DESC";
        }

        return $this;

    }

    //LIMIT
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    //changing fields values to ? for pdo statement.
    private function inClause($list)
    {

        $list = array_map(function ($i) {

            return "?";

        }, $list);

        $list = implode(",", $list);

        return "($list)";
    }

    public function buildJoin()
    {

        $sql = array();
        $objects = array($this);

        foreach ($this->joins as $obj) {

            $leftTable  = $this->table;
            $rightTable = $obj['subject']->table;

            $sql[] = $obj['join'] . " " . $rightTable;
            $sql[] = "ON $leftTable." . $obj['on'] . " = $rightTable." . $obj['on'];

            
            list($_objects, $_sql) = $obj['subject']->buildJoin();
            $objects = array_merge($objects, $_objects);
            $sql = array_merge($_sql, $sql);
        }

        return array($objects, $sql);

    }

    //building sql
    private function buildSQL($delimiter = "\n")
    {

        $sql = array();
        $fields = array();
        $where = array();
        $params = array();
        $orderBy = array();

        list($_objects, $_sql) = $this->buildJoin();

        foreach ($_objects as $object) {
            $fields  = array_merge($fields, $object->fields);
            $where   = array_merge($where, $object->where);
            $params  = array_merge($params, $object->params);
            $orderBy = array_merge($orderBy, $object->order);
        }

        $fields = implode(", ", $fields);

        $sql[] = "SELECT $fields";

        $sql[] ="FROM $this->table";

        $sql = array_merge($sql, $_sql);

        if (!empty($where)) {
            $sql[] = "WHERE ". implode(" AND ", $where);
        }

        // if (!empty($this->on)) {

        //     $sql[] = "ON ".implode(" ", $this->on);
        // }

        // if (!empty($this->groupBy)) {

        //     $sql[] = "GROUP BY $this->groupBy";
        // }
        
        if (!empty($orderBy)) {
            $orderBy = implode(", ", $orderBy);
            $sql[] = "ORDER BY $orderBy";
        }

        if (!empty($this->limit)) {

            $sql[] = "LIMIT $this->limit ";
        }

        $sql = implode($delimiter, $sql);
        return array($sql, $params);
    }


    //Executeing query
    private function runSQL($debug = false)
    {
        list($sql, $params) = $this->buildSQL();

        if ($debug) {

            Log::query($sql, $params);
        }

        try {

            $query = $this->dbh->prepare($sql);

            foreach ($params as $key => $val) {
                
                $type = is_null($val) ? PDO::PARAM_NULL : PDO::PARAM_STR;
                $type = is_bool($val) ? PDO::PARAM_BOOL : PDO::PARAM_STR;
                $type = is_integer($val) ? PDO::PARAM_INT : PDO::PARAM_STR;

                $query->bindValue($key+1, $val, $type);
            }
            
            $query->execute();
            return $query;
            
        } catch (PDOException $e) {

            Log::error($e);
        }

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
