<?php
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if($operation == 'display'){
	/*$tttt = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE optionid > 0");
	foreach($tttt as $k=>$v){
		$hasoo = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODSOPTION)." WHERE id  = {$v['optionid']} AND goodsid = {$v['goodsid']}");
		if(empty($hasoo)){
			$ddd['optionid'] = 0;
			$ddd['optionname'] = "";
			pdo_update(BEST_ORDERGOODS,$ddd,array('id'=>$v['id']));
		}
	}*/
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']}");
	$list = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} ORDER BY time DESC LIMIT ".($pindex - 1)*$psize.",".$psize);
	foreach($list as $k=>$v){
		$list[$k]['canyunum'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND hdid = {$v['id']}");
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('web/huodong');
}elseif ($operation == 'xiajia') {
	$hdid = intval($_GPC['id']);
	$goodslist = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONGGOODS)." WHERE weid = {$_W['uniacid']} AND hdid = {$hdid}");
	foreach($goodslist as $k=>$v){
		$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE id = {$v['goodsid']}");
		$goodslist[$k]['goodsres'] = $goodsres;
	}
	include $this->template('web/huodong');
}elseif ($operation == 'doxiajia') {
	$hdid = intval($_GPC['hdid']);
	$goodsid = intval($_GPC['goodsid']);
	pdo_delete(BEST_HUODONGGOODS,array('hdid'=>$hdid,'goodsid'=>$goodsid));
	$mhdres = pdo_fetchall("SELECT id FROM ".tablename(BEST_MERCHANTHD)." WHERE hdid = {$hdid}");
	foreach($mhdres as $k=>$v){
		pdo_delete(BEST_MERCHANTHDGOODS,array('mhdid'=>$v['id'],'goodsid'=>$goodsid));
	}
	message('下架成功！', $this->createWebUrl('huodong', array('op' => 'xiajia', 'id' => $hdid)), 'success');
}elseif ($operation == 'canyu') {
	$hdid = intval($_GPC['id']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND hdid = {$hdid}");
	$merchanthdlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND hdid = {$hdid} ORDER BY time DESC LIMIT ".($pindex - 1)*$psize.",".$psize);
	foreach($merchanthdlist as $k=>$v){
		$merchanthdlist[$k]['merchant'] = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE id = {$v['merchant_id']}");
		$merchanthdlist[$k]['ordernum'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE mhdid = {$v['id']} AND status >= 0");
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('web/huodong');
}elseif ($operation == 'canyuedit') {
	$id = intval($_GPC['id']);
	$merchanthd = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE id = {$id}");
	$hdid = $merchanthd['hdid'];	
	$hdgoods = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONGGOODS)." WHERE weid = {$_W['uniacid']} AND hdid = {$hdid}");
	$goodsarr = array();
	$nowindex = 0;
	foreach($hdgoods as $k=>$v){
		$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
		if($goodsres['hasoption'] == 1){
			$goodsoptions = pdo_fetchall("SELECT * FROM ".tablename(BEST_GOODSOPTION)." WHERE goodsid = {$goodsres['id']}");
			foreach($goodsoptions as $kk=>$vv){
				$goodsarr[$nowindex]['goods'] = $goodsres;
				$goodsarr[$nowindex]['optionname'] = $vv['title'];
				$goodsarr[$nowindex]['optionid'] = $vv['id'];
				$goodsarr[$nowindex]['has'] = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHDGOODS)." WHERE mhdid = {$id} AND goodsid = {$v['goodsid']} AND optionid = {$vv['id']}");
				$nowindex++;
			}
		}else{
			$goodsarr[$nowindex]['goods'] = $goodsres;
			$goodsarr[$nowindex]['optionname'] = '';
			$goodsarr[$nowindex]['optionid'] = 0;
			$goodsarr[$nowindex]['has'] = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHDGOODS)." WHERE mhdid = {$id} AND goodsid = {$v['goodsid']} AND optionid = 0");
			$nowindex++;
		}
	}
	if (checksubmit('submit')) {
		$data = array(
			'sharetitle' => $_GPC['sharetitle'],
			'sharethumb' => $_GPC['sharethumb'],
			'sharedes' => $_GPC['sharedes'],
			'manjian' => $_GPC['manjian'],
			'yunfei' => $_GPC['yunfei'],
			'canziti'=>intval($_GPC['canziti']),
			'cansonghuo'=>intval($_GPC['cansonghuo']),
			'candmfk'=>intval($_GPC['candmfk']),
			'daojishi'=>intval($_GPC['daojishi']),
			'buydetail'=>intval($_GPC['buydetail']),
		);
		pdo_update(BEST_MERCHANTHD, $data, array('id' => $id));
		pdo_delete(BEST_MERCHANTHDGOODS,array('mhdid'=>$id));
		if(!empty($_GPC['goodsid'])){
			foreach($_GPC['goodsid'] as $k=>$v){
				$goodsoptionid = explode("-",$v);
				$datam['weid'] = $_W['uniacid'];
				$datam['goodsid'] = $goodsoptionid[0];
				$datam['optionid'] = $goodsoptionid[1];
				$datam['mhdid'] = $id;
				$datam['time'] = TIMESTAMP;
				pdo_insert(BEST_MERCHANTHDGOODS,$datam); 
			}
		}
		message('操作成功！', $this->createWebUrl('huodong', array('op' => 'canyu', 'id' => $hdid)), 'success');
	}
	include $this->template('web/huodong');
}elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	$goodslist = pdo_fetchall("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} ORDER BY createtime DESC");
	$yunfeilist = pdo_fetchall("SELECT * FROM ".tablename(BEST_YUNFEI)." WHERE weid = {$_W['uniacid']}");
	if (!empty($id)) {
		$huodong = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE id = {$id}");
		if (empty($huodong)) {
			message('抱歉，活动不存在或是已经删除！','', 'error');
		}
		$hasorder = pdo_fetch("SELECT id FROM ".tablename(BEST_ORDER)." WHERE hdid = {$id}");
		if (!empty($hasorder)) {
			message('抱歉，该活动中已有订单产生不能修改！', '', 'error');
		}
		foreach($goodslist as $kk=>$vv){
			$goodslist[$kk]['has'] = pdo_fetch("SELECT id FROM ".tablename(BEST_HUODONGGOODS)." WHERE weid= {$_W['uniacid']} AND hdid = {$id} AND goodsid = {$vv['id']}");
		}
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('请输入活动名称！');
		}
		if (empty($_GPC['starttime'])) {
			message('请选择活动开始时间！');
		}
		if (empty($_GPC['endtime'])) {
			message('请选择活动结束时间！');
		}
		$starttime = strtotime($_GPC['starttime']);
		$endtime = strtotime($_GPC['endtime']);
		if($starttime >= $endtime){
			message('活动开始时间不得大于结束时间！');
		}
		$pstype = intval($_GPC['pstype']);
		if($pstype == 0){
			$yfid = intval($_GPC['yfid']);
			$canziti = intval($_GPC['canziti']);
			$cansonghuo = intval($_GPC['cansonghuo']);
			$candmfk = intval($_GPC['candmfk']);
			$manjian = $_GPC['manjian'];
		}else{
			$yfid = $canziti = $cansonghuo = $candmfk = 0;
			$manjian = 0;
		}
		$data = array(
			'weid' => intval($_W['uniacid']),
			'title' => $_GPC['title'],
			'sharetitle' => $_GPC['sharetitle'],
			'sharethumb' => $_GPC['sharethumb'],
			'sharedes' => $_GPC['sharedes'],
			'time' => TIMESTAMP,
			'starttime' => $starttime,
			'endtime' => $endtime,
			'canziti'=>$canziti,
			'cansonghuo'=>$cansonghuo,
			'candmfk'=>$candmfk,
			'yfid'=>$yfid,
			'manjian' => $manjian,
			'pstype'=>$pstype,
			'candj'=>$_GPC['candj'],
			'autofield'=>$_GPC['autofield'],
		);
		if (empty($id)) {
			pdo_insert(BEST_HUODONG, $data);
			$id = pdo_insertid();
			
		} else {
			unset($data['time']);
			pdo_update(BEST_HUODONG, $data, array('id' => $id));
			pdo_delete(BEST_HUODONGGOODS,array('hdid'=>$id));		
		}
		if(!empty($_GPC['goodsid'])){
			foreach($_GPC['goodsid'] as $k=>$v){
				$datam['weid'] = $_W['uniacid'];
				$datam['goodsid'] = $v;
				$datam['hdid'] = $id;
				pdo_insert(BEST_HUODONGGOODS,$datam); 
			}
		}
		message('操作成功！', $this->createWebUrl('huodong', array('op' => 'display', 'id' => $id)), 'success');
	}
	include $this->template('web/huodong');
}elseif ($operation == 'tingzhi') {
	$id = intval($_GPC['id']);
	$huodong = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE id = {$id}");
	if (empty($huodong)) {
		message('抱歉，活动不存在或是已经删除！', '', 'error');
	}
	if($huodong['endtime'] < TIMESTAMP){
		message('抱歉，活动已经结束了！', '', 'error');
	}
	$data['tqjs'] = 1;
	pdo_update(BEST_HUODONG,$data,array('id'=>$id));
	message('提前结束成功！', $this->createWebUrl('huodong', array('op' => 'display', 'id' => $id)), 'success');
}elseif ($operation == 'deletecanyu') {
	$mhdid = intval($_GPC['id']);
	$hdid = intval($_GPC['hdid']);
	$merchanthd = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE id = {$mhdid}");
	if (empty($merchanthd)) {
		message('抱歉，代理商活动不存在或是已经删除！', '', 'error');
	}
	$hasorder = pdo_fetch("SELECT id FROM ".tablename(BEST_ORDER)." WHERE mhdid = {$mhdid}");
	if (!empty($hasorder)) {
		message('抱歉，代理商活动下已经存在订单不能删除！', '', 'error');
	}
	pdo_delete(BEST_MERCHANTHD,array('id'=>$mhdid));
	pdo_delete(BEST_MERCHANTHDGOODS,array('mhdid'=>$mhdid));
	message('删除代理商结束成功！', $this->createWebUrl('huodong', array('op' => 'canyu', 'id' => $hdid)), 'success');
}elseif ($operation == 'peihuo') {
	$id = intval($_GPC['id']);
	$hdorders = pdo_fetchall("SELECT id FROM ".tablename(BEST_ORDER)." WHERE isjl = 0 AND hdid = {$id} AND status >= 1");
	$hdordersids = "(";
	foreach($hdorders as $k=>$v){
		$hdordersids .= $v['id'].",";
	}
	$hdordersids = substr($hdordersids,0,-1).")";
	$hdordergoods = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE jlid = 0 AND orderid in {$hdordersids} AND hdid = {$id} ORDER BY mhdid ASC");
	$goodsarr = array();
	$agentsarr = array();
	foreach($hdordergoods as $k=>$v){
		$merchanthd = pdo_fetch("SELECT merchant_id FROM ".tablename(BEST_MERCHANTHD)." WHERE id = {$v['mhdid']}");
		$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE id = {$merchanthd['merchant_id']}");
		
		$kkk = $v['goodsid'].$v['optionid'].$v['mhdid'];
		$agentsarr[$kkk]['goodsname'] = $v['goodsname'].' '.$v['optionname'];
		$hasatotal = empty($agentsarr[$kkk]['total']) ? 0 : $agentsarr[$kkk]['total'];
		$agentsarr[$kkk]['total'] = $hasatotal+$v['total'];
		$agentsarr[$kkk]['merchantres'] = $merchant['realname'].' '.$merchant['telphone'].' '.$merchant['address'];
		
		$kk = $v['goodsid'].$v['optionid'];
		$goodsarr[$kk]['goodsname'] = $v['goodsname'].' '.$v['optionname'];
		$hastotal = empty($goodsarr[$kk]['total']) ? 0 : $goodsarr[$kk]['total'];
		$goodsarr[$kk]['total'] = $hastotal+$v['total'];
		$goodsarr[$kk]['merchantres'] = '';
	}
	
	/* 输入到CSV文件 */
	$html = "\xEF\xBB\xBF";
	/* 输出表头 */
	$filter = array('商品','数量','代理商信息');
	foreach ($filter as $key => $title) {
		$html .= $title . "\t,";
	}
	$html .= "\n";
	$alltotal = 0;
	foreach($goodsarr as $kk=>$vv){
		$html .= $vv['goodsname']. "\t, ";
		$html .= $vv['total']. "\t, ";
		$html .= $vv['merchantres']. "\t, ";
		$html .= "\n";
		$alltotal += $vv['total'];
	}
	$html .= "合计\t, ".$alltotal."\t, \t, ";
	$html .= "\n";
	foreach($agentsarr as $kk=>$vv){
		$html .= "\n";
		$html .= $vv['goodsname']. "\t, ";
		$html .= $vv['total']. "\t, ";
		$html .= $vv['merchantres']. "\t, ";
	}
	/* 输出CSV文件 */
	header("Content-type:text/csv");
	header("Content-Disposition:attachment; filename=配货单.csv");
	echo $html;
	exit();
}elseif ($operation == 'tongzhi') {
	$id = intval($_GPC['id']);
	$huodong = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE id = {$id}");
	if (empty($huodong)) {
		message('抱歉，活动不存在或是已经删除！', '', 'error');
	}
	if($huodong['endtime'] < TIMESTAMP){
		message('抱歉，活动已经结束了！', '', 'error');
	}
	
	$merchantlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND status = 1");
	if($this->module['config']['istplon'] == 1 && !empty($merchantlist)){
		$account_api = WeAccount::create();
		$or_paysuccess_redirect = $_W["siteroot"].'app/'.str_replace("./","",$this->createMobileUrl("dohd",array("id"=>$huodong['id'])));
		foreach($merchantlist as $k=>$v){
			$postdata = array(
				'first' => array(
					'value' => "你好，最新一期的活动已经审核发布",
					'color' => '#ff510'
				),
				'keyword1' => array(
					'value' => $huodong['title'],
					'color' => '#ff510'
				),
				'keyword2' => array(
					'value' => date("Y-m-d H:i:s",$huodong['starttime']).'至'.date("Y-m-d H:i:s",$huodong['endtime']),
					'color' => '#ff510'
				),
				'keyword3' => array(
					'value' => '活动发布成功',
					'color' => '#ff510'
				),
				'remark' => array(
					'value' => '点击详情查看更多详细信息',
					'color' => '#ff510'
				),							
			);
			$account_api->sendTplNotice($v['openid'],$this->module['config']['huodong_tz'],$postdata,$or_paysuccess_redirect,'#980000');
		}
	}
	message('通知代理商成功！', $this->createWebUrl('huodong', array('op' => 'display', 'id' => $id)), 'success');
}elseif ($operation == 'teamjiang') {
	$id = intval($_GPC['id']);
	$huodong = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE id = {$id}");
	if (empty($huodong)) {
		message('抱歉，活动不存在或是已经删除！', '', 'error');
	}
	if($huodong['endtime'] < TIMESTAMP){
		message('抱歉，活动已经结束了！', '', 'error');
	}
	if (checksubmit('submit')) {
		pdo_delete(BEST_HUODONGTEAMJIANG,array('hdid'=>$id));
		$lastendmoney = 0;
		foreach($_GPC['startmoney'] as $k=>$v){
			$num = $k+1;
			$lastnum = $k;
			$startmoney = $v;
			$endmoney = $_GPC['endmoney'][$k];
			$jiangli = $_GPC['jiangli'][$k];
			if(empty($startmoney)){
				message('请填写项目'.$num.'的起始价格！', '', 'error');
			}
			if(empty($endmoney)){
				message('请填写项目'.$num.'的截止价格！', '', 'error');
			}
			if(empty($jiangli)){
				message('请填写项目'.$num.'的奖励！', '', 'error');
			}
			if($endmoney <= $startmoney){
				message('项目'.$num.'的起始价格不得大于截止价格！', '', 'error');
			}
			if($lastendmoney >= $startmoney){
				message('项目'.$num.'的起始价格不得小于项目'.$lastnum.'截止价格！', '', 'error');
			}
			$lastendmoney = $endmoney;
			$data = array(
				'weid' => intval($_W['uniacid']),
				'hdid' => $id,
				'startmoney' => $startmoney,
				'endmoney' => $endmoney,
				'jiangli' => $jiangli,
				'displayerorder' => $num,
			);
			pdo_insert(BEST_HUODONGTEAMJIANG, $data);
		}
		message('操作成功！', $this->createWebUrl('huodong', array('op' => 'teamjiang', 'id' => $id)), 'success');
	}
	$teamjiang = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONGTEAMJIANG)." WHERE weid = {$_W['uniacid']} AND hdid = {$id} ORDER BY displayerorder");
	include $this->template('web/huodong');
}elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$huodong = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE id = {$id}");
	if (empty($huodong)) {
		message('抱歉，活动不存在或是已经删除！', '', 'error');
	}
	if($huodong['endtime'] > TIMESTAMP){
		message('抱歉，活动还没结束不能删除！', '', 'error');
	}
	pdo_delete(BEST_HUODONG,array('id'=>$id));
	pdo_delete(BEST_HUODONGGOODS,array('hdid'=>$id));
	pdo_delete(BEST_HUODONGTEAMJIANG,array('id'=>$id));
	message('删除活动成功！', $this->createWebUrl('huodong', array('op' => 'teamjiang', 'id' => $id)), 'success');
}
?>