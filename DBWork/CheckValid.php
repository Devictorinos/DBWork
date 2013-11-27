<?php

namespace DBWork;

class CheckValid
{

    private $config;

    public function __construct()
    {

        $this->config = new Config();
    }


    public function validID($id)
    {

        return $this->isIntNumber($id) && $id >0;
    }

    public function validSTR($string)
    {
        return $this->isValidSTR($string);
    }


    private function isIntNumber($number)
    {
        if (!is_int($number) && !is_string($number) && !is_numeric($number)) {
            return false;
        }
        
        return (bool) preg_match("/^-?(([1-9][0-9]*|0))$/", $number);
    }

    private function isValidSTR($string)
    {
        return (bool) preg_match("/^[a-zA-Z\-]+$/i", $string);
    }
}


 //$valid = new CheckValid();

 //$val = $valid->validSTR("gdfgf-gdfgdf");

 //echo $val;
// echo '<br>';
// if($val === true)echo 'num';else echo 'not num';
