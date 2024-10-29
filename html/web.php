<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Web;

$web = new web(3,8,5);
var_dump($web->dataCreate(function($id){
    return "./web.php?id=" . $id;
}));

$web = new web(10,30,5);
var_dump($web->dataCreate(function($id){
    return "./web.php?id=" . $id;
}));
