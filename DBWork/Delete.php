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

class Delete extends Query
{
    public $where   = array();
    public $params  = array();
    public $limit   = array();

    public function __call($method, $agrs)
    {
        if (preg_match("/^where(\d+)/", $method, $matches)) {
            $group = (int)$matches[1];
            $this->where($group, $agrs[0], $agrs[1], $agrs[2]);
        }

        if (preg_match("/^whereIn(\d+)/", $method, $matches)) {
            $group = (int)$matches[1];
            $this->whereIn($group, $agrs[0], $agrs[1]);
        }

        if (preg_match("/^whereBetween(\d+)/", $method, $matches)) {
            $group = (int)$matches[1];
            $this->whereBetween($group, $agrs[0], $agrs[1], $agrs[2]);
        }

         // echo "<pre>";
         // print_r($this);
         // echo "</pre>";
        return $this;
    }

    //WHERE CLAUSE
    /**
    * method where, public access is assumed
    */
    public function where($group, $field, $operation, $subject)
    {
        $this->where[$group][]  = "$this->table.`$field` $operation ?";
        $this->params[$group][] = $subject;
         // echo "<pre>";
         // print_r($this);
         // echo "</pre>";
        return $this;
    }

    //WHERE IN
    /**
    * method whereIn, public access is assumed
    */
    public function whereIn($group, $field, array $list)
    {
        $flist = $this->inClause($list);
        $this->where[$group][] = " $this->table.`$field` IN $flist";
        $this->params[$group][] = isset($this->params[$group]) ? array_merge($this->params[$group], $list) : $list;
         // echo "<pre>";
         // print_r($this);
         // echo "</pre>";
        return $this;
    }

    /**
    * method limit, public access is assumed
    */
    public function limit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }


    private function inClause($list)
    {
        $list = array_map(function ($i) {
            return "?";

        }, $list);

        $list = implode(",", $list);

        return "($list)";
    }


    private function buildSQL($delimiter = "\n")
    {
        $sql     = array();
        $where   = array();
        $params  = array();
        $limit   = array();
        
        

        //BUILDING QUERY
        $sql[] = " DELETE FROM $this->table";
        //var_dump($sql);

        foreach ($this->where as $key => $value) {
            $where[$key] = isset($where[$key]) ? array_merge($where[$key], $value) : $value;
        }

        foreach ($this->params as $key => $value) {
            
            $params[$key] = isset($params[$key]) ? array_merge($params[$key], $value) : $value;
            
        }
        // print_r($params);
        // echo "<br>";
        // print_r($where);

        if (!empty($where)) {
            
            $whereSQL = array();

            foreach ($where as $group) {

                $whereSQL[] = "(" . implode(" AND ", $group) . ")";
            }

            $sql[] = " WHERE " . implode(" OR ", $whereSQL);
            
        }

        if (!empty($limit)) {
            
            $sql[] = " LIMIT $this->limit";
        }

        $sql = implode($delimiter, $sql);

        // var_dump($sql);
        // var_dump($params);
        $flatten_patterns = array();
        array_walk_recursive($params, function ($a) use (&$flatten_patterns) {
            $flatten_patterns[] = $a;
        });

        return array($sql, $flatten_patterns);
    }

    //EXECUTE QUERY
    /**
    * method runSQL, public access is assumed
    */
    public function runSQL($debug = false)
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
        
        
        //var_dump($query);
    }
}
