<?php
namespace  MyPHP;
/**
---  CREATE COOKIE OBJECT   ---
$c = new cookie();

---  CREATE/UPDATE COOKIE   ---
$c->setName('myCookie')         // REQUIRED
->setValue($value,true)       // REQUIRED - 1st param = data string/array, 2nd param = encrypt (true=yes)
->setExpire('+1 hour')        // optional - defaults to "0" or browser close
->setPath('/')                // optional - defaults to /
->setDomain('.domain.com')    // optional - will try to auto-detect
->setSecure(false)            // optional - default false
->setHTTPOnly(false);         // optional - default false
$c->createCookie();             // you could chain this too, must be last

---  DESTROY COOKIE   ---
$c->setName('myCookie')->destroyCookie();
OR
$c->destroyCookie('myCookie');

---  GET COOKIE   ---
$cookie = $c->getCookie('myCookie',true);   // 1st param = cookie name, 2nd param = whether to decrypt
// if the value is an array, you'll need to unserialize the return
 */
Class Cookies {
    // cookie加密键值串，可以根据自己的需要修改
    const DES_KEY = 'o89L7234kjW2Wad72SHw22lPZmEbP3dSj7TT10A5Sh60';
    private $errors = array();
    private $cookieName = null;
    private $cookieData = null;
    private $cookieKey = null;
    private $cookieExpire = 0;
    private $cookiePath = '/';
    private $cookieDomain = null;
    private $cookieSecure = false;
    private $cookieHTTPOnly = false;

    /**
     * 构造函数 设置域
     * @access public
     * @return none
     */
    public function __construct()
    {
        $this->cookieDomain = $this->getRootDomain();
    }

    /**
     * 获取cookie值
     * @access public
     * @param string $cookieName cookie to be retrieved
     * @param bool $decrypt whether to decrypt the values
     */
    public function getCookie($cookieName=null, $decrypt=false)
    {
        if(is_null($cookieName)){
            $cookieName = $this->cookieName;
        }
        if(isset($_COOKIE[$cookieName])){
            return ($decrypt?$this->cookieEncryption($_COOKIE[$cookieName],true):base64_decode($_COOKIE[$cookieName]));
        } else {
            $this->pushError($cookieName.' not found');
            return false;
        }
    }

    /**
     * 创建cookie
     * @access public
     * @return bool true/false
     */
    public function createCookie()
    {
        if(is_null($this->cookieName)){
            $this->pushError('Cookie name was null');
            return false;
        }



        $ret = setcookie(
            $this->cookieName,
            $this->cookieData,
            $this->cookieExpire,
            $this->cookiePath,
            $this->cookieDomain,
            $this->cookieSecure,
            $this->cookieHTTPOnly
        );
        return $ret;
    }

    /**
     * 清除cookie
     * @access public
     * @param string $cookieName to kill
     * @return bool true/false
     */
    public function destroyCookie($cookieName=null)
    {
        if(is_null($cookieName)){
            $cookieName = $this->cookieName;
        }

        $ret = setcookie(
            $cookieName,
            null,
            (time()-1),
            $this->cookiePath,
            $this->cookieDomain
        );

        return $ret;
    }

    /**
     * 设置cookie名称
     * @access public
     * @param string $name cookie name
     * @return mixed obj or bool false
     */
    public function setName($name=null)
    {
        if(!is_null($name)){
            $this->cookieName = $name;
            return $this;
        }
        $this->pushError('Cookie name was null');
        return false;
    }

    /**
     * 设置cookie值
     * @access public
     * @param string $value cookie value
     * @return bool whether the string was a string
     */
    public function setValue($value=null, $encrypt=false)
    {
        if(!is_null($value)){
            if(is_array($value)){
                $value = serialize($value);
            }
            $data = ($encrypt?$this->cookieEncryption($value):base64_encode($value));
            $len = (function_exists('mb_strlen')?mb_strlen($data):strlen($data));

            if($len>4096){
                $this->pushError('Cookie data exceeds 4kb');
                return false;
            }
            $this->cookieData = $data;
            unset($data);
            return $this;
        }
        $this->pushError('Cookie value was empty');
        return false;
    }

    /**
     * 设置cookie的过期时间
     * @access public
     * @param string $time +1 week, etc.
     * @return bool whether the string was a string
     */
    public function setExpire($time=0)
    {
        $pre = substr($time,0,1);
        if(in_array($pre, array('+','-'))){
            $this->cookieExpire = strtotime($time);
            return $this;
        } else {
            $this->cookieExpire = 0;
            return $this;
        }
    }

    /**
     * 设置cookie的保存路径
     * @access public
     * @param string $path
     * @return object $this
     */
    public function setPath($path='/')
    {
        $this->cookiePath = $path;
        return $this;
    }

    /**
     * 设置cookie所属的域
     * @access public
     * @param string $domain
     * @return object $this
     */
    public function setDomain($domain=null)
    {
        if(!is_null($domain)){
            $this->cookieDomain = $domain;
        }
        return $this;
    }

    /**
     *
     * @access public
     * @param bool $secure true/false
     * @return object $this
     */
    public function setSecure($secure=false)
    {
        $this->cookieSecure = (bool)$secure;
        return $this;
    }

    /**
     * HTTPOnly flag, not yet fully supported by all browsers
     * @access public
     * @param bool $httponly yes/no
     * @return object $this
     */
    public function setHTTPOnly($httponly=false)
    {
        $this->cookieHTTPOnly = (bool)$httponly;
        return $this;
    }

    /**
     * Jenky bit to retrieve root domain if not supplied
     * @access private
     * @return string Le Domain
     */
    private function getRootDomain()
    {
        $host = $_SERVER['HTTP_HOST'];
        $parts = explode('.', $host);
        if(count($parts)>1){
            $tld = array_pop($parts);
            $domain = array_pop($parts).'.'.$tld;
        } else {
            $domain = array_pop($parts);
        }
        return '.'.$domain;
    }

    /**
     * Value Encryption
     * @access private
     * @param string $str string to be (de|en)crypted
     * @param string $decrypt whether to decrypt or not
     * @return string (de|en)crypted string
     */
    private function cookieEncryption($str=null, $decrypt=false)
    {
        if(is_null($str)){
            $this->pushError('Cannot encrypt/decrypt null string');
            return $str;
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $key_size = mcrypt_get_key_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $key = substr(self::DES_KEY,0,$key_size);
        if($decrypt){
            $return = mcrypt_decrypt(MCRYPT_3DES, $key, base64_decode($str), MCRYPT_MODE_ECB, $iv);
        } else {
            $return = base64_encode(mcrypt_encrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB, $iv));
        }
        return $return;
    }

    /**
     * Add error to errors array
     * @access public
     * @param string $error
     * @return none
     */
    private function pushError($error=null)
    {
        if(!is_null($error)){
            $this->errors[] = $error;
        }
        return;
    }

    /**
     * Retrieve errors
     * @access public
     * @return string errors
     */
    public function getErrors()
    {
        return implode("<br />", $this->errors);
    }
}