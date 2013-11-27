<?php

require_once 'autoload.php';


$db= new\DBWork\DBWork;

$query = $db->select('test', 't', ['DISTINCT t.`name` as  "test name", count(f.id) as "fruits id"'])
->join('fruit','f')
->on('f.`fruitTest`','=','t.`id`'."\n")
//->on('t.`status`','=',"'active'", true)
//->onBetween('t.`id`', 30, 35, true)
->onJoin('test_2','t2','left')
->on('t2.`Tname`','=','f.`id`','on');

//->onIn('t.`id`', [30,35,32]);


// ->where('t.`id`', "<", 35)
// ->andOr('t.`id`', "<",34,'t.`id`', ">",25,true)
// ->where('t.`id`', "<", 35,false)
// ->where('t.`id`', "<", 35,true)
// ->whereBetween('t.`id`',5, 8, false)
// ->whereIn('t.`if`',[3,5,7,8,9],false)
// ->orderBy('t.`id`' ,false);


$rows = $query->getAll(true);

var_dump($rows);

