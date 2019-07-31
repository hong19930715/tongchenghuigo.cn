<?php
global $_W,$_GPC;
$member['openid'] = trim($_GPC['openid']);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	$status = intval($_GPC['status']);
	if($status > 0){
		if($status == 99){
			$status = 0;
		}
		$conditions = "weid = {$_W['uniacid']} AND isjl = 0 AND from_user = '{$member['openid']}' AND status = {$status}";
	}else{
		$conditions = "weid = {$_W['uniacid']} AND isjl = 0 AND from_user = '{$member['openid']}' AND status >= 0";
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE ".$conditions);
	$allpage = ceil($total/10)+1;
	$page = intval($_GPC["page"]);
	$pindex = max(1, $page);
	$psize = 10;
	$orderlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDER)." WHERE ".$conditions." ORDER BY createtime DESC LIMIT ".($pindex - 1)*$psize.",".$psize);
	foreach($orderlist as $k=>$v){
		$merchant = pdo_fetch("SELECT name FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND id = {$v['merchant_id']}");
		$orderlist[$k]['merchantname'] = $merchant['name'];
		if($v['status'] == 0){
			$orderlist[$k]['status'] = '待付款';
		}
		if($v['status'] == 1){
			$orderlist[$k]['status'] = $v['pstype'] == 0 || $v['pstype'] == 3 ? '待发货' : '待自提';
		}
		if($v['status'] == 2){
			$orderlist[$k]['status'] = '待收货';
		}
		if($v['status'] == 4){
			$orderlist[$k]['status'] = '已完成';
		}
		if($v['status'] == -2){
			$orderlist[$k]['status'] = '退款中';
		}
		if($v['status'] == -3){
			$orderlist[$k]['status'] = '已退款';
		}
	}
	$notext = '暂无订单';
	if($status == 0){
		$notext = '暂无待付款订单';
	}
	if($status == 1){
		$notext = '暂无待发货/自提订单';
	}
	if($status == 2){
		$notext = '暂无待收货订单';
	}
	if($status == 4){
		$notext = '暂无已完成订单';
	}
	if($status == -1){
		$notext = '暂无已取消订单';
	}
	if($status == -2){
		$notext = '暂无退款中订单';
	}
	if($status == -3){
		$notext = '暂无已退款订单';
	}
	$data['notext'] = $notext;
	$data['orderlist'] = $orderlist;
	$data['allpage'] = $allpage;
	$this->result(0,"我的订单", $data);
}elseif($operation == 'detail'){
	$ordersn = trim($_GPC['ordersn']);
	$orderres = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE ordersn = '{$ordersn}' AND weid = {$_W['uniacid']} AND from_user = '{$member['openid']}'");
	$huodongres = pdo_fetch("SELECT endtime FROM ".tablename(BEST_HUODONG)." WHERE id = {$orderres['hdid']}");
	if($huodongres['endtime'] < TIMESTAMP){
		$orderres['canfukuan'] = 0;
	}else{
		$orderres['canfukuan'] = 1;
	}
	
	$ordergoods = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE orderid = {$orderres['id']}");
	if($orderres['status'] == 0 && $orderres['canfukuan'] = 1){
		foreach($ordergoods as $k=>$v){
			$goodsres = pdo_fetch("SELECT total FROM ".tablename(BEST_GOODS)." WHERE id = {$v['goodsid']}");
			if($goodsres['total'] < $v['total']){
				$orderres['canfukuan'] = 0;
			}
		}
	}else{
		$orderres['canfukuan'] = 0;
	}

	if($orderres['pstype'] == 0 || $orderres['pstype'] == 3){
		$address = explode("|",$orderres['address']);
	}
	if($orderres['expresscom'] != 'ZFPS' && $orderres['expresscom'] != 'SF' && $orderres['expresscom'] != ''){
		include_once(ROOT_PATH.'Express.class.php');
		$idkdn = $this->module['config']['kdnid'];
		$keykdn = $this->module['config']['kdnkey'];
		$urlkdn = "http://api.kdniao.cc/api/dist";
		$shipperCode = $orderres['expresscom'];//快递公司简称，官方有文档
		$logisticCode = $orderres['expresssn'];//快递单号//
		$a = new Express(); 
		$logisticResult = $a->getOrderTracesByJson($idkdn,$keykdn,$urlkdn,$shipperCode,$logisticCode);
		$data = json_decode($logisticResult,true);
		if($data['State'] != "0"){
			$expressres = $data['Traces'];
			rsort($expressres);
		}
	}else{
		$expressres = '';
	}
	$orderres['time'] = date("Y-m-d H:i:s",$orderres['createtime']);
	$data['order'] = $orderres;
	$data['expressres'] = $expressres;
	$data['address'] = $address;
	$data['goodslist'] = $ordergoods;
	$this->result(0,"订单详情", $data);
}elseif ($operation == 'cancelorder') {
	$orderid = intval($_GPC['id']);
	$orderres = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE id = {$orderid} AND weid = {$_W['uniacid']} AND status = 0 AND from_user = '{$member['openid']}'");
	if(empty($orderres)){
		$this->result(1,"不存在该订单！", '');
	}else{
		$datacancel['status'] = -1;
		pdo_update(BEST_ORDER,$datacancel,array('id'=>$orderres['id']));
		$this->result(0,"取消订单成功！", '');
	}
}elseif($operation == 'shouhuo'){
	$orderid = intval($_GPC['id']);
	$orderres = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE id = {$orderid} AND weid = {$_W['uniacid']} AND status = 2 AND from_user = '{$member['openid']}'");
	if(empty($orderres)){
		$this->result(1,"不存在该订单！", '');
	}
	$data['status'] = 4;
	pdo_update(BEST_ORDER,$data,array('id'=>$orderres['id']));
	if($orderres['isdmfk'] == 0){
		//利润写进代理商数据库，同时设置可提现时间
		$hasmerchantaccount = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTACCOUNT)." WHERE merchant_id = {$orderres['merchant_id']} AND orderid = {$orderres['id']}");
		if(empty($hasmerchantaccount)){
			$datamerchant['weid'] = $_W['uniacid'];
			$datamerchant['merchant_id'] = $orderres['merchant_id'];
			$datamerchant['money'] = $orderres['alllirun'];
			$datamerchant['time'] = TIMESTAMP;
			$datamerchant['explain'] = '代理团结算';
			$datamerchant['orderid'] = $orderres['id'];
			$datamerchant['candotime'] = TIMESTAMP + ($this->module['config']['dltxhour'])*3600;
			pdo_insert(BEST_MERCHANTACCOUNT,$datamerchant);
		}
	}
	$this->result(0,"确认收货成功！", '');
}elseif($operation == 'getqrcode'){
	$account_api = WeAccount::create();
	$access_token = $account_api->getAccessToken();
	$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
	$data = array(
		"scene"=>$_GPC['scene'],
		"page"=>"cy163_salesjl/pages/merhexiao/merhexiao",
		"width"=>430,
	);
	$data = json_encode($data);
	$response = $this->send_post($url,$data);
	$result=$this->data_uri($response,'image/png');
	$this->result(0,'核销码！', $result);
}elseif($operation == 'refund'){
	$orderid = intval($_GPC['orderid']);
	$orderres = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE id = {$orderid} AND (status = 1 OR status = 2) AND from_user = '{$member['openid']}'");
	$refund_price = $orderres['price']-$orderres['yunfei'];
	$resarr['refund_price'] = $refund_price;
	$this->result(0,'退款！', $resarr);
}elseif($operation == 'dorefund'){
	$orderid = intval($_GPC['orderid']);
	$orderres = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE id = {$orderid} AND (status = 1 OR status = 2) AND from_user = '{$member['openid']}'");
	if(empty($orderres)){
		$this->result(1,"没有该订单！", '');
	}
	if($orderres['isdmfk'] == 1){
		$this->result(1,"该订单不能退款！", '');
	}
	
	$refund_price = $_GPC['refund_price'];
	if($refund_price <= 0){
		$this->result(1,"请填写退款金额！", '');
	}
	$can_refund_price = $orderres['price']-$orderres['yunfei'];
	if($refund_price > $can_refund_price){
		$this->result(1,"退款金额超限！", '');
	}
	
	if($orderres['cantktime'] < TIMESTAMP){
		$this->result(1,"已经超过允许的退款时间！", '');
	}
	$refund_desc = trim($_GPC['refund_desc']);
	if(empty($refund_desc)){
		$this->result(1,"请填写退款原因！", '');
	}
	
	$data['refund_desc'] = $refund_desc;
	$data['tktime'] = TIMESTAMP;
	$data['refund_price'] = $refund_price;
	$data['status'] = -2;
	pdo_update(BEST_ORDER,$data,array('id'=>$orderres['id']));

	$this->result(0,'申请退款成功！', '');
}
?>