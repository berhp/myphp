<?php
/**
 * Page分页类
 */
namespace MyPHP;
class Page{
    /**
     * 分页功能(wait)
     * @param int $total 总共有多少条数据
     * @param int $p_show 显示多少个,默认5(即最多有5个) 1 2 3 4 5
     * @param int $page 当前第几页，默认1
     * @param int $pageSize 每页多少条,默认20
     * @return array
     * @tutorial
     *  1.格式:    50 条记录 10/17 页 上一页 下一页 第一页 上5页   6   7   8   9  10 下5页 最后一页
     *  2.支持自定义事件
     *Sample:
        $count  = 'sql语句' ---- 总记录数
        $page 	= new \MyPHP\Page($count,6);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%'); //自定义参数
        $show	= $page->show();
        $data	= $db->where(array('cid'=>$cid))->limit("$page->firstRow,$page->listRows")->order('id asc')->select(); //数据
     */
    public $rollPage = 5;    // 分页栏每页显示的页数
    public $listRows = 20;  // 默认列表每页显示行数
    public $firstRow;      // 起始行数
    protected $totalPages;// 总页数
    protected $totalRows; // 总行数
    protected $nowPage;// 当前页
    protected $coolPages;// 分页的栏的总页数
    protected $config  = array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','search'=>'','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');// 分页显示定制


    public function __construct($totalRows,$listRows='') {
        $this->totalRows = $totalRows;
        if(!empty($listRows)) {
            $this->listRows = intval($listRows);
        }


        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty( $_REQUEST['p'] ) ? intval( $_REQUEST['p'] ) : 1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);  //起始行数
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    public function show(){
        if( $this->totalRows == 0 )  return '';
        //获取当前url完整地址
        $pageURL = 'http';
        //p($_SERVER);die;
        //if ($_SERVER["HTTPS"] == "on")  $pageURL .= "s";
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80"){
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }else{
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        $pageURL = str_replace(".html","",$pageURL);
        $pageURL_num = stripos($pageURL,"/p/");


//        $a = U('',array('p'=>25));
//        p($a);
        //die;

        $upRow = $this->nowPage - 1;  //上翻页
        $downRow = $this->nowPage + 1;//下翻页
        if ( $upRow > 0 ){
            $url = U('',array('p'=>$upRow));
            $upPage="<a href='{$url}'>".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $url = U('',array('p'=>$downRow));
            $downPage="<a href='$url'>".$this->config['next']."</a>";
        }else{
            $downPage="";
        }
//p($downPage);die;

        $nowCoolPage = ceil($this->nowPage/$this->rollPage);
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $url = U('',array('p'=>$preRow));
            $url2 = U('',array('p'=>1));
            $prePage = "<a href='{$url}'>上".$this->rollPage."页</a>";
            $theFirst = "<a href='{$url2}' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $url = U('',array('p'=>$nextRow));
            $url2 = U('',array('p'=>$theEndRow));
            $nextPage = "<a href='{$url}' >下".$this->rollPage."页</a>";
            $theEnd = "<a href='{$url2}' >".$this->config['last']."</a>";
        }


        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $url = U('',array('p'=>$page));
                    $linkPage .= "&nbsp;<a id='big' href='{$url}'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr  =  str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }




}