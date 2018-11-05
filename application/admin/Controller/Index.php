<?php 
namespace app\admin\controller;

/**
* 后台首页的控制器
*/
class Index extends Common
{
	public function index()
	{
		return $this->fetch();
	}
	public function menu()
	{
		$this->assign('menus',$this->admin['menus']);
		return $this->fetch();
	}
	public function main()
	{
		return $this->fetch();
	}
	public function top()
	{
		return $this->fetch();
	}
}
?>