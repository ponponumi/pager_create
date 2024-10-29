<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Core;

$core = new Core(5,6,5);
var_dump($core->rangeCalc());
var_dump($core->rangeListGet());
$core = new Core(1,3,5);
var_dump($core->rangeCalc());
var_dump($core->rangeListGet());
$core = new Core(8,13,5);
var_dump($core->rangeCalc());
var_dump($core->rangeListGet());
$core = new Core(1,8,5);
var_dump($core->rangeCalc());
var_dump($core->rangeListGet());
