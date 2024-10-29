<?php

namespace Ponponumi\PagerCreate;

class Core{
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
}
