<?php
if (!function_exists('send_email')) {
    function send_email($to,$subject,$body){
        require '../extend/PHPMailer/class.phpmailer.php';
        $mail             = new PHPMailer();
        /*服务器相关信息*/
        $mail->IsSMTP();   //启用smtp服务发送邮件                     
        $mail->SMTPAuth   = true;  //设置开启认证             
        $mail->Host       = 'smtp.163.com';   	 //指定smtp邮件服务器地址  
        $mail->Username   = 'zwj24995';  	//指定用户名	
        $mail->Password   = 'zwj995168';		//邮箱的第三方客户端的授权密码
        /*内容信息*/
        $mail->IsHTML(true);
        $mail->CharSet    ="UTF-8";		 	
        $mail->From       = 'zwj24995@163.com';	 		
        $mail->FromName   ="商城管理员";	//发件人昵称
        $mail->Subject    = $subject; //发件主题
        $mail->MsgHTML($body);	//邮件内容 支持HTML代码
        $mail->AddAddress($to);  //收件人邮箱地址
        //$mail->AddAttachment("test.png"); //附件
        return $mail->Send();			//发送邮箱
    }
}

if(!function_exists('send_sms')){
    function send_sms($to,$datas,$tempId=1){
        include_once("../extend/SmsSDK.php");
        $accountSid= '8a216da8662360a40166754b8f6b1fc1';
        //主帐号令牌,
        $accountToken= '5d759824e2b74e2686abb85560076145';
        //应用Id，
        $appId='8a216da8662360a40166754b8fcc1fc8';
        //请求地址
        $serverIP='app.cloopen.com';
        //请求端口，生产环境和沙盒环境一致
        $serverPort='8883';
        //REST版本号，在官网文档REST介绍中获得。
        $softVersion='2013-12-26';
        $rest = new REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);

        if($result == NULL ) {
            return FALSE;
        }
        if($result->statusCode!=0) {
            return FALSE;
        }
        return TRUE;
    }
}
/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $expire = 0,$key = '') {
    $key  = md5(empty($key) ? config('key') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time():0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}
/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = ''){
    $key    = md5(empty($key) ? config('key') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);
    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/*
*
* 作用：双重MD5加密
* @param:$pwd string 明文
* @param:$salt string 盐
* @return string 
* 
*/
if(!function_exists('md6')){
	function md6($pwd,$salt='123456'){
		return md5(md5($pwd).$salt);
	}
}
/*
*
* 作用：分类的格式化操作
* @param:$data array 格式化数据
* @param:$id int 指定查找的分类id
* @param:$lev int 指定层次数字
* @return array 
* 
*/
if(!function_exists('get_cate_tree')){
	function get_cate_tree($data,$id=0,$lev=1,$isClear=false){
		static $list = [];//保存结果
		if($isClear){
			// 根据传递的参数确认是否需要重置已有的数据
			$list = [];
		}
		foreach ($data as $key => $value) {
			if($value['parent_id']==$id){
				$value['lev'] = $lev;
				$list[] = $value;
				get_cate_tree($data,$value['id'],$lev+1,false);
			}
		}
		return $list;
	}
}
/*
*
* 作用：图片转移到资源服务器
* @param:$local_dir string 本地资源地址
* @param:$cdn_dir string 服务器地址
* @return  
* 
*/
if(!function_exists('img_to_cdn')){
	function img_to_cdn($local_dir,$cdn_dir=''){
		// 上传到服务器的地址 没有传递使用本地的地址
		$cdn_dir= $cdn_dir?$cdn_dir:$local_dir;
		require_once "../extend/ftp.php";
		// 从配置信息中读取资源服务信息
		$config = config('cdn_config');
		$obj = new \ftp($config['host'],$config['port'],$config['user'],$config['pwd']);
		return $obj->up_file($local_dir,$cdn_dir);
	}
}