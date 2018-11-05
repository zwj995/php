<?php
    namespace app\index\controller;
    use think\Request;
    use think\Db;
    /**
     * 
     */
    class Collection extends Common
    {
        public function index(){
            $id = session('user.user_id');
            $coll= Db::name('coll')->alias('a')->join('shop_goods b','a.goods_id=b.id')->where('user_id',$id)->select();
            // dump($coll);exit;
            $this->assign("coll",$coll);
            return $this->fetch();
        }
        public function addcoll(){
            $tmp = [
                'goods_id' => input('goods_id'),
                'user_id' => session('user.user_id'),
                'is_coll' => 1
            ];
            // dump ($tmp);exit;
            
            Db::name('coll')->insert($tmp);
            
        }
    }
    