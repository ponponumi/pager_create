<?php

namespace Ponponumi\PagerCreate;

use Ponponumi\HtmlAttributeCreate\Create;

class Web
{
    public $core;
    public string|null $prev = "";
    public string|null $next = "";
    public $ellipsisOn = true;

    public function __construct(int $now, int $max = 1, int $display = 5)
    {
        $this->core = new Core($now,$max,$display);
    }

    public function ellipsisSet(string|null $ellipsis)
    {
        $this->core->ellipsisSet($ellipsis);
    }

    public function ellipsisMode($ellipsisOn)
    {
        $this->ellipsisOn = $ellipsisOn;
    }

    private function rowCreate($id, callable|null $urlCreate = null)
    {
        $url = null;

        if($urlCreate !== null){
            $url = $urlCreate($id);
        }

        return [
            "url" => $url,
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

            if($this->ellipsisOn && $pagerData["list"][0] >= 3){
                // 省略記号を表示し、かつ開始が3以上であれば
                $result[] = $this->rowCreate($pagerData["ellipsis"]);
            }
        }

        foreach($pagerData["list"] as $item){
            $result[] = $this->rowCreate($item,$urlCreate);
        }

        $count = count($pagerData["list"]);

        if(abs($pagerData["max"] - $pagerData["list"][$count - 1]) >= 1){
            // 差が1以上であれば
            if($this->ellipsisOn && abs($pagerData["max"] - $pagerData["list"][$count - 1]) >= 2){
                // 省略記号を表示し、かつ差が2以上であれば
                $result[] = $this->rowCreate($pagerData["ellipsis"]);
            }

            $result[] = $this->rowCreate($pagerData["max"],$urlCreate);
        }

        return $result;
    }

    public function dataCreateUrlReplace(string $url,string $idReplace)
    {
        // URLを置き換えて、データを取得する
        return $this->dataCreate(function ($id) use ($url, $idReplace){
            return str_replace($idReplace,strval($id),$url);
        });
    }

    public function htmlCreate(array $data,array $setting=[])
    {
        // HTMLを生成する
        $html = '<ul>';

        foreach($data as $dataItem){
            $item = '<li>';
            $text = $dataItem["id"];

            if($dataItem["url"] !== null && $dataItem["url"] !== ""){
                $text = '<a href="' . $dataItem["url"] . '">' . $text . '</a>';
            }

            $item .= $text;
            $item .= '</li>';

            $html .= $item;
        }

        $html .= '</ul>';

        return $html;
    }

    public function htmlCreateCallback(callable $urlCreate,array $setting=[])
    {
        // HTMLをコールバックで生成する
        $data = $this->dataCreate($urlCreate);
        return $this->htmlCreate($data,$setting);
    }
}
