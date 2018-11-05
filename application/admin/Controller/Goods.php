<?php 
namespace app\admin\Controller;
use think\Db;
/**
* 商品控制器
*/
class Goods extends Common
{
	//ajax请求获取属性
	public function showAttr(){
		$type_id =input('type_id');
		//调用自定义方法根据type_id的值获取属性
		$data = model('Attribute')->getAttrByTypeId($type_id);
		//dump($data);
		if (!$data) {
			return '没有数据';
		}
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function testMove()
	{
		// 需要转移图片的地址
		$img_dir = 'uploads/20180926/9ce35a739abcb6dcf6eb8c6526c9935c.jpg';
		require_once "../extend/ftp.php";
		$obj = new \ftp('192.168.153.134','21','ftpuser','123456');
		$obj->up_file($img_dir,$img_dir);
	}
	public function add()
	{
		if($this->request->isGet()){
			//获取所有的类型
			$type = model('Type')->getAllInfo(); 
			$this->assign('type',$type);
			// 获取所有的分类
			$category = model('Category')->getCateTree();
			$this->assign('category',$category);
			return $this->fetch();
		}
		$model = model('Goods');
		$result = $model->addGoods();
		if($result === FALSE){
			$this->error($model->getError());
		}
		$this->success('添加成功','index');
	}
	// 显示商品列表
	public function index()
	{
		// 调用模型下的自定义方法获取数据
		$model = model('Goods');
		$data = $model->listData();
		$this->assign('data',$data);
		// 获取所有的分类
		$category = model('Category')->getCateTree(0,true);
		$this->assign('category',$category);
		return $this->fetch();
	}

	//ajax切换状态
	public function changeStatus(){
		$model = model('Goods');
		//返回FLASE表示修改失败 返回0或者1表示正常
		$result = $model->changeStatus();
		// ajax请求通过status判断操作是否正常 0 表示异常 如果为1表示正常 可以通过goods_status判断最终的商品对应的状态
		if ($result === FALSE) {
			return json(['status'=>0,'msg'=>$model->getError()]);
		}
		return json(['status'=>1,'goods_status'=>$result]);
	}
	public function del(){
		$goods_id = input('id/d',0);
		//修改is_del状态
		Db::name('goods')->where('id',$goods_id)->setField('is_del',1);
		$this->success('ok','index');
	}
	public function delPic(){
		$tmp=[
			'id' => input('img_id'),
			'goods_id' => input('goods_id')
		];
		 Db::name('goods_img')->where($tmp)->delete();
		return  1;
	}
	public function recycle(){
		//调用模型下的自定义方法获取数据
		$model = model('Goods');
		//查询已经被删除的商品
		$data = $model->listData(1);
		$this->assign('data',$data);
		//获取所有的分类
		$category = model('Category')->getCateTree(0,true);
		$this->assign('category',$category);
		return $this->fetch();
	}
	//商品还原
	public function restore(){
		$goods_id = input('id/d',0);
		//修改is_del 状态
		Db::name('goods')->where('id',$goods_id)->setField('is_del',0);
		$this->success('ok','index');
	}
		//商品还原
		public function remove(){
			$goods_id = input('id/d',0);
			//修改is_del 状态
			Db::name('goods')->where('id',$goods_id)->delete();
			$this->success('ok','index');
		}
		public function edit(){
			$goods_id = input('id/d',0);
			$model = model('Goods');
			if ($this->request->isGet()) {
				$goods_info = $model->get($goods_id);
				// dump($goods_info);exit;
				$this->assign('goods_info',$goods_info);
				//获取所有分类
				$category = model('Category')->getCateTree();
				$this->assign('category',$category);
				//获取属性
				$goods_attr = db('type')->select();
				$this->assign('type',$goods_attr);
				//获取已有的相册
				$pics = Db::name('goods_img')->where('goods_id',$goods_id)->select();
				$this->assign('pics',$pics);

				return $this->fetch();
			}
			$result = $model->editGoods();
			if ($result === FALSE) {
				$this->error($model->getError());
			}
			$this->success('ok','index');
		}
}