<?php
/**
 * 分页类的封装
 * @author zhoushuqiang
 * @version 1.0
 */

namespace MyPHP;


class Pagination{

    //sql查询总条数
    public $Count=0;

    //分页显示条数
    public $PageSize = 20;

    //当前分页
    public $Page = 1;

    //显示可点击page按钮数目，默认为十个
    public $ShowPage = 10;

    //显示page按钮数目偏移量
    public $ShowPageOffset = 5;

    //需要搜索的Url
    public $URL;

    //搜索参数部分
    public $Search = array();

    /*
     * 初始化分页,由于此框架处于仿TP，所以就用TP一些内在函数写
     * @author zhoushuqiang
     * @version 1.0
     * @param mixed array $PageConf     分页初始化配置
     * @param mixed array $Search       分页搜索部分字符串
     * @example $PageConf
     * $PageConf = array(
     *      'Count'=>500,
     *      'PageSize'=>20,
     *      'Page'=>I('Page'),
     *      'ShowPage'=>10,
     *      'URL'=>'/admin/Home/Login/Login.html',
     *  );
     *
     * @remark 基于bootstrap 3.3.7 制作，需要引入bootstrap3.3.7,否则无法查看效果
     *
     * 自建Css样式实例
     *   .pagination .jump , .ClassCheckbox , .go , .TotalPage {
     *   display: block;
     *   width: 32px;
     *   height: 32px;
     *   line-height: 32px;
     *   float: left;
     *   margin: 0px 5px 0px 5px;
     *   padding: 0px;
     *   }
     *   .pagination .go{
     *       height: 18px;
     *       line-height: 18px;
     *       margin-top: 8px;
     *   }
     *   .pagination .TotalPage{
     *       width: 120px;
     *   }
     *   .pagination .ClassCheckbox input{
     *       text-align: center;
     *       outline: none;
     *       border: 1px solid #2F2F2F;
     *       padding: 0px;
     *       height: 32px;
     *       width: 32px;
     *       margin: auto;
     *   }
     *
     * @js部分现在还未完成
     * @example
     *
     * $(".go").click(function(){
     *     var TotalPage = parseInt($(this).attr('value'));
     *     var Page = parseInt($(".Page input").val());
     *     var url = $(this).attr('url');
     *     Page = Page>TotalPage || Page==0?1:Page;
     *     window.location.href = url+'&Page='+Page;
     * });
     * */
    public function __construct($PageConf = array(),$Search=array()){
        foreach($PageConf as $key=>$value){
            $this->$key = $value;
        }
        $this->URL =$this->URL?$this->URL:'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $this->Search =  $Search ? $Search :array();
    }

