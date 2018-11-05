<?php
    namespace app\index\controller;
    use think\Controller;
    /**
     * 公共控制器
     */
    class Common extends  Controller
    {
        public function __construct()
        {
            parent::__construct();
            //获取所有的分类信息
            $category = model('Category')->getCateTree();
            $this->assign('category',$category);
        }
    }


    