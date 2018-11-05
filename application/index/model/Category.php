<?php
namespace app\index\model;
use think\Model;
class Category extends Model
{
    //获取到所有分类的信息
    public function getCateTree($id=0,$isClear=false){
        //获取所有的分类信息
        $data = $this->all();
        //格式化数据
        return get_cate_tree($data,$id,1,$isClear);
    }
}

    

