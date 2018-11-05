<?php
namespace app\admin\Controller;
use think\Db;

class Rule extends Common
{
	public function add()
	{
		$model = model('Rule');
		if($this->request->isGet()){
			// 获取已有的权限层次信息
			$rules = $model->getRules();
			return $this->fetch('add',['rules'=>$rules]);
		}
		$model->insert(input());
		$this->success('ok','index');
	}
    public function index(){
        $model = model('Rule');
        $data = $model->getRules();
        $this->assign('data',$data);
        return $this->fetch();
    }
    public function del(){
		// 子权限考虑是否可以删除
		$rule_id = input('id/d',0);
		// 修改is_del 状态
        Db::name('rule')->where('id',$rule_id)->delete();
        $this->success('ok','index');
    }
    
}
