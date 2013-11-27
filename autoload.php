<?php

spl_autoload_register(function ($class){
	// var_dump($class);
	$class = str_replace("\\","/",$class);
	include $class. '.php'; 


});