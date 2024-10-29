<?php

namespace Ponponumi\PagerCreate;

class Core
{
    public int $max;
    public int $now;
    public int $display;
    public string|null $ellipsis = "…";

    public function __construct(int $now, int $max = 1, int $display = 5)
    {
        if($max < $now){
            $this->now = $max;
        }else{
            $this->now = $now;
        }

        $this->max = $max;
        $this->display = $display;
    }

    public function ellipsisSet(string|null $ellipsis)
    {
        // 省略記号の設定をする
        $this->ellipsis = htmlspecialchars($ellipsis);
    }

    public function rangeCalc()
    {
        // 範囲を計算する
        $start = $this->now - intval(floor($this->display / 2));
        $end = $start + $this->display - 1;
    }
}
