#PHP7垃圾回收机制详解

####PHP进行内存管理的核心算法一共两项：
一是引用计数，二是写时拷贝

####php5和php7垃圾回收上的区别

PHP5和PHP7的垃圾回收机制都属于引用计数，但是在复杂数据类型的算法处理上：PHP7中zval有了新的实现方式。

最基础的变化就是 *zval 需要的内存不再是单独从堆上分配，不再自己存储引用计数。复杂数据类型（比如字符串、数组和对象）的引用计数由其自身来存储。

这种实现方式有以下好处：

1、简单数据类型不需要单独分配内存，也不需要计数；

2、不会再有两次计数的情况。在对象中，只有对象自身存储的计数是有效的；

3、由于现在计数由数值自身存储，所以也就可以和非 zval 结构的数据共享，比如 zval 和 hashtable key 之间。

什么叫做引用计数？

由于PHP是用C来写的，C里面有一种东西叫做结构体，我们PHP的变量在C中就是用这种方式存储的。

每个PHP的变量都存在于一个叫做zval的容器中，一个zval容器，除了包含变量名和值，还包括两个字节的额外信息：

●　一个叫做'is_ref'，是个布尔值，用来表示这个变量是否属于引用集合,通过这个字节，我们php才能把普通变量和引用变量区分开来。

●　第二个额外字节就是'refcount'，用来表示指向这个容器的变量的个数。

PHP5 与 PHP7 引用计数的对比

PHP 7 的计数放到了具体的 value 中，zval 不存在写时复制（写时分离）。

并且 PHP 7 的有一个专门的 zend_reference 用来表示引用,解决了引用之后写时复制问题。

详情请看 [PHP7的垃圾回收机制](https://www.phpmianshi.com/?id=54)
