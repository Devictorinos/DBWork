<?php

require_once 'autoload.php';

$db = new \DBWork\DBWork('localhost', 'test', 'root', '123');

// alias
// joins


// $categories = $db->select('categories', '*')->orderBy("CategoryName", false);

// $products   = $db->select('products', '*')
//     ->orderBy("ProductName", false)
//     ->where("ProductID", ">", "20")
//     ->join($categories, 'CategoryID')
//     ->limit(1);

//$categories = $db->select('categories','c' ,['*']);
$products   = $db->select('products','p' ,['sum(ProductID) as Pcount']);
$products1   = $db->select('products','pp' , ['ProductName as PPname']);
$catigorys  = $db->select('categories', 'c', 'CategoryName');
// $categories->where1('CategoryName', "LIKE", "%A%");
 //$products->where2('ProductID', ">", 20);
 //$products->where1('ProductID', ">", 25);


$products->Join($catigorys, 'c', 'CategoryID');
$products->Join($products1,"pp", 'ProductID');
//$products->groupBy('SupplierID');
//$products->buildJoin();
//$products->getAll(true);
//var_dump($products->getAll(true));
// $query = $db->select('test', 't', ['DISTINCT t.`name` as  "test name", count(f.id) as "fruits id"'])
// ->join('fruit','f')
// ->on('f.`fruitTest`','=','t.`id`'."\n")
// //->on('t.`status`','=',"'active'", true)
// //->onBetween('t.`id`', 30, 35, true)
// ->onJoin('test_2','t2','left')
// ->on('t2.`Tname`','=','f.`id`','on');

//->onIn('t.`id`', [30,35,32]);


// ->where('t.`id`', "<", 35)
// ->andOr('t.`id`', "<",34,'t.`id`', ">",25,true)
// ->where('t.`id`', "<", 35,false)
// ->where('t.`id`', "<", 35,true)
// ->whereBetween('t.`id`',5, 8, false)
// ->whereIn('t.`if`',[3,5,7,8,9],false)
// ->orderBy('t.`id`' ,false);


// $rows = $query->getAll(true);
// 

// var_dump($rows);
//$del = $db->delete('people');
//$del->whereIn1("id", [3, 4, 5, 6, 12, 11]);
// $del->whereIn2("id", [3, 4, 5, 6, 7, 8]);

//$del->runSQL(true);
//
//$del = $db->truncate("people")->runSQL(true);
// $update = $db->update("people");
// $update->set([
//                 "id"    => "3",
//                 "email"      => "rotem@gmail.com",
//                 "age"   => "31",
//                 "name" => "viktorino"
//             ]);
// $result = $update->runSQL(true);

//  var_dump($result);
//  
 
$res = $db->insert('people')->fieldsValues([
                                 "email"  => "RORORO@gmail.com",
                                 "age"   => "34",
                                 "name" => "ROROR"
                                 ])->runSQL(true);
