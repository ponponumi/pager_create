<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Web;

$web = new web(3,8,5);
echo $web->htmlCreateUrlReplace("./index.php?id={pageid}","{pageid}",[
    "ulAttribute" => "#pager.pagerList.menu",
]);
