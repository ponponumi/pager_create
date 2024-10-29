<?php

namespace Ponponumi\PagerCreate;

use Ponponumi\HtmlAttributeCreate\Create;

class Web
{
    public $core;
    public string|null $prev = "";
    public string|null $next = "";

    public function __construct(int $now, int $max = 1, int $display = 5)
    {
        $this->core = new Core($now,$max,$display);
    }

    public function ellipsisSet(string|null $ellipsis)
    {
        $this->core->ellipsisSet($ellipsis);
    }

    private function rowCreate($id, callable $urlCreate)
    {
        return [
            "url" => $urlCreate($id),
            "id" => $id,
        ];
    }

    public function dataCreate(callable $urlCreate)
    {
        // ページャーのデータを作成
        $pagerData = $this->core->pagerDataGet();
        $result = [];

        if($pagerData["list"][0] >= 2){
            // 2以上であれば
            $result[] = $this->rowCreate(1,$urlCreate);
        }

        foreach($pagerData["list"] as $item){
            $result[] = $this->rowCreate($item,$urlCreate);
        }

        $count = count($pagerData["list"]);

        if(abs($pagerData["max"] - $pagerData["list"][$count - 1]) >= 1){
            // 差が1以上であれば
            $result[] = $this->rowCreate($pagerData["max"],$urlCreate);
        }

        return $result;
    }
}
