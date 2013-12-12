<?php

namespace DBWork;

use Exception;

class Log
{
    public static function error(Exception $e)
    {
        echo $e->getMessage();
    }

    public static function query($sql, $params)
    {

        $highlight = array("SELECT","as"," IN ","DELETE","TRUNCATE","JOIN","GROUP BY","LEFT JOIN","RIGHT JOIN","OUTER JOIN","FROM","BETWEEN","LIKE","WHERE","LIMIT","ORDER BY","ASC","DESC");

        $regex = '/' . implode('|', $highlight).'/';
        $sql = preg_replace($regex, '<span style="color:red">$0</span>', $sql);
        $sql = preg_replace('/ AND |ON | OR/', '<em style="color:violet">$0</em>', $sql);

        $sql = preg_replace_callback('/\?/', function ($matches) use (&$params) {
            return '<strong style="color:blue">{<em>' . array_shift($params) . '</em>}</strong>';
        }, $sql);

        echo("<pre>");
        echo($sql);
        echo("</pre>");
    }
}
