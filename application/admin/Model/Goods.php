<?php 
namespace app\admin\model;
use think\Model;
use think\Db;
/**
* 商品模型
*/
class Goods extends Model
{
	public function editGoods()
	{
		$data = input();
		// 考虑取消勾选操作后 没有内容提交无法更新状态
		if(!isset($data['is_hot'])){
			$data['is_hot']=0;
		}
		if(!isset($data['is_new'])){
			$data['is_new']=0;
		}
		if(!isset($data['is_rec'])){
			$data['is_rec']=0;
		}
		// 检查数据合法性
		$obj = validate('Goods');
		if($obj->check($data) === FALSE){
			$this->error = $obj->getError();
			return FALSE;
		}
		// 检查货号
		if($this->checkGoodsSn($data,'edit') === FALSE){
			$this->error = '货号错误';
			return FALSE;
		}
		// 实现商品图片上传 编辑图片上传不是必须
		if($this->uploadGoodsThumb($data,FALSE) ===FALSE){
			return FALSE;
		}
		// 修改数据
		Goods::allowField(true)->isUpdate(true)->save($data,['id'=>$data['id']]);

	}
	// 商品添加入库
	public function addGoods()
	{
		// 1、接受数据
		$data = input();
		// 2、数据进行验证
		$obj = validate('Goods');
		if($obj->check($data) === FALSE){
			$this->error = $obj->getError();
			return FALSE;
		}
		// 商品货号的检查
		if($this->checkGoodsSn($data) === FALSE){
			$this->error = '货号错误';
			return FALSE;
		}
		// 实现商品图片上传
		if($this->uploadGoodsThumb($data) ===FALSE){
			return FALSE;
		}
		// 增加上架时间
		$data['addtime']=time();
		// 写入数据
		Goods::startTrans();//开启事务
		try{
			Goods::allowField(true)->save($data);
			$goods_id = Goods::getLastInsID();//获取写入数据的主键
			//实现商品的相册上传
			$this->uploadPics($goods_id);
			model('GoodsAttr')->insertData($goods_id,input('attr_id/a'),input('attr/a'));
			Goods::commit();
		}catch(\Exception $e){
			Goods::rollback();
			$this->error = '数据写入错误';
			return FALSE;
		}
	}
	//相册上传
	public function uploadPics($goods_id){
		//1.获取上传数组格式的对象
		$files = request()->file('pics');
		$list = [];
		//循环上传
		foreach ($files as $file) {
			$info = $file->validate(['ext'=>'jpg,png'])->move('uploads');
			if (!$info) {
				return FALSE;
			}
			//组装上传的文件地址
			//将"\"符号转换为"/" 考虑linux下将"\"不作为目标分隔符
			$goods_img = str_replace('\\','/',$info->getPathName());
			//3.打开文件
			$img =  \think\Image::open($goods_img);
			//组装缩略图保存地址 缩略图保存地址与上传图片地址一致文件名称在上传文件名称前追加thumb_
			//getFileName上传文件成功后获取文件的名称
			$goods_thumb = 'uploads/'.date('Ymd').'/thumb_'.$info->getFileName();
			// 4、生成缩略图
			$img->thumb(100,100)->save($goods_thumb);
			// 将商品图片转移到资源服务器下
			img_to_cdn($goods_img);
			img_to_cdn($goods_thumb);
			$list[]=[
				'goods_id'=>$goods_id,
				'goods_img'=>$goods_img,
				'goods_thumb'=>$goods_thumb
			];
		}
		if($list){
			Db::name('goods_img')->insertAll($list);
		}
	}

