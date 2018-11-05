<?php 
namespace app\admin\model;
use think\Model;
/**
* 
*/
class GoodsAttr extends Model
{
	//接受提交的属性id数组
	//接受提交的属性值数组
	public function insertData($goods_id,$attr_id,$attr_value)
	{
		$list= [];//保存要写入到数据表中数据
		foreach ($attr_value as $key => $value) {
			$list[]=[
				'goods_id'=>$goods_id,
				'attr_id'=>$attr_id[$key],
				'attr_value'=>$value
			];
		}
		if($list){
			// 批量写入数据
			$this->saveAll($list);
		}
	}
}

?>