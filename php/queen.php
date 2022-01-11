<?php
/*
 * @Date: 2022-01-11 10:24:01
 * @Author: zhaoke
 * @LastEditors: zhaoke
 * @LastEditTime: 2022-01-11 10:44:23
 * @FilePath: /blog-go/Users/smzdm/Code/mianshi/php/queen.php
 */
// 不知道大家有没有下过国际象棋，不过没关系，问题是这样的，在8×8格的国际象棋上摆放八个皇后，
// 使其不能互相攻击，即任意两个皇后都不能处于同一行、同一列或同一斜线上，问有多少种摆法。
// 请设计程序算出结果，那种计算机语言不限。
class queen
{
    const n = 6; // 皇后个数 常量
    private static $qp = []; // 棋盘
    private static $count = 0; // 多少解
 
    /**  棋盘模型
    $qp = [
    // y 0, 1, 2, 3, 4, 5, 6, 7   // x
        [0, 0, 0, 0, 0, 0, 0, 0], // 0
        [0, 0, 0, 0, 0, 0, 0, 0], // 1
        [0, 0, 0, 0, 0, 0, 0, 0], // 2
        [0, 0, 0, 0, 0, 0, 0, 0], // 3
        [0, 0, 0, 0, 0, 0, 0, 0], // 4
        [0, 0, 0, 0, 0, 0, 0, 0], // 5
        [0, 0, 0, 0, 0, 0, 0, 0], // 6
        [0, 0, 0, 0, 0, 0, 0, 0], // 7
    ];*/
    public function __construct()
    {
        // 构建棋盘
        for ($i=0;$i<self::n;$i++) {
            for ($j=0;$j<self::n;$j++) {
                static::$qp[$i][$j] = 0;
            }
        }
    }
    // 需要扫描当前摆放皇后的左上，中上，右上方向是否有其他皇后，有的话存在危险，没有则表示
    // 安全，并不需要考虑当前位置棋盘下方的安全性，因为下面的皇后还没有摆放
    private static function check(array $arr, int $x, int $y){
        for ($i=$x,$k=0;$i>-1;$i--,$k++) {
            // x上边
            if ($arr[$i][$y]) return false;
 
            // x左上
            $y1 = $y-$k;
            if ($y1 > -1) { // y边界0
                if ($arr[$i][$y1]) return false;
            }
 
            // x右上
            $y2 = $y+$k;
            if ($y2 < self::n) { // y边界7
                if ($arr[$i][$y2]) return false;
            }
        }
 
        return true;
    }
 
    public function main(){
        $n = 1; // 行号
        $jl = []; // 记录每行皇后位置
        $e = self::n - 1; // 7
        $f = self::n - 2; // 6
        $g = self::n - 3; // 5
 
        for($k=0;$k<self::n;$k++){
            $tmp = self::$qp;
            $tmp[0][$k] = 1;
            $jl[0] = $k;
            $a = 0;
 
            for ($i=1;$i<self::n;$i++) {
                for ($j=$a;$j<self::n;$j++) {
                    if ($a) $a = 0;
 
                    if (self::check($tmp, $i, $j)) {
                        $tmp[$i][$j] = 1;
                        $jl[$n] = $j;
                        $n++;
                        break;
                    }
 
                    // 回溯
                    if ($j == $e) {
                        a:
                        $n--;
                        if ($n == -1) die;
                        $tmp[$n][$jl[$n]] = 0;
 
                        if ($jl[$n] == $e) goto a;
                        $i = $n;
                        $j = $jl[$n];
                    }
                }
 
                if ($i == $e) {
                    static::$count++;
                    echo self::n,'皇后解法数：',self::$count,'<br/>';
 
                    // 打印皇后分布
                    foreach ($tmp as $v1) {
                        foreach ($v1 as $v2) {
                            echo $v2 ? '1 ' : '0 ';
                        }
                        echo '<br/>';
                    }
 
                    // 搜索其他解法
                    if ($jl[$e] != $e) { // 7
                        $n = $e;
                        $tmp[$e][$jl[$e]] = 0; // 7
                        $i = $f; // 6
                        $a = $e; // 7
 
                    } else {
                        $n = $f; // 6
                        $tmp[$f][$jl[$f]] = 0; // 6
                        $tmp[$e][$jl[$e]] = 0; // 7
                        $i = $g; // 5
                        $a = $e; // 7
                    }
 
                }
            }
        }
 
    }
}
 
$obj = new queen();
$obj -> main();
