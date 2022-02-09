<?php
/*
 * @Date: 2022-02-09 16:32:01
 * @Author: zhaoke
 * @LastEditors: zhaoke
 * @LastEditTime: 2022-02-09 16:36:07
 * @FilePath: /blog-go/Users/smzdm/Code/mianshi/php/redpackage.php
 */

/**
 * 抢红包函数
 * @param array $packet 红包数组，totalNum：总个数，totalMoney：总金额，leftNum：剩下的个数，leftMoney：剩下的金额
 * @return float 分到的红包金额
 */
function getRedPacket(Array &$packet)
{
    //红包发完了
    if ($packet['leftNum'] < 1) {
        return 0.0;
    } //红包只剩一个了
    elseif ($packet['leftNum'] === 1) {
        $packet['leftNum']--;
        return $packet['leftMoney'];
    } else {
        //保证用户抢到的红包不出现0.00
        $min = 0.01;
        //该计算保证结果的期望值接近红包总额/红包总数的2倍（减一是为了保证最后一个红包不为0），化为整数计算是因为浮点数运算费时
        $max = (($packet['leftMoney'] * 100 - 1) / $packet['leftNum']) * 2 * random_int(0, 100) / 100;
        //化为浮点数，并保证精度是2
        $max = round($max / 100, 2);
        $money = $max < $min ? $min : $max;
        $packet['leftNum']--;
        $packet['leftMoney'] -= $money;
        return $money;
    }
}

//测试
$packet = [
    'totalNum' => 5,
    'totalMoney' => 10
];
$packet['leftNum'] = $packet['totalNum'];
$packet['leftMoney'] = $packet['totalMoney'];
$sent = 0.00;
while ($packet['leftNum'] > 0) {
    $money = getRedPacket($packet) ;
    echo $money . '    ';
    $sent += $money;
}
echo $sent;