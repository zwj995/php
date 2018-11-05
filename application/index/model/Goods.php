<?php 
namespace app\index\model;
use think\Model;
use think\Db;
/**
* 
*/
class Goods extends Model
{
	// 获取推荐状态的商品
	public function getRecGoods($field) 
	{
		return $this->where([$field=>1])->limit(5)->order('id')->select();
	}
	// 根据商品id获取信息
	public function getGoodsInfo($goods_id)
	{
		$data = [];
		// 获取商品基本信息
		$info = $this->where('id',$goods_id)->find();
		if(!$info || ($info['is_del']==1)){
			return FALSE;
		}
		$data['info'] = $info->toArray();//保存商品的基本信息
		// 获取商品相册
		$data['img'] = Db::name('goods_img')->where('goods_id',$goods_id)->select();
		// 获取商品属性
		$sql = "SELECT a.*,b.attr_name,b.attr_type FROM shop_goods_attr a LEFT JOIN shop_attribute b on a.attr_id=b.id WHERE a.goods_id=$goods_id";
		$attr = $this->query($sql);
		foreach ($attr as $key => $value) {
			if ($value['attr_type']===1) {
				//唯一属性
				$data['unique'][]=$value;
			}else{
				// 以属性的id作为下标 可以准确判断是否存在(直接增加相同属性的值)
				// 单选属性
				// if(array_key_exists($value['attr_id'], $radio)){
				// 	$radio[$value['attr_id']][]=$value;
				// }else{
				// 	$radio[$value['attr_id']][]=$value;
				// }
				$data['radio'][$value['attr_id']][]=$value;
			}
		}
		return $data;
	}
}

?>