	// 商品缩略图上传
	public function uploadGoodsThumb(&$data,$isMust=true)
	{
		// 1、使用request对象调用file方法获取File类对象
		$file = request()->file('goods_img');
		if(!$file){
			if ($isMust) {
				//必须上传图片
				$this->error = '商品缩略图必须上传';
				return FALSE;
			}else{
				//非必须上传 由于没有图片终止后续代码
				return TRUE;
			}
		}
		// 2、调用move方法上传图片 使用validate方法限制格式
		$info = $file->validate(['ext'=>'jpg,png'])->move('uploads');
		if(!$info){
			// 上传的文件异常
			$this->error = $file->getError();
			return FALSE;
		}
		// 组装上传的文件地址
		// 将"\"符号转换为"/" 考虑linux下下将"\"不作为目录分隔符
		$data['goods_img'] = str_replace('\\','/', $info->getPathName());
		// 3、打开文件
		$img = \think\Image::open($data['goods_img']);
		//组装缩略图保存地址 缩略图保存地址与上传图片地址一致文件名称在上传文件名称前追加thumb_
		//getFileName上传文件成功后获取文件的名称
		$data['goods_thumb'] = 'uploads/'.date('Ymd').'/thumb_'.$info->getFileName();
		// 4、生成缩略图
		$img->thumb(100,100)->save($data['goods_thumb']);
		// 将商品图片转移到资源服务器下
		img_to_cdn($data['goods_img']);
		img_to_cdn($data['goods_thumb']);
	}

	// 商品货号的检查
	public function checkGoodsSn(&$data,$method='add')
	{
		if($data['goods_sn']){
			if ($data['goods_sn']) {
				$map = [
					'goods_sn'=>$data['goods_sn']
				];
				if ($method == 'edit') {
					//需要排除当前商品
					$map['id']=['neq',$data['id']];
				}
			}
			// 检查唯一
			if(Goods::get(['goods_sn'=>$data['goods_sn']])){
				return FALSE;
			}
		}else{
			// 生成唯一
			$data['goods_sn'] = strtoupper('SHOP'.uniqid()); 
		}
	}
	public function listData($is_del=0){
		$where = ['is_del'=>$is_del];//保存查询的条件
		//使用关键字搜索
		$keyword = input('keyword');
		if ($keyword) {
			//有提交关键字作为条件 模糊查询
			$where['a.goods_name']=['like','%'.$keyword.'%'];
		}
		//使用上下架搜索
		$is_sale=input('is_sale/d',0);
		if ($is_sale) {
			//提交的值为1或者2
			$is_sale -=1;
			$where['a.is_sale']=$is_sale;
		}
		// 实现使用推荐状态搜索
		$intro_type = input('intro_type');
		if ($intro_type) {
			$where['a.'.$intro_type]=1;
		}
		
		// 分类作为条件搜索
		$cate_id = input('cate_id');
		if($cate_id){
			// 获取当前条件分类下的所有子分类
			$tree = model('Category')->getCateTree($cate_id);
			$cate_ids=[];//保存所有分类的id
			foreach ($tree as $key => $value) {
				$cate_ids[]=$value->id;
			}
			// 将本身的id追加到$cate_ids中
			$cate_ids[]=$cate_id;
			// mysql原生方式in语法需要逗号分隔的字符串。TP中可以使用数组或者字符串格式
			$where['a.cate_id']=['in',$cate_ids];
		}
		// 连表查询商品信息以及所属分类
		return Goods::alias('a')->field('a.*,b.cname')->join('shop_category b','a.cate_id=b.id','left')->where($where)->paginate(8,false,['query'=>input()]);
	}

	public function changeStatus(){
		$goods_id = input('goods_id/d',0);
		$field = input('field');

		//过滤field
		$allowField = ['is_hot','is_new','is_rec','is_sale'];
		if (!in_array($field,$allowField)) {
			$this->error = '字段错误';
			return FAlSE;
		}
		//查询数据
		$goods_info = Goods::get($goods_id);
		if (!$goods_info) {
			$this->error = '商品id错误';
			return FALSE;
		}
		//计算要修改的值
		$change_status = $goods_info->$field?0:1;
		// setField为query类下的方法 作用指定只修改某一个字段的内容 第一个参数为字段名称 第二个参数为字段所对应的值
		$this->where('id',$goods_id)->setField($field,$change_status);
		return $change_status;
	}
}
