<!--
 * @Date: 2022-02-22 15:16:24
 * @Author: zhaoke
 * @LastEditors: zhaoke
 * @LastEditTime: 2022-02-23 17:28:18
 * @FilePath: /blog-go/Users/smzdm/Code/mianshi/golang/leetcode.md
-->
# 1. 两数之和
给定一个整数数组 nums 和一个整数目标值 target，请你在该数组中找出 和为目标值 target  的那 两个 整数，并返回它们的数组下标。

你可以假设每种输入只会对应一个答案。但是，数组中同一个元素在答案里不能重复出现。

你可以按任意顺序返回答案。

 

示例 1：

    输入：nums = [2,7,11,15], target = 9
    输出：[0,1]
    解释：因为 nums[0] + nums[1] == 9 ，返回 [0, 1] 。
    示例 2：

    输入：nums = [3,2,4], target = 6
    输出：[1,2]
    示例 3：

    输入：nums = [3,3], target = 6
    输出：[0,1]

```golang
func main() {
	res := twoSum([]int{2, 7, 11, 15}, 9)
	fmt.Println(res)
}

func twoSum(nums []int, target int) []int {
    var res []int
	var valueMap = make(map[int]int)
	for index, num := range nums {
		if index2, ok := valueMap[num]; ok {
			res = append(res, index2)
			res = append(res, index)
			break
		}
		valueMap[target-num] = index
	}
	return res
}
```
# 2. 两数相加

给你两个 非空 的链表，表示两个非负的整数。它们每位数字都是按照 逆序 的方式存储的，并且每个节点只能存储 一位 数字。

请你将两个数相加，并以相同形式返回一个表示和的链表。

你可以假设除了数字 0 之外，这两个数都不会以 0 开头。

 

示例 1：


    输入：l1 = [2,4,3], l2 = [5,6,4]
    输出：[7,0,8]
    解释：342 + 465 = 807.
    示例 2：

    输入：l1 = [0], l2 = [0]
    输出：[0]
    示例 3：

    输入：l1 = [9,9,9,9,9,9,9], l2 = [9,9,9,9]
    输出：[8,9,9,9,0,0,0,1]

```golang
type ListNode struct {
	Val  int
	Next *ListNode
}

func main() {
	var l1 ListNode
	l1.Val = 9
	l1.Next = &ListNode{Val: 9, Next: &ListNode{Val: 9, Next: nil}}
	var l2 ListNode
	l2.Val = 9
	l2.Next = &ListNode{Val: 9, Next: nil}
	res := addTwoNumbers(&l1, &l2)
	for res != nil {
		fmt.Println(res.Val)
		res = res.Next
	}
	fmt.Println(res)
}

func addTwoNumbers(l1 *ListNode, l2 *ListNode) *ListNode {
	var head *ListNode
	var cur *ListNode
	var sum, plus int
	for true {
		if l1 == nil && l2 == nil {
			break
		}
		var l1Val, l2Val int
		if l1 != nil {
			l1Val = l1.Val
			l1 = l1.Next
		}
		if l2 != nil {
			l2Val = l2.Val
			l2 = l2.Next
		}
		var total = l1Val + l2Val + plus
		sum, plus = total%10, total/10
		if head == nil {
			head = &ListNode{Val: sum}
			cur = head
		} else {
			cur.Next = &ListNode{Val: sum}
			cur = cur.Next
		}
	}
	if plus != 0 {
		cur.Next = &ListNode{Val: plus}
	}
	return head
}
```

# 3.无重复字符的最长子串
给定一个字符串 s ，请你找出其中不含有重复字符的 最长子串 的长度。

示例 1:

    输入: s = "abcabcbb"
    输出: 3 
    解释: 因为无重复字符的最长子串是 "abc"，所以其长度为 3。
    示例 2:

    输入: s = "bbbbb"
    输出: 1
    解释: 因为无重复字符的最长子串是 "b"，所以其长度为 1。
    示例 3:

    输入: s = "pwwkew"
    输出: 3
    解释: 因为无重复字符的最长子串是 "wke"，所以其长度为 3。
         请注意，你的答案必须是 子串 的长度，"pwke" 是一个子序列，不是子串。

```golang

func main() {
	s := "pwwkew"
	var length = lengthOfLongestSubstring(s)
	fmt.Println(length)
}

func lengthOfLongestSubstring(s string) int {
	var start, step, length int
	var sMap = make(map[string]int)
	for step = 0; step < len(s); step++ {
		if index, ok := sMap[string(s[step])]; ok {
			if index >= start {
				start = index + 1
			}
		}
		sMap[string(s[step])] = step
		if (step - start + 1) > length {
			length = step - start + 1
		}
	}
	return length
}
```



来源：力扣（LeetCode）
链接：https://leetcode-cn.com/problems/two-sum
著作权归领扣网络所有。商业转载请联系官方授权，非商业转载请注明出处。