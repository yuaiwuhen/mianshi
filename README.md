>*  [PHP](#php)
>*  [Mysql](#mysql)
>*  [Nginx](#nginx)
>*  [前端](#前端)
>*  [通信协议](#通信协议)
>*  [kafka](#kafka)
>*  [es](#es)
>*  [分布式](#分布式)
>*  [其他](#其他)

<p id="php">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E6%95%B0%E6%8D%AE%E5%BA%93%E7%AF%87)PHP

*   请详细描述ZendMM的工作原理。
> ZendMM是php的内存管理逻辑，ZMM基于libc的基础上自己实现了一套内存管理机制，简单说就是在os、libc与应用之间新增了一个中间层，专门对内存进行管理，ZMM基于libc的内存管理方法重写的内存的释放和获取的e方法，在程序运行时，内存的释放和获取并不是直接和OS进行交互的，而是通过ZMM来实现，ZMM在向OS申请内存时不是需要多少申请多少，而是申请一块相对来说比较大的内存，保存在缓冲区内，下次申请直接从缓冲区内获取分配内存，同样在释放内存时，内存也不是立马回到os中，而是在zmm的缓冲区中标识该内存为可用状态，因此产生了很多的内存碎片，看起来内存使用情况很多，在php5.2及之前，由于没有很好的垃圾回收机制，所以不适合用来做守护进程长期运行。在php5.3及之后，到现在的php7，引入了引用计数的同步回收算法，新的GC机制。
> 如果需要完全禁用ZendMM，则可以使用USE_ZEND_ALLOC=0env var 启动PHP 。这样，每次对ZendMM API的调用（如emalloc()）都将定向到libc调用，并且ZendMM将被禁用。这在调试内存时特别有用。

*   PHP 7 的垃圾回收和 PHP 5 有什么区别？
> PHP的垃圾回收机制以引用计数为基础
> PHP5.2与PHP5.3的垃圾回收机制有很大的区别，5.2基于引用计数算法进行垃圾回收，引用计数为0时即触发回收机制，再着对于循环引用无法触发回收机制，refcount回不到0，造成内存泄露，因此php5.2及之前的版本无法胜任守护进程长期运行的工作。
> PHP5.3及之后为了解决循环引用造成的内存泄露问题，在原来引用计数算法的基础上，引进了同步回收算法，新增了一个疑似垃圾的根缓冲区，当根缓冲区满了之后自动进行内存回收处理，通过模拟删除、模拟恢复操作之后，清空缓冲区的的所有根，然后销毁所有refcount为0的zval,并回收其内存。这种同步算法可将内存泄露控制一个阈值之内，阈值由缓冲区大小有关，因此解决了php5.2的循环引用的问题。
> PHP7的垃圾回收机制与PHP5.3差别不大，PHP7主要区别在于对zval结构体进行了优化。
> 简单的数据类型不需要单独分配内存，也不需要计数，减少了大量的内存分配和释放操作，避免了内存分配的头部冗余。
> 复杂数据类型引用计数由数值自己本身存储，因此可以和非zval结构的数据共享。

*   描述一下 cli 模式下的几个生命周期？

>*    请求开始阶段
>*    模块初始化阶段（MINIT）
>*    模块激活阶段（RINT）
>*    请求结束阶段
>*    停用模块[RSHUTDOWN]
>*    关闭模块[MSHUTDOWN]

*   php-fpm 运行机制？
> 首先启动一个master进程和多个worker进程
> master进程负责监听端口、cgi、php环境初始化、子进程状态，接受来自web server服务器的请求
> worker进程负责处理php请求，每个进程内部嵌入了一个php解释器，是php代码真正执行的地方

*   php-fpm 的生命周期
>fpm通过sapi接口与php进程交互

>1. 模块初始化阶段（module init）：
    这个阶段主要进行php框架、zend引擎的初始化操作。这个阶段一般是在SAPI启动时执行一次，对于FPM而言，就是在fpm的master进行启动时执行的。php加载每个扩展的代码并调用其模块初始化例程（MINIT），进行一些模块所需变量的申请,内存分配等。
>2. 请求初始化阶段（request init）：
    当一个页面请求发生时，在请求处理前都会经历的一个阶段。对于fpm而言，是在worker进程accept一个请求并读取、解析完请求数据后的一个阶段。在这个阶段内，SAPI层将控制权交给PHP层，PHP初始化本次请求执行脚本所需的环境变量。
>3. php脚本执行阶段
    php代码解析执行的过程。Zend引擎接管控制权，将php脚本代码编译成opcodes并顺次执行
>4. 请求结束阶段（request shutdown）：
    请求处理完后就进入了结束阶段，PHP就会启动清理程序。这个阶段，将flush输出内容、发送http响应内容等，然后它会按顺序调用各个模块的RSHUTDOWN方法。 RSHUTDOWN用以清除程序运行时产生的符号表，也就是对每个变量调用unset函数。
>5. 模块关闭阶段（module shutdown）：
该阶段在SAPI关闭时执行，与模块初始化阶段对应，这个阶段主要是进行资源的清理、php各模块的关闭操作，同时，将回调各扩展的module shutdown钩子函数。这是发生在所有请求都已经结束之后，例如关闭fpm的操作。（这个是对于CGI和CLI等SAPI，没有“下一个请求”，所以SAPI立刻开始关闭。）

*  php-fpm创建进程方式，各自的优缺点
>1. static 模式（静态模式）
static 模式始终会保持一个固定数量的子进程，这个数量由 pm.max_children 的配置决定
>2. dynamic 模式（动态模式）
子进程的数量是动态变化的，启动时，会生成固定数量的子进程，可以理解为最小子进程数，通过 pm.statr_servers 配置决定，而最大子进程数则由 pm.max_children 控制，子进程数会在 pm.start_servers ~ pm.max_children 范围内波动，另外，闲置的子进程数还可以由 pm.min_spare_servers 和 pm.max_spare_servers 两个配置参数控制。总结：闲置的子进程也可以有最小数目和最大数目，而如果闲置的子进程超过 pm.max_spare_servers, 则会被杀死。
>3. ondemand 模式（动态需求模式）
这种模式和 dynamic 模式相反。因为这种模式把内存放在第一位，每个闲置进程在持续闲置了 pm.process_idle_timeout 秒后就会被杀死，因为这种模式，到了服务器低峰期的时候，内存就会降下来，如果服务器长时间没有请求，就只有一个主进程。其弊端是，遇到高峰期或者 pm.process_idle_timeout 设置太小，无法避免服务器频繁创建进程的问题。

*  常见魔术方法和函数
>* __construct()，类的构造函数
>* __destruct()，类的析构函数
>* __call()，在对象中调用一个不可访问方法时调用
>* __callStatic()，用静态方式中调用一个不可访问方法时调用
>* __get()，获得一个类的成员变量时调用
>* __set()，设置一个类的成员变量时调用
>* __isset()，当对不可访问属性调用isset()或empty()时调用
>* __unset()，当对不可访问属性调用unset()时被调用。
>* __sleep()，执行serialize()时，先会调用这个函数
>* __wakeup()，执行unserialize()时，先会调用这个函数
>* __toString()，类被当成字符串时的回应方法
>* __invoke()，调用函数的方式调用一个对象时的回应方法
>* __set_state()，调用var_export()导出类时，此静态方法会被调用。
>* __clone()，当对象复制完成时调用
>* __autoload()，尝试加载未定义的类
>* __debugInfo()，打印所需调试信息
>* [100 个最常用的 PHP 函数](https://segmentfault.com/a/1190000018674933)

*  php 数组遍历为什么能保证有序
>* 使用映射表于bucket实现有序性，映射表于bucket内存紧挨着，左边是映射表，右边是bucket
>* 映射表保存了hash之后的值（转为负数），bucket按照插入顺序保存了插入的值，遍历的时候便利bucket就保持了有序性
> [剖析PHP数组的有序性](https://segmentfault.com/a/1190000019964143)

*   php-fpm 模式下，kill -9 master-pid，会怎么样？kill matser-pid 呢？
> kill -9 强制退出，会丢失数据

*   内存分配流程？为什么要这么设计？
> PHP有自己的内存管理逻辑，ZMM。大致逻辑为首先检查缓存，如果命中则使用缓存中的内存块，否则在堆层（heap）从小块内存，大块内存，剩余内存中查找合适内存，如果有则返回分配好的内存地址，否则向OS申请一块内存并返回。
> 这么设计的原因
> 避免直接向os申请内存，减少分配和释放操作，减轻os负担

*   GC 的出现是为了解决什么问题？什么时候会触发 GC？说下大概流程
> 自动处理内存资源的分配与释放，解决内存泄露的问题
> php version&gt;=5.3中当疑似垃圾根缓冲区满的时候，自动触发GC机制。GC机制见上面的回答。

*   `nginx` 和 `php-fpm` 的通信机制
> uninx socket/tcp socket

*   fast-cgi 和 cgi 区别
> cgi（`common gateway interface`）是一种协议,它规定了要传那些数据并以什么格式传递给后方处理这个请求的协议。
> `fast-cgi`是用来提高cgi程序性能的，使用`master`进程来初始化执行环境，加载配置文件，同时启动多个`worder`进程来处理请求，避免了重复的初始化环境和解析配置文件，提高了效率

*   `php-fpm` 创建 `worker` 进程的规则是什么？不同场景下怎么选择？
> `dynamic、static、ondemand`
> 默认为`dynamic`
> static 固定数量的worker，推荐内存较大的服务器进行配置
> dynamic 动态分配，推荐内存较少或者vps上使用，具体可采用 `内存/20~30M`得出

*   php 和 mysql 的通信机制？长链接和短链接啥区别？怎么实现的？连接池要怎么实现？
> 长链接、短链接、连接池
> 长链接、短链接的区别
> 连接池实现

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E7%BB%93%E6%9E%84)结构

*   PHP 7 中对zVal做了哪些修改？
_以php 7.0.0为例，后续版本还在不断的优化更新_
> 优化了zval结构体的大小，在64位系统下只需要16个字节，主要为2个部分，value和扩充字段，而扩充字段氛围u1和u2两个部分，u1为type info，u2为各种辅助字段。
> 调整了zval的类型，数量达到了17种，其中新增了’引用’（is_reference）这种新的类型，同时将is_bool拆分为is_true和is_false
> 调整了zval的引用计数规则，对于在zval的value字段能保存下来的值，就不再进行引用计数了，这部分类型有`is_long,is_double,is_null,is_false,is_true`，对于复杂类型，如is_array,is_object，如果放不下其值（value只有1个指针大小，8个字节），那么value用来保存一个指针，这个指针指向具体的值，引用计数也作用于这个值上，而不在作用于zval上了。
> **这样做有几个优点：**
> 不再需要分配的内存的简单类型的值，避免了内存分配的头部冗余，及减少了不必要的内存分配和释放
> 避免了两次引用计数，像对象，在php5的时候它有两套引用计数，一个是zval的，另一个是obj本身的计数
> 复杂的类型的值都内嵌了引用计数，因此它们可以不依赖zval机制而进行共享
> 新增了是否需要引用的标志位（`IS_TYPE_REFCOUNTED`）

*   PHP 7 中哪些变量类型在栈，哪些变量类型在堆？变量在栈会有什么优势？PHP 7是如何让变量新建在栈的？
> 简单类型如整型，布尔，浮点存放在栈，复杂类型如数组、对象、可变字符串存放在堆，但对应的名称在栈。
> 变量在栈的优势是互相隔离，各自运行。栈内存更新快，内存可直接存取。
> 新建的局部简单类型变量就默认在栈。
> 栈：`LIFO pop,push,top`

*   php 里的数组是怎么实现的？
> HashTable+双向链表
> 参考资料 [php数组实现原理](https://blog.csdn.net/mysteryflower/article/details/101549756)

*   详细描述PHP中HashMap的结构是如何实现的？
> HashMap的基本思想，使用哈希函数将复杂的键值转换为整数，然后整数可用作普通c数组的偏移量。但是问题在于2^32或者2^64的个数比字符串的数目（无穷多个）的数目少的多，这样就会出现两个不同的键的hash值一样的情况，PHP5在PHP7在处理这种冲突上是不一样的，都采用连接法方式处理冲突，但在具体实现上有着一些区别，php7在php5的基础上优化了HashMap结构体，去掉很多冗余的内存消耗。

php5中的处理方式

> 将具有相同哈希的所有元素（bucket）存储在一个双向链表中，这些元素都是单独分配内存的，同时还有另外一个双向链表，用于保存数组中元素的顺序。
> 查找时，将计算哈希值，然后遍历可能的链接列表，直到找到匹配的条目。
> 以上结构是低效的:
> Bukets需要分开分配，每次额外需要分配8/16个字节，比较冗余。分开分配意味着这些buckets会分布在内存空间的不同地址中，这对缓存不友好（`cache-unfriendly`）。
> zval也需要分开分配，除了上述的问题，其次这也会带来额外的头开销冗余（header overhead）。同时还要在每个bucket中保存一个指向zval结构的指针。
> 双向链表中的每个bucket需要4个指针用于链表的连接，这回带来16/32字节的开销，遍历这种链表也是对缓存不友好的。

php7中的处理方式

> arData保存了所有的buckets(也就是数组的元素)，这个数组被分配的内存大小为2的幂次方，arData直接包含bucket结构，避免了过多的法分配和释放（`alloc/frees`）内存操作，同时避免了头开销冗余（header overhead）和额外的指针分配内存。
> 关于元素的顺序，arData数组以插入的顺序保存元素。当某元素被删除时，只是将删除元素zval类型标记为`IS_UNDEF`。相比php5中的双向链表的方式，这里的每个bucket只需要保存两个指针，这两个指针的开销为8/16字节。同时对于缓存是友好的（cache-friendly）。不足的地方是arData很少会缩小，除非进行显式操作。

*   下面代码中，在PHP 7下， $a 和 $b、$c、$d 分别指向什么zVal结构？

*   $d 被修改的时候，PHP 7 / PHP 5 的内部分别会有哪些操作？

    ```php
    $a = 'string';
    $b = &$a;
    $c = &$b;
    $d = $b;
    $d = 'to';

*   JIT 是做了哪些优化，从而对PHP的速度有不少提升？
    > JIT可直接将源码编译成机器码，省去了中间字节码（opcode）的转换，再由php解释器转换成机器码（native code）的过程，大幅提升了执行效率。

    #### 
    [<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%AD%97%E7%AC%A6%E4%B8%B2%E6%93%8D%E4%BD%9C)字符串操作

*   strtr 和 str_replace 有什么区别，两者分别用在什么场景下？
    > strtr是字符串转换 str_replace是字符串替换
>     strtr是基于原字符串 str_replace是基于替换后的字符串
>     strtr在使用的时候有两种形式分别为：
>     `1.strtr(string, from, to); 2.strtr(string, ['from'=&gt;'to']); `
>     推荐使用第2种用法，因为第1种用法有些特别注意的地方，
>     一是from和to替换的规则是逐个转换,并不是整体替换，可看到下面的例子中you的o被替换成了O,显示不是我们想要的结果
    

*   strtr的程序是如何实现的？
    > 两种方式
    
    > 第1种情况下，将from和to的每个字符利用hashtable一一对应起来，逐个完成字符串的转换。
    > 第2种情况下，首先使用key到主字符串中根据kmp算法查找key的位置（O(n)），如果找到则使用value进行替换（替换m次），效率为O(n*m)


*   字符串在手册中介绍，「PHP的字符串是二进制安全的」，这句话怎么理解，为什么是二进制安全？
> 不会因为\0而中断字符串
> [摘自知乎问题答案](https://www.zhihu.com/question/28705562)
> c中的strlen函数就不算是binary safe的，因为它依赖于特殊的字符'\0'来判断字符串是否结束，所以对于字符串str = "1234\0123"来说，`strlen(str)=4`
> 而在php中，strlen函数是binary safe的，因为它不会对任何字符（包括'\0'）进行特殊解释，所以在php中，`strlen(str)=8`
> 所以，我理解的二进制安全的意思是：只关心二进制化的字符串,不关心具体格式.只会严格的按照二进制的数据存取。不会妄图已某种特殊格式解析数据。

*   字符串连接符.，在内核中有哪些操作？多次.连接，是否会造成内存碎片过多？
> 分配新的内存地址和释放旧的内存。
> 多次使用.连接字符串会频繁的alloc/frees，造成更多的内存碎片。

*   接口和抽象类的区别是什么？
>*  抽象类是一种不能被实例化的类，只能作为其他类的父类来使用。抽象类是通过关键字 abstract 来声明的。
>*  抽象类与普通类相似，都包含成员变量和成员方法，两者的区别在于，抽象类中至少要包含一个抽象方法，抽象方法没有方法体，该方法天生就是要被子类重写的。
>*  接口是通过 interface 关键字来声明的，接口中的成员常量和方法都是 public 的，方法可以不写关键字 public，接口中的方法也是没有方法体。接口中的方法也天生就是要被子类实现的。
>*  抽象类和接口实现的功能十分相似，最大的不同是接口能实现多继承。在应用中选择抽象类还是接口要看具体实现。
>*  子类继承抽象类使用 extends，子类实现接口使用 implements。

*   返回一个301重定向的方法

302:
```php
    header("Location:your_dest_url");
```

301:
```php
    header( "HTTP/1.1 301 Moved Permanently" ) ;
    header("Location:your_dest_url");
```

*   strtoupper 在转换中文时存在乱码，你如何解决？php echo strtoupper (‘ab 你好 c’);
>   用mbstring扩展，内部有个函数
>   string mb_convert_case (string $str ,int $mode [,string $encoding = mb_internal_encoding()])
        $mode有三种模式： 

        1.MB_CASE_UPPER：转成大写 

        2.MB_CASE_LOWER：转成小写 

        3.MB_CASE_TITLE ：转成首字母大写

*   csrf与xss攻击的详解与区别
>*  CSRF的基本概念、缩写、全称：CSRF(Cross-site request forgery)：跨站请求伪造。
>       *   Token 验证 解决
>*  XSS的基本概念：XSS（Cross Site Scripting）：跨域脚本攻击。
>       *   不需要你做任何的登录认证，它会通过合法的操作（比如在url中输入、在评论框中输入），向你的页面注入脚本（可能是js、hmtl代码块等）
>       *   XSS的防范措施主要有三个：编码、过滤、校正

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%A4%9A%E7%BA%BF%E7%A8%8B)多线程

*   PHP中创建多线程、多进程有哪些方式？互斥信号该如何实现？
> Pecl中有个扩展`pthreads`提供了多线程特性
> 多进程可使用`proc_open/popen`函数
> pthreads的`Mutex`类可以操作互斥信号；

*   PHP中使用多线程和多进程分别有哪些优缺点？
> 线程比较轻量级，通过共享内存变量可实现线程间通信，但读写变量时存在同步问题，需要加锁。
> 开销大量线程会比进程更耗资源。
> 线程发生致命错误会导致整个进程崩溃。
> 进程相对线程来说更稳定，利用进程间通信（IPC）也可以实现数据共享。
> 共享内存也需要加锁，存在同步、死锁问题。
> 单个线程的退出不会导致整个进程退出，父进程还有机会重建流程。
> PHP原生使用的就是多进程模式

*   线上环境中，PHP进程偶尔会卡死（死锁），请问如何检测本质问题？
> 通过`ps aux | grep php-cgi` 查看进程启动时间，定位启动时间较早的进程
> 通过`lsof -p [pid] `查看进程都干了些啥
> 进一步分析进程 `strace -p [pid]`
> 通过`gdb attach [pid]`分析获取调用堆栈
> 或使用 [Swoole Tracker](https://www.swoole-cloud.com/tracker/index)

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E7%AE%A1%E9%81%93)管道

*   Laravel的中间件的顺序执行，是如何实现的？
> 中间件核心类`Illuminate\Routing\Pipeline`，其中的`then()`方法利用了`array_reduce()`函数，将反转后(`array_reverse()`)的中间件数组分别以闭包的形式暂存到一个类似栈的结构中，这个结构我们可以理解为一个大的闭包，里面嵌套的包含了所有需要执行的中间件闭包，执行的时候顺序为从最外层开始往里面执行（后进先出），最后执行`Initial`。

*   实现管道的makeFn函数
<div class="highlight highlight-text-html-php position-relative overflow-auto" data-snippet-clipboard-copy-content="function pipe($input, $list) {
    $fn = makeFn($list);
    return $fn($input);
}
$r = pipe(0, [$a, $b, $c]);
echo $r;
//$a, $b, $c 类似于
$a = function($input, $next) {
    $input++;
    $output = $next($input);
    return $output;
};
function makeFn($list){
    //请实现
    return function($input) use($list) {
        foreach($list as $item){
            $item($input, $item);
        }
    }
}"><pre><span class="pl-k">function</span> <span class="pl-en">pipe</span>(<span class="pl-s1"><span class="pl-c1">$</span>input</span>, <span class="pl-s1"><span class="pl-c1">$</span>list</span>) {
    <span class="pl-s1"><span class="pl-c1">$</span>fn</span> = <span class="pl-en">makeFn</span>(<span class="pl-s1"><span class="pl-c1">$</span>list</span>);
    <span class="pl-k">return</span> <span class="pl-s1"><span class="pl-c1">$</span>fn</span>(<span class="pl-s1"><span class="pl-c1">$</span>input</span>);
}
<span class="pl-s1"><span class="pl-c1">$</span>r</span> = <span class="pl-en">pipe</span>(<span class="pl-c1">0</span>, [<span class="pl-s1"><span class="pl-c1">$</span>a</span>, <span class="pl-s1"><span class="pl-c1">$</span>b</span>, <span class="pl-s1"><span class="pl-c1">$</span>c</span>]);
<span class="pl-k">echo</span> <span class="pl-s1"><span class="pl-c1">$</span>r</span>;
<span class="pl-c">//$a, $b, $c 类似于</span>
<span class="pl-s1"><span class="pl-c1">$</span>a</span> = <span class="pl-k">function</span>(<span class="pl-s1"><span class="pl-c1">$</span>input</span>, <span class="pl-s1"><span class="pl-c1">$</span>next</span>) {
    <span class="pl-s1"><span class="pl-c1">$</span>input</span>++;
    <span class="pl-s1"><span class="pl-c1">$</span>output</span> = <span class="pl-s1"><span class="pl-c1">$</span>next</span>(<span class="pl-s1"><span class="pl-c1">$</span>input</span>);
    <span class="pl-k">return</span> <span class="pl-s1"><span class="pl-c1">$</span>output</span>;
};
<span class="pl-k">function</span> <span class="pl-en">makeFn</span>(<span class="pl-s1"><span class="pl-c1">$</span>list</span>){
    <span class="pl-c">//请实现</span>
    <span class="pl-k">return</span> <span class="pl-k">function</span>(<span class="pl-s1"><span class="pl-c1">$</span>input</span>) <span class="pl-k">use</span>(<span class="pl-s1"><span class="pl-c1">$</span>list</span>) {
        <span class="pl-k">foreach</span>(<span class="pl-s1"><span class="pl-c1">$</span>list</span> <span class="pl-k">as</span> <span class="pl-s1"><span class="pl-c1">$</span>item</span>){
            <span class="pl-s1"><span class="pl-c1">$</span>item</span>(<span class="pl-s1"><span class="pl-c1">$</span>input</span>, <span class="pl-s1"><span class="pl-c1">$</span>item</span>);
        }
    }
}</pre></div>

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%86%85%E5%AD%98%E4%BC%98%E5%8C%96)内存优化

*   使用cUrl下载大文件时，占用内存太大，有没比较优化的方式？
> 使用cUrl下载时，文件在存入本地磁盘之前会将文件先放在内存中，文件很大时会占用内存，比较优化的方式是使用流下载，利用`CURLOPT_FILE`选项传递一个可写的文件流给到cUrl。

*   PHP 上传大文件（比如：2 GiB的视频），需要修改php.ini的哪些配置以免受到上传的大小限制？或者你有其它更好的方式？
> 修改`upload_max_filesize`，如果nginx+php-fpm还要修改nginx的`client_max_body_size`；
> 可以使用文件流的形式上传

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#cli)Cli

*   用PHP实现一个定时任务器？
> PCNTL扩展，`pcntl_alarm()` [参考实现](https://www.cnblogs.com/CpNice/p/4528610.html)
> swoole_timer

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%AE%89%E5%85%A8)安全

*   PHP中密码加密，使用什么方式加密？这种加密的优点是什么？
> `password_hash()`
> `password_hash()`使用足够强度的单向散列算法创建密码的散列（hash），来产生足够强的盐值，并且会自动进行合适的轮次。`password_hash()`是`crypt()`的一个简单封装，并且完全与现有的密码哈希兼容。

*   PHP 7.2 新增的加密方法的名称是？
> Argon2

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%8F%8D%E5%B0%84)反射

*   实现如下函数(PHP 7)
```php
    echo a(1, 3); //4
    echo a(3)(5); //8
    echo a(1, 2)(3, 4, 5)(6); //21
    //实现不了
    function a(){
        $sum = array_sum(func_get_args());
        return function() use($sum) {
            return $sum+=array_sum(func_get_args());
        };
    }
```

*   如何读取某函数的参数列表，以及参数的默认值。
> 通过`func_get_args()`获取参数列表，通过` func_get_arg(index)`获取参数值

*   描述下IoC （DI）的实现原理
> DI（依赖注入）是Ioc（控制反转）的一种实现方式。常见注入方式有`setter、contructor injection、property injection`。 [laravel服务容器-----深入理解控制反转（IoC）和依赖注入（DI）](https://www.cnblogs.com/lishanlei/p/7627367.html)
> DI的实现依赖于php的反射api的能力。DI容器的实现是通过反射api的能力递归分析类的依赖关系并实例化所有依赖。

<p id="mysql">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E6%95%B0%E6%8D%AE%E5%BA%93%E7%AF%87)数据库篇

*   搭建MySQL分布式，有哪些方式？
> 客户端方案或者中间代理方案

*   MySQL主从同步，和主主同步有哪些区别，以及优劣势？
> 主节点 binary log dump 线程
> 当从节点连接主节点时，主节点会创建一个log dump 线程，用于发送bin-log的内容。在读取bin-log中的操作时，此线程会对主节点上的bin-log加锁，当读取完成，甚至在发动给从节点之前，锁会被释放。


> 从节点I/O线程
> 当从节点上执行`start slave`命令之后，从节点会创建一个I/O线程用来连接主节点，请求主库中更新的bin-log。I/O线程接收到主节点binlog dump 进程发来的更新之后，保存在本地relay-log中。


> 从节点SQL线程
> SQL线程负责读取relay log中的内容，解析成具体的操作并执行，最终保证主从数据的一致性。

> 对于每一个主从连接，都需要三个进程来完成。当主节点有多个从节点时，主节点会为每一个当前连接的从节点建一个binary log dump 进程，而每个从节点都有自己的I/O进程，SQL进程。从节点用两个线程将从主库拉取更新和执行分成独立的任务，这样在执行同步数据任务的时候，不会降低读操作的性能。比如，如果从节点没有运行，此时I/O进程可以很快从主节点获取更新，尽管SQL进程还没有执行。如果在SQL进程执行之前从节点服务停止，至少I/O进程已经从主节点拉取到了最新的变更并且保存在本地relay日志中，当服务再次起来之后，就可以完成数据的同步。

> **推荐阅读**
> [[深度探索MySQL主从复制原理](https://zhuanlan.zhihu.com/p/50597960)]

*   Laravel中，多态一对多，多对多，数据库要怎么设计？比如一个关键词表tags，需要关联用户、帖子、评论、视频等表。
> tags表，主要字段tags_id,tags_name
> 关联表tags_relation，主要字段,id均为个表的主键relation_id,tags_id,video_id,post_id,comment_id,video_id

*   MySQL防止注入有哪些方式？
> 最好采用预编译语句`PreparedStatement`的方式
> mysql语句中使用`mysql_real_escape_string()`函数进行转义
> 打开php的设置项`magic_quotes_gpc=on`，将自动把用户提交对sql的查询进行转换

*   描述MySQL的注入原理？
> 通过把SQL命令插入到Web表单提交或输入域名或页面请求的查询字符串，最终达到欺骗服务器执行恶意的SQL命令

*   怎么解决数据库中常见的 N+1 效率问题
比如：
```php
    $users = SELECT * FROM `users` WHERE `gender` = 'male';
    foreach ($users as &$user)
    $user['posts'] = SELECT * FROM `posts` WHERE `user_id` = $user['id'];
``` 
> 先查出所有gender为male的用户id，再根据这些用户id去查询posts

```sql
    #查出所有用户id
    select user_id from `users` where `gender` = 'male';
    #使用in查出所有user的posts数据 
    select * from `posts` where `user_id` in(userid);
    #然后在php中按用户整理posts数据返回
```

*   哪些情况下字段允许null，哪些情况下不允许？
> 尽量设置not null
> 理由
> 含有null值的列难以进行查询优化
> 影响索引效率，使得索引、索引的统计信息以及比较运算更加复杂

*   MySQL中脏读应该怎么处理？
引申：比如京东的库存，0点多人抢购的时候库存问题？
> 解决mysql脏读的方法：1、serializable可避免脏读、不可重复读、虚读情况的发生；2、repeatable read可以避免脏读、不可重复读情况的发生；3、read committed可以避免脏读情况发生。
> 

*   如下数据库中会有哪些值

```sql
    START TRANSACTION;
    INSERT INTO `users` (`name`) VALUES('a');
    START TRANSACTION;
    INSERT INTO `users` (`name`) VALUES('b');
    START TRANSACTION;
    INSERT INTO `users` (`name`) VALUES('c');
    ROLLBACK;
    COMMIT;
    ROLLBACK;
```
> a, b
> 在一个事务没有`COMMIT`或者`ROLLBACK`时再`START TRANSACTION`，会自动提交前面的事务。

*   Elasticsearch 如何实现类似SQL的
```sql
    WHERE `id` = 12 AND `gender`  IN ('male', 'unknow’);
```

*   innodb 的数据组织方式？
> InnoDB 存储引擎以页（默认为16KB）为基本单位存储

*   B + 树的结构和插入细节？为什么主键一般都要自增？和 B 树什么区别？为什么索引要使用 B + 树不是 B 树也不是其他的平衡树？
> B + 树的结构

> M阶段B树每个节点最多有M个孩子

> 除了根节点和叶子节点外，其他每个节点至少有ceil(M/2)个孩子

> B+树的中间节点没有卫星数据的。所以同样大小的磁盘页可以容纳更多的节点元素。（这就意味着B+会更加矮胖，查询的IO次数会更少）。

> 所有查询都要查找到叶子节点，查询性能稳定。

> 所有叶子节点形成有序链表，便于范围查询。

> B树查找性能是不稳定的（如果要查找的数据分别在根节点和叶子节点，他们的性能就会不同）。但B+树的每一次都是稳定的。

>一个m阶的B+树具有如下几个特征：

> 1、有k个子树的中间节点包含有k个元素（B树中是k-1个元素），每个元素不保存数据，只用来索引，所有数据都保存在叶子节点。 

>2、所有的叶子结点中包含了全部元素的信息，及指向含这些元素记录的指针，且叶子结点本身依关键字的大小自小而大顺序链接。 

>3、所有的中间节点元素都同时存在于子节点，在子节点元素中是最大（或最小）元素。


> [为什么mysql索引要使用B+树，而不是B树，红黑树](https://segmentfault.com/a/1190000021488885)

* MySQL中存储索引用到的数据结构是B+树，B+树的查询时间跟树的高度有关，是log(n)，如果用hash存储，那么查询时间是O(1)。既然hash比B+树更快，为什么mysql用B+树来存储索引呢？

> 答：一、从内存角度上说，数据库中的索引一般时在磁盘上，数据量大的情况可能无法一次性装入内存，B+树的设计可以允许数据分批加载。

> 二、从业务场景上说，如果只选择一个数据那确实是hash更快，但是数据库中经常会选中多条这时候由于B+树索引有序，并且又有链表相连，它的查询效率比hash就快很多了。

* 为什么不用红黑树或者二叉排序树？

> 答：树的查询时间跟树的高度有关，B+树是一棵多路搜索树可以降低树的高度，提高查找效率

* 既然增加树的路数可以降低树的高度，那么无限增加树的路数是不是可以有最优的查找效率？

>答：这样会形成一个有序数组，文件系统和数据库的索引都是存在硬盘上的，并且如果数据量大的话，不一定能一次性加载到内存中。有序数组没法一次性加载进内存，这时候B+树的多路存储威力就出来了，可以每次加载B+树的一个结点，然后一步步往下找，

* 在内存中，红黑树比B树更优，但是涉及到磁盘操作B树就更优了，那么你能讲讲B+树吗？

> B+树是在B树的基础上进行改造，它的数据都在叶子结点，同时叶子结点之间还加了指针形成链表。

* 为什么B+树要这样设计？

> 答：这个跟它的使用场景有关，B+树在数据库的索引中用得比较多，数据库中select数据，不一定只选一条，很多时候会选中多条，比如按照id进行排序后选100条。如果是多条的话，B树需要做局部的中序遍历，可能要跨层访问。而B+树由于所有数据都在叶子结点不用跨层，同时由于有链表结构，只需要找到首尾，通过链表就能把所有数据取出来了。



*   常见的优化（这里我就不展开了，主要考察覆盖索引查询和最左匹配）
> 

*   `redolog/undolog/binlog` 的区别？binlog 的几种格式？说下两阶段提交？
> [必须了解的mysql三大日志-binlog、redo log和undo log]https://segmentfault.com/a/1190000023827696

> 由 binlog 和 redo log 的区别可知： binlog 日志只用于归档，只依靠 binlog 是没有 `crash-safe 能力的。但只有 redo log 也不行，因为 redo log 是 InnoDB `特有的，且日志上的记录落盘后会被覆盖掉。因此需要 binlog 和 redo log二者同时记录，才能保证当数据库发生宕机重启时，数据不会丢失。

>数据库事务四大特性中有一个是 原子性 ，具体来说就是 原子性是指对数据库的一系列操作，要么全部成功，要么全部失败，不可能出现部分成功的情况。实际上， 原子性 底层就是通过 undo log 实现的。 undo log 主要记录了数据的逻辑变化，比如一条 ` INSERT语句，对应一条 DELETE 的 undo log ，对于每个 UPDATE 语句，对应一条相反的 UPDATE 的undo log ，这样在发生错误时，就能回滚到事务之前的数据状态。同时， undo log 也是 MVCC `(多版本并发控制)实现的关键

|  | redo log | binlog |
| :-----:| :----: | :----: |
| 文件大小 | redo log 的大小是固定的。 | binlog 可通过配置参数 max_binlog_size 设置每个 binlog 文件的大小。 |
| 实现方式 | redo log 是 InnoDB 引擎层实现的，并不是所有引擎都有。 | binlog 是 Server 层实现的，所有引擎都可以使用 binlog 日志 |
| 记录方式 | redo log 采用循环写的方式记录，当写到结尾时，会回到开头循环写日志。 | binlog通过追加的方式记录，当文件大小大于给定值后，后续的日志会记录到新的文件上 |
| 适用场景 | redo log 适用于崩溃恢复(crash-safe) | binlog 适用于主从复制和数据恢复 |

    binlog日志有三种格式，分别为STATMENT、ROW和MIXED。

    在 MySQL 5.7.7之前，默认的格式是STATEMENT，MySQL 5.7.7之后，默认值是ROW。日志格式通过binlog-format指定。

    STATMENT 基于SQL语句的复制(statement-based replication, SBR)，每一条会修改数据的sql语句会记录到binlog中。 优点：不需要记录每一行的变化，减少了binlog日志量，节约了IO, 从而提高了性能； 缺点：在某些情况下会导致主从数据不一致，比如执行sysdate()、slepp()等。
    ROW 基于行的复制(row-based replication, RBR)，不记录每条sql语句的上下文信息，仅需记录哪条数据被修改了。 优点：不会出现某些特定情况下的存储过程、或function、或trigger的调用和触发无法被正确复制的问题； 缺点：会产生大量的日志，尤其是alter table的时候会让日志暴涨
    MIXED 基于STATMENT和ROW两种模式的混合复制(mixed-based replication, MBR)，一般的复制使用STATEMENT模式保存binlog，对于STATEMENT模式无法复制的操作使用ROW模式保存binlog

MySQL想要准备事务的时候会先写redolog、binlog分成两个阶段。

[MySQL两阶段提交串讲](https://zhuanlan.zhihu.com/p/343449447)

    两阶段提交的第一阶段 （prepare阶段）：写rodo-log 并将其标记为prepare状态。

    紧接着写binlog

    两阶段提交的第二阶段（commit阶段）：写bin-log 并将其标记为commit状态。

    你有没有想过这样一件事，binlog默认都是不开启的状态！

    也就是说，如果你根本不需要binlog带给你的特性（比如数据备份恢复、搭建MySQL主从集群），那你根本就用不着让MySQL写binlog，也用不着什么两阶段提交。

    只用一个redolog就够了。无论你的数据库如何crash，redolog中记录的内容总能让你MySQL内存中的数据恢复成crash之前的状态。

    所以说，两阶段提交的主要用意是：为了保证redolog和binlog数据的安全一致性。只有在这两个日志文件逻辑上高度一致了。你才能放心地使用redolog帮你将数据库中的状态恢复成crash之前的状态，使用binlog实现数据备份、恢复、以及主从复制。而两阶段提交的机制可以保证这两个日志文件的逻辑是高度一致的。没有错误、没有冲突。


*   执行 insert 语句的过程是什么
>1. 客户端（通常是你的服务）发出更新语句” update t set b = 200 where id = 2 “ 并向MySQL服务端建立连接；
>2. MySQL连接器负责和客户端建立连接，获取权限，维持和管理连接；
>3. MySQL拿到一个查询请求后，会先到查询缓存看看（MySQL8.x已经废弃了查询缓存），看之前是否已经执行过，如果执行过，执行语句及结果会以key-value形式存储到内存中，如果命中缓存会返回结果。如果没命中缓存，就开始真正执行语句。分析器会先做词法分析，识别出关键字update，表名等等；之后还会做语法分析，判断输入的语句是否符合MySQL语法；
>4. 经过分析器，MySQL已经知道语句是要做什么。优化器接着会选择使用哪个索引（如果多个表，会选择表的连接顺序）；
>5. MySQL服务端最后一个阶段是执行器会调用引擎的接口去执行语句；
>6. 事务开始（任何一个操作都是事务），写undo log ，记录记录上一个版本数据，并更新记录的回滚指针和事务ID；
>7. 执行器先调用引擎取id=2这一行。id是主键，引擎直接用树搜索找到这一行；
>       1. 如果id=2这一行所在的数据页本来就在内存 中，就直接返回给执行器更新；
>       2. 如果记录不在内存，接下来会判断索引是否是唯一索引；
>           1. 如果不是唯一索引，InnoDB会将更新操作缓存在change buffer中；
>           2. 如果是唯一索引，就只能将数据页从磁盘读入到内存，返回给执行；
>8. 执行器拿到引擎给的行数据，把这个值加上1，比如原来是N，现在就是N+1，得到新的一行数据，再调用引擎接口写入这行新数据；
>9. 引擎将这行数据更新到内存中，同时将这个更新操作记录到redo log 里面；
>10. 执行器生成这个操作的binlogbinlog ；
>11. 执行器调用引擎的提交事务接口；
>12. 事务的两阶段提交：commit的prepare阶段：引擎把刚刚写入的redo log刷盘；
>13. 事务的两阶段提交：commit的commit阶段：引擎binlog刷盘。



*   事务隔离级别和不同级别会出现的问题，innodb 默认哪个级别？MVCC 怎么实现的？快照读和当前读有啥区别？幻读的问题怎么解决？
> 

[mysql事务和锁，一次性讲清楚](https://juejin.cn/post/6855129007336521741)

| 隔离级别 | 脏读 | 不可重复读 | 幻读 |
| :-----:| :----: | :----: | :----: |
| 未提交读（READ UNCOMMITTED） | 可能 | 可能	| 可能 | 
| 已提交读（READ COMMITTED） | 不可能 | 可能 | 可能 | 
| (默认)可重复读（REPEATABLE READ） | 不可能 | 不可能 | 可能（对InnoDB不可能） | 
| 串行化（SERIALIZABLE） | 	不可能 | 	不可能 | 	不可能 | 


MVCC实现

>在InnoDB中，每行记录实际上都包含了两个隐藏字段：事务id(trx_id)和回滚指针(roll_pointer)。

>trx_id：事务id。每次修改某行记录时，都会把该事务的事务id赋值给trx_id隐藏列。

>roll_pointer：回滚指针。每次修改某行记录时，都会把undo日志地址赋值给roll_pointer隐藏列。

> 如果数据库隔离级别是未提交读（READ UNCOMMITTED），那么读取版本链中最新版本的记录即可。如果是是串行化（SERIALIZABLE），事务之间是加锁执行的，不存在读不一致的问题。但是如果是已提交读（READ COMMITTED）或者可重复读（REPEATABLE READ），就需要遍历版本链中的每一条记录，判断该条记录是否对当前事务可见，直到找到为止(遍历完还没找到就说明记录不存在)。InnoDB通过ReadView实现了这个功能。ReadView中主要包含以下4个内容：

>* m_ids：表示在生成ReadView时当前系统中活跃的读写事务的事务id列表。
>* min_trx_id：表示在生成ReadView时当前系统中活跃的读写事务中最小的事务id，也就是m_ids中的最小值。
>* max_trx_id：表示生成ReadView时系统中应该分配给下一个事务的id值。
>* creator_trx_id：表示生成该ReadView事务的事务id。  
> 在MySQL中，READ COMMITTED和REPEATABLE READ隔离级别的的一个非常大的区别就是它们生成ReadView的时机不同。READ COMMITTED在每次读取数据前都会生成一个ReadView，这样就能保证每次都能读到其它事务已提交的数据。REPEATABLE READ 只在第一次读取数据时生成一个ReadView，这样就能保证后续读取的结果完全一致。

> 在快照读读情况下，mysql通过mvcc来避免幻读。

> 在当前读读情况下，mysql通过next-key来避免幻读。

*  主从同步流程（异步同步）
>* 主库把数据变更写入binlog文件
>* 从库I/O线程发起dump请求
>* 主库I/O线程推送binlog至从库
>* 从库I/O线程写入本地的relay log文件（与binlog格式一样）
>* 从库SQL线程读取relay log并重新串行执行一遍，得到与主库相同的数据

*   主从同步，数据库主库和从库不一致，常见有这么几种优化方案：

>1. 业务可以接受，系统不优化
>2. 强制读主，高可用主库，用缓存提高读性能
>3. 在cache里记录哪些记录发生过写请求，来路由读主还是读从

* InnoDB 内存结构包含四大核心组件
>* 缓冲池 (Buffer Pool)，可以参考沈健老师文章 [缓冲池 (buffer pool)，这次彻底懂了！！！](https://mp.weixin.qq.com/s?__biz=MjM5ODYxMDA5OQ==&mid=2651962467&idx=1&sn=899ea157b0fc6f849ec80a4d055a309b&chksm=bd2d09bf8a5a80a972a2e16a190ed7dffe03f89015ead707bdfcc5aeb8388fb278f397c125f1&scene=21#wechat_redirect)

>* 写缓冲 (Change Buffer)，可以参考沈健老师文章 [写缓冲 (change buffer)，这次彻底懂了！！！](https://mp.weixin.qq.com/s?__biz=MjM5ODYxMDA5OQ==&mid=2651962450&idx=1&sn=ce17c4da8d20ce275f75d0f2ef5e40c9&chksm=bd2d098e8a5a809834aaa07da0d7546555385543fb6d687a7cf94d183ab061cd301a76547411&scene=21#wechat_redirect)

>* 自适应哈希索引 (Adaptive Hash Index)，[可以参考沈健老师文章自适应哈希索引](https://mp.weixin.qq.com/s?__biz=MjM5ODYxMDA5OQ==&mid=2651962875&idx=1&sn=c6b3e7dc8a41609cfe070026bd27b71d&chksm=bd2d08278a5a813108b1f4116341ff31170574b9098e2708cbc212b008a1fac8dfd1ffeabc6b&scene=21#wechat_redirect)
>* 日志缓冲 (Log Buffer)，[可以参考沈健老师文章 事务已提交，数据却丢了，赶紧检查下这个配置！！！ | 数据库系列](https://mp.weixin.qq.com/s?__biz=MjM5ODYxMDA5OQ==&mid=2651962887&idx=1&sn=4806f481448b1c3ddfbbd53e732a7bb5&chksm=bd2d0bdb8a5a82cd50bc155ed2ba57f105bfd76ff78992823ed85214b5c767eef17e691a2255&scene=21#wechat_redirect)


*   explain 的 type 字段有哪些（知乎）

>* system：系统表，少量数据，往往不需要进行磁盘IO
>* const：常量连接
>* eq_ref：主键索引(primary key)或者非空唯一索引(unique not null)等值扫描
>* ref：非主键非唯一索引等值扫描
>* range：范围扫描
>* index：索引树扫描
>* ALL：全表扫描(full table scan)

*   MySQL中update语句的执行流程

> 当你执行这条命令的时候，执行器首先会让InnoDB去查找到这一行，看这一行的数据页有没有在内存中，如果有就直接返回，如果没有就在磁盘中找，再读入到内存中，最后在返回。

> 执行器拿到了引擎给的数据之后，就会把这个user_name这个属性修改为“XXX”，得到一行新的数据，然后再调用引擎接口写入这行新的数据。

> 引擎把这行新的数据更新到内存的同时也会将这个更新操作记录在redo log里，这时候redo log会处于一个准备状态，然后告知执行器已经执行完成，可以随时提交事务。

> 执行器再生成这个操作的bin log，把这个bin log写入到磁盘中。

> 最后执行器再调用引擎的提交事务接口，然后把redo log 的准备状态改成提交状态，这时候更新完成。

*   死锁什么时候会出现？应用层应该怎么做避免死锁？mysql 是怎么处理死锁的呢？

> MySQL 出现死锁的几个要素为：

>* 两个或者两个以上事务
>* 每个事务都已经持有锁并且申请新的锁
>* 锁资源同时只能被同一个事务持有或者不兼容
>* 事务之间因为持有锁和申请锁导致彼此循环等待

> 减少死锁：

>* 使用事务，不使用 lock tables 。
>* 保证没有长事务。
>* 操作完之后立即提交事务，特别是在交互式命令行中。
>* 如果在用 (SELECT ... FOR UPDATE or SELECT ... LOCK IN SHARE MODE)，尝试降低隔离级别。
>* 修改多个表或者多个行的时候，将修改的顺序保持一致。
>* 创建索引，可以使创建的锁更少。
>* 最好不要用 (SELECT ... FOR UPDATE or SELECT ... LOCK IN SHARE MODE)。
>* 如果上述都无法解决问题，那么尝试使用 lock tables t1, t2, t3 锁多张表
>* 合理的设计索引，区分度高的列放到组合索引前面，使业务 SQL 尽可能通过索引定位更少的行，减少锁竞争。
>* 调整业务逻辑 SQL 执行顺序， 避免 update/delete 长时间持有锁的 SQL 在事务前面。
>* 避免大事务，尽量将大事务拆成多个小事务来处理，小事务发生锁冲突的几率也更小。
>* 以固定的顺序访问表和行。比如两个更新数据的事务，事务 A 更新数据的顺序为 1，2;事务 B 更新数据的顺序为 2，1。这样更可能会造成死锁。
>* 在并发比较高的系统中，不要显式加锁，特别是是在事务里显式加锁。如 select … for update 语句，如果是在事务里（运行了 start transaction 或设置了autocommit 等于0）,那么就会锁定所查找到的记录。
>* 尽量按主键/索引去查找记录，范围查找增加了锁冲突的可能性，也不要利用数据库做一些额外额度计算工作。比如有的程序会用到 “select … where … order by rand();”这样的语句，由于类似这样的语句用不到索引，因此将导致整个表的数据都被锁住。
>* 优化 SQL 和表设计，减少同时占用太多资源的情况。比如说，减少连接的表，将复杂 SQL 分解为多个简单的 SQL。


> * MySQL有两种死锁处理方式：  
> 1. 等待，直到超时（innodb_lock_wait_timeout=50s）。   
> 2. 发起死锁检测，主动回滚一条事务，让其他事务继续执行（innodb_deadlock_detect=on）。  
> 3. 由于性能原因，一般都是使用死锁检测来进行处理死锁。


*  MySQL 遇到过死锁问题吗，你是如何解决的？
>排查死锁的步骤：

>查看死锁日志 show engine innodb status;  
>找出死锁 Sql  
>分析 sql 加锁情况  
>模拟死锁案发  
>分析死锁日志  
>分析死锁结果

* 常用的分库分表中间件
>sharding-jdbc  
>Mycat

*  分库分表可能遇到的问题

>* 事务问题：需要用分布式事务
>* 跨节点 Join 的问题：解决这一问题可以分两次查询实现
>* 跨节点的 count,order by,group by 以及聚合函数问题：分别在各个节点上得到结果后在应用程序端进行合并。
>* 数据迁移，容量规划，扩容等问题
>* ID 问题：数据库被切分后，不能再依赖数据库自身的主键生成机制啦，最简单可以考虑 UUID
>* 跨分片的排序分页问题（后台加大 pagesize 处理？）

*  limit 1000000 加载很慢的话，你是怎么解决的呢？
>* 方案一：如果 id 是连续的，可以这样，返回上次查询的最大记录 (偏移量)，再往下 limit   
>* 方案二：在业务允许的情况下限制页数： 建议跟业务讨论，有没有必要查这么后的分页啦。因为绝大多数用户都不会往后翻太多页。
>* 方案三：order by + 索引（id 为索引）
>* 方案四：利用延迟关联或者子查询优化超多分页场景。（先快速定位需要获取的 id 段，然后再关联）


*  InnoDB 四种事务隔离级别：

>* 读未提交 (Read Uncommitted)
>* 读提交 (Read Committed, RC)
>* 可重复读 (Repeated Read, RR)
>* 串行化 (Serializable)   

>不同事务的隔离级别，实际上是一致性与并发性的一个权衡与折衷。   
>InnoDB 使用不同的锁策略 (Locking Strategy) 来实现不同的隔离级别。

>读未提交 (Read Uncommitted)
>* 这种事务隔离级别下，select 语句不加锁。

>* 此时，可能读取到不一致的数据，即 “读脏”。这是并发最高，一致性最差的隔离级别。

>串行化 (Serializable)
>* 这种事务的隔离级别下，所有 select 语句都会被隐式的转化为 select … in share mode.

>* 这可能导致，如果有未提交的事务正在修改某些行，所有读取这些行的 select 都会被阻塞住。

>* 这是一致性最好的，但并发性最差的隔离级别。 在互联网大数据量，高并发量的场景下，几乎不会使用上述两种隔离级别。

> 可重复读 (Repeated Read, RR) 这是 InnoDB 默认的隔离级别，在 RR 下：
>* 普通的 select 使用快照读 (snapshot read)，这是一种不加锁的一致性读 (Consistent Nonlocking Read)，底层使用 MVCC 来实现；

>* 加锁的 select (select … in share mode /select … for update), update, delete 等语句，它们的锁，依赖于它们是否在唯一索引 (unique index) 上使用了唯一的查询条件 (unique search condition)，或者范围查询条件 (range-type search condition)：

>* 在唯一索引上使用唯一的查询条件，会使用记录锁 (record lock)，而不会封锁记录之间的间隔，即不会使用间隙锁 (gap lock) 与临键锁 (next-key lock)
>* 范围查询条件，会使用间隙锁与临键锁，锁住索引记录之间的范围，避免范围间插入记录，以避免产生幻影行记录，以及避免不可重复的读

> 读提交 (Read Committed, RC) 这是互联网最常用的隔离级别，在 RC 下：
>* 普通读是快照读；

>* 加锁的 select, update, delete 等语句，除了在外键约束检查 (foreign-key constraint checking) 以及重复键检查 (duplicate-key checking) 时会封锁区间，其他时刻都只使用记录锁；
>* 此时，其他事务的插入依然可以执行，就可能导致，读取到幻影记录。

*   select for update 有什么含义，会锁表还是锁行还是其他？

>select 查询语句是不会加锁的，但是 select for update 除了有查询的作用外，还会加锁呢，而且它是悲观锁哦。至于加了是行锁还是表锁，这就要看是不是用了索引 / 主键啦。 没用索引 / 主键的话就是表锁，否则就是是行锁。

*  MySQL 事务得四大特性以及实现原理
>* 原子性： 事务作为一个整体被执行，包含在其中的对数据库的操作要么全部被执行，要么都不执行。
>* 一致性： 指在事务开始之前和事务结束以后，数据不会被破坏，假如 A 账户给 B 账户转 10 块钱，不管成功与否，A 和 B 的总金额是不变的。
>* 隔离性： 多个事务并发访问时，事务之间是相互隔离的，即一个事务不影响其它事务运行效果。简言之，就是事务之间是进水不犯河水的。
>* 持久性： 表示事务完成以后，该事务对数据库所作的操作更改，将持久地保存在数据库之中。

>事务 ACID 特性的实现思想
>* 原子性：是使用 undo log 来实现的，如果事务执行过程中出错或者用户执行了 rollback，系统通过 undo log 日志返回事务开始的状态。
>* 持久性：使用 redo log 来实现，只要 redo log 日志持久化了，当系统崩溃，即可通过 redo log 把数据恢复。
>* 隔离性：通过锁以及 MVCC, 使事务相互隔离开。
>* 一致性：通过回滚、恢复，以及并发情况下的隔离性，从而实现一致性。


*   int 占多少字节？bigint 呢？int (3) 和 int (11) 有区别吗？可以往 int (3) 里存 1 亿吗？varchar 最长多少？
> int 4个字节
> bigint 8个字节
> tinyint 1个字节 smallint 2个字节 mediumint 3个字节
> int(3)和int(11)占用字节数无区别，都是4个字节。3和11表示在字段添加了`zerofill`属性后填充0之后的长度
> int(3) 取值范围 `-2^31 ~ 2^31-1`， 可以存1亿
> varchar受限mysql的行长度，行长度和其他非大型字段（如text、blob）加起来不能超过65535个字节
> 其次根据字符集的不同，GBK每个字符占用2个字节，UTF8下占用3个字节，因此分别在GBK和UTF8下的最大长度分别是`65535/2=32766`和`65535/3=21845`
> _引申_
> [为什么mysql的varchar字符长度会被经常性的设置成255？](https://blog.csdn.net/w790634493/article/details/80650611?depth_1-utm_source=distribute.pc_relevant.none-task&amp;utm_source=distribute.pc_relevant.none-task)

*   sql 的执行流程
> **建立TCP连接**
> 连接成功后会验证权限
> 半双工通信
> **查询缓存，如果命中则直接返回**
> 一个大小写敏感的哈希查找实现的
> 返回结果前再次验证权限
> **进行语法解析和预处理**
> 通过mysql关键字将语句解析，生成解析树，验证是否有错误的关键字，顺序是否正确
> 预处理器根据mysql的规则，检查语句是否合法，库表字段是否存在，并验证权限
> **进行优化转换成执行计划**
> 一条sql会有多种方式查询，选择一种执行此计划成本最小的
> **调用存储引擎的API执行查询**
> 返回结果

参考资料 [一条SQL的执行过程](https://zhuanlan.zhihu.com/p/70295845)

*   MySQL 索引使用有哪些注意事项呢？

>可以从两个维度回答这个问题：索引哪些情况会失效，索引不适合哪些场景

> * 索引哪些情况会失效

> 查询条件包含 or，会导致索引失效。

> 隐式类型转换，会导致索引失效，例如 age 字段类型是 int，我们 where age = “1”，这样就会触发隐式类型转换。

> like 通配符会导致索引失效，注意:”ABC%” 不会失效，会走 range 索引，”% ABC” 索引会失效

> 联合索引，查询时的条件列不是联合索引中的第一个列，索引失效。

> 对索引字段进行函数运算。

> 对索引列运算（如，+、-、*、/），索引失效。

> 索引字段上使用（!= 或者 < >，not in）时，会导致索引失效。

> 索引字段上使用 is null， is not null，可能导致索引失效。

> 相 join 的两个表的字符编码不同，不能命中索引，会导致笛卡尔积的循环计算

> mysql 估计使用全表扫描要比使用索引快，则不使用索引。
> * 索引不适合哪些场景

> 数据量少的不适合加索引

> 更新比较频繁的也不适合加索引

> 离散性低的字段不适合加索引（如性别）

*   MySQL 锁的分类，作用，你在实际工作的中使用场景
>* 基本锁 - [ 共享锁（Shared Locks：S锁）和排它锁（Exclusive Locks：X锁）]
>* 意向锁 - [ intention lock,分为意向共享锁（IS锁）和意向排他锁（IX锁）]
>* 行锁 - [ record Locks、gap locks、next-key locks、Insert Intention Locks ]
>* 自增锁 - [ auto-inc locks ]

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#redis)Redis

**推荐阅读**

*   [[吃透了这些Redis知识点，阿里P8都问不倒你！（干货）](https://www.toutiao.com/i6681583952743891460/)]
*   [[Redis深入浅出——字符串和SDS](https://blog.csdn.net/qq193423571/article/details/81637075)]
*   [[Redis深入理解之简单动态字符串（SDS）](https://blog.csdn.net/TCJGGSDDU/article/details/81275462)]
*   [[Redis hash实现详解](https://www.jianshu.com/p/7f53f5e683cf)]
*   [[查漏补缺，Redis为什么会这么快，看完这七点你就知道了](https://www.cnblogs.com/kx33389/p/11298169.html)]
*   [[Redis设计与实现3 哈希对象（ ziplist /hashtable）](https://www.jianshu.com/p/2095df8ae4a8)]
*   [[什么是跳表？Redis为什么使用跳表来实现有序集合？](https://blog.csdn.net/ChaunceyChen/article/details/89370236)]
*   [[Redis 源码日志](https://app.yinxiang.com/shard/s3/nl/317377/a52d05dd-7bb7-4dad-af76-82e88d3eb7ca/)]

**名词解释**
`RDB：Redis DB hdfs: fsimage`
`AOF: AppendOnlyFile hdfs: edit logs`
`SDS: Simple dynamic string`
`LRU: Least recently used`

*   sds 的结构是什么？为什么要存长度？跟 c 里的字符串有什么区别？
> `Header[len,alloc,flags,free] + char buf[] + \0`
> len 已使用字节数,alloc 总字节数,flags header类型,free 未使用的字节数, char buf[] 保存字符串的数组
> 为什么要存长度:避免O(n)的复杂度获取字符串长度带来的额外开销，提升性能考虑
> **与c的字符串区别**
> sds 通过空间预分配策略和惰性空间释减少了内存操作次数
> sds 保存了字符串的长度，获取字符串长度的复杂度为O(1)
> sds 使用len来判断字符串是否结束，是二进制安全的，可保存文本或二进制数据。
> sds API安全，不会造成缓冲区溢出
> **扩容策略**
> **空间预分配**
> _`SDS_MAX_PREALLOC = 1mb（默认）`_
> 当扩容之后的长度小于`SDS_MAX_PREALLOC`时，那么会分配同样大小的未使用空间
> 当扩容之后的长度大于`SDS_MAX_PREALLOC`时，会分配`SDS_MAX_PREALLOC`的未使用空间
> **惰性空间释放策略**
> 当需要收缩时，不立即使用内存重分配来回收缩短后多出来的字节，而是使用表头的free成员将这些字节记录起来，并等待将来使用

[Redis深入理解之简单动态字符串](https://blog.csdn.net/TCJGGSDDU/article/details/81275462)

*  缓存如何保证一致性
>1. 想要提高应用的性能，可以引入「缓存」来解决

>2. 引入缓存后，需要考虑缓存和数据库一致性问题，可选的方案有：「更新数据库 + 更新缓存」、「更新数据库 + 删除缓存」
>3. 更新数据库 + 更新缓存方案，在「并发」场景下无法保证缓存和数据一致性，且存在「缓存资源浪费」和「机器性能浪费」的情况发生
>4. 在更新数据库 + 删除缓存的方案中，「先删除缓存，再更新数据库」在「并发」场景下依旧有数据不一致问题，解决方案是「延迟双删」，但这个延迟时间很难评估，所以推荐用「先更新数据库，再删除缓存」的方案
>5. 在「先更新数据库，再删除缓存」方案下，为了保证两步都成功执行，需配合「消息队列」或「订阅变更日志」的方案来做，本质是通过「重试」的方式保证数据一致性
>6. 在「先更新数据库，再删除缓存」方案下，「读写分离 + 主从库延迟」也会导致缓存和数据库不一致，缓解此问题的方案是「延迟双删」，凭借经验发送「延迟消息」到队列中，延迟删除缓存，同时也要控制主从库延迟，尽可能降低不一致发生的概率

*    用过 redis 哪些数据结构，使用场景是什么
>1. string 缓存，分布式锁，计数器，分布式系统唯一id
>2. hash 电商购物车，用户信息，商品信息等属性信息
>3. list 微博/微信信息流，消息队列，朋友圈的点赞列表、评论列表、排行榜
>4. set 抽奖，好友、关注、粉丝、点赞、感兴趣的人集合，在电商购物中，通过条件来筛选商品
>5. zset(有序集合) 热搜榜或者新闻排行榜，在直播系统中，实时排行信息包含直播间在线用户列表，各种礼物排行榜，弹幕消息（可以理解为按消息维度的消息排行榜）等信息
>6. bitmap 位图类型； 签到，登陆
>7. geo 地理位置类型；附近的人
>8. HyperLogLog 基数统计类型。统计uv

*   redis 的 connect 和 pconnect 的区别，pconnect 有什么问题
> connect：脚本结束之后连接就释放了。
>pconnect：脚本结束之后连接不释放，连接保持在php-fpm进程中。所以使用pconnect代替connect，可以减少频繁建立redis连接的消耗。
> 问题： 不能同时连接两个db？

*   redis 如何实现分布式锁，有什么问题
> 1. SET key value NX PX 6000 设置锁，value需要使用唯一id保证不被其他程序释放
> 2. 使用lua脚本获取当前锁value值是否是当前持有的，并释放锁
> 问题总结
>1. 死锁：设置过期时间
>2. 过期时间评估不好，锁提前过期：守护线程，自动续期
>3. 锁被别人释放：锁写入唯一标识，释放锁先检查标识，再释放

*   Redlock 具体如何使用
>1. 客户端先获取「当前时间戳T1」
>2. 客户端依次向这 5 个 Redis 实例发起加锁请求（用前面讲到的 SET 命令），且每个请求会设置超时时间（毫秒级，要远小于锁的有效时间），如果某一个实例加锁失败（包括网络超时、锁被其它人持有等各种异常情况），就立即向下一个 Redis 实例申请加锁
>3. 如果客户端从 >=3 个（大多数）以上 Redis 实例加锁成功，则再次获取「当前时间戳T2」，如果 T2 - T1 < 锁的过期时间，此时，认为客户端加锁成功，否则认为加锁失败
>4. 加锁成功，去操作共享资源（例如修改 MySQL 某一行，或发起一个 API 请求）
>5. 加锁失败，向「全部节点」发起释放锁请求（前面讲到的 Lua 脚本释放锁）

*   为什么Redis一定要用跳表来实现有序集合
>* 每两个结点提取一个结点到上一级(我们把原始链表的上一级叫做第一级索引)。我们把抽取出来的这一层叫做 索引或 索引层
>* 时间复杂度 O(logn) 空间复杂度 O(n)

> 插入、删除、查找以及迭代输出有序序列，红黑树也可以完成，时间复杂度和跳表一样。但是按照区间来查找数据这个操作，红黑树的效率没有跳表高。
> 对于按照区间查找数据这个操作，跳表可以做到O(logn)的时间复杂度定位区间的起点，然后在原始链表中顺序往后遍历就可以了。
> 当然，redis之所以用跳表来实现有序集合还有其它的原因。比如，相比于红黑树，跳表的代码看起来更易于理解、可读性更好也不容易出错。而且跳表也更加的灵活，他可以通过改变索引构建策略，有效平衡执行效率和内存消耗。
> 不过，跳表也不能完全代替红黑树。红黑树比跳表出现的更早一些，很多编程语言中的Map类型都是基于红黑树实现的，当我们做业务开发的时候直接拿来用就好了，但是对于跳表我们就需要手动实现了。

*  redis主从同步的原理
>1. 建立连接阶段（即准备阶段）
>2. 数据同步阶段(全量同步->部分同步->命令传播)
>* 步骤1：请求同步数骤
>* 步骤2：创建RDB同步数据
>* 步骤3：恢复RDB同步数据
>* 步骤4：请问部分同步数据
>* 步骤5：恢复部分同步数据
>* 步骤6：数据同步工作完成
>3. 命令传播阶段

*   Redis哨兵、复制、集群的设计原理与区别
>主从复制是为了数据备份，哨兵是为了高可用，Redis主服务器挂了哨兵可以切换，集群则是因为单实例能力有限，搞多个分散压力，简短总结如下：

>1. 主从模式：读写分离，备份，一个Master可以有多个Slaves。
>2. 哨兵sentinel：监控，自动转移，哨兵发现主服务器挂了后，就会从slave中重新选举一个主服务器,着眼于高可用。
>3. 集群：为了解决单机Redis容量有限的问题，将数据按一定的规则分配到多台机器，内存/QPS不受限于单机，可受益于分布式集群高扩展性,着眼于提高并发量。

*   redis cluster 用的什么协议同步数据
>Gossip 协议又称 epidemic 协议（epidemic protocol），是基于流行病传播方式的节点或者进程之间信息交换的协议，在P2P网络和分布式系统中应用广泛，它的方法论也特别简单：   
    ```
    在一个处于有界网络的集群里，如果每个节点都随机与其他节点交换特定信息，经过足够长的时间后，集群各个节点对该份信息的认知终将收敛到一致。
    ```

*   redis哨兵的选举用的什么协议
>哨兵的选举采用的是Raft算法，Raft是一个用户管理日志一致性的协议，它将分布式一致性问题分解为多个子问题：Leader选举、日志复制、安全性、日志压缩等。Raft将系统中的角色分为领导者（Leader）、跟从者（Follower）和候选者（Candidate）

>主节点选取
>* 选择健康状态从节点（排除主观下线、断线），排除5秒钟没有心跳的、排除主节点失联超过10*down-after-millisecends。
>* 选择最高优先级中复制偏移量最大的从机。
>* 如果还没有选出来，则按照ID排序，获取运行ID最小的从节点。

*   hash 怎么实现的？怎么解决 hash 冲突？除了 hashTable 还有别的吗？
> 数组+链表
> 链地址法
> 当键和值的字符串长度都小于64字节 且 键值对数量小于512个时使用
> `zipList` 否则使用`hashTable`

*   zset 怎么实现的？跳表是怎么插入的？为什么选择跳表不用其他平衡二叉树？除了跳表还有别的吗？
> HashTable+skiplist
> **插入步骤**
> 采用丢硬币的方式确定插入的level k, 然后再level1到levelk各个层按顺序插入元素
> **丢硬币确定K**
> 丢硬币实验，如遇到正面，继续丢，遇到反面，则停止
> c代码

```c
  int random_level()  
    {  
        K = 1;  
        while (random(0,1))  
            K++;  
        return K;  
    }   
```
> **为什么选择跳表而不用其他平衡二叉树**
> 范围查找更简单
> 插入和删除操作可能导致子树的调整，逻辑比较复杂。skiplist只需要修改相邻两个节点指针，简单快速
> skiplist比树占用更少的内存
> skiplist更容易实现
> ziplist、dict
> 推荐阅读

*   [[Redis 为什么用跳表而不用平衡树？](https://juejin.im/post/57fa935b0e3dd90057c50fbc)]

*   为什么 redis 用跳表？
> 因为redis中的zset数据结构需要支持按区间查找所有元素，在跳表中，只要定位到两个区间端点在最底层级的位置，然后按顺序遍历元素就可以了，非常高效。而红黑树只能定位到端点后，再从首位置开始每次都要查找后继节点，相对来说比较耗时。此外，跳表实现起来很容易且易读，红黑树实现起来相对困难

*   rehash 过程？会主动 rehash 吗？
> **过程**
> 创建ht[1]并分配至少2倍于ht[0] table的空间
> 将ht[0] table中的所有键值对迁移到ht[1] table
> 将ht[0]数据清空，并将ht[1]替换为新的ht[0]
> 会主动rehash，redis会在定时任务中主动rehash
> 为什么要rehash?
> 减少hash冲突，防止链表长度过长影响查找性能

*   用 redis 可以实现队列吗？有什么优点和缺点？
> 可以利用`list/zset`实现队列效果

*   用 redis 怎么实现一个延时队列？
> `zset`

*   rdb 和 aof 过程？rdb 为什么可以用创建子进程的方式进行？（这里考察一个 cow）这两种持久化方式会丢数据吗？
> rdb过程
> 在指定的时间间隔内将内存中的数据集快照写入磁盘
> aof过程
> 将每一个收到的写命令都通过write函数追加到日志文件中
>* appendfsync always：每次写入都刷盘，对性能影响最大，占用磁盘IO比较高，数据安全性最高
 >* appendfsync everysec：1秒刷一次盘，对性能影响相对较小，节点宕机时最多丢失1秒的数据
 >* appendfsync no：按照操作系统的机制刷盘，对性能影响最小，数据安全性低，节点宕机丢失数据取决于操作系统

> rdb的三种触发机制`save、bgsave、自动化`。子进程方式主要是利用了写时复制技术，子进程共享父进程的虚拟空间结构和物理空间，当有发生更改行为时，再为子进程相应的分配物理空间
> rdb所持久化的数据是fork发生时的数据，在这样的条件下进行持久化数据，如果因为某些情况宕机，则会丢失一段时间数据。aof everysec模式可能会丢失1秒数据
![image](https://s6.51cto.com/oss/202010/21/4104cfab694e9cee8a4a4f3ba4cbfbca.png)

*   数据过期和淘汰策略
>Redis的过期策略  
>Redis同时使用了惰性过期和定期过期两种过期策略。但是Redis定期删除是随机抽取机制，不可能扫描删除掉所有的过期Key。因此需要内存淘汰机制。
>* 定时过期：每个设置过期时间的key都需要创建一个定时器，到过期时间就会立即清除。该策略可以立即清除过期的数据，对内存很友好；但是会占用大量的CPU资源去处理过期的数据，从而影响缓存的响应时间和吞吐量。
>* 惰性过期：只有当访问一个key时，才会判断该key是否已过期，过期则清除。该策略可以最大化地节省CPU资源，却对内存非常不友好。极端情况可能出现大量的过期key没有再次被访问，从而不会被清除，占用大量内存。
>* 定期过期：每隔一定的时间，会扫描一定数量的数据库的expires字典中一定数量的key，并清除其中已过期的key。该策略是前两者的一个折中方案。通过调整定时扫描的时间间隔和每次扫描的限定耗时，可以在不同情况下使得CPU和内存资源达到最优的平衡效果。(expires字典会保存所有设置了过期时间的key的过期时间数据，其中key是指向键空间中的某个键的指针，value是该键的毫秒精度的UNIX时间戳表示的过期时间。键空间是指该Redis集群中保存的所有键。)

> Redis的内存淘汰策略
>* Redis的内存淘汰策略是指在Redis的用于缓存的内存不足时，怎么处理需要新写入且需要申请额外空间的数据。
>* no-eviction：当内存不足以容纳新写入数据时，新写入操作会报错。
>* allkeys-lru：当内存不足以容纳新写入数据时，在键空间中，移除最近最少使用的key。
>* allkeys-random：当内存不足以容纳新写入数据时，在键空间中，随机移除某个key。
>* volatile-lru：当内存不足以容纳新写入数据时，在设置了过期时间的键空间中，移除最近最少使用的key。
>* volatile-random：当内存不足以容纳新写入数据时，在设置了过期时间的键空间中，随机移除某个key。
>* volatile-ttl：当内存不足以容纳新写入数据时，在设置了过期时间的键空间中，有更早过期时间的key优先移除。

> 过期键删除策略和内存淘汰机制之间的关系：

>* 过期健删除策略强调的是对过期健的操作，如果有健过期了，而内存还足够，不会使用内存淘汰机制，这时也会使用过期健删除策略删除过期健。
>* 内存淘汰机制强调的是对内存的操作，如果内存不够了，即使有的健没有过期，也要删除一部分，同时也针对没有设置过期时间的健。

*   Redis集群方案应该怎么做？都有哪些方案？
>1. twemproxy
    大概概念是，它类似于一个代理方式，使用方法和普通redis无任何区别，设置好它下属的多个redis实例后，使用时在本需要连接redis的地方改为连接twemproxy，它会以一个代理的身份接收请求并使用一致性hash算法，将请求转接到具体redis，将结果再返回twemproxy。使用方式简便(相对redis只需修改连接端口)，对旧项目扩展的首选。 问题：twemproxy自身单端口实例的压力，使用一致性hash后，对redis节点数量改变时候的计算值的改变，数据无法自动移动到新的节点。

>2. codis
    目前用的最多的集群方案，基本和twemproxy一致的效果，但它支持在 节点数量改变情况下，旧节点数据可恢复到新hash节点。

>3. redis cluster3.0
    自带的集群，特点在于他的分布式算法不是一致性hash，而是hash槽的概念，以及自身支持节点设置从节点。具体看官方文档介绍。

>4. 在业务代码层实现
    起几个毫无关联的redis实例，在代码层，对key 进行hash计算，然后去对应的redis实例操作数据。 这种方式对hash层代码要求比较高，考虑部分包括，节点失效后的替代算法方案，数据震荡后的自动脚本恢复，实例的监控，等等。

*   Redis集群方案什么情况下会导致整个集群不可用？
>有A，B，C三个节点的集群,在没有复制模型的情况下,如果节点B失败了，那么整个集群就会以为缺少5501-11000这个范围的槽而不可用。

*   说说Redis哈希槽的概念？
>   Redis集群没有使用一致性hash,而是引入了哈希槽的概念，Redis集群有16384个哈希槽，每个key通过CRC16校验后对16384取模来决定放置哪个槽，集群的每个节点负责一部分hash槽。

*   Redis集群的主从复制模型是怎样的？
>   为了使在部分节点失败或者大部分节点无法通信的情况下集群仍然可用，所以集群使用了主从复制模型,每个节点都会有N-1个复制品.

*   Redis集群会有写操作丢失吗？为什么？
>   Redis并不能保证数据的强一致性，这意味这在实际中集群在特定的条件下可能会丢失写操作。

*   怎么理解Redis事务？
>事务是一个单独的隔离操作：事务中的所有命令都会序列化、按顺序地执行。事务在执行的过程中，不会被其他客户端发送来的命令请求所打断。事务是一个原子操作：事务中的命令要么全部被执行，要么全部都不执行。

*   Redis事务相关的命令有哪几个？
>   MULTI、EXEC、DISCARD、WATCH

*   Redis持久化数据和缓存怎么做扩容？
>*  如果Redis被当做缓存使用，使用一致性哈希实现动态扩容缩容。
>*  如果Redis被当做一个持久化存储使用，必须使用固定的keys-to-nodes映射关系，节点的数量一旦确定不能变化。否则的话(即Redis节点需要动态变化的情况），必须使用可以在运行时进行数据再平衡的一套系统，而当前只有Redis集群可以做到这样。

*   缓存雪崩 击穿 穿透
> 缓存穿透

> 缓存穿透是指，缓存和数据库都没有的数据，被大量请求，比如订单号不可能为-1，但是用户请求了大量订单号为-1的数据，由于数据不存在，缓存就也不会存在该数据，所有的请求都会直接穿透到数据库。    
  
>注意：穿透的意思是，都没有，直接一路打到数据库。

>解决方案 
>* 接口增加业务层级的Filter，进行合法校验，这可以有效拦截大部分不合法的请求。
>* 作为第一点的补充，最常见的是使用布隆过滤器，针对一个或者多个维度，把可能存在的数据值hash到bitmap中，bitmap证明该数据不存在则该数据一定不存在，但是bitmap证明该数据存在也只能是可能存在，因为不同的数值hash到的bit位很有可能是一样的，hash冲突会导致误判，多个hash方法也只能是降低冲突的概率，无法做到避免。
>* 另外一个常见的方法，则是针对数据库与缓存都没有的数据，对空的结果进行缓存，但是过期时间设置得较短，一般五分钟内。而这种数据，如果数据库有写入，或者更新，必须同时刷新缓存，否则会导致不一致的问题存在。

> 缓存击穿

> 缓存击穿是指数据库原本有得数据，但是缓存中没有，一般是缓存突然失效了，这时候如果有大量用户请求该数据，缓存没有则会去数据库请求，会引发数据库压力增大，可能会瞬间打垮。

>解决方案 
>* 如果是热点数据，那么可以考虑设置永远不过期。
>* 如果数据一定会过期，那么就需要在数据为空的时候，设置一个互斥的锁，只让一个请求通过，只有一个请求去数据库拉取数据，取完数据，不管如何都需要释放锁，异常的时候也需要释放锁，要不其他线程会一直拿不到锁。

>缓存雪崩

>缓存雪崩是指缓存中有大量的数据，在同一个时间点，或者较短的时间段内，全部过期了，这个时候请求过来，缓存没有数据，都会请求数据库，则数据库的压力就会突增，扛不住就会宕机。

>解决方案 
>* 如果是热点数据，那么可以考虑设置永远不过期。
>* 缓存的过期时间除非比较严格，要不考虑设置一个波动随机值，比如理论十分钟，那这类key的缓存时间都加上一个1~3分钟，过期时间在7~13分钟内波动，有效防止都在同一个时间点上大量过期。
>* 方法1避免了有效过期的情况，但是要是所有的热点数据在一台redis服务器上，也是极其危险的，如果网络有问题，或者redis服务器挂了，那么所有的热点数据也会雪崩（查询不到），因此将热点数据打散分不到不同的机房中，也可以有效减少这种情况。
>* 也可以考虑双缓存的方式，数据库数据同步到缓存A和B，A设置过期时间，B不设置过期时间，如果A为空的时候去读B，同时异步去更新缓存，但是更新的时候需要同时更新两个缓存。

*   redis 为什么快？（主要考察一个 IO 多路复用和单线程不加锁）
> redis都是对内存操作，速度极快(QPS 10w+)
> 单线程避免了多线程的同步和加锁、上下文切换消耗的cpu时间
> 单线程天然支持原子操作，代码写起来更简单
> IO多路复用技术，利用Linux的epoll函数，一个线程可以管理多个socket连接

*   一致性哈希是什么？节点较少时数据分布不均匀怎么办？
> 一致性哈希是指能够在Hash输出空间发生变化时，引起最小的变动。
> 节点较少时可采用虚拟节点来解决不均匀的问题

*   简单说下几种 key 的淘汰策略，redis 里的 lru 算法，什么时候会触发？实现细节是什么？怎么保证淘汰合理的 key？
> **几种策略**
> noeviction(默认策略)：对于写请求不再提供服务，直接返回错误（DEL请求和部分特殊请求除外）
> allkeys-lru：从所有key中使用LRU算法进行淘汰
> volatile-lru：从设置了过期时间的key中使用LRU算法进行淘汰
> allkeys-random：从所有key中随机淘汰数据
> volatile-random：从设置了过期时间的key中随机淘汰
> volatile-ttl：在设置了过期时间的key中，根据key的过期时间进行淘汰，越早过期的越优先被淘汰
> LRU: 是一种缓存置换算法。即在缓存有限的情况下，如果有新的数据需要加载进缓存，则需要将最不可能被继续访问的缓存剔除掉
> **何时触发**
> 当redis已使用的内存超过配置maxmemory的值时触发
> **实现细节**
> edis会基于server.maxmemory_samples配置选取固定数目的key，然后比较它们的lru访问时间，然后淘汰最近最久没有访问的key，maxmemory_samples的值越大，Redis的近似LRU算法就越接近于严格LRU算法，但是相应消耗也变高，对性能有一定影响，样本值默认为5。
> **推荐阅读**
> 
> *   [[LRU原理和Redis实现——一个今日头条的面试题](https://zhuanlan.zhihu.com/p/34133067)]

*   lua 脚本的作用是什么？
> 原子性操作
> 减少网络开销
> 可移植
> 代码复用
> **推荐阅读**
> 
> *   [[如何优雅地在Redis中使用Lua](https://cloud.tencent.com/developer/article/1420672)]

*   缓存击穿 / 穿透 / 雪崩的处理策略
> **缓存击穿**
> 指热点key的失效，导致大量请求穿破缓存，打到数据库，造成数据压力过大
> 处理策略
> 设置热点数据永不过期
> **缓存穿透**
> 指频繁请求一个数据库不存在key，造成大量请求直接打倒数据库，导致数据库压力过大甚至崩溃
> 处理策略
> 缓存空值
> **缓存雪崩**
> 指在某一个时间，缓存集中失效
> 处理策略
> 缓存时间加入随机数

<p id="nginx">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#nginx)Nginx

*   LVS 和 Nginx 分别作用在 osi 哪一层？
*   负载均衡算法

<p id="es">
</p>

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#ES)ES搜索

*   深度分页会有什么问题
> ES 数据分布在每个shard中，每个shard分布一部分数据，它并不知道数据是如何排序的，因此只有通过协调节点进行以下操作query/fetch：
>（1）协调节点发送请求到shard分布的数据节点，请求数据
>（2）每个分片在本地执行查询，并使用本地的Term/Document Frequency信息进行打分，添加结果到大小为from + size的本地有序优先队列中。
>（3）每个分片返回个自己优先队列中所有文档的ID和排序值给协调节点，协调节点合并这些值到自己的优先队列中，产生一个全局排序后的列表（注：这里只先返回_id标识和排序值用于排序，不返回整个数据，避免网络开销）
>（4）协调节点根据排序后的列表获取相应的数据进行二次请求（注：这次是根据10个文档_id进行GET操作）
>（5）获得数据进行返回

> 解决
>1. Scroll
>* 使用scroll，每次只能获取一页的内容，然后会返回一个scrollid，根据scrollid可以不断地获取下一页的内容，所以scroll并不适用于有跳页的情景。但是在真正的使用场景中，第10000条数据已经是很后面的数据了，可以“折衷”一下，不提供跳转页面功能，只能下一页的翻页。
>2. search_after 
>* 上述的 scroll search 的方式，官方的建议并不是用于实时的请求，因为每一个 scroll_id 不仅会占用大量的资源（特别是排序的请求），而且是生成的历史快照，对于数据的变更不会反映到快照上。这种方式往往用于非实时处理大量数据的情况，比如要进行数据迁移或者索引变更之类的。那么在实时情况下如果处理深度分页的问题呢？es 给出了 search_after 的方式，这是在 >= 5.0 版本才提供的功能。
>* searchAfter的方式通过维护一个实时游标来避免scroll的缺点，它可以用于实时请求和高并发场景。
>* 它的缺点是不能够随机跳转分页，只能是一页一页的向后翻，并且需要至少指定一个唯一不重复字段来排序(注:每个文档具有一个唯一值的字段应该用作排序规范的仲裁器。否则，具有相同排序值的文档的排序顺序将是未定义的。建议的方法是使用字段_id，它肯定包含每个文档的一个唯一值)。
>* 此外还有一个与scorll的不同之处是searchAfter的读取数据的顺序会受索引的更新和删除影响而scroll不会，因为scroll读取的并不是不可变的快照，而是依赖于上一页最后一条数据，所以无法跳页请求，用于滚动请求，于scroll类似，不同之处在于它是无状态的。

*   倒排索引的原理
>1. 关键词->[文档id1，文档id2]格式存储，可以直接找到包含当前关键字的文档
>2. 增加了一层字典树 term index，不存储所有的单词，只存储单词前缀，通过字典书找到单词所在的块，也就是单词的大概位置，再在块里二分查找，找到对应的单词，再找到单词对应的文档列表
>3. 为了进一步节省内存，Lucene 还用了 FST（Finite State Transducers）对 Term Index 做进一步压缩，term index 在内存中是以FST的形式保存的。Term dictionary 在磁盘上是以分 block 的方式保存的，一个block 内部利用公共前缀压缩，比如都是 Ab 开头的单词就可以把 Ab 省去
>4. Posting List 增量压缩，分割成块，按需分配空间
>5. 快速求交并集(Bitmap存储)，对于 Integer使用 Skip List（跳表）做合并计算
> [Elasticsearch 倒排索引原理 |](https://xiaoming.net.cn/2020/11/25/Elasticsearch%20%E5%80%92%E6%8E%92%E7%B4%A2%E5%BC%95/)


*   lsm 树原理
>* LSM树（Log Structured Merge Tree，结构化合并树）的思想非常朴素，就是将对数据的修改增量保持在内存中，达到指定的大小限制后将这些修改操作批量写入磁盘（由此提升了写性能），是一种基于硬盘的数据结构，与B-tree相比，能显著地减少硬盘磁盘臂的开销。当然凡事有利有弊，LSM树和B+树相比，LSM树牺牲了部分读性能，用来大幅提高写性能。
>* 读取时需要合并磁盘中的历史数据和内存中最近的修改操作,读取时可能需要先看是否命中内存，否则需要访问较多的磁盘文件（存储在磁盘中的是许多小批量数据，由此降低了部分读性能。但是磁盘中会定期做merge操作，合并成一棵大树，以优化读性能）。LSM树的优势在于有效地规避了磁盘随机写入问题，但读取时可能需要访问较多的磁盘文件。
>* 代表数据库：nessDB、leveldb、hbase等
>* 核心思想的核心就是放弃部分读能力，换取写入的最大化能力，放弃磁盘读性能来换取写的顺序性。极端的说，基于LSM树实现的HBase的写性能比Mysql高了一个数量级，读性能低了一个数量级。


<p id="前端">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%89%8D%E7%AB%AF%E7%AF%87)前端篇

*   描述XSS注入原理，以及如何防止？
> XSS又叫CSS (Cross Site Script) ，跨站脚本攻击。它指的是恶意攻击者往Web页面里插入恶意html代码，当用户浏览该页之时，嵌入其中Web里面的html代码会被执行，从而达到恶意攻击用户的特殊目的。
> **防范**
> 不相信任任何用户的输入，对每个用户的输入都做严格检查，过滤，在输出的时候，对某些特殊字符进行转义，替换等

*   描述HTML 5中新增的 EventSource 的功能和应用场景？
*   ES 6中的`Promise`对象是做什么的？
> 解决回调地狱

*   解释ES 6中`async、await`的使用场景？
>    它们是基于promises的语法糖，使异步代码更易于编写和阅读。通过使用它们，异步代码看起来更像是老式同步代码
*   ES 6中 遍历器`Iterator`怎么写，其作用是什么？
```javascript
for (let item of obj) {
  console.log(item);
}
```

*   写出下面代码执行后输出的内容

```javascript
    var p1 = new Promise(resolve =>  {
        console.log(1);
        resolve(2);
    })
    let p2 = new Promise(resolve =>  {
        console.log(3);
        resolve(p1);
    });
    p1.then(re =>  {
        console.log(re);
    });
    p2.then(re =>  {
        console.log(re);
    });
```

#### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#vue)Vue

*   vue 和 angularJS 中检测脏数据的原理有什么区别？
>*  angular中的脏值检测是比对数据是否有变更，来决定是否更新视图，最简单的方式就是通过 setInterval() 定时轮询检测数据变动
>*  vue中的数据劫持主要通过 ES5 提供的Object.defineProperty方法来实现，监控对数据的操作，从而可以自动触发数据同步。并且，由于是在不同的数据上触发同步，可以精确的将变更发送给绑定的视图，而不是对所有的数据都执行一次检测。

> 区别：
>* angular脏值检测原理上支持低端IE（记得最早的NG支持IE8），理论上兼容性更好；而vue数据劫持需要支持ES5的浏览器。
>* angular脏值检测适合大数据量的更新；而vue数据劫持适合小数据量（细颗粒度）的更新。

*   vue中，vuex的主要作用是什么？
>* 解决了组件之间统一状态的共享问题，实现组件之间的数据持久化。在项目中可以用vuex存放数据，不用每次都要请求后端服务器，这就在保证了数据新鲜度的同时提高了使用性能。

*   vue中 data 和computed 有什么区别？
> data 和 computed 最核心的区别在于 data 中的属性并不会随赋值变量的改动而改动，而computed 会

```javascript
    {
        computed: {
            now() {
                return new Date();
            }
        }
    }
```

*   上面的now变量，是否能够在每次调用时得到当前时间？
> 会

<p id="kafka">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E9%80%9A%E8%AE%AF%E5%8D%8F%E8%AE%AE%E7%AF%87)kafka
*   kafka 的架构，大致储存结构
>  一个kafka体系架构包括若干Producer、Broker、Consumer和一个zookeeper集群。
>* Producer 消息生产者，将消息push到Kafka集群中的Broker。
>* Consumer 消息消费者，从Kafka集群中pull消息，消费消息。
>* Consumer Group 消费者组，由一到多个Consumer组成，即每条消息只能被Consumer Group中的一个Consumer消费；但是可以被多个Consumer Group组消费。这样就实现了单播和多播。
>* Broker 一台Kafka服务器就是一个Broker,一个集群由多个Broker组成，每个Broker可以容纳多个Topic。
>* Topic 消息的类别或者主题，逻辑上可以理解为队列。Producer只关注push消息到哪个Topic,Consumer只关注订阅了哪个Topic。
>* Partition 负载均衡与扩展性考虑，一个Topic可以分为多个Partition,物理存储在Kafka集群中的多个Broker上。可靠性上考虑，每个Partition都会有备份Replica。
>* Replica Partition的副本，为了保证集群中的某个节点发生故障时，该节点上的Partition数据不会丢失，且Kafka仍能继续工作，所以Kafka提供了副本机制，一个Topic的每个Partition都有若干个副本，一个Leader和若干个Follower。
>* Leader Replica的主角色，Producer与Consumer只跟Leader交互。
>* Follower Replica的从角色，实时从Leader中同步数据，保持和Leader数据的同步。Leader发生故障时，某个Follower会变成新的Leader。
>* Controller Kafka集群中的其中一台服务器，用来进行Leader election以及各种Failover（故障转移）。
>* ZooKeeper Kafka通过Zookeeper存储集群的meta等信息。

>Kafka 最终的存储实现方案， 即基于顺序追加写日志 + 稀疏哈希索引。
>* 稀疏哈希索引: 把消息的 Offset 设计成一个有序的字段，这样消息在日志文件中也就有序存放了，也不需要额外引入哈希表结构， 可以直接将消息划分成若干个块，对于每个块，我们只需要索引当前块的第一条消息的 Offset ，先根据 Offset 大小找到对应的块， 然后再从块中顺序查找

![image](https://image.z.itpub.net/zitpub.net/JPG/2021-11-01/D499A4B194425DF178D3A375AFA152DF.jpg)
> Kafka 消息写入到磁盘的日志目录布局: Log 对应了一个命名为<topic>-<partition>的文件夹。举个例子，假设现在有一个名为“topic-order”的 Topic，该 Topic 中有4个 Partition，那么在实际物理存储上表现为“topic-order-0”、“topic-order-1”、“topic-order-2”、“topic-order-3” 这4个文件夹。
> 向 Log 中写入消息是顺序写入的。但是只有最后一个 LogSegement 才能执行写入操作，之前的所有 LogSegement 都不能执行写入操作。为了更好理解这个概念，我们将最后一个 LogSegement 称为"activeSegement"，即表示当前活跃的日志分段。随着消息的不断写入，当 activeSegement 满足一定的条件时，就需要创建新的 activeSegement，之后再追加的消息会写入新的 activeSegement。

>为了更高效的进行消息检索，每个 LogSegment 中的日志文件（以“.log”为文件后缀）都有对应的几个索引文件：偏移量索引文件（以“.index”为文件后缀）、时间戳索引文件（以“.timeindex”为文件后缀）、快照索引文件 （以“.snapshot”为文件后缀）。其中每个 LogSegment 都有一个 Offset 来作为基准偏移量（baseOffset），用来表示当前 LogSegment 中第一条消息的 Offset。偏移量是一个64位的 Long 长整型数，日志文件和这几个索引文件都是根据基准偏移量（baseOffset）命名的，名称固定为20位数字，没有达到的位数前面用0填充。比如第一个 LogSegment 的基准偏移量为0，对应的日志文件为00000000000000000000.log。

>[18张图带你搞透Kafka的存储架构
](https://z.itpub.net/article/detail/23669855206850749BA50599BE2D1F00)

*   如果消费者数超过分区数会怎么样?
>   多余消费者，一直接不到消息而处于空闲状态。

>假设多个消费者负责同一个分区，那么会有什么问题呢？
>   造成消息处理的重复，且不能保证消息的顺序

*   怎么保证数据的可靠投递？
>1. 副本同步机制： kafka的partition为主从结构，在一个partition里，存在leader和follower，当数据发送给leader后，需要确保follower和leader数据同步后才发送给producer一个ack
>2. ack应答机制：
>* 0：producer不等待broker的ack，broker已接受到还没写入磁盘就返回成功，可能会造成数据丢失
>* 1：producer等待broker的ack，partition的leader落盘成功返回ack，follower同步成功之前leader故障，数据可能会丢失
>* -1（all）：leader和follower全部落盘成功才返回ack，当follow同步完成之后，leader挂了，producer又发了一次写入broker请求，结果在另外一个leader又写了一次，造成数据重复
>3. 故障时副本一致性问题解决
    > LEO（log-end-offset）：每个副本的最后一个offset
    > HW（high watemark）：所有副本中最小的LEO

*   消费者的 offset 存在哪里？
>*  消费者如果是根据javaapi来消费，也就是【kafka.javaapi.consumer.ConsumerConnector】，我们会配置参数【zookeeper.connect】来消费。这种情况下，消费者的offset会更新到zookeeper的【consumers/{group}/offsets/{topic}/{partition}】目录下
>*  如果是根据kafka默认的api来消费，即【org.apache.kafka.clients.consumer.KafkaConsumer】，我们会配置参数【bootstrap.servers】来消费。而其消费者的offset会更新到一个kafka自带的topic【__consumer_offsets】下面，查看当前group的消费进度，则要依靠kafka自带的工具【kafka-consumer-offset-checker】

*   如何通过 offset 定位消息？

    index文件的序号就是message在日志文件中的相对偏移量
    OffsetIndex是稀疏索引，也就是说不会存储所有的消息的相对offset和position
    消息检索的过程，以这个partition目录下面，00000000001560140916为例：
    定位offset为1560140921的message

    ①定位到具体的segment日志文件，采用二分法先定位到index索引文件
    由于log日志文件的文件名是这个文件中第一条消息的offset-1。
    因此可以根据offset定位到这个消息所在日志文件：00000000001560140916.log

    这个过程是利用二分法进行查找的。
    ②计算查找的offset在日志文件的相对偏移量
    segment文件中第一条消息的offset = 1560140917
    计算message相对偏移量：
    需要定位的offset - segment文件中第一条消息的offset + 1 = 1560140921 - 1560140917 + 1 = 5
    查找index索引文件， 可以定位到该消息在日志文件中的偏移字节为456。
    综上，直接读取文件夹00000000001560140916.log中偏移456字节的数据即可。
    1560140922 -1560140917 +1 = 6 如果查找的offset在日志文件的相对偏移量在index索引文件不存在，可根据其在index索引文件最接近的上限偏移量，往下顺序查找。

<p id="通信协议">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E9%80%9A%E8%AE%AF%E5%8D%8F%E8%AE%AE%E7%AF%87)通讯协议篇

*   详细描述 HTTPS（SSL）工作原理？
> 首先使用非对称加密方式完成握手，生成并传输后续加密内容用的密钥，握手成功后使用密钥采用对称加密方式进行数据传输

*   从输入 URL 到页面展示到底发生了什么？
>1. 输入地址
>2. 浏览器查找域名的 IP 地址
>3. 浏览器向 web 服务器发送一个 HTTP 请求
>4. 服务器的永久重定向响应
>5. 服务器处理请求
>6. 服务器返回一个 HTTP 响应
>7. 浏览器显示 HTML
>8. 浏览器发送请求获取嵌入在 HTML 中的资源（如图片、音频、视频、CSS、JS等等）

*   TCP三次握手
> 为了防止已失效的连接请求报文段突然又传送到了服务端，因而产生资源浪费
>*  第一次握手：**客户端A将标志位SYN置为1,随机产生一个值为seq=J（J的取值范围为=1234567）的数据包到服务器，客户端A进入SYN_SENT状态，等待服务端B确认；
>*  第二次握手：**服务端B收到数据包后由标志位SYN=1知道客户端A请求建立连接，服务端B将标志位SYN和ACK都置为1，ack=J+1，随机产生一个值seq=K，并将该数据包发送给客户端A以确认连接请求，服务端B进入SYN_RCVD状态。
>*  第三次握手：**客户端A收到确认后，检查ack是否为J+1，ACK是否为1，如果正确则将标志位ACK置为1，ack=K+1，并将该数据包发送给服务端B，服务端B检查ack是否为K+1，ACK是否为1，如果正确则连接建立成功，客户端A和服务端B进入ESTABLISHED状态，完成三次握手，随后客户端A与服务端B之间可以开始传输数据了。
![](https://pic3.zhimg.com/80/v2-1644292933f3925f447272de1cca6752_720w.jpg)

*   TCP 四次挥手
>* 第一次挥手： Client发送一个FIN，用来关闭Client到Server的数据传送，Client进入FIN_WAIT_1状态。
>* 第二次挥手： Server收到FIN后，发送一个ACK给Client，确认序号为收到序号+1（与- SYN相同，一个FIN占用一个序号），Server进入CLOSE_WAIT状态。
>* 第三次挥手： Server发送一个FIN，用来关闭Server到Client的数据传送，Server进入LAST_ACK状态。
>* 第四次挥手： Client收到FIN后，Client进入TIME_WAIT状态，接着发送一个ACK给Server，确认序号为收到序号+1，Server进入CLOSED状态，完成四次挥手。
![](https://pic4.zhimg.com/80/v2-a1956234c6575bad2c2ea5297a6fe38f_720w.jpg)

*   服务器使用PHP时，客户端的IP能伪造吗？如果能，列出伪造方法；如果不能，说明原因？
> PHP获取客户单ip主要通过
> `HTTP_CLIENT_IP` 存在于http请求的header
> `HTTP_X_FORWARDED_FOR` 请求转发路径，客户端IP，代理1IP，代理2IP
> `HTTP_X_REAL_IP` 这个用得比较少
> 以上三个都是从http头获取的，并不可靠，可伪造
> 可通过curl方式设置http头
> `REMOTE_ADDR` 是直接从TCP中获取的IP，基本不会被伪造
> 参考资料 [PHP正确获取客户端的IP](https://www.cnblogs.com/lushaoyan/p/11088213.html)

*   描述域名劫持的各种方法，为什么HTTPS不能被劫持？
> 劫持方法
> 假扮域名注册人和域名注册商通信
> 是伪造域名注册人在注册商处的账户信息
> 是伪造域名注册人的域名转移请求
> 是直接进行一次域名转移请求
> 是修改域名的DNS记录
> 严格来说HTTPS也可能会被劫持，证书文件被篡改或信任了不安全的证书，同样会被中间人黑客冒用身份，进行劫持

*   描述HTTP协议是什么，以及`HTTP 2` 和 `HTTP 1.1` 有什么区别？
> HTTP协议是工作于应用层基于TCP/IP通信协议的超文本传输协议。
> 区别
> 1、多路复用，一个连接并发处理多个请求
> 2、数据压缩，使用HPACK算法对header的数据进行压缩
> 3、服务器推送，可通过开启nginx/apache相关配置支持

*   详细描述IP协议、TCP协议，以及UDP协议与它们的区别。
> 区别
> IP工作于网络互联层，TCP、UDP工作于传输层
> TCP（`Transmission Control Protocol`）面向连接、传输可靠（保证数据正确性）、有序（保证数据顺序）、传输大量数据（流模式）、速度慢、对系统资源的要求多，程序结构较复杂，每一条TCP连接只能是点到点的，TCP首部开销20字节。
> UDP（`User Data Protocol`）面向非连接 、传输不可靠（可能丢包）、无序、传输少量数据（数据报模式）、速度快，对系统资源的要求少，程序结构较简单 ，UDP支持一对一，一对多，多对一和多对多的交互通信，UDP的首部开销小，只有8个字节。

*   TCP协议中，最大传输单元MTU一般最大是多少，在TCP协议中，如果一个数据被分割成多个包，这些包结构中什么字段会被标记相同。
> 1500
> ID

*   UDP分包和TCP分包会有哪些区别？
> 

*   HTTP协议中 `Transfer-Encoding: Chunked` 适用于哪些应用场景，这个与使用 `Content-Length: xxx` 在收到的报文包上有哪些区别？
> `Transfer-Encoding: Chunked` 允许HTTP由网页服务器发送给客户端应用（ 通常是网页浏览器）的数据可以分成多个部分
> **`Transfer-Encoding: Chunked` 适用场景**
> 数据由后台动态计算，无法计算准确获取需要传输的数据长度时。具体场景如：
> **报文包的区别**
> `Transfer-Encoding: Chunked`， 将数据进行分块编码传输，每个分块包含十六进制的长度值和数据，长度值独占一行，长度不包括它结尾的 `CRLF（\r\n）`，也不包括分块数据结尾的 `CRLF`。最后一个分块长度值必须为 0，对应的分块数据没有内容，表示实体结束。
> 常常会在头部增加一个类似`Trailder:xxx`，用以指定末尾还会传递一个 xxx 的拖挂首部，比如数据的md5值。
> 如
```
b\r\n
01234567890\r\n

0\r\n
\r\n


xxx:sejfdijfejsljfeoij24jsjdfesljf
\r\n
```
> `Content-Length: xxx`, 数据包为完整的数据，长度和设置的值完全一致

<p id="分布式">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%88%86%E5%B8%83%E5%BC%8F%E7%AF%87)分布式篇

*   描述`epoll`和`poll`、`select`的区别，为什么`epoll`会具备性能优势？
> **区别**
> 支持一个进程所能打开的最大连接数（epoll、epoll有数量限制，select无）
> FD剧增后带来的IO效率问题(poll,select对连接进行线性遍历，epoll根据每个fd上的callback函数实现，只有活跃的socket才会主动调用callback)
> 消息传递方式（poll、select核需要将消息传递到用户空间，都需要内核拷贝动作，epoll通过内核和用户空间共享一块内存来实现）
> **epoll性能优势**
> 减少了用户态和内核态之间的文件句柄拷贝；
> 减少了对可读可写文件句柄的遍历。

*   描述下惊群的原因？有什么有效的方法可以避免惊群？
> 高并发环境下，多个线程/进程同时在等待一个事件结束或完成，当这个事件完成时，多个进程/线程被唤醒，但只有一个进程或者线程进行相应处理，其他进程或线程响应失败。
> 加锁

*   什么是Hash一致性，这个方法主要运用在什么场景？
> 一致性Hash算法（`Consistent Hashing`）是一种hash算法，它能够在Hash输出空间发生变化时，引起最小的变动。
> 首先将目标值（如服务器计算机名称或者ip，在一定的空间内是唯一的）hash后与`2^32`取模获得一个整数值（一定小于2^32），这个整数值必分布在由0~2^32内的整数构成的hash环上，由此确定节点位置。
> 接下来，使用相同的方法计算出将要存储的数据key，并确定该数据再环上的位置，从该位置顺时针寻找最近的节点位置，将数据存放在该节点。
> 删除某载体节点时（载体发生故障），被删的载体节点上的数据将同样按照顺时针顺序寻找下一个载体节点，将数据迁移至该节点。
> 新增载体节点时（负载新增载体），那么在新增载体节点和逆时针方向最近的载体节点位置之间的数据将迁移到这台新增的载体节点。
> 可以看出，新增和删除节点对于整个数据环境影响很小，只会影响附近的数据的迁移
> 在节点很少的情况下，容易因为节点分布不均匀，导致某一节点负载增高，有崩溃的风险，从而引发雪崩效应，所有载体节点崩溃。
> 此时，我们可以将节点虚拟成多个节点，针对一个真实节点计算出多个虚拟的节点位置，尽可能将节点均匀分布在hash环上。
> 缓存集群场景

*   设计一个多重缓存的拓扑结构
> 

*   IO 多路复用是什么？有哪些 api？
> IO多路复用是同时监视多个文件描述符（FD）的读写就绪状况，这样多个文件描述符的I/O操作都能在一个线程内并发交替地顺序完成
> api : seelct、poll、epoll

*   select 和 epoll 的区别？水平触发和边缘触发的区别是啥？使用的时候需要注意什么？
> 区别
> select 无差别轮询所有FD，找出发生io读写的FD进行操作。 时间复杂度为O（n）。有最大fd限制为32/1024,64/2048。同时维护一个用来存放大量fd的数据结构，需要比较大的内存来支持用户态和内核态之间的拷贝动作。
> poll 无差别轮询。采用链表存储FD。
> epoll 非轮询，只有活跃的fd才会调用callback函数去操作。时间复杂度为O（1）。有最大FD上限为最大可以打开文件的数目（1GB内存的机器上大约是10万左右）。通过mmap让内核态和用户态空间共享一块内存来实现传递，减少不必要的拷贝动作。采用红黑树和链表存储FD。
> 水平触发（`LT`）是指FD在报告后，没有被处理，下次poll时会再次报告该fd
> 边缘触发（`ET`）是每个FD只报告一次，直到该FD出现第二次可读写事件之前都不会再报告，无论FD中是否还有数据可读写。
> 需要注意的是在ET模式下，read一个FD的时候一定要把它的buffer读光，也就是说一直读到read返回值小于请求值，或者遇到`EAGAIN`错误。
> 可通过`cat /proc/sys/fs/file-max`查看最大可打开文件的数目

*   epoll 储存描述符的数据结构是什么？
> 红黑树和链表

*   select 有描述符限制吗？是多少？
> 有，默认1024，可通过修改头文件重新编译内核来修改这个限制

_**关于IO多路复用，参考资料**_
[[彻底搞懂epoll高效运行的原理](https://app.yinxiang.com/shard/s3/nl/317377/65b1482f-1df8-4674-8b7b-896c25fe8f4c/)]
[[I/O多路复用技术(multiplexing)是什么？](https://www.zhihu.com/question/28594409)]
[[漫谈五种IO模型（主讲IO多路复用）](https://www.jianshu.com/p/6a6845464770)]
[[select、poll、epoll之间的区别](https://www.cnblogs.com/aspirant/p/9166944.html)]

*   进程 / 线程 / 协程区别？go 和 swoole 的协程实现有啥区别？
> **进程**
> 应用程序启动的实例
> **线程**
> 属于进程，是程序的执行者
> 一个进程至少包含一个主线程，也可以由更多的子线程
> 线程有两种调度策略，分时调度和抢占式调度
> **协程**
> 轻量级线程，创建、切换、挂起、销毁均为内存操作
> 协程是属于线程，协程是在线程里执行的
> 用户手动切换，所以又叫用户空间线程
> 协作式调度
> **swoole 协程**
> 协程客户端必须在协程的上下文环境中使用
> 协程调度器是单线程的，无法利用多核CPU，同一时间只有一个在调度
> 不允许多个协程同时读取同一个socket资源
> **go 协程**
> 协程调度器是多线程的，可利用多核CPU，同一时间可能会有多个协程在执行
> 允许多个协程同时读取同一个socket资源
> 原生支持协程，不需要声明协程环境
> 通过GPM调度模型实现

*   swoole 协程的原理？
> swoole为用户的每个请求创建一个协程，当在执行某个协程代码的过程中发现这行代码遇到了`Co::sleep()`或者产生了IO操作，swoole将会把这个相关的Fd放到EventLoop中，然后让出这个协程的CPU给其他协程使用，即挂起(`yield`)，在该Fd相关操作有结果了就继续执行这个协程，即恢复(`resume`)，所有操作完成后，调用end方法返回结果，并销毁此协程
> 协程适合IO密集型应用，因为协程在IO阻塞时会自动调度，减少IO阻塞导致的时间损失，提高了效率
> [Swoole 实现协程基本概念和底层原理](https://zhuanlan.zhihu.com/p/96471009)

<p id="其他">
</p>

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E7%BB%BC%E5%90%88%E7%AF%87)综合篇

*   描述OAuth2的工作原理？
> 

*   列出几个中文分词工具？
> Jieba, SnowNLP, PkuSeg, THULAC, HanLP

*   git 放弃未提交的文件有哪些方法？
> git clean -fdx
> git reset
> git checkout .

*   git删除远程分支、Tag有什么方法？
> git push origin --delete [branch_name]

*   git覆盖远程仓库有什么办法？
> git push -u origin [branch_name]

*   CentOS 下安装php扩展有哪些方法？
> yum
> pecl
> 源码编译

*   布隆过滤器，什么时候用？优点是什么？
> 在海量数据中寻找某个别数据时可采用

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E7%AE%97%E6%B3%95)算法

*   leetcode easy 级别的题目
*   排序
*   聚类
*   列表搜索
*   图表搜索

**推荐阅读**
[[必学十大经典排序算法，看这篇就够了(附完整代码/动图/优质文章)(修订版)](https://mp.weixin.qq.com/s/IAZnN00i65Ad3BicZy5kzQ)][php版本](https://codeantenna.com/a/LAyfFxnSUh)
![](https://mmbiz.qpic.cn/mmbiz_png/gsQM61GSzIMLb3kBhQibib6HpVZIdyA3icibVsahXIq2TkjOBESPLYKgRydvROy5PyPTOVXiaJHuqI0OasGEiaGbsfXQ/640?wx_fmt=png&tp=webp&wxfrom=5&wx_lazy=1&wx_co=1)
### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E6%95%B0%E6%8D%AE%E7%BB%93%E6%9E%84)数据结构

*   什么是HashMap？
> [[用漫画告诉你什么是HashMap](https://zhuanlan.zhihu.com/p/78079598)]

*   树
*   栈
*   堆
*   数组
*   列表
*   队列

### 
[<svg class="octicon octicon-link" viewbox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>](#%E5%85%B6%E4%BB%96)其他

*   硬盘如何储存数据的？
> [视频：你的硬盘是如何存储数据的？](https://sspai.com/post/55277)

*   设计秒杀系统，需要支持 100W 以上 QPS
> 假设一个请求rt（response time）为100ms ，1s可以处理十个，那么并发就是10万
> URL动态加密,防止恶意访问
> 页面放到cdn
> 前端客户端限制按钮 比如2s只能发一次请求，无货置灰
> nginx 限流，限ip，服务降级熔断
> 数据预热，库存提前放redis
> lua 脚本查询库存并减库存
> 消息队列处理支付等业务
> 秒杀数据库，服务器与其他业务分离
> 分布式锁 redis set nx px lua脚本释放锁
> 提高门槛，比如先报名才能抢

*   设计微博首页，需要拉取所有关注用户的最近 20 条微博
> 基本概念
>* 推模式（也叫写扩散）：和名字一样，就是一种推的方式，发送者发送了一个消息后，立即将这个消息推送给接收者，但是接收者此时不一定在线，那么就需要有一个地方存储这个数据，这个存储的地方我们称为：同步库。推模式也叫写扩散的原因是，一个消息需要发送个多个粉丝，那么这条消息就会复制多份，写放大，所以也叫写扩散。这种模式下，对同步库的要求就是写入能力极强和稳定。读取的时候因为消息已经发到接收者的收件箱了，只需要读一次自己的收件箱即可，读请求的量极小，所以对读的QPS需求不大。归纳下，推模式中对同步库的要求只有一个：写入能力强。
>* 拉模式（也叫读扩散）：这种是一种拉的方式，发送者发送了一条消息后，这条消息不会立即推送给粉丝，而是写入自己的发件箱，当粉丝上线后再去自己关注者的发件箱里面去读取，一条消息的写入只有一次，但是读取最多会和粉丝数一样，读会放大，所以也叫读扩散。拉模式的读写比例刚好和写扩散相反，那么对系统的要求是：读取能力强。另外这里还有一个误区，很多人在最开始设计feed流系统时，首先想到的是拉模式，因为这种和用户的使用体感是一样的，但是在系统设计上这种方式有不少痛点，最大的是每个粉丝需要记录自己上次读到了关注者的哪条消息，如果有1000个关注者，那么这个人需要记录1000个位置信息，这个量和关注量成正比的，远比用户数要大的多，这里要特别注意，虽然在产品前期数据量少的时候这种方式可以应付，但是量大了后就会事倍功半，得不偿失，切记切记。
>* 推拉结合模式：推模式在单向关系中，因为存在大V，那么一条消息可能会扩散几百万次，但是这些用户中可能有一半多是僵尸，永远不会上线，那么就存在资源浪费。而拉模式下，在系统架构上会很复杂，同时需要记录的位置信息是天量，不好解决，尤其是用户量多了后会成为第一个故障点。基于此，所以有了推拉结合模式，大部分用户的消息都是写扩散，只有大V是读扩散，这样既控制了资源浪费，又减少了系统设计复杂度。但是整体设计复杂度还是要比推模式复杂。关注微信订阅号码匠笔记，回复架构获取一些列的架构知识。

>* 如果产品中是双向关系，那么就采用推模式。
>* 如果产品中是单向关系，且用户数少于1000万，那么也采用推模式，足够了。
>* 如果产品是单向关系，单用户数大于1000万，那么采用推拉结合模式，这时候可以从推模式演进过来，不需要额外重新推翻重做。
>* 永远不要只用拉模式。
>* 如果是一个初创企业，先用推模式，快速把系统设计出来，然后让产品去验证、迭代，等客户数大幅上涨到1000万后，再考虑升级为推拉集合模式。

> 微博应该采用推拉结合，大v使用拉模式，其他用户使用推模式
[别瞎搞了！微博、知乎就是这么设计Feed流系统的~](https://blog.csdn.net/weixin_38405253/article/details/108891774)

*   抢红包算法设计
> 随机0.01～（总金额/总数）*2之间的数

*   设计一个短链系统
> 一、短链生成的几种方法

>1. 哈希算法 (MurmurHash算法 对于规律性较强的 key，MurmurHash 的随机分布特征表现更良好)
>2. 统一发号器
    >* Redis 自增：Redis 性能好，单机就能支撑 10W+请求，如果作为发号器，需要考虑 Redis 持久化和灾备。
    >* MySQL 自增主键：这种方案和 Redis 的方案类似，是利用数据库自增主键的提醒实现，保证 ID 不重复且连续自动创建。
    >* Snowflake：这是一种目前应用比较广的 ID 序列生成算法，美团的 Leaf 是对这种算法的封装升级服务。但是这个算法依赖于服务器时钟，如果有时钟回拨，可能会有 ID 冲突。（有人会较真毫秒中的序列值是这个算法的瓶颈，话说回来了，这个算法只是提供了一种思路，如果觉得序列长度不够，自己加就好，但是每秒百万级的服务真的又这么多吗？）

> 二、存储
> 三、查询（布隆过滤器->缓存->db->302）302临时跳转，301永久跳转 

*   分布式 id 的几种实现和优缺点
>1. UUID (Universally Unique Identifier) 的标准型式包含 32 个 16 进制数字，以连字号分为五段，形式为 8-4-4-4-12 的 36 个字符，示例：550e8400-e29b-41d4-a716-446655440000，到目前为止业界一共有 5 种方式生成 UUID。
>       *  UUID 的优点：性能非常高：本地生成，没有网络消耗。
>       * UUID 的缺点：
>           1. 不易于存储：UUID 太长，16 字节 128 位，通常以 36 长度的字符串表示，很多场景不适用。
>           2. 信息不安全：基于 MAC 地址生成 UUID 的算法可能会造成 MAC 地址泄露，这个漏洞曾被用于寻找梅丽莎病毒的制作者位置。
>           3. ID 作为主键时在特定的环境会存在一些问题，比如做 DB 主键的场景下，UUID 就非常不适用。MySQL 官方有明确的建议主键要尽量越短越好，36 个字符长度的 UUID 不符合要求；UUID 还对 MySQL 索引不利，如果作为数据库主键，在 InnoDB 引擎下，UUID 的无序性可能会引起数据位置频繁变动，严重影响性能。

>2. snowflake是Twitter开源的分布式ID生成算法，结果是一个long型的ID。其核心思想是：使用41bit作为毫秒数，10bit作为机器的ID（5个bit是数据中心，5个bit的机器ID），12bit作为毫秒内的流水号（意味着每个节点在每毫秒可以产生 4096 个 ID），最后还有一个符号位，永远是0。
>       *  snowflake的优点：
>           * 毫秒数在高位，自增序列在低位，整个ID都是趋势递增的。
>           * 不依赖数据库等第三方系统，以服务的方式部署，稳定性更高，生成ID的性能也是非常高的。
>           * 可以根据自身业务特性分配bit位，非常灵活。
>       *  snowflake的缺点：
>           * 强依赖机器时钟，如果机器上时钟回拨，会导致发号重复或者服务会处于不可用状态。
>           * MongDB 的 ObjectID 可以算作是和snowflake类似方法，通过“时间+机器码+pid+inc”共12个字节，通过4+3+2+3的方式最终标识成一个24长度的十六进制字符。

>3. 美团开源的Leaf
>       *  号段模式：该模式需要建 DB 表, 需要有专门的服务来提供获取 id 的接口, 存在网络延迟
>       *  Snowflake 模式：为了追求更高的性能，需要通过 RPC Server 来部署 Leaf 服务，那仅需要引入 leaf-core 的包，把生成 ID 的 API 封装到指定的 RPC 框架中即可
>*    缺点，可能就是相对来说比较复杂。

>4. sharding-jdbc 是一个开源的主键生成组件。它的特点是简单易用，可以指定 workerId 或者不指定, 直接通过 jar 的方式引入即可。看它的名字就知道，它需要 DB 支持。

>5. uid-generator 是百度开源的一个分布式 ID 生成器。需要建 DB 表, 需要有专门的服务来提供获取 id 的接口, 存在网络延迟。

*   降级 限流 熔断实现原理
>1. 熔断：服务熔断，一旦触发「异常统计条件」，则，直接熔断服务，在「调用方」直接返回，不再 rpc 调用远端服务；
>1. 降级：降级是配合「熔断」的，熔断后，不再调用远端服务器的 rpc 接口，而采用本地的 fallback 机制，返回一个「备用方案」/「默认取值」；
>1. 限流：限制「速率」，或从业务层限制「总数」，被限流的请求，直接进入「降级」fallback 流程；
>1. 异步 RPC：通过异步访问，提升系统访问性能；

*   布隆过滤器的实现原理和使用场景
>   布隆过滤器是由一个很长的二进制向量 (位向量) 和一系列随机均匀分布的散列 (哈希) 函数组成。用多个散列函数，将每个数据映射到位数组中，这样可以高效地插入元素或者判断某个元素可能存在与一定不存在，而且可以减少内存空间开销。

>  使用场景
>*  去重：比如爬给定网址的时候对已经爬取过的 URL 去重。
>*  判断给定数据是否存在、 防止缓存穿透、邮箱的垃圾邮件过滤、黑名单功能等等。


*   进程间通信有哪几种方式
>1. 管道/匿名管道(pipe)
>2. 有名管道(FIFO)
>3. 信号(Signal)
>4. 消息(Message)队列
>5. 共享内存(share memory)
>6. 信号量(semaphore)
>7. 套接字(socket)

*   进程线程协程区别
>1. 协程既不是进程也不是线程，协程仅是一个特殊的函数。协程、进程和线程不是一个维度的。
>2. 一个进程可以包含多个线程，一个线程可以包含多个协程。虽然一个线程内的多个协程可以切换但是这多个协程是串行执行的，某个时刻只能有一个线程在运行，没法利用CPU的多核能力。
>3. 协程与进程一样，也存在上下文切换问题。
>4. 进程的切换者是操作系统，切换时机是根据操作系统自己的切换策略来决定的，用户是无感的。进程的切换内容包括页全局目录、内核栈和硬件上下文，切换内容被保存在内存中。 进程切换过程采用的是“从用户态到内核态再到用户态”的方式，切换效率低。
>5. 线程的切换者是操作系统，切换时机是根据操作系统自己的切换策略来决定的，用户是无感的。线程的切换内容包括内核栈和硬件上下文。线程切换内容被保存在内核栈中。线程切换过程采用的是“从用户态到内核态再到用户态”的方式，切换效率中等。
>6. 协程的切换者是用户(编程者或应用程序),切换时机是用户自己的程序来决定的。协程的切换内容是硬件上下文，切换内存被保存在用自己的变量(用户栈或堆)中。协程的切换过程只有用户态(即没有陷入内核态),因此切换效率高。

>* 进程间的信息难以共享数据，父子进程并未共享内存，需要通过进程间通信（IPC），在进程间进行信息交换，性能开销较大。创建进程（一般是调用 fork 方法）的性能开销较大。所以有了线程

>* 线程的优势：
>   * 线程之间能够非常方便、快速地共享数据。
>       * 只需将数据复制到进程中的共享区域就可以了，但需要注意避免多个线程修改同一份内存。
>   * 创建线程比创建进程要快 10 倍甚至更多。
>       * 线程都是同一个进程下自家的孩子，像是内存页、页表等就不需要了。
>* 协程的优势：
>   1. 节省 CPU：避免系统内核级的线程频繁切换，造成的 CPU 资源浪费。好钢用在刀刃上。而协程是用户态的线程，用户可以自行控制协程的创建于销毁，极大程度避免了系统级线程上下文切换造成的资源浪费。
>   2. 节约内存：在 64 位的Linux中，一个线程需要分配 8MB 栈内存和 64MB 堆内存，系统内存的制约导致我们无法开启更多线程实现高并发。而在协程编程模式下，可以轻松有十几万协程，这是线程无法比拟的。
>   3. 稳定性：前面提到线程之间通过内存来共享数据，这也导致了一个问题，任何一个线程出错时，进程中的所有线程都会跟着一起崩溃。
>   4. 开发效率：使用协程在开发程序之中，可以很方便的将一些耗时的IO操作异步化，例如写文件、耗时 IO 请求等。

*   502 504 什么原因，如何处理
>*  502 Bad Gateway：作为网关或者代理工作的服务器尝试执行请求时，从上游服务器接收到无效的响应。
>      1. max_children
>      2. request_terminate_timeout、max_execution_time
>      3. 数据库
>      4. 网关服务是否启动如php-fpm
>*  504 Gateway Time-out：作为网关或者代理工作的服务器尝试执行请求时，未能及时从上游服务器（URI标识出的服务器，例如HTTP、FTP、LDAP）或者辅助服务器（例如DNS）收到响应。
>       * 504错误一般是与nginx.conf配置有关了。
>       * 主要与以下几个参数有关：fastcgi_connect_timeout、fastcgi_send_timeout、fastcgi_read_timeout、fastcgi_buffer_size、fastcgi_buffers、fastcgi_busy_buffers_size、fastcgi_temp_file_write_size、fastcgi_intercept_errors。特别是前三个超时时间。如果fastcgi缓冲区太小会导致fastcgi进程被挂起从而演变为504错误

*   TCP 粘包如何解决
>*  造成TCP粘包的原因
    1）发送方原因

    TCP默认使用Nagle算法（主要作用：减少网络中报文段的数量），而Nagle算法主要做两件事：

    只有上一个分组得到确认，才会发送下一个分组
    收集多个小分组，在一个确认到来时一起发送
    Nagle算法造成了发送方可能会出现粘包问题

    （2）接收方原因

    TCP接收到数据包时，并不会马上交到应用层进行处理，或者说应用层并不会立即处理。实际上，TCP将接收到的数据包保存在接收缓存里，然后应用程序主动从缓存读取收到的分组。这样一来，如果TCP接收数据包到缓存的速度大于应用程序从缓存中读取数据包的速度，多个包就会被缓存，应用程序就有可能读取到多个首尾相接粘到一起的包。

    如何处理粘包现象？
    （1）发送方

    对于发送方造成的粘包问题，可以通过关闭Nagle算法来解决，使用TCP_NODELAY选项来关闭算法。

    （2）接收方

    接收方没有办法来处理粘包现象，只能将问题交给应用层来处理。

    （2）应用层

    应用层的解决办法简单可行，不仅能解决接收方的粘包问题，还可以解决发送方的粘包问题。

    解决办法：循环处理，应用程序从接收缓存中读取分组时，读完一条数据，就应该循环读取下一条数据，直到所有数据都被处理完成