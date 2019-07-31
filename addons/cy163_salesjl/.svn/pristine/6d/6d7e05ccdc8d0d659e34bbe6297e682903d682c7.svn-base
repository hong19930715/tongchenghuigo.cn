<?php
global $_GPC, $_W;
$merchant = $this->checkmergentauth();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	include $this->template('merchantprofile');
} elseif ($operation == 'post') {
	if (empty($_GPC['realname'])) {
		$resArr['error'] = 1;
		$resArr['msg'] = '请填写您的真实姓名！';
		echo json_encode($resArr);
		exit();
	}
	if(!$this->isMobile($_GPC['telphone'])){
		$resArr['error'] = 1;
		$resArr['msg'] = '请填写正确的手机号码！';
		echo json_encode($resArr);
		exit();
	}
	$isidcard = $this->isCard($_GPC['idcard']);
	if(empty($isidcard)){
		$resArr['error'] = 1;
		$resArr['msg'] = '请输入正确的身份证号！';
		echo json_encode($resArr);
		exit();
	}
	if (empty($_GPC['address'])) {
		$resArr['error'] = 1;
		$resArr['msg'] = '请填写代理商地址！';
		echo json_encode($resArr);
		exit();
	}
	if (empty($_GPC['avatar'])) {
		$resArr['error'] = 1;
		$resArr['msg'] = '请上传代理商Logo！';
		echo json_encode($resArr);
		exit();
	}
	$data = array(
		'realname' => trim($_GPC['realname']),
		'telphone' => trim($_GPC['telphone']),
		'idcard' => trim($_GPC['idcard']),
		'address' => trim($_GPC['address']),
		'avatar' => $_GPC['avatar'],
	);
	pdo_update(BEST_MERCHANT, $data, array('id' => $merchant['id'], 'weid' => $_W['uniacid']));
	$resArr['error'] = 0;
	$resArr['msg'] = '修改资料成功！';
	echo json_encode($resArr);
	exit();
}
?>