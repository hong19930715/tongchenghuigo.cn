<?php
global $_W, $_GPC;
$member = $this->Mcheckmember();
$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$member['openid']}'");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	if($merchant['istz'] == 1){
		$message = "你已经是团长了！";
		include $this->template('error');
		exit;
	}
	$tzorder = pdo_fetch("SELECT * FROM ".tablename(BEST_TZORDER)." WHERE openid = '{$merchant['openid']}'");
	if(!empty($tzorder)){
		$ordersn = date('Ymd').random(11,1);
		pdo_update(BEST_TZORDER,array('price'=>$this->module['config']['tuanzhangfee'],'ordersn'=>$ordersn),array('id'=>$tzorder['id']));
		$tzorder = pdo_fetch("SELECT * FROM ".tablename(BEST_TZORDER)." WHERE openid = '{$merchant['openid']}'");
	}
	include $this->template('sqtz');
}elseif($operation == 'dosq'){
	$wxqrcode = $_GPC['wxqrcode'];
	if(empty($wxqrcode)){
		$resArr['error'] = 1;
		$resArr['message'] = "请上传微信二维码！";
		echo json_encode($resArr);
		exit;
	}
	$sqprice = $this->module['config']['tuanzhangfee'];
	if($sqprice <= 0){
		$data['istz'] = 1;
		$data['tztime'] = TIMESTAMP;
	}
	$data['wxqrcode'] = $wxqrcode;
	pdo_update(BEST_MERCHANT,$data,array('openid'=>$merchant['openid']));
	
	if($sqprice > 0){
		$datatzorder = array(
			'weid'=>$_W['uniacid'],
			'openid'=>$merchant['openid'],
			'ordersn'=>date('Ymd').random(11,1),
			'time'=>TIMESTAMP,
			'price'=>$sqprice,
		);
		pdo_insert(BEST_TZORDER,$datatzorder);
	}
	$resArr['price'] = $sqprice;
	$resArr['error'] = 0;
	$resArr['message'] = "申请团长成功！";
	echo json_encode($resArr);
	exit;
}
?>