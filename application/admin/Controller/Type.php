<?php
    namespace app\admin\controller;
    use think\Db;

    /**
     * 类型控制器
     */
    class Type extends Common{
        public function add(){
            if ($this->request->isGet()) {
                return $this->fetch();
           }
           Db::name('type')->insert(input());
           model('Type')->updateCahe();
           $this->success('ok','index');
        }
        public function index(){
            $data = model('Type')->getAllInfo();
            $this->assign('data',$data);
            return $this->fetch();
        }
        public function del(){
		$type_id = input('id/d',0);
		Db::name('type')->where('id',$type_id)->delete();
		// model('Type')->updateCahe();
		$this->success('ok','index');
        }
        public function edit(){
            $obj = Db::name('type');
            if ($this->request->isGet()) {
                $info = $obj->where('id',input('id'))->find();
                $this->assign('info',$info);
                return $this->fetch();
            }
            $obj->update(input());
            model('Type')->updateCahe();
            $this->success('ok','index');
        }
    }