    /*
     * 回调分页的HTML
     * @author zhoushuqiang
     * @version 1.0
     * @return PageHtml
     * */
    public  function ShowHtml(){

        //获取总共分页量
        $TotalPage = ceil(intval($this->Count)/intval($this->PageSize));

        //计算当前分页
        $page = intval($this->Page) && $this->Page<=$TotalPage && $this->Page?intval($this->Page):1;

        //Previous page
        $Previous = $page-1>0?$page-1:1;

        //next page
        $next = $page+1<=$TotalPage?$page+1:1;

        //计算应该正确的显示ShowPage条数
//        $ShowPageStart =$this->ShowPage < $TotalPage && $page -$this->ShowPageOffset>0 ? ($page > $this->ShowPage? $page-$this->ShowPageOffset : $page-$this->ShowPageOffset):(1);

//        $ShowPageEnd = $this->ShowPage < $TotalPage  && $page +$this->ShowPageOffset<$TotalPage ? ($page > $this->ShowPage? $page+$this->ShowPageOffset : $page+$this->ShowPageOffset) : ($page+$this->ShowPage > $TotalPage?$this->ShowPage:$TotalPage);

        $ShowPageStart = 1;
        //总页数大于 需要展示的分页数 并且 当前分页 减去 偏移量大于零
        if($TotalPage > $this->ShowPage && ($page - $this->ShowPageOffset >0)){
            $ShowPageStart = $page -  $this->ShowPageOffset;
        }

        $ShowPageEnd = $TotalPage;
        if($TotalPage>$this->ShowPage && $page + $this->ShowPageOffset <= $TotalPage){
            $ShowPageEnd = $page + $this->ShowPageOffset;
        }


        $search = $this->URL.'?';
        if($this->Search){
             if(is_array($this->Search)){
                 foreach($this->Search as $key=>$value){
                     $search .= $key.'='.$value.'&';
                 }
             }else{
                 $search .=  $this->Search;
             }
        }
//        $search = trim($search,'&');
//        if($TotalPage <= 1){
//            return '';
//        }
        $PageHtml = '';
        if($TotalPage<=3){
            $PageHtml .= '<nav style="text-align: center;-height: 36px;">';
            $PageHtml .= '<ul class="pagination" search="'.$search.'">';
            $PageHtml .= '<li><p class="TotalPage"><small>第</small>&nbsp;<strong style="color: red;">'.$page.'页</strong>&nbsp;/&nbsp;<small>共计</small>&nbsp;<strong style="color: blue;">'.$TotalPage.'页</strong>&nbsp;&nbsp;<span>共计：&nbsp;'.$this->Count.'&nbsp;条</span></p></li>';
            for($ShowPageStart;$ShowPageStart<=$ShowPageEnd;$ShowPageStart++){
                $PageHtml .= '<li><a href="'.$search.'Page='.$ShowPageStart.'">'.$ShowPageStart.'</a></li>';
            }
            $PageHtml .= '</ul>';
            $PageHtml .= '</nav>';
        }
        if($TotalPage>3 && $TotalPage<intval($this->ShowPage)){
            $PageHtml .= '<nav style="text-align: center;-height: 36px;">';
            $PageHtml .= '<ul class="pagination" search="'.$search.'">';
            $PageHtml .= '<li><p class="TotalPage"><small>第</small>&nbsp;<strong style="color: red;">'.$page.'页</strong>&nbsp;/&nbsp;<small>共计</small>&nbsp;<strong style="color: blue;">'.$TotalPage.'页</strong>&nbsp;&nbsp;<span>共计：&nbsp;'.$this->Count.'&nbsp;条</span></p></li>';
            $PageHtml .= '<li><a href="'.$search.'Page='.$Previous.'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
            for($ShowPageStart;$ShowPageStart<=$ShowPageEnd;$ShowPageStart++){
                $PageHtml .= '<li><a href="'.$search.'Page='.$ShowPageStart.'">'.$ShowPageStart.'</a></li>';
            }
            $PageHtml .= '<li><a href="'.$search.'Page='.$next.'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
            $PageHtml .= '</ul>';
            $PageHtml .= '</nav>';
        }
        if($TotalPage > intval($this->ShowPage)){
            $PageHtml .= '<nav style="text-align: center;-height: 36px;">';
            $PageHtml .= '<ul class="pagination" search="'.$search.'">';
            $PageHtml .= '<li><p class="TotalPage"><small>第</small>&nbsp;<strong style="color: red;">'.$page.'页</strong>&nbsp;/&nbsp;<small>共计</small>&nbsp;<strong style="color: blue;">'.$TotalPage.'页</strong>&nbsp;&nbsp;<span>共计：&nbsp;'.$this->Count.'&nbsp;条</span></p></li>';
            $PageHtml .= '<li><a href="'.$search.'Page='.$Previous.'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
            for($ShowPageStart;$ShowPageStart<=$ShowPageEnd;$ShowPageStart++){
                $PageHtml .= '<li><a href="'.$search.'Page='.$ShowPageStart.'">'.$ShowPageStart.'</a></li>';
            }
            $PageHtml .= '<li><a href="'.$search.'Page='.$next.'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
            $PageHtml .= '<li><p class="jump">跳转</p> <p class="ClassCheckbox Page"><input type="text" value="'.$page.'"></p><p class="go btn btn-xs btn-primary" value="'.$TotalPage.'"url='.$search.'>确认</p></li>';
            $PageHtml .= '</ul>';
            $PageHtml .= '</nav>';
        }

        return $PageHtml;
    }

    public function LimitStart(){
        return  $this->Page <= ceil($this->Count/$this->PageSize)?($this->Page-1)*$this->PageSize:0;
    }
}