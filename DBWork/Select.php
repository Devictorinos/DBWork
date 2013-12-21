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

    //OR

    public function __call($method, $args)
    {
        if (preg_match("/^where(\d+)/", $method, $matches)) {
            $group = (int)$matches[1];
            $this->where($group, $args[0], $args[1], $args[2]);
            // var_dump($args);
        }

        if (preg_match("/^whereIn(\d+)/", $method, $matches)) {
            $group = (int)$matches[1];
            //var_dump($group);
            $this->whereIn($group, $args[0], $args[1]);
        }

        if (preg_match("/^whereBetween(\d+)/", $method, $matches)) {
            $group = (int)$matches[1];
            $this->whereBetween($group, $args[0], $args[1], $args[2]);
        }

        // echo "<pre>";
        //  print_r($this);
        //  echo "</pre>";
        return $this;
    }

    //WHERE CLAUSE
    public function where($group, $field, $operation, $subject)
    {
        $this->where[$group][]  = "$this->alias.$field $operation ?";
        $this->params[$group][] = $subject;
         // echo "<pre>";
         // print_r($this);
         // echo "</pre>";
        return $this;
    }

    //WHERE IN
    public function whereIn($group, $field, array $list)
    {
        $fList  =  $this->inClause($list);
        $this->where[$group][] = "$this->alias.$field IN  $fList";
        $this->params[$group][] = isset($this->params[$group]) ? array_merge($this->params[$group], $list) : $list;
        return $this;

    }

    //WHERE BETWEEN
    public function whereBetween($group, $field, $a, $b)
    {
        $this->where[$group][] = "($this->alias.$field BETWEEN ? AND ?) ";
        $this->params[$group][] = $a;
        $this->params[$group][] = $b;
        return $this;
    }

    //JOIN
    public function join(Select $table, $alias, $on)
    {
        return $this->innerJoin($table, $alias, $on);
    }

    //INNER JOIN
    public function innerJoin(Select $table, $alias, $on)
    {
        $this->joins[] = array("subject"=>$table, "ali"=>$alias, "on"=>$on, "join"=>"INNER JOIN");
        var_dump($this);
        return $this;
        
    }

    //LEFT JOIN
    public function leftJoin(Select $table, $alias, $on)
    {
        $this->joins[] = array("subject"=>$table, "ali"=>$alias, "on"=>$on, "join"=>"LEFT JOIN");
        return $this;
    }

    //RIGHT JOIN
    public function rightJoin(Select $table, $alias, $on)
    {
        $this->joins[] = array("subject"=>$table, "ali"=>$alias, "on"=>$on, "join"=>"RIGHT JOIN");
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
            $this->order[] = "$this->alias.$field ASC";
        } else {
            $this->order[] = "$this->alias.$field DESC";
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

            $leftTable  = $this->alias;
            $rightTable = $obj['subject']->table;
            var_dump($obj['join']);
            $sql[] = $obj['join'] . " " . $rightTable . " as " . $obj['ali'];
            $sql[] = "ON $leftTable." . $obj['on'] . " = ".$obj['ali']."." . $obj['on'];

            
            list($_objects, $_sql) = $obj['subject']->buildJoin();
            $objects = array_merge($objects, $_objects);
            $sql = array_merge($_sql, $sql);
        }

        return array($objects, $sql);

    }

    //building sql
    private function buildSQL($delimiter = "\n")
    {

        $sql      = array();
        $fields   = array();
        $where    = array();
        $params   = array();
        $orderBy  = array();

        list($_objects, $_sql) = $this->buildJoin();

        foreach ($_objects as $object) {
            $fields  = array_merge($fields, $object->fields);
            
            foreach ($object->where as $key => $value) {
                $where[$key] = isset($where[$key]) ? array_merge($where[$key], $value) : $value;
            }

            foreach ($object->params as $key => $value) {
                $params[$key] = isset($params[$key]) ? array_merge($params[$key], $value) : $value;
            }

            $orderBy = array_merge($orderBy, $object->order);
        }

        $fields = implode(", ", $fields);

        $sql[] = "SELECT $fields";

        $sql[] ="FROM $this->table as $this->alias";

        $sql = array_merge($sql, $_sql);

        if (!empty($where)) {
            
            $whereSql = array();

            foreach ($where as $group) {
                $whereSql[] = "(" . implode(" AND ", $group) . ")";
            }

            $whereSql = "WHERE " . implode(' OR ', $whereSql);

            $sql[] = $whereSql;
        }

        if (!empty($orderBy)) {
            $orderBy = implode(", ", $orderBy);
            $sql[] = "ORDER BY $orderBy";
        }

        if (!empty($this->limit)) {

            $sql[] = "LIMIT $this->limit ";
        }

        $sql = implode($delimiter, $sql);

        $flatten_params = array();
        array_walk_recursive($params, function ($a) use (&$flatten_params) {
            $flatten_params[] = $a;
        });

        return array($sql, $flatten_params);
    }


    //Executeing query
    protected function runSQL($debug = false)
    {
        list($sql, $params) = $this->buildSQL();

        if ($debug) {
            Log::query($sql, $params);
        }

        try {

            $query = $this->dbh->prepare($sql);

            foreach ($params as $key => $val) {
                
                $type = is_null($val)    ? PDO::PARAM_NULL : PDO::PARAM_STR;
                $type = is_bool($val)    ? PDO::PARAM_BOOL : PDO::PARAM_STR;
                $type = is_integer($val) ? PDO::PARAM_INT  : PDO::PARAM_STR;

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
