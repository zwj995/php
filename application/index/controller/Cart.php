<?php 
namespace app\index\controller;
use think\Request;
use think\Db;
/**
 * 
 */
class Cart extends Common
{
    public function addCart(){
        //接收数据 
        $goods_id = input('goods_id');
        $goods_count = input('goods_count');
        $goods_attr = input('goods_attr/a');
        //将属性值数组格式转换为字符串格式
        $goods_attr_ids = $goods_attr?implode(',',$goods_attr):"";
        model('Cart')->addCart($goods_id,$goods_count,$goods_attr_ids);
        echo 'ok';
    }
    //购物车数量减少
    public function editadd(){
        $goods_id = input('goods_id');
        $goods_count = input('goods_count');
        $sql = Db::name('Cart')->where('goods_id','=',$goods_id)->setInc('goods_count');
        // if($sql){
        //     return 1;
        // }else{
        //     return 0;
        // }
    }   
    //购物车数量减少
     public function editdec(){
        $goods_id = input('goods_id');
        $goods_count = input('goods_count');
        $sql = Db::name('Cart')->where('goods_id','=',$goods_id)->setDec('goods_count');
        // if($sql){
        //     return 1;
        // }else{
        //     return 0;
        // }
    }
    //购物车列表
    public function index(){
        $model = model('cart');
        $data = model('Cart')->listData();  
        $this->assign('data',$data);
        //计算总金额
        $total = $model->getTotal($data);
        $this->assign('total',$total);

        return $this->fetch();
    } 

    public function del(){
        $goods_id = input('goods_id');
        $goods_attr_ids = input('goods_attr_ids');
        model('Cart')->del($goods_id,$goods_attr_ids);
        $this->success('ok','index');
    }
    public function test(){
        dump(cookie('cart'));
    }

}
