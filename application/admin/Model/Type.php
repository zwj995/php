<?php
namespace app\admin\model;
use think\Db;
use think\Model;
    class Type extends Model
    {
        public function getAllInfo(){
            //实例化封装的redis的操作类
            $obj = new \RedisCache('192.168.153.134');
            $type_info = $obj->get('type_info');
            if (!$type_info) { 
                $data = Db::name('type')->select();
                //格式化数据 转换为使用主键作为下标的数据
                foreach ($data as $key => $value) {
                    $type_info[$value['id']]=$value;
                }
                $obj->set('type_info',$type_info,0);
            }
            return $type_info;
        }
        public function updateCahe(){
            $obj = new \RedisCache('192.168.153.134');
            return $obj->delete('type_info');
        }
    }
