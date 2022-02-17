<?php

/**
     快速排序
    快速排序使用分治法（Divide and conquer）策略来把一个串行（list）分为两个子串行（sub-lists）。

    快速排序又是一种分而治之思想在排序算法上的典型应用。本质上来看，快速排序应该算是在冒泡排序基础上的递归分治法。

    快速排序的名字起的是简单粗暴，因为一听到这个名字你就知道它存在的意义，就是快，而且效率高！它是处理大数据最快的排序算法之一了。虽然 Worst Case 的时间复杂度达到了 O(n²)，但是人家就是优秀，在大多数情况下都比平均时间复杂度为 O(n logn) 的排序算法表现要更好，可是这是为什么呢，我也不知道。好在我的强迫症又犯了，查了 N 多资料终于在《算法艺术与信息学竞赛》上找到了满意的答案：

    快速排序的最坏运行情况是 O(n²)，比如说顺序数列的快排。但它的平摊期望时间是 O(nlogn)，且 O(nlogn) 记号中隐含的常数因子很小，比复杂度稳定等于 O(nlogn) 的归并排序要小很多。所以，对绝大多数顺序性较弱的随机数列而言，快速排序总是优于归并排序。

    我们从数组中选择一个元素，我们把这个元素称之为中轴元素吧，然后把数组中所有小于中轴元素的元素放在其左边，所有大于或等于中轴元素的元素放在其右边，显然，此时中轴元素所处的位置的是有序的。也就是说，我们无需再移动中轴元素的位置。

    从中轴元素那里开始把大的数组切割成两个小的数组(两个数组都不包含中轴元素)，接着我们通过递归的方式，让中轴元素左边的数组和右边的数组也重复同样的操作，直到数组的大小为1，此时每个元素都处于有序的位置。
 */

function quickSort($arr)
{
    $len = count($arr);
    if ($len <= 1) {
        return $arr;
    }
    $middle = $arr[0];
    $left = $right = [];
    for ($i = 1; $i < $len; $i++) {
        if ($arr[$i] < $middle) {
            $left[] = $arr[$i];
        } else {
            $right[] = $arr[$i];
        }
    }
    $left = quickSort($left);
    $right = quickSort($right);
    $left[] = $middle;
    return array_merge($left, $right);
}


/**
选择排序
过程简单描述：
首先，找到数组中最小的那个元素，其次，将它和数组的第一个元素交换位置(如果第一个元素就是最小元素那么它就和自己交换)。其次，在剩下的元素中找到最小的元素，将它与数组的第二个元素交换位置。如此往复，直到将整个数组排序。这种方法我们称之为选择排序。
 */

function selectSort($arr)
{
    $len = count($arr);
    for ($i = 0; $i < $len; $i++) {
        $min = $arr[$i];
        $minIndex = $i;
        for ($j = $i + 1; $j < $len; $j++) {
            if ($arr[$j] < $min) {
                $min = $arr[$j];
                $minIndex = $j;
            }
        }
        echo $min, '-', $minIndex, PHP_EOL;
        $tmp = $arr[$i];
        $arr[$i] = $arr[$minIndex];
        $arr[$minIndex] = $tmp;
    }
    return $arr;
}

/**
 2、插入排序
过程简单描述：

1、从数组第2个元素开始抽取元素。

2、把它与左边第一个元素比较，如果左边第一个元素比它大，则继续与左边第二个元素比较下去，直到遇到不比它大的元素，然后插到这个元素的右边。

3、继续选取第3，4，….n个元素,重复步骤 2 ，选择适当的位置插入。
 */

function insertionSort($arr)
{
    $len = count($arr);
    for ($i = 1; $i < $len; $i++) {
        $preIndex = $i - 1;
        $current = $arr[$i];
        while ($preIndex >= 0 && $arr[$preIndex] > $current) {
            $arr[$preIndex + 1] = $arr[$preIndex];
            $preIndex--;
            echo implode(',', $arr), PHP_EOL;
        }
        $arr[$preIndex + 1] = $current;
    }
    return $arr;
}

