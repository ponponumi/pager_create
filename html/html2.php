<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Ponponumi\PagerCreate\Web;

$web = new web(7,13,5);
$web->firstAndLastModeChange(false);
echo $web->htmlCreateUrlReplace("./index.php?id={pageid}","{pageid}",[
    "ulAttribute" => "#pager.pagerList.menu",
    "liAttribute" => "#item.item",
    "nowAttribute" => "#now.item.now",
    "ellipsisAttribute" => "#ellipsis.item.ellipsis",
    "aroundButtonMode" => true,
    "prevAttribute" => "#prev-btn",
    "nextAttribute" => "#next-btn",
    "nowNotLink" => true,
]);
