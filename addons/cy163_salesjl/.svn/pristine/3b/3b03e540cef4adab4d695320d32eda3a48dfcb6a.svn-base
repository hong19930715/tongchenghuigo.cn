<?php
global $_W, $_GPC;
$member = $this->Mcheckmember();
$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$member['openid']}' AND openid != ''");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	if(empty($merchant) || $merchant['status'] == 0){
		if($this->module['config']['agentreg'] == 0){
			$message = "代理商申请通道已关闭！";
			include $this->template('error');
			exit;
		}
		/*if($this->module['config']['agentreg'] == 2){
			if($member['dlopenid'] == ''){
				$message = "你的注册链接不合法！";
				include $this->template('error');
				exit;
			}else{
				$dlmerchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$member['dlopenid']}' AND openid != '' AND istz = 1");
				if(empty($dlmerchant)){
					$message = "你的推荐人已被取消团长身份！";
					include $this->template('error');
					exit;
				}
			}
		}*/
		include $this->template('merchantregister');
	}else{
		$total1 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND status = 0");
		$total2 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND status = 1");
		$total3 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND status = 2");
		$total4 = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND status = 4");
		include $this->template('merchantcenter');
	}
}elseif ($operation == 'register') {
	if(!checksubmit('submit')){
		exit;
	}
	if($this->module['config']['agentregtype'] == 0){
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
			$resArr['msg'] = "请输入正确的身份证号！";
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['name'])) {
			$resArr['error'] = 1;
			$resArr['msg'] = '请填写商家名称！';
			echo json_encode($resArr);
			exit();
		}
		if (empty($_GPC['address'])) {
			$resArr['error'] = 1;
			$resArr['msg'] = '请填写商家地址！';
			echo json_encode($resArr);
			exit();
		}
		$data = array(
			'weid' => $_W['uniacid'],
			'realname' => trim($_GPC['realname']),
			'telphone' => trim($_GPC['telphone']),
			'idcard' => trim($_GPC['idcard']),
			'name' => trim($_GPC['name']),
			'address' => trim($_GPC['address']),
			'addtime'=>TIMESTAMP,
			'openid'=>$member['openid'],
		);
		pdo_insert(BEST_MERCHANT,$data);
	}else{
		$data = array(
			'weid' => $_W['uniacid'],
			'addtime'=>TIMESTAMP,
			'openid'=>$member['openid'],
			'name' => $member['nickname'],
		);
		pdo_insert(BEST_MERCHANT,$data);
	}
	/*if(!empty($dlmerchant) && $this->module['config']['istplon'] == 1){
		$or_paysuccess_redirect = $_W["siteroot"].'app/'.str_replace("./","",$this->createMobileUrl("merchantteam",array("op"=>"xiaji")));
		$postdata = array(
			'first' => array(
				'value' => "您好，您的团队有新的成员加入。",
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => $data['realname'],
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => date("Y-m-d H:i:s",TIMESTAMP),
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "点击查看详情",
				'color' => '#ff510'
			),	
		);
		$account_api = WeAccount::create();
		$account_api->sendTplNotice($member['dlopenid'],$this->module['config']['rutuan_tz'],$postdata,$or_paysuccess_redirect,'#980000');
	}*/
	$resArr['error'] = 0;
	$resArr['msg'] = '申请开通资料提交成功，请等待管理员审核！';
	echo json_encode($resArr);
	exit();
}elseif ($operation == 'tuandui') {
	$fopenid = trim($_GPC['fopenid']);
	if(empty($merchant)){
		$message = "你还不是代理商，请先开通！";
		if($member['dlopenid'] == ''){
			if($fopenid != $member['openid']){
				$dlmerchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$fopenid}' AND openid != '' AND istz = 1");
				if(!empty($dlmerchant)){
					$datamember['dlopenid'] = $fopenid;
					pdo_update(BEST_MEMBER,$datamember,array('openid'=>$member['openid']));
				}
			}
		}
		$btns = '前往开通';
		$url = $this->createMobileUrl('merchant');
		include $this->template('error');
		exit;
	}
	if($merchant['istz'] == 1){
		$message = "你是团长不能加入！";
		$btns = '进入代理中心';
		$url = $this->createMobileUrl('merchant');
		include $this->template('error');
		exit;
	}
	if($merchant['fopenid'] != ''){
		$message = "你的团队信息已被绑定！";
		$btns = '进入代理中心';
		$url = $this->createMobileUrl('merchant');
		include $this->template('error');
		exit;
	}
	if($merchant['openid'] == $fopenid){
		$message = "不能绑定自己！";
		$btns = '进入代理中心';
		$url = $this->createMobileUrl('merchant');
		include $this->template('error');
		exit;
	}
	$fmerchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$fopenid}'");
	if($fmerchant['istz'] == 0){
		$message = "你的推荐人不是团长身份！";
		$btns = '进入代理中心';
		$url = $this->createMobileUrl('merchant');
		include $this->template('error');
		exit;
	}
	$data['fopenid'] = $fopenid;
	pdo_update(BEST_MERCHANT,$data,array('id'=>$merchant['id']));
	if($this->module['config']['istplon'] == 1){
		$or_paysuccess_redirect = $_W["siteroot"].'app/'.str_replace("./","",$this->createMobileUrl("merchantteam",array("op"=>"xiaji")));
		$postdata = array(
			'first' => array(
				'value' => "您好，您的团队有新的成员加入。",
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => $merchant['realname'],
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => date("Y-m-d H:i:s",TIMESTAMP),
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "点击查看详情",
				'color' => '#ff510'
			),	
		);
		$account_api = WeAccount::create();
		$account_api->sendTplNotice($fopenid,$this->module['config']['rutuan_tz'],$postdata,$or_paysuccess_redirect,'#980000');
	}
	$message = "绑定团队成功！";
	$btns = '进入代理中心';
	$url = $this->createMobileUrl('merchant');
	include $this->template('error');
	exit;
}
?>