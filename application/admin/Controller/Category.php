<?php 
namespace app\admin\controller;
use think\Db;
/**
* 分类控制器
*/
class Category extends Common
{
	// 分类的添加
	public function add()
	{
		$model = model('Category');
		if($this->request->isGet()){
			// 调用模型下自定义方法获取数据
			$category = $model->getCateTree();
			$this->assign('category',$category);
			return $this->fetch();
		}
		// 获取query对象
		$queryObj = Db::name('category');
		// 表单提交入库
		// 调用方法写入 返回受影响行数
		$result = $queryObj->insert(input());
		if(!$result){
			$this->error('fail');
		}
		$this->success('ok');
	}

	// 分类的列表
	public function index()
	{
		// 1、获取所有的分类
		$category = model('Category')->getCateTree();
		$this->assign('category',$category);
		return $this->fetch();
	}

	// 分类的删除
	public function del()
	{
		// 1、获取当前要删除的分类
		$id = input('id',0,'intval');
		$model = model('Category');
		// 调用模型下自定义的方法进行删除
		$result = $model->del($id);
		if($result === FALSE){
			// 调用getError方法获取到错误信息
			$this->error($model->getError());
		}
		$this->success('ok');
	}

	// 实现分类的编辑
	public function edit()
	{
		// 获取要修改的分类的标识
		$cate_id = input('id',0,'intval');
		//获取模型对象
		$model = model('Category');
		if($this->request->isGet()){
			// 获取当前要修改的分类的信息
			$info = $model->get($cate_id);
			// 赋值模板显示
			$this->assign('info',$info);
			// 获取所有的分类信息
			$category = $model->getCateTree();
			$this->assign('category',$category);
			return $this->fetch();
		}
		// 入库修改
		$result = $model->editCategory();
		if($result === FALSE){
			$this->error($model->getError());
		}
		$this->success('ok','index');
	}
	
	// 生成md5密文
	public function makeMd5()
	{
		return md5('admin');
	}
}

?>