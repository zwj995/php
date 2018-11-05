<?php
namespace app\index\controller;
use think\Request;
use think\Db;
use think\controller;
/**
 * 
 */
class User extends Common
{
    // 邮件注册激活
    public function active(Request $request){
        $key = input('key');
        //解密
        $user_id = think_decrypt($key);
        if (!$user_id) {
            echo '参数错误';exit;
        }
        Db::name('User')->where('id',$user_id)->setField('status',1);
        }
    public function testEmail(){
        dump(send_email('zwj24995@163.com','激活邮件','点击激活'));
    }
    //发送短信验证码
    public function sendSms(){
        $tel = input('tel');
        //生成验证码
        $code = rand(1000,9999);
        $result = send_sms($tel,[$code,10]);
        if (!$result) {
            return json(['status'=>'0','msg'=>'网络错误']);
        }
        //保存验证码的相关信息  time为记录验证码的生成时间
		session('telCode',['code'=>$code,'time'=>time()]);
        return json(['status'=>1,'msg'=>'ok']);
    }

    //手机号注册
    public function  registTel(Request $request){
        if ($request->isGet()) {
            return $this->fetch();
        }
        $model = model('User');
        $result = $model->registTel();
        if ($request === FALSE) {
            $this->error($model->getError);
        }
        $this->success('ok','login');
    }

    public function test2(){
        dump(send_sms('13266514442',['2345','10']));
    }


    //注册
    public function regist(Request $request){
        if ($request->isGet()) {
            return $this->fetch();
        }
        $model = model('User');
        $result=$model->regist();
        if ($result===FALSE) {
            $this->error('$model->getError');
        }
        $this->success('ok','login');
    }


    //登陆
    public function login(Request $request){
        if ($request->isGet()) {
            return $this->fetch();
        }
        $model = model('User');
        $result = $model->login();
        if ($result === FALSE) {
            $this->error($model->getError());
        }
        $this->redirect('index/index/index');
    }
    //退出
    public function logout()
	{
        session('user',null);
        $this->success('退出成功','index/index');
    }
    

}
