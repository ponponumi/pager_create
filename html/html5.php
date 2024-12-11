<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Web;

$web = new web($_GET["id"] ?? 1, 30, 5);
$web->tagModeChange("div");
$web->idAndClassDirectly(true);
echo $web->htmlCreateUrlReplace("./html5.php?id={pageid}","{pageid}",[
    "ulAttribute" => 'id="pager" class="pagerList menu"',
    "liAttribute" => ' class="item"',
    "nowAttribute" => 'id="now" class="item now"',
    "ellipsisAttribute" => 'class="item ellipsis"',
    "prevAttribute" => 'id="prev-btn"',
    "nextAttribute" => 'id="next-btn"',
]);
