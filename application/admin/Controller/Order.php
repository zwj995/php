<?php
namespace app\admin\controller;
use think\Db;
/**
 * 
 */
class Order extends Common
{
    public function index(){

        $data = Db::name('order')->alias('a')->join('shop_user b','a.user_id=b.id','left')->field('a.*,b.username')->select();        
        $this->assign('data',$data);
        return $this->fetch();
    }
    public function send(){
        if ($this->request->isGet()) {
            return $this->fetch();
        }
        //存在前置条件的判断
        $data = input();
        $data['order_status']=3;
        Db::name('order')->update($data);
    }
}
