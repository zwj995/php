<?php
namespace app\index\controller;
use think\Request;
use think\Db;
use think\controller;
/**
 * 
 */
class listz extends Common
{
        public function index(){
        // cookie('hisgory',null);exit;
        $id = session('listz');
       
       
        if (!isset($_COOKIE['hisgory'])) {     //检查cookie是否设置 第一次访问  
            $his[] = $id;                     //访问过的url放到数组里
          }else {
            // dump($_COOKIE['hisgory']);exit; 
            $his = explode(',', $_COOKIE['hisgory']); 
            // dump($his);exit; 
            $his[] = $id ;  
            
            array_unshift($his, $id );         //开头插入  新的url
            $his = array_unique($his);         //移除数组中重复url
            if (count($his)>5) {               //数量大于5 就移除尾部url
              array_pop($his);
            }
          }
        
        cookie('hisgory',implode(',', $his));
        // dump(cookie('hisgory'));

        //查询cookie对应的数据
        $hisgory_goods = cookie('hisgory'); 
        // dump($hisgory_goods);exit; 

        $listz = db('goods')->where('id','in',$hisgory_goods)->select();

        $this->assign('listz',$listz);
        return $this->fetch();
        
     }  

}

