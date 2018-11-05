<?php

    namespace app\admin\Model;
    use think\Model;
    use think\Db;
    /**
     * 属性模型
     */

     class Attribute extends Model
     {
         public function listData(){
		// 方式一直接使用连表查询数据
        //  return Attribute::alias('a')->field('a.*,b.type_name')->join('shop_type b ','a.type_id=b.id','left')->paginate(1);
        // 方式二转换为两条SQL语句执行
        // 1、获取属性信息
        // $attribute = $this->paginate(2);
        // 2.循环根据type_id获取类型名称
        // $list=[];
        // foreach ($attribute as $key => $value) {
        //     //每一个value为对象保存完整的数据
        //     $value = $value->toArray();

        //     $type_id = $value['type_id'];
        //     //根据类型ID获取类型名称
        //     $type_info = Db::name('type')->field('type_name')->where('id',$type_id)->find();
        //     $value['type_name']=$type_info['type_name'];
        //     $list[]=$value;
        // }
        // dump($list);exit;
        // 方式三 使用两条SQL语句实现
        // 1、获取所有的类型信息
        // $type_info = Db::name('type')->select();
        // //获取所有的属性信息
        // $attribute = $this->paginate(2);
        // foreach ($attribute as $key => $value) {
        //     $value = $value->toArray();
        //     $type_id = $value['type_id'];
        //     foreach ($type_info as $k => $v) {
        //         if ($v['id']==$type_id) {
        //             $value['type_name' ]=$v['type_name']; 
        //         }
        //     }
        //     $list[]=$value;
        // }
        // dump($list);exit;
        // 方式四 两条SQL语句处理循环嵌套
        // 1、获取所有的类型信息
        //     $type_info = Db::name('type')->select();
        //     //将数据转换为使用主键组作为下标
        //     $type=[];
        //     foreach ($type_info as $key =>$value) {
        //         $type[$value['id']]=$value;
        //     }
        //     //获取所有的属性信息
        //     $attribute = $this->paginate(2);
        //     foreach ($attribute as $key => $value) {
        //         $value = $value->toArray();
        //         $type_id = $value['type_id'];
        //         $value['type_name']=$type[$type_id]['type_name'];
        //         $list[]=$value;    
        // } 
        // dump($list);exit;
        //方式五 使用缓存实现
		// $type = cache('type');
		// if(!$type){
		// 	// echo 'db';
		// 	// 走数据库查询
		// 	$type_info = Db::name('type')->select();
		// 	$type=[];
		// 	foreach ($type_info as $key => $value) {
		// 		$type[$value['id']]=$value;
		// 	}
		// 	cache('type',$type);
		// }
		// $attribute = $this->paginate(2);
		// foreach ($attribute as $key => $value) {
		// 	$value = $value->toArray();
		// 	$type_id = $value['type_id'];
		// 	$value['type_name']=$type[$type_id]['type_name'];
		// 	$list[]=$value;
		// }
        // dump($list);exit;
        //1.获取所有的分类
        // $type_info = model('type')->updateCahe();
        $type_info = model('type')->getAllInfo();
        //dump(config());exit;
        // dump($type_info);exit;

        //2.获取当前页对应的数据
        $attribute = $this->paginate(10);
        // dump($type_info);exit;

        $list=[];//保存完整属性对象
        foreach ($attribute as $key => $value) {
            $value = $value->toArray();
            $type_id = $value['type_id'];//获取属性对应的type_id
            $value['type_name']=$type_info[$type_id]['type_name'];
            // dump($type_id);exit;

            $list[]=$value;
        }
        //data为数据 page为分页导航菜单
        return['list'=>$list,'page'=>$attribute->render()];
    }   
    public function del(){
        $attr_id = input('id');
        return $this->where('id',$attr_id)->delete();
    }
    public function edit(){
        $data = request()->param();
        if ($data['attr_input_type']==1) {
            //input 文本框输入
            unset($data['attr_values']);
        }else{
            if (!$data['attr_values']) {
                $this->error = 'select选择默认值必须填写';
                return FALSE;
            }
        }
        return $this->update($data);
    }
    //根据类型ID获取属性
    public function getAttrByTypeId($type_id){
        $attribute = $this->all(['type_id'=>$type_id]);
        $list = [];//保存数据
        foreach ($attribute as $key => $value) {
            $value = $value->toArray();
            if ($value['attr_input_type']==2) {
                //为select选择需要将attr_values中的数据转换为数组格式(模板需要)
                $value['attr_values'] = explode(',',$value['attr_values']);
            }
            $list[]=$value;
        }
        return $list;
    }  
}
     