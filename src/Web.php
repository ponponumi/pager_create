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

    public function optionGet(array $array,string $key,$default = null)
    {
        if(array_key_exists($key,$array)){
            return $array[$key];
        }else{
            return $default;
        }
    }

    public function ellipsisSet(string|null $ellipsis)
    {
        $this->core->ellipsisSet($ellipsis);
    }

    public function ellipsisMode($ellipsisOn)
    {
        $this->ellipsisOn = $ellipsisOn;
    }

    private function rowCreate($id, callable|null $urlCreate = null, $type = "")
    {
        $url = null;

        if($urlCreate !== null){
            $url = $urlCreate($id);
        }

        return [
            "url" => $url,
            "id" => $id,
            "type" => $type,
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
                $result[] = $this->rowCreate($pagerData["ellipsis"],null,"ellipsis");
            }
        }

        foreach($pagerData["list"] as $item){
            $itemType = "";

            if($pagerData["now"] === $item){
                $itemType = "now";
            }

            $result[] = $this->rowCreate($item,$urlCreate,$itemType);
        }

        $count = count($pagerData["list"]);

        if(abs($pagerData["max"] - $pagerData["list"][$count - 1]) >= 1){
            // 差が1以上であれば
            if($this->ellipsisOn && abs($pagerData["max"] - $pagerData["list"][$count - 1]) >= 2){
                // 省略記号を表示し、かつ差が2以上であれば
                $result[] = $this->rowCreate($pagerData["ellipsis"],null,"ellipsis");
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

    public function attributeGet(array $setting,string $key,int $getMode = 3)
    {
        // HTML属性を返す
        $attribute = $this->optionGet($setting,$key,"");

        if(is_string($attribute)){
            return Create::htmlAttribute($attribute,1,$getMode);
        }else{
            return "";
        }
    }

    public function attributeAllGet(array $setting,string $key)
    {
        // HTML属性を全て返す
        return $this->attributeGet($setting,$key);
    }

    public function attributeClassesGet(array $setting,string $key)
    {
        // HTML属性をクラスのみ返す
        return $this->attributeGet($setting,$key,2);
    }

    public function htmlCreate(array $data,array $setting=[])
    {
        // HTMLを生成する

        // まずはオプションを取得する
        $ulAttribute = $this->attributeAllGet($setting,"ulAttribute");
        $liAttribute = $this->attributeClassesGet($setting,"liAttribute");
        $ellipsisAttribute = $this->attributeClassesGet($setting,"ellipsisAttribute");

        $html = '<ul' . $ulAttribute . '>';

        foreach($data as $dataItem){
            $itemAttribute = $liAttribute;

            switch($dataItem["type"]){
                case "ellipsis":
                    // 省略記号なら
                    break;
                case "now":
                    // 現在値なら
                    break;
            }

            $item = '<li' . $itemAttribute . '>';
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

    public function htmlCreateUrlReplace(string $url,string $idReplace,array $setting=[])
    {
        // HTMLをURLの置き換えで生成する
        $data = $this->dataCreateUrlReplace($url,$idReplace);
        return $this->htmlCreate($data,$setting);
    }
}
