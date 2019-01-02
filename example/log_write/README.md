**演示高并下写Log**
请准备2个php版本,同样代码下,进行测试:
如[php7.3](http://php.net/downloads.php#v7.3.0)
如[php7.1.25](http://php.net/downloads.php#v7.1.25)


```
  源码,请查看 log_write.php

 测试时：
 请用浏览器打开多个窗口,
 在不同的窗口中,传不同的参数,并行访问,注意观察最后的返回结果:
 如:
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs1
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs2
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs3
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs4
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs5
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs6
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs7
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs8
  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs9
  
★运行结果如下: 如php7.3
//起初内存消耗
Array
(
    [start_time] => 1546417444.2006
    [start_usage] => 468992
    [over_time] => 1546417444.2076
    [over_usage] => 670488
    [offset_time] => 0.007s
    [offset_usage] => 196.77kb
)
//期末内存消耗
Array
(
    [start_time] => 1546417444.2006
    [start_usage] => 468992
    [over_time] => 1546417447.2938
    [over_usage] => 1217096
    [offset_time] => 3.0932s
    [offset_usage] => 730.57kb
    [last_offset_time] => 3.0862s
    [last_offset_usage] => 533.76kb
)
//高并发下,若写失败,则会有详细循环哪个$i数据失败
Array
(
)
//仅是一个标识,程序结束了。
over
  

★运行异常结果如下: 如php7.1.25
Array
(
    [start_time] => 1546418317.7116
    [start_usage] => 446008
    [over_time] => 1546418317.7196
    [over_usage] => 675016
    [offset_time] => 0.008s
    [offset_usage] => 223.64kb
)
Array
(
    [start_time] => 1546418317.7116
    [start_usage] => 446008
    [over_time] => 1546418327.6291
    [over_usage] => 1227016
    [offset_time] => 9.9176s
    [offset_usage] => 762.7kb
    [last_offset_time] => 9.9096s
    [last_offset_usage] => 539.02kb
)
Array
(
    [0] => 204874
    [1] => 204875
    [2] => 204876
)
over
Array
(
    [type] => 2
    [message] => file_put_contents(/home/www/example/log_write/Application/Runtime/Logs/20190102_log): failed to open stream: Resource temporarily unavailable
    [file] => E:\www\myphp\MyPHP\Library\Log\write.class.php
    [line] => 99
)
```