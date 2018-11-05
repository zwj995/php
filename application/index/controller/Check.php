<?php
namespace app\index\controller;
use think\Request;
use think\Db;
/**
 * 下单
 */
class Check extends Common
{
    public function checkUserLogin(){
        //获取用户的ID
        $user_id = model('Cart')->getUserInfo();
        if (!$user_id) {
            $this->error('先登陆','user/login');
        }
        return $user_id;
    }
    
    //显示出结算页
    public function show(){
        //判断用户的登陆状态
        $user_id = $this->checkUserLogin();
        //获取用户的购物车内容
        $data = model('Cart')->listData();
        // dump($data);exit; 
        //计算总金额
        $total = model('Cart')->getTotal($data);
        $this->assign('data',$data);
        $this->assign('total',$total);
        return $this->fetch();
    }
    
    //下单
    public function order(){
        //1.写入订单表
        $user_id = $this->checkUserLogin();
        //接受提交的内容
        $order = input();
        $order['user_id'] = $user_id;
        //生成订单号
        $order['order_id'] = date('YmdHis').rand(1000000,9999999);
        $order['time']=time();
        
        
        // dump($order);exit;
        Db::name('order')->insert($order);
        $order_id = Db::name('order')->getLastInsId();
        //2.写入订单详情表
        //获取购物车数据
        $data = model('Cart')->listData();
        $total = model('Cart')->getTotal($data);
        foreach ($data as $key => $value) {
            $order_detail[]=[
                'order_id'=>$order_id,
                'goods_id'=>$value['goods_id'],
                'goods_count'=>$value['goods_count'],
                'goods_attr_ids'=>$value['goods_attr_ids']
            ];
        }
        Db::name('order_detail')->insertAll($order_detail);
        //需要删除购物车中的内容
        //3.根据支付的方式处理
        if ($order['pay']==1) {
            //货到付款
            return '货到付款';
        }elseif ($order['pay']==2) {
            $this->alipay($order_id,$total['money']);
        }else{
            return '微信支付';
        }
    }
    //支付宝支付
    public function alipay($order_id,$money,$subject='测试商品购买',$body='desc-测试商品购买'){
        require_once '../extend/alipay/config.php';
        require_once '../extend/alipay/pagepay/service/AlipayTradeService.php';
        require_once '../extend/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = trim($order_id);

        //订单名称 必填
        $subject = trim($subject);

        //付款金额 必填 单位为元
        $total_amount = trim($money);

        //商品描述，可空
        $body = trim($body);
    	//构造参数
		$payRequestBuilder = new \AlipayTradePagePayContentBuilder();
		$payRequestBuilder->setBody($body);
		$payRequestBuilder->setSubject($subject);
		$payRequestBuilder->setTotalAmount($total_amount);
		$payRequestBuilder->setOutTradeNo($out_trade_no);

		$aop = new \AlipayTradeService($config);
		// dump($payRequestBuilder);exit();
		// 返回值为一个html代码的字符串格式，在HTML中存在一个表单，表单属于自动提交
		$response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
		//输出表单 即发生请求
		var_dump($response);
		exit();
    }


	// 同步回调
    public function returnUrl(){
        require_once ("../extend/alipay/config.php");
        require_once '../extend/alipay/pagepay/service/AlipayTradeService.php';
        $arr = $_GET;
        $alipaySevice = new \AlipayTradeService($config);
        // 签名的检查
        $result = $alipaySevice->check($arr);
        if (!$result) {
            echo "验证失败";exit;
        }
        //订单的订单号 对应在order表中的id字段
        $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
        //按照自己的业务逻辑处理订单
        $order_info = Db::name('order')->find($out_trade_no);
        if (!$order_info || $order_info['pay_status'] == 1) {
            echo '已经处理完成';exit;
        }
        // 修改订单的状态为已经支付
		Db::name('order')->where('id',$out_trade_no)->setField('pay_status',1);
		echo 'ok';
    }	
    // 异步回调 不能验证站内的用户登录情况
    public function notifyUrl(){
        require_once '../extend/alipay/config.php';
        require_once '../extend/alipay/pagepay/Service/AlipayTradeService.php';
        $arr = $_POST;
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writelog(var_export($_POST,true));
        //签名检查
        $result = $alipaySevice->check($arr);
        if (!$result) {
            echo 'fail';exit;
        }

        //订单号
        $out_trade_no = $_POST['out_trade_no'];

        //支付宝交易号
        $trade_no = $_POST['trade_no'];
        //交易状态
        $trade_status = $_POST['trade_status'];

        if ($_POST['trade_status'] == 'TRADE_FINISHED') {
         //订单完成后不能再次处理的状态   
        }else if ($_POST['trade_status'] == 'TRADE_SUCCESS'){
            //订单支付完成，可以后续再次操作 例如退款
            $order_info = Db::name('order')->find($out_trade_no);
            if (!$order_info) {
                echo 'fail';exit;
            }
            if ($order_info['pay_status']==1) {
                //说明订单已经被处理 输出success通知支付宝订单已经被正确处理
                echo 'success';exit;
            }
            //说明订单信息正常并且用户支付完成 而商家没有处理订单
            Db::name('order')->where('id',$out_trade_no)->setField('pay_status',1);
        }
    }
}

