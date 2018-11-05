<?php
namespace app\index\controller;
use think\Db;
class Goods extends Common
{
	// 商品详情页
	public function index()
	{
		$goods_id = input('goods_id/d',0);
		// 调用模型方法获取相关信息
        $goods_info = model('Goods')->getGoodsInfo($goods_id);         
		if($goods_info === FALSE){
			$this->error('参数错误','index/index/index');
		}
		// dump($goods_info);
		$this->assign('goods_info',$goods_info);
		session('listz',$goods_id);
		// dump(session('listz'));exit;
		return $this->fetch();


	}
}
?>