<?php
namespace app\index\model;
use think\Model;
use think\Request;
/**
 * 
 */
class User extends Model
{

    //注册
    public function regist(){
        $data = input();
        //检查用户名重复
        if ($this->get(['username'=>$data['username']])) {
            $this->error = '用户名重复';
            return FALSE;
        }
        //验证邮箱唯一
        if ($this->get(['email'=>$data['email']])) {
            $this->error = '邮箱重复';
            return FALSE;
        }
        //生成盐
        $data['salt'] = rand(100000,999999);
        $data['pwd']=md6($data['password'],$data['salt']);
        $this->allowField(true)->save($data);

        //获取用户对的ID标识
        $user_id = $this->getLastInsId();
        $key = think_encrypt($user_id,7200);//加密之后的密文
        //组装链接地址
        $link = url('index/user/active',['$key'=>$key],'html',true);
        //发送激活邮件
        send_email($data['email'],'注册邮件激活',$link);
    }
    public function login(){
        $data = input();
        //获取用户信息
        $user_info = $this->get(['username'=>$data['username']]);
        if (!$user_info) {
            $this->error = '用户名错误';
            return FALSE;
        }
        //根据用户提交的密码按照注册的规则加密与数据库中密码进行比对
        if ($user_info->getAttr('pwd') != md6($data['password'],$user_info->getAttr('salt'))) {
            $this->error = '密码错误';
            return FALSE;
        }
        //保存用户状态
        // dump($user_info);exit;
        session('user',['user_id'=>$user_info->id,'username'=>$user_info->username]);
        // dump(session('user'));exit;
        //登陆完成触发转移
        model('Cart')->cookie2db();
    }
    // 手机号注册
    public function registTel(){
        $data = input();
        //检查验证码
        //取出session信息
        $sessionData = session('telCode');
        //判断用户提交的验证码与session中保存的是否一致
        if (!$sessionData || $sessionData['code'] != $data['telcode']) {
            $this->error = '验证码错误';
            return FALSE;
        }
        //判断是否过期
        if ($sessionData['time']+600 <time()) {
            $this->error='验证码失效';
            return FALSE;
        }
        //检查用户名是否重复
        if ($this->get(['username'=>$data['username']])) {
            $this->error='用户名重复';
            return FALSE;
        }
        //检查手机号是否重复
        if ($this->get(['tel'=>$data['tel']])) {
            $this->error='手机号重复';
            return FALSE;
        }
        //生成盐
        $data['salt'] = rand(100000,999999);
        $data['pwd']=md6($data['password'],$data['salt']);
        $this->allowField(true)->save($data);
        //销毁session
        session('telCode',null);
    }

}
