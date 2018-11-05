<?php 
namespace app\admin\validate;
use think\Validate;

/**
* 商品验证器
*/
class Goods extends Validate
{
	protected $rule = [
		'goods_name'=>'require',
		'cate_id'=>'require|gt:0',
		'shop_price'=>'require|gt:0',
		'market_price'=>'require|checkMarketPrice'
	];
	// 自定义的市场价格校验规则
	public function checkMarketPrice($value,$rule,$data)
	{
		if($data['market_price']<=$data['shop_price']){
			return FALSE;
		}
		return TRUE;
	}
}

?>