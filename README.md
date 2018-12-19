## 安装MyPHP

**MyPHP主要目录结构**

      myphp
           ├── _tokenTemp                          //Api单点登录的token默认存放目录(程序生成),非单点登录访问api不会生成文件
           │
           ├── Common                              //公共配置
           │       └── Common                          //自定义适用于整个架构的公共函数,包含于(api 前端 后端..)
           │              └── functions.php                  
           │       └── Config                          //自定义其他配置文件(适用于整个架构),会加载此目录下的所有.php的文件
           │              └── config.php
		   │
           ├── example                             //一些演示文件,查看后请删除此目录
		   │          
           ├── ext                                 //预留引用第三方的扩展源,如 PHPExcel, phpqrcode
           │
           ├── h5                                  //前端h5源文件夹
           │
           ├── MyPHP                               //MyPHP框架的源码目录
           │       └── Common                          //MyPHP框架低层的 函数目录 
           │       └── Config                          //MyPHP框架低层的 配置目录
           │       └── Fonts                           //MyPHP框架低层的 字体目录, (生成图片验证码等需要)
           │       └── Library                         //MyPHP框架低层的 各种类文件
           │              └── DB                           //MyPHP框架低层的 DB类目录
           │              └── Log                          //MyPHP框架低层的 Log类目录
           │              └── MyPHP                        //MyPHP框架低层的 MyPHP类目录
           │              └── init.class.php               //实现引入文件类,含路由等的实现类
           │       └── Template                        //MyPHP框架低层的 模板目录
           │       └── MyPHP.php                       //初始引入文件
           │
           ├── Public                              //资源存放目录
           │
           ├── shellbat                            //所有php计划任务存放目录
           │
           └── Uploads                             //上传资源存放目录
           │
           ├──admin.php                            //后台管理网站入口文件
           │
           ├──api.php                              //Api接口入口文件
           │
           ├──favicon.ico                          //请替换为你网站的小图标（浏览器会使用它,存放至网站根目录)
           │
           └──index.php                            //前台门户网站入口文件


**PHP版本需求：5.3+以上 **

**添加以下依赖： **
```
<pre>
 zip,gd,mysqli
</pre>
```

**请根据实际项目添加以下依赖扩展： **
```
<pre>
  redis,Memcache
</pre>
```

**Window中使用步骤如下：**
1. 搭建好你本地的PHP相关环境
2. 下载[myphp](https://github.com/berhp/myphp/archive/master.zip)
3. 将下载后的myphp-master.zip文件,直接解压到你项目根目录下,然后运行,如:
```
   http://localhost/yourPath/index.php   自动生成,目录"Application"(可在index.php中配置),运行成功会显示: "欢迎使用myphp:1.0.0"
   
   http://localhost/yourPath/api.php     自动生成,目录"Api"(可在api.php中配置),运行成功会显示: "{"Welcome":"api demo","Version":"1.0.0","url":"http:\/\/localhost\/yourPath\/api.php\/Home\/v1\/Home\/Index\/index"}"
   
   http://localhost/yourPath/admin.php   自动生成,目录"System"(可在admin.php中配置),运行成功会显示: "欢迎使用myphp:1.0.0"
```
   
**Linux中使用步骤如下：**
1. 搭建好你本地的PHP相关环境,注意zip,unzip功能要安装
2. 将myphp.zip下载到你的www目录
如:
```
mkdir -p /home/www
cd /home/www
wget https://github.com/berhp/myphp/archive/master.zip
unzip master.zip
```

3. 对你的www目录,授权对应的apache或nginx的用户组,如:
```
/**
 * 说明: chown -R daemon:daemon /home/www/ 标识授权daemon组的daemon用户 对/home/www有操作权限
 * 终端中输入以下命令:
 */
mkdir -p /home/www
chown -R daemon:daemon /home/www/
```

4.在浏览器中运行,如
```
   http://xx.xx.xx/yourPath/index.php   自动生成,目录"Application"(可在index.php中配置),运行成功会显示: "欢迎使用myphp:1.0.0"
   
   http://xx.xx.xx/yourPath/api.php     自动生成,目录"Api"(可在api.php中配置),运行成功会显示: "{"Welcome":"api demo","Version":"1.0.0","url":"http:\/\/localhost\/yourPath\/api.php\/Home\/v1\/Home\/Index\/index"}"
   
   http://xx.xx.xx/yourPath/admin.php   自动生成,目录"System"(可在admin.php中配置),运行成功会显示: "欢迎使用myphp:1.0.0"
```


## 详细使用文档

参考[MyPHP官方文档](http://doc.berhp.cn/myphp)

