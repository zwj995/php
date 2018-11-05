<?php
namespace app\index\controller;
use think\Request;
use think\Db;
/**
 * 订单物流
 */
class Order extends Common
{
    public function index(){ 
        $user_id = session('user.user_id');
        
        $data = Db::name('Order')->alias('a')->join('shop_order_detail b','a.id=b.order_id')->join('shop_goods c','c.id=b.goods_id')->field('a.*,b.goods_count,c.goods_name,c.goods_thumb,c.shop_price')->where('user_id',$user_id)->select();

        // $data = db('Order')->select();
        // dump($data);exit;
        $this->assign('data',$data);

        foreach ($data as $key => $value) {
            $order_detail[]=['id'=>$value['id']];
        }
        foreach($order_detail as $val){
            foreach($val as $va){
                $a[] = $va;
            }
        }
        $b = implode(',', $a);
        $img = db('order_detail')->where('id','in',$b)->field('goods_id')->select();

        $this->assign('img',$img);
        return $this->fetch();
    }
    public function del(){
        $order_id = input('order_id');
        model('Order')->del($order_id);
        $this->success('ok','index');
    }
    //查看订单物流
    public function expire(){
        //接受订单的ID
        $order_id = input('id');
        // dump($order_id);
        //根据订单信息ID获取快递的信息
        $order_info = Db::name('order')->find($order_id);
        // dump($order_info);exit;
        //调用接口获取快递物流信息

        $url ='http://v.juhe.cn/exp/index?key=d043fcfb7886a0860e018f1b9f52ddaf&com='.$order_info['com'].'&no='.$order_info['no'];
        // cookie('result',null);
        // exit;
         //读取Cookie缓存
        $result= cookie('result')?cookie('result'):[];
        // dump($result);exit;
        if (!$result) {
            $result = file_get_contents($url);
            //$result = json_encode($result,true);
            //最后写入数据到cookie中
            cookie('result',$result,3600);
           // dump(cookie('result'));
        }
        $result = json_decode($result,true);
        //dump($result);die;
        $this->assign('data',$result['result']['list']);
        return $this->fetch();
    }
    //测试cookie
    public function test(){
        dump(cookie('result'));
    }
}
