<?php
namespace app\index\model;
use think\Request;
use think\Model;
/**
 * 
 */
class Order extends Model
{
    public function listData(){
        $data = $this->all();
        dump($data);
    }
    public function del($order_id){
        $order_id = ['order_id'=>$order_id];
        // dump($order_id);exit;
        $this->where($order_id)->delete();
    }
}
