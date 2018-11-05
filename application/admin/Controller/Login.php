<?php 
namespace app\admin\controller;
use think\Db;
/**
* 登录控制器
*/
class Login extends Common
{
	public $is_check_login = FALSE;
	// 登录
	public function index()
	{
		if($this->request->isGet()){
			return $this->fetch();
		}
		$model = model('Admin');
		$result = $model->login();
		if($result === FALSE){
			$this->error($model->getError());
		}
		$this->success('登录成功','admin/index/index');
	}

	// 生成验证码
	public function captcha()
	{
		$config = ['codeSet'=>'123456789'];
		$obj = new \think\captcha\Captcha($config);
		return $obj->entry();
	}
	public function logout()
	{
		cookie('admin_info',null);
		$this->success('退出成功','index');
	}
}