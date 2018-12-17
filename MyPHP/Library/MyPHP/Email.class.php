<?php 
/**
 * 邮件类（发送邮件）SMTP
 * @author berhp
 * @tutorial 
 * 	1. 根据第三方类，PHPMailer.class.php  + SMTP.class.php 二次开发适用于我们框架中
 *  2. 需要在第三方邮件服务商中开启 POP3/SMTP服务,如QQ邮箱，163邮箱等
 *  3. 若是使用QQ邮箱，需要在php.ini中开启php_openssl.dll扩展功能
 * @tutorial 
$config=array(
	    'EMAIL_NICKNAME' 		=> 'helian', //自定义发件人昵称
		'EMAIL_FROM_NAME' 		=> '2881589006@qq.com',   // *发件人邮箱
		'EMAIL_SMTP' 			=> 'smtp.qq.com',   	  // *smtp
		'EMAIL_USERNAME' 		=> '2881589006@qq.com',   // *账号
		'EMAIL_PASSWORD' 		=> 'ackzrrldvpurddfa',   // *密码  注意: 163和QQ邮箱是授权码；不是登录的密码
		'EMAIL_SMTP_SECURE' 	=> 'ssl',   			  // 链接方式 如果使用QQ邮箱；需要把此项改为  ssl
		'EMAIL_PORT' 			=> '465', 	  			  // *端口 如果使用QQ邮箱；需要把此项改为  465
		'EMAIL_CHARSET' 		=> "UTF-8", 			  // *邮件字符集
		'EMAIL_ENCODING' 		=> "base64", 			  // *邮件编码方式
);
 * @example
	//$email = new \MyPHP\Email($config); //框架外需传基础配置
	$email = new \MyPHP\Email();
	$email->address = '343217456@qq.com';
	$email->cc = '2881589009@qq.com';
	$email->bcc = 'bingeman@126.com';
	$email->subject = '邮件标题';
	$email->body = '你好<br>邮件内容:测试验证邮箱<a href="https://www.baidu.com/">点击验证</a>';
	$email->attachment = '1.png';
	$r = $email->sendmail();
	if($r===false){
		echo '发送失败:'.$email->_msg;
	}else{
		echo '发送成功';
	}
 */
namespace MyPHP;
use MyPHP\Email\PHPMailer;
use MyPHP\Email\SMTP;
class Email{
    public $subject=''; //邮件标题
    public $address=''; //收信人地址，多个用数组
    public $cc=''; //抄送，多个用数组
    public $bcc=''; //秘密抄送，多个用数组
    public $body=''; //邮件正文，支持html标签
    public $attachment=''; //附件(的路径)，多个用数组

	/**
	 * @param unknown $config //基本配置，框架外使用需传参
	 */
	public function __construct($config=array()){
		$config = $config ? $config : C('email');
		foreach ($config as $k=>$v){
			$this->$k=$v;
		}
	}
	
	/**
	 * 发送邮件 (ok)
	 * @return array=arrray(
         'code' => true,  //true-正确  false-错误
         'msg' => '',    //错误信息
       )
	 */
  public function sendmail(){
    set_time_limit(0);
    $mail = new PHPMailer();
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host = $this->EMAIL_SMTP;
    $mail->Port = $this->EMAIL_PORT;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = $this->EMAIL_SMTP_SECURE;
    $mail->CharSet = $this->EMAIL_CHARSET;
    $mail->Encoding = $this->EMAIL_ENCODING;
    $mail->Username = $this->EMAIL_FROM_NAME;
    $mail->Password = $this->EMAIL_PASSWORD;
    $mail->From = $this->EMAIL_FROM_NAME;
    $mail->FromName = $this->EMAIL_NICKNAME;
    $mail->Subject = $this->subject;
    
    if(is_array($this->cc)){
      foreach($this->cc as $v){
        $mail->addCC($v);
      }
    }else{ $mail->addCC($this->cc,''); }
    
    if(is_array($this->bcc)){
      foreach($this->bcc as $v){
        $mail->addBCC($v);
      }
    }else{ $mail->addBCC($this->bcc,''); }
    
    if(is_array($this->address)){
       foreach($this->address as $v){
         $mail->addAddress($v);
       }
    }else{ $mail->addAddress($this->address,''); }
    
    if(is_array($this->attachment)){
      foreach($this->attachment as $v){
        $mail->addAttachment($v);
      }
    }else{ $mail->addAttachment($this->attachment,''); }

    $mail->IsHTML(true);  //支持html标签
    $mail->Body = $this->body;
    if(!$mail->Send()) {
      $msg = $mail->ErrorInfo;
      return array( 'code'=>false, 'msg'=>$msg );
    }else{
      return array( 'code'=>true, 'msg'=>'' );
    }
    
  }




}