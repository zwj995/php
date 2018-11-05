<?php 
namespace app\admin\model;
use think\Model;
/**
* 分类模型
*/
class Category extends Model
{
	// 获取到所有的分类信息
	public function getCateTree($id=0,$isClear=false)
	{
		//获取所有的分类信息
		$data = $this->all();
		//格式化数据
		return get_cate_tree($data,$id,1,$isClear);
	}

	// 删除
	public function del($cate_id)
	{
		// 1、判断是否存在子分类
		$hasSon = Category::get(['parent_id'=>$cate_id]);
		if($hasSon){
			// 设置属性记录错误信息
			$this->error = '有子分类'; 
			return FALSE;
		}
		return Category::destroy($cate_id);
	}
	// 分类修改
	public function editCategory()
	{
		// 1、接受数据
		$data = input();
		// 2、判断是否能够修改
		// 修改的分类的父分类不能是当前被修改分类下的子分类
		// $data['parent_id']为要设置的父分类
		// 获取当前分类下的子分类
		$son = $this->getCateTree($data['id']);
		foreach ($son as $key => $value) {
			if($data['parent_id'] == $value['id']){
				$this->error = '分类设置错误';
				return FALSE;
			}
		}
		// 3、不能将自己设置为自己的父分类
		if($data['parent_id']==$data['id']){
			$this->error = '不能设置为自己为父';
			return FALSE;
		}

		// 4、数据入库修改
		// isUpdate设置为修改操作 allowField过滤非数据表中的字段
		$this->isUpdate(true)->allowField(true)->save($data,['id'=>$data['id']]);
	}
}

?>