/**
 希尔排序
希尔排序可以说是插入排序的一种变种。无论是插入排序还是冒泡排序，如果数组的最大值刚好是在第一位，要将它挪到正确的位置就需要 n - 1 次移动。也就是说，原数组的一个元素如果距离它正确的位置很远的话，则需要与相邻元素交换很多次才能到达正确的位置，这样是相对比较花时间了。

希尔排序就是为了加快速度简单地改进了插入排序，交换不相邻的元素以对数组的局部进行排序。

希尔排序的思想是采用插入排序的方法，先让数组中任意间隔为 h 的元素有序，刚开始 h 的大小可以是 h = n / 2,接着让 h = n / 4，让 h 一直缩小，当 h = 1 时，也就是此时数组中任意间隔为1的元素有序，此时的数组就是有序的了。
 */

function shellSort($arr, $step = -1)
{
    $len = count($arr);
    if (-1 == $step) {
        $step = floor($len / 2);
    }
    if (!$step) {
        $step = 1;
    }
    for ($i = $step; $i < $len; $i = $i + $step) {
        $j = $i - $step;
        $current = $arr[$i];
        while ($j >= 0 && $arr[$j] > $current) {
            $arr[$j + $step] = $arr[$j];
            $j = $j - $step;
        }
        $arr[$j + $step] = $current;
    }
    $step = $step / 2;
    if ($step < 1) {
        return $arr;
    }
    return shellSort($arr, floor($step));
}

/**
3、冒泡排序
1、把第一个元素与第二个元素比较，如果第一个比第二个大，则交换他们的位置。接着继续比较第二个与第三个元素，如果第二个比第三个大，则交换他们的位置….

我们对每一对相邻元素作同样的工作，从开始第一对到结尾的最后一对，这样一趟比较交换下来之后，排在最右的元素就会是最大的数。

除去最右的元素，我们对剩余的元素做同样的工作，如此重复下去，直到排序完成。
 */

function BubbleSort($arr)
{
    $len = count($arr);
    for ($i = 0; $i < $len; $i++) {
        for ($j = 0; $j < $len - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                $temp = $arr[$j + 1];
                $arr[$j + 1] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
    }
    return $arr;
}

/**
 归并排序
将一个大的无序数组有序，我们可以把大的数组分成两个，然后对这两个数组分别进行排序，之后在把这两个数组合并成一个有序的数组。由于两个小的数组都是有序的，所以在合并的时候是很快的。

通过递归的方式将大的数组一直分割，直到数组的大小为 1，此时只有一个元素，那么该数组就是有序的了，之后再把两个数组大小为1的合并成一个大小为2的，再把两个大小为2的合并成4的 ….. 直到全部小的数组合并起来。

和选择排序一样，归并排序的性能不受输入数据的影响，但表现比选择排序好的多，因为始终都是 O(nlogn) 的时间复杂度。代价是需要额外的内存空间。
 */

function mergeSort($arr)
{
    if (count($arr) <= 1) {
        return $arr;
    }
    $middle = floor(count($arr) / 2);
    $left = array_slice($arr, 0, $middle);
    $right = array_slice($arr, $middle);
    return merge(mergeSort($left), mergeSort($right));
}
function merge($left, $right)
{
    $return = [];
    while (count($left) > 0 && count($right) > 0) {
        if ($left[0] <= $right[0]) {
            $return[] = array_shift($left);
        } else {
            $return[] = array_shift($right);
        }
    }
    foreach ($left as $l) {
        $return[] = $l;
    }
    foreach ($right as $r) {
        $return[] = $r;
    }
    return $return;
}
$arr = [9, 1, 14, 4, 3, 10, 12, 6, 8, 2, 13, 7, 9, 11];
echo implode(',', mergeSort($arr));

