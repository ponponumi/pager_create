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
    public $idAndClassDirectlyWrite = false;
    public string $listTag = "ul";
    public string $itemTag = "li";

    /**
     * ページャーを作成します。
     *
     * このクラスでは、HTMLデータを作成します。
     *
     * @param int $now 現在のページ番号を渡して下さい。maxより大きい値を渡すと、maxに上書きされます。
     * @param int $max 最後のページ番号を渡して下さい。初期状態では1です。
     * @param int $display 画面に表示するボタン数を渡して下さい。初期状態では5です。
     * @param string $prev 前へボタンのテキストを入力して下さい。初期状態では"<<"です。
     * @param string $next 次へボタンのテキストを入力して下さい。初期状態では">>"です。
     */
    public function __construct(int $now, int $max = 1, int $display = 5, string $prev = "<<", string $next = ">>")
    {
        $this->core = new Core($now,$max,$display);
        $this->prev = $prev;
        $this->next = $next;
    }

    public function tagModeChange(string $tagMode="ul")
    {
        switch($tagMode){
            case "ul":
                $this->listTag = "ul";
                $this->itemTag = "li";
                break;
            case "ol":
                $this->listTag = "ol";
                $this->itemTag = "li";
                break;
            case "div":
                $this->listTag = "div";
                $this->itemTag = "div";
                break;
        }
    }

    public function optionGet(array $array,string $key,$default = null)
    {
        if(array_key_exists($key,$array)){
            return $array[$key];
        }else{
            return $default;
        }
    }

    /**
     * 省略記号の設定をします。
     *
     * @param string|null $ellipsis 省略記号を渡して下さい。
     * @return void
     */
    public function ellipsisSet(string|null $ellipsis)
    {
        $this->core->ellipsisSet($ellipsis);
    }

    /**
     * 省略記号の表示モードを設定します。
     *
     * @param $ellipsisOn 省略記号を表示する場合はtrue、表示しない場合はfalseを渡して下さい。
     * @return void
     */
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

    /**
     * ページャーのデータを作成します。
     *
     * @param callable $urlCreate ここには、ページ番号を含んだURLを作成するためのコールバック関数を渡して下さい。
     * @return array
     */
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

    /**
     * URLを置き換えてデータを作成します。
     *
     * @param string $url ここには、URLの形式(例: "http://localhost/archive/{id}" )を渡して下さい。
     * @param string $idReplace ここには、上記のURLからページIDに置き換えたい部分(例: "{id}" )を渡して下さい。
     * @return array
     */
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
            if($this->idAndClassDirectlyWrite){
                // HTML属性形式で書くなら
                $first = substr($attribute, 0, 1);

                if($first !== " "){
                    // 最初がスペースでなければスペースを追加
                    $attribute = " " . $attribute;
                }

                return $attribute;
            }else{
                // CSSセレクタ形式で書くなら
                return Create::htmlAttribute($attribute,1,$getMode);
            }
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
                $result["prev"] = '<' . $this->itemTag . $prevAttribute . '><a href="' . $id . '">' . htmlspecialchars($this->prev) . '</a></' . $this->itemTag . '>';
            }

            if($index !== $count - 1){
                $id = $urlCreate($now["id"] + 1);
                $result["next"] = '<' . $this->itemTag . $nextAttribute . '><a href="' . $id . '">' . htmlspecialchars($this->next) . '</a></' . $this->itemTag . '>';
            }
        }

        return $result;
    }

    /**
     * HTMLを生成します
     *
     * @param array $data ページャーのデータを渡して下さい。
     * @param array $setting 設定データを渡して下さい。
     * @return string
     */
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

        $html = '<' . $this->listTag . $ulAttribute . '>';
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

            $item = '<' . $this->itemTag . $itemAttribute . '>';
            $text = $dataItem["id"];

            if($dataItem["url"] !== null && $dataItem["url"] !== ""){
                $text = '<a href="' . $dataItem["url"] . '">' . $text . '</a>';
            }

            $item .= $text;
            $item .= '</' . $this->itemTag . '>';

            $pagerHtml .= $item;
        }

        if($aroundButtonMode){
            // 前へと次へのボタンを表示するなら
            $aroundData = $this->aroundCreate($data, $nowPage, $prevAttribute, $nextAttribute);
            $pagerHtml = $aroundData["prev"] . $pagerHtml . $aroundData["next"];
        }

        $html .= $pagerHtml;
        $html .= '</' . $this->listTag . '>';

        return $html;
    }

    /**
     * HTMLをコールバックで生成します
     *
     * @param callable $urlCreate ここには、ページ番号を含んだURLを作成するためのコールバック関数を渡して下さい。
     * @param array $setting 設定データを渡して下さい。
     * @return string
     */
    public function htmlCreateCallback(callable $urlCreate,array $setting=[])
    {
        // HTMLをコールバックで生成する
        $data = $this->dataCreate($urlCreate);
        return $this->htmlCreate($data,$setting);
    }

    /**
     * URLを置き換えてHTMLを作成します。
     *
     * @param string $url ここには、URLの形式(例: "http://localhost/archive/{id}" )を渡して下さい。
     * @param string $idReplace ここには、上記のURLからページIDに置き換えたい部分(例: "{id}" )を渡して下さい。
     * @param array $setting 設定データを渡して下さい。
     * @return string
     */
    public function htmlCreateUrlReplace(string $url,string $idReplace,array $setting=[])
    {
        // HTMLをURLの置き換えで生成する
        $data = $this->dataCreateUrlReplace($url,$idReplace);
        return $this->htmlCreate($data,$setting);
    }

    /**
     * HTMLをコールバックで生成し、出力します
     * 基本は、htmlCreateCallbackメソッドと同じですが、このメソッドはHTMLを返さず、echoします。
     *
     * @param callable $urlCreate ここには、ページ番号を含んだURLを作成するためのコールバック関数を渡して下さい。
     * @param array $setting 設定データを渡して下さい。
     * @return void
     */
    public function htmlEchoCallback(callable $urlCreate, array $setting = [])
    {
        // HTMLをコールバックで生成し、出力する
        echo $this->htmlCreateCallback($urlCreate, $setting);
    }

    /**
     * URLを置き換えてHTMLを生成し、出力します
     * 基本は、htmlCreateUrlReplaceメソッドと同じですが、このメソッドはHTMLを返さず、echoします。
     *
     * @param string $url ここには、URLの形式(例: "http://localhost/archive/{id}" )を渡して下さい。
     * @param string $idReplace ここには、上記のURLからページIDに置き換えたい部分(例: "{id}" )を渡して下さい。
     * @param array $setting 設定データを渡して下さい
     * @return void
     */
    public function htmlEchoUrlReplace(string $url,string $idReplace,array $setting=[])
    {
        // HTMLをURLの置き換えで生成し、出力する
        echo $this->htmlCreateUrlReplace($url, $idReplace, $setting);
    }

    /**
     * 最初と最後のボタンを出力するかどうか選びます。
     *
     * @param $newValue 出力する場合はtrue、出力しない場合はfalseを渡して下さい。
     * @return void
     */
    public function firstAndLastModeChange($newValue)
    {
        // 最初と最後のボタンを出力するかどうかを変える
        $this->firstAndLastMode = $newValue;
    }

    /**
     * ID名とクラス名のHTML属性を、直接書くかどうか選べるように変更します
     *
     * @param mixed $newValue ID名とクラス名のHTML属性を直接書きたい場合はtrue、CSSセレクタと同じ形式で書きたい場合はfalseを渡して下さい。
     * @return void
     */
    public function idAndClassDirectly($newValue)
    {
        // ID名とクラス名を直接書くかどうか選べるように変更
        $this->idAndClassDirectlyWrite = $newValue;
    }
}
