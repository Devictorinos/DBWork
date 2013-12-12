<?php

namespace DBWork;

use PDO;
use PDOException;

class Truncate extends Query
{


    private function buildSQL($delimiter = "\n")
    {
        $sql      = array();
        $params   = array();
        //BUILDING QUERY
        $sql[] = "TRUNCATE $this->table";

        $sql = implode($delimiter, $sql);

        return $sql;
    }

    public function runSQL($debug = false)
    {
        $sql = $this->buildSQL();
        $params = array();

        if ($debug) {
            Log::Query($sql, $params);
        }

        try {
            $query = $this->dbh->prepare($sql);
            
            $query->execute();

            return $query;

        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
