<?php
namespace app\index\controller;
use think\Db;
class Index extends Common
{
    public function index()
    {
    	$goodsModel = model('Goods');
    	$data=[];//保存推荐状态的商品
    	// 获取热卖商品
    	$data['hot'] = $goodsModel->getRecGoods('is_hot');
    	// 获取推荐商品
    	$data['rec'] = $goodsModel->getRecGoods('is_rec');
    	// 获取新品商品
    	$data['new'] = $goodsModel->getRecGoods('is_new');
    	$this->assign('data',$data);
    	// 赋值区分为首页
		$this->assign('homepage',1);
		$id = session('listz');

		//浏览记录

		if (!isset($_COOKIE['hisgory'])) {     //检查cookie是否设置 第一次访问  
            $his[] = $id;                     //访问过的uri放到数组里
          }else {
            $his = explode(',', $_COOKIE['hisgory']);  
            $his[] = $id ;  
            
            array_unshift($his, $id );         //开头插入  新的uri
            $his = array_unique($his);         //移除数组中重复uri
            if (count($his)>5) {               //数量大于5 就移除尾部uri
              array_pop($his);
            }
          }
        
        cookie('hisgory',implode(',', $his));
        // dump(cookie('hisgory'));
        //...........
        $hisgory_goods = cookie('hisgory');

        // $hisgory_goods = explode(',',$hisgory_goods);
        // $last = count($hisgory_goods)-1;
        
        // $in = '0,'.$last;
        
		   $bbb = db('goods')->where('id','in',$hisgory_goods)->select();
		//    dump($bbb);exit;
		   $this->assign('listz',$bbb);  
		   //浏览记录 end

        $goods_info = Db::name('goods')->where('id',$id)->field
('id,goods_thumb,goods_name')->select(); 
        // dump($goods_info);exit;
        //创建cookie
        // cookie('hisgory',$goods_info); 
        return $this->fetch();
    }
}
