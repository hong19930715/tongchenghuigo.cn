<?php
global $_GPC, $_W;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {	
	$keyword = trim($_GPC['keyword']);
	$status = intval($_GPC['status']);
	$condition = "weid = {$_W['uniacid']} AND status = {$status} ";
	if(!empty($keyword)){
		$condition .= "AND name like '%{$keyword}%' ";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$total=pdo_fetchcolumn("SELECT count(id) FROM ".tablename(BEST_MERCHANT)." WHERE ".$condition);
	$merchant = pdo_fetchall("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE ".$condition." ORDER BY addtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
	foreach($merchant as $k=>$v){
		$merchant[$k]['moneylist'] = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANTACCOUNT)." WHERE merchant_id = {$v['id']} ORDER BY time DESC");
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('web/merchant');
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE id = {$id} AND status = 0");
	if (empty($merchant)) {
		message('抱歉，该代理商不存在！', $this->createWebUrl('merchant', array('op' => 'display')), 'error');
	}
	$data = array(
		'status' => 1,
	);
	pdo_update(BEST_MERCHANT, $data, array('id' => $id, 'weid' => $_W['uniacid']));
	if($_W["account"]["type_name"] == "公众号"){
		if($this->module['config']['istplon'] == 1){
			$or_paysuccess_redirect = $_W["siteroot"].'app/'.str_replace("./","",$this->createMobileUrl("merchant"));
			$postdata = array(
				'first' => array(
					'value' => "你的代理商资格审核已经处理",
					'color' => '#ff510'
				),
				'keyword1' => array(
					'value' => "代理商注册",
					'color' => '#ff510'
				),
				'keyword2' => array(
					'value' => "通过",
					'color' => '#ff510'
				),
				'keyword3' => array(
					'value' => date("Y年m月d日H:i",TIMESTAMP),
					'color' => '#ff510'
				),
				'remark' => array(
					'value' => '点击查看详情',
					'color' => '#ff510'
				),			
			);
			$account_api = WeAccount::create();
			$account_api->sendTplNotice($merchant['openid'],$this->module['config']['agent_tz'],$postdata,$or_paysuccess_redirect,'#980000');
		}
	}
	message('审核代理商成功！', $this->createWebUrl('merchant', array('op' => 'display')), 'success');
	include $this->template('web/merchant');
}elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$merchant = pdo_fetch("SELECT id,openid FROM " . tablename(BEST_MERCHANT) . " WHERE id = {$id} AND status = 1");
	if (empty($merchant)) {
		message('抱歉，该代理商不存在！', $this->createWebUrl('merchant', array('op' => 'display')), 'error');
	}
	pdo_update(BEST_MERCHANT,array('status'=>0), array('id' => $id));
	message('禁用代理商成功！', $this->createWebUrl('merchant', array('op' => 'display')), 'success');
}elseif ($operation == 'edit') {
	$id = intval($_GPC['id']);
	$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE id = {$id}");
	if (empty($merchant)) {
		message('抱歉，该商户不存在！', $this->createWebUrl('merchant', array('op' => 'display')), 'error');
	}
	$dododo = intval($_GPC['dododo']);
	if($dododo == 1){
		$data['name'] = trim($_GPC['name']);
		$data['telphone'] = trim($_GPC['telphone']);
		$data['address'] = trim($_GPC['address']);
		$data['openid'] = trim($_GPC['openid']);
		$data['avatar'] = trim($_GPC['avatar']);
		$data['realname'] = trim($_GPC['realname']);
		$data['idcard'] = trim($_GPC['idcard']);
		$data['status'] = intval($_GPC['status']);
		$data['usetx'] = intval($_GPC['usetx']);
		$data['txdisaccount'] = $_GPC['txdisaccount'];
		$data['istz'] = intval($_GPC['istz']);
		$data['tzintype'] = intval($_GPC['tzintype']);
		$data['tztime'] = strtotime($_GPC['tztime']);
		$data['wxqrcode'] = $_GPC['wxqrcode'];
		pdo_update(BEST_MERCHANT,$data, array('id' => $id));
		message('编辑代理商资料成功！', $this->createWebUrl('merchant', array('op' => 'display')), 'success');
	}else{
		include $this->template('web/editmerchant');
	}
}elseif($operation == 'deletedu') {
	$id = intval($_GPC['id']);
	$money = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTACCOUNT)." WHERE id = {$id}");
	if (empty($money)) {
		$resarr['error'] = 1;
		$resarr['msg'] = '不存在该记录！';
		echo json_encode($resarr);
		exit();
	}
	pdo_delete(BEST_MERCHANTACCOUNT,array('id'=>$id));
	$resarr['error'] = 0;
	$resarr['msg'] = '删除成功！';
	echo json_encode($resarr);
	exit();
}elseif($operation == 'account') {
	$id = intval($_GPC['id']);
	$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE id = {$id} AND weid = {$_W['uniacid']}");
	$allmoney = pdo_fetchcolumn("SELECT SUM(money) FROM ".tablename(BEST_MERCHANTACCOUNT)." WHERE merchant_id = '{$merchant['id']}'");
	$allmoney = empty($allmoney) ? 0 : $allmoney;
	include $this->template('web/merchant');
}elseif($operation == 'doaccount') {
	$money = $_GPC['money'];
	if(empty($money)){
		message('请填写金额！');
	}
	$data = array(
		'weid'=>$_W['uniacid'],
		'merchant_id'=>$_GPC['merchant_id'],
		'money'=>$money,
		'explain'=>$_GPC['explain'],
		'time'=>TIMESTAMP,
	);
	pdo_insert(BEST_MERCHANTACCOUNT,$data);
	message('操作成功！', '', 'success');
}
?>