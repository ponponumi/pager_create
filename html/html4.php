<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Web;

$web = new web(5,7,5);
$web->idAndClassDirectly(true);
echo $web->htmlCreateUrlReplace("./index.php?id={pageid}","{pageid}",[
    "ulAttribute" => 'id="pager" class="pagerList menu"',
    "liAttribute" => ' class="item"',
    "nowAttribute" => 'id="now" class="item now"',
    "ellipsisAttribute" => 'class="item ellipsis"',
    "prevAttribute" => 'id="prev-btn"',
    "nextAttribute" => 'id="next-btn"',
]);
