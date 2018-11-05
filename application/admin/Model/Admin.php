<?php 
namespace app\admin\model;
use think\Model;
/**
* 管理员模型
*/
class Admin extends Model
{
	public function updateAdmin()
	{
		// 1、接受参数
		$data = input();
		// 检查用户名对的重复
		$where = [
			'username'=>$data['username'],
			'id'=>['neq',$data['id']]
		];
		$user_info = Admin::get($where);
		if($user_info){
			$this->error = '用户名重复';
			return FALSE;
		}
		if($data['password']){
			// 有提交密码 修改密码
			$data['password']=md5($data['password']);
		}else{
			// 没有提交密码表示不修改
			unset($data['password']);
		}
		return $this->isUpdate(true)->allowField(true)->save($data);
	}

	public function addAdmin(){
		//1.接受参数
		$data = input();
		$user_info = Admin::get(['username'=>$data['username']]);
		if ($user_info) {
			$this->error = '用户名重复';
			return FALSE;
		}
		//密码加密
		$data['password'] = md5($data['password']);
		return $this->save($data);
	}


	// 登录
	public function login()
	{
		// 1、接受参数
		$data = input();
		// 2、比对验证码
		$obj = new \think\captcha\Captcha();
		if(!$obj->check($data['captcha'])){
			// $this->error = '验证码错误';
			// return FALSE;
		}
		// 比对用户名与密码的匹配
		$where = [
			'username'=>$data['username'],
			'password'=>md5($data['password'])
		];
		$user_info = Admin::get($where);
		if(!$user_info){
			$this->error = '验证码错误';
			return FALSE;
		}
		// 保存用户登录状态
		$time = isset($data['remember'])?3600*24*7:0;
		//使用cookie保存多个信息
		cookie('admin_info',$user_info->toArray(),$time);
	}
}