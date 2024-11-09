<?php

namespace Ponponumi\PagerCreate;

class Core
{
    /**
     * ページャーを作成するクラス
     *
     * このクラスでは、ページャーを作成し、
     * HTMLとして返します。
     *
     * @package Ponponumi\PagerCreate
     */

    public int $max;
    public int $now;
    public int $display;
    public string|null $ellipsis = "…";

    public function __construct(int $now, int $max = 1, int $display = 5)
    {
        if($now < 1){
            $now = 1;
        }

        if($max < 1){
            $max = 1;
        }

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

    private function startCheck(int $input)
    {
        // スタートの数値を確認する
        // 1以上であればその数値、1未満であれば1を返す
        if($input < 1){
            $input = 1;
        }

        return $input;
    }

    private function endCheck(int $input)
    {
        // エンドの数値を確認する
        // maxを超えていなければその数値、超えていればmaxを返す
        if($input > $this->max){
            $input = $this->max;
        }

        return $input;
    }

    public function rangeCalc()
    {
        // 範囲を計算する
        $start = $this->now - intval(floor($this->display / 2));
        $start = $this->startCheck($start);
        $end = $start + $this->display - 1;
        $endNew = $this->endCheck($end);

        if($end !== $endNew){
            $start = $endNew - $this->display + 1;
            $start = $this->startCheck($start);
        }

        $result = [
            "start" => $start,
            "end" => $endNew,
        ];

        return $result;
    }

    public function rangeListGet()
    {
        // リストを返す
        $range = $this->rangeCalc();
        return range($range["start"],$range["end"]);
    }

    public function pagerDataGet()
    {
        // ページャーのデータを取得する
        $list = $this->rangeListGet();

        $result = [
            "max" => $this->max,
            "now" => $this->now,
            "list" => $list,
            "startSkip" => false,
            "endSkip" => false,
            "ellipsis" => $this->ellipsis,
        ];

        $count = count($list);

        if($list[0] >= 3){
            $result["startSkip"] = true;
        }

        if(abs($this->max - $list[$count - 1]) >= 2){
            $result["endSkip"] = true;
        }

        return $result;
    }
}
