<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Core;

$core = new Core(5,6,5);
var_dump($core->rangeCalc());
