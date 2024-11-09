<?php

namespace Ponponumi\PagerCreate;

use Ponponumi\HtmlAttributeCreate\Create;

class Web
{
    public $core;
    public string $prev = "";
    public string $next = "";
    public $ellipsisOn = true;
    private $urlCreate = null;
    public $firstAndLastMode = true;

    public function __construct(int $now, int $max = 1, int $display = 5, string $prev = "<<", string $next = ">>")
    {
        $this->core = new Core($now,$max,$display);
        $this->prev = $prev;
        $this->next = $next;
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

        $this->urlCreate = $urlCreate;

        if($pagerData["list"][0] >= 2 && $this->firstAndLastMode){
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

        if(abs($pagerData["max"] - $pagerData["list"][$count - 1]) >= 1 && $this->firstAndLastMode){
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

        if(is_string($attribute) && $attribute !== ""){
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

    private function aroundCreate(array $data,array $now,string $prevAttribute,string $nextAttribute)
    {
        $count = count($data);
        $index = array_search($now, $data);

        $urlCreate = $this->urlCreate;

        $result = [
            "prev" => "",
            "next" => "",
        ];

        if($urlCreate !== null){
            if($index !== 0){
                $id = $urlCreate($now["id"] - 1);
                $result["prev"] = '<li' . $prevAttribute . '><a href="' . $id . '">' . htmlspecialchars($this->prev) . '</a></li>';
            }

            if($index !== $count - 1){
                $id = $urlCreate($now["id"] + 1);
                $result["next"] = '<li' . $nextAttribute . '><a href="' . $id . '">' . htmlspecialchars($this->next) . '</a></li>';
            }
        }

        return $result;
    }

    public function htmlCreate(array $data,array $setting=[])
    {
        // HTMLを生成する

        // まずはオプションを取得する
        $ulAttribute = $this->attributeAllGet($setting,"ulAttribute");
        $liAttribute = $this->attributeClassesGet($setting,"liAttribute");
        $ellipsisAttribute = $this->attributeClassesGet($setting,"ellipsisAttribute");
        $nowAttribute = $this->attributeAllGet($setting,"nowAttribute");
        $prevAttribute = $this->attributeAllGet($setting,"prevAttribute");
        $nextAttribute = $this->attributeAllGet($setting,"nextAttribute");
        $aroundButtonMode = $this->optionGet($setting,"aroundButtonMode",false);
        $nowNotLink = $this->optionGet($setting,"nowNotLink",true);

        $html = '<ul' . $ulAttribute . '>';
        $pagerHtml = "";
        $nowPage = [];

        foreach($data as $dataItem){
            $itemAttribute = $liAttribute;

            switch($dataItem["type"]){
                case "ellipsis":
                    // 省略記号なら
                    $itemAttribute = $ellipsisAttribute;
                    break;
                case "now":
                    // 現在値なら
                    $itemAttribute = $nowAttribute;
                    $nowPage = $dataItem;

                    if($nowNotLink){
                        // 現在値をリンク化しない場合
                        $dataItem["url"] = null;
                    }

                    break;
            }

            $item = '<li' . $itemAttribute . '>';
            $text = $dataItem["id"];

            if($dataItem["url"] !== null && $dataItem["url"] !== ""){
                $text = '<a href="' . $dataItem["url"] . '">' . $text . '</a>';
            }

            $item .= $text;
            $item .= '</li>';

            $pagerHtml .= $item;
        }

        if($aroundButtonMode){
            // 前へと次へのボタンを表示するなら
            $aroundData = $this->aroundCreate($data, $nowPage, $prevAttribute, $nextAttribute);
            $pagerHtml = $aroundData["prev"] . $pagerHtml . $aroundData["next"];
        }

        $html .= $pagerHtml;
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

    public function htmlEchoCallback(callable $urlCreate, array $setting = [])
    {
        // HTMLをコールバックで生成し、出力する
        echo $this->htmlCreateCallback($urlCreate, $setting);
    }

    public function htmlEchoUrlReplace(string $url,string $idReplace,array $setting=[])
    {
        // HTMLをURLの置き換えで生成し、出力する
        echo $this->htmlCreateUrlReplace($url, $idReplace, $setting);
    }

    public function firstAndLastModeChange($newValue)
    {
        // 最初と最後のボタンを出力するかどうかを変える
        $this->firstAndLastMode = $newValue;
    }
}
