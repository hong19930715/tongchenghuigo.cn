<?php
global $_W,$_GPC;
$openid = trim($_GPC['openid']);
if(empty($openid)){
	$this->result(1,"获取您的身份信息失败！", '');
}
$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE openid = '{$openid}' AND weid = {$_W['uniacid']}");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	$weicj = $hascj = array();
	$hdlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} ORDER BY endtime DESC");
	foreach($hdlist as $k=>$v){
		$merchanthd = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHD)." WHERE hdid = {$v['id']} AND weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']}");
		if(empty($merchanthd)){
			if($v['endtime'] > TIMESTAMP){
				$v['canjia'] = 1;
			}else{
				$v['canjia'] = 0;
			}
			$v['starttime'] = date("Y-m-d H:i:s",$v['starttime']);
			$v['endtime'] = date("Y-m-d H:i:s",$v['endtime']);
			$weicj[$k] = $v;
		}else{
			$v['starttime'] = date("Y-m-d H:i:s",$v['starttime']);
			$v['endtime'] = date("Y-m-d H:i:s",$v['endtime']);
			$v['merchanthdid'] = $merchanthd['id'];
			$hascj[$k] = $v;
		}
	}
	$res['hascj'] = $hascj;
	$res['weicj'] = $weicj;
	$this->result(0,"活动管理", $res);
}elseif($operation == 'dohd'){
	$hdid = intval($_GPC['id']);
	$hdres = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} AND id = {$hdid}");
	$hdgoods = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONGGOODS)." WHERE hdid = {$hdid} AND weid = {$_W['uniacid']} AND weid = {$_W['uniacid']}");
	$goodsarr = array();
	$nowindex = 0;
	foreach($hdgoods as $k=>$v){
		$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
		if($goodsres['hasoption'] == 1){
			$goodsoptions = pdo_fetchall("SELECT * FROM ".tablename(BEST_GOODSOPTION)." WHERE goodsid = {$goodsres['id']}");
			foreach($goodsoptions as $kk=>$vv){
				$goodsarr[$nowindex]['title'] = '['.$vv['title'].']'.$goodsres['title'];
				$goodsarr[$nowindex]['selid'] = "{$vv['goodsid']}-{$vv['id']}";
				$goodsarr[$nowindex]['price'] = $vv['normalprice'];
				$goodsarr[$nowindex]['dlprice'] = $vv['dailiprice'];
				$goodsarr[$nowindex]['lirun'] = $vv['normalprice']-$vv['dailiprice'];
				$nowindex++;
			}
		}else{			
			$goodsarr[$nowindex]['title'] = $goodsres['title'];
			$goodsarr[$nowindex]['price'] = $goodsres['normalprice'];
			$goodsarr[$nowindex]['dlprice'] = $goodsres['dailiprice'];
			$goodsarr[$nowindex]['lirun'] = $goodsres['normalprice']-$goodsres['dailiprice'];
			$goodsarr[$nowindex]['selid'] = "{$goodsres['id']}-0";
			$nowindex++;
		}
	}
	$hdres['starttime'] = date("Y年m月d日 H:i:s",$hdres['starttime']);
	$hdres['endtime'] = date("Y年m月d日 H:i:s",$hdres['endtime']);
	if($hdres['pstype'] == 0){
		$hdres['psmsg'] = '[平台配送]';
		$hdres['psmsg'] .= $hdres['canziti'] == 1 ? '支持自提' : '';
		$hdres['psmsg'] .= $hdres['cansonghuo'] == 1 ? '支持配送' : '';
	}else{
		$hdres['psmsg'] = '参与活动后代理商自定义配送信息';
	}
	$data['hdres'] = $hdres;
	$data['goodslist'] = $goodsarr;
	$this->result(0,"参加活动", $data);
}elseif($operation == 'dodohd'){
	$hdid = intval($_GPC['id']);
	$hdres = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} AND id = {$hdid}");
	if(empty($hdres)){
		$this->result(1,"不存在该活动！", '');
	}
	if($hdres['endtime'] < TIMESTAMP){
		$this->result(1,"活动已经结束，不能参加！", '');
	}
	$hasmerchanthd = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHD)." WHERE merchant_id = {$merchant['id']} AND weid = {$_W['uniacid']} AND hdid = {$hdid}");
	if(!empty($hasmerchanthd)){
		$this->result(1,"您已参加过该活动！", '');
	}
	$goodsidarr = $this->messistr2array($_GPC['goodsid']);
	if(!empty($goodsidarr)){
		$data2['weid'] = $_W['uniacid'];
		$data2['hdid'] = $hdid;
		$data2['merchant_id'] = $merchant['id'];
		$data2['time'] = TIMESTAMP;
		$data2['sharetitle'] = $hdres['sharetitle'];
		$data2['sharethumb'] = $hdres['sharethumb'];
		$data2['sharedes'] = $hdres['sharedes'];
		$data2['canziti'] = 1;
		pdo_insert(BEST_MERCHANTHD,$data2);
		$mhdid = pdo_insertid();
		foreach($goodsidarr as $k=>$v){
			$goodsoptionid = explode("-",$v['name']);
			$data['weid'] = $_W['uniacid'];
			$data['mhdid'] = $mhdid;
			$data['optionid'] = $goodsoptionid[1];
			$data['goodsid'] = $goodsoptionid[0];
			$data['time'] = TIMESTAMP;
			pdo_insert(BEST_MERCHANTHDGOODS,$data);
		}
		$this->result(0,"参加活动成功！", $mhdid);
	}else{
		$this->result(1,"没有选择任何商品，不能参加活动！", '');
	}
}elseif($operation == 'goods'){
	$hdid = intval($_GPC['id']);
	$hdgoods = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONGGOODS)." WHERE weid = {$_W['uniacid']} AND hdid = {$hdid}");
	$goodsarr = array();
	$nowindex = 0;
	foreach($hdgoods as $k=>$v){
		$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
		if($goodsres['hasoption'] == 1){
			$goodsoptions = pdo_fetchall("SELECT * FROM ".tablename(BEST_GOODSOPTION)." WHERE goodsid = {$goodsres['id']}");
			foreach($goodsoptions as $kk=>$vv){
				$goodsarr[$nowindex]['thumbs'] = tomedia($goodsres['thumb']);
				$goodsarr[$nowindex]['title'] = '['.$vv['title'].']'.$goodsres['title'];
				$goodsarr[$nowindex]['price'] = $vv['normalprice'];
				$goodsarr[$nowindex]['dailiprice'] = $vv['dailiprice'];
				$goodsarr[$nowindex]['lirun'] = $vv['normalprice']-$vv['dailiprice'];
				$nowindex++;
			}
		}else{
			$goodsarr[$nowindex]['thumbs'] = tomedia($goodsres['thumb']);
			$goodsarr[$nowindex]['title'] = $goodsres['title'];
			$goodsarr[$nowindex]['price'] = $goodsres['normalprice'];
			$goodsarr[$nowindex]['dailiprice'] = $goodsres['dailiprice'];
			$goodsarr[$nowindex]['lirun'] = $goodsres['normalprice']-$goodsres['dailiprice'];
			$nowindex++;
		}
	}
	$this->result(0,"活动商品列表", $goodsarr);
}elseif($operation == 'detail'){
	$id = intval($_GPC['id']);
	$merchanthd = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND id = {$id}");
	$merchantgoods = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANTHDGOODS)." WHERE mhdid = {$id} AND weid = {$_W['uniacid']}");
	foreach($merchantgoods as $k=>$v){
		$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
		if($v['optionid'] > 0){
			$goodsoptions = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODSOPTION)." WHERE id = {$v['optionid']}");
			$merchantgoods[$k]['title'] = '['.$goodsoptions['title'].']'.$goodsres['title'];
			$merchantgoods[$k]['price'] = $goodsoptions['normalprice'];
			$merchantgoods[$k]['total'] = $goodsoptions['stock'];
			$sales = pdo_fetchcolumn("SELECT SUM(a.total),b.id FROM ".tablename(BEST_ORDERGOODS)." as a,".tablename(BEST_ORDER)." as b WHERE a.mhdid = {$id} AND a.optionid = {$v['optionid']} AND b.status >= 1 AND a.orderid = b.id");
			$merchantgoods[$k]['sales'] = empty($sales) ? 0 : $sales;
		}else{			
			$merchantgoods[$k]['title'] = $goodsres['title'];
			$merchantgoods[$k]['price'] = $goodsres['normalprice'];
			$merchantgoods[$k]['total'] = $goodsres['total'];
			$sales = pdo_fetchcolumn("SELECT SUM(a.total),b.id FROM ".tablename(BEST_ORDERGOODS)." as a,".tablename(BEST_ORDER)." as b WHERE a.mhdid = {$id} AND a.goodsid = {$v['goodsid']} AND b.status >= 1 AND a.orderid = b.id");
			$merchantgoods[$k]['sales'] = empty($sales) ? 0 : $sales;
		}
	}
	$allprice = pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND mhdid = {$id} AND status >= 1");
	$allprice = empty($allprice) ? "0.00" : $allprice;
	$alllirun = pdo_fetchcolumn("SELECT SUM(alllirun) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND mhdid = {$id} AND status >= 1");
	$alllirun = empty($alllirun) ? "0.00" : $alllirun;
	$allordernum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND mhdid = {$id} AND status >= 1");
	
	$merchanthd['sharethumb2'] = $merchanthd['sharethumb'];
	$merchanthd['sharethumb'] = tomedia($merchanthd['sharethumb']);
	
	$huodong = pdo_fetch("SELECT pstype FROM ".tablename(BEST_HUODONG)." WHERE id = {$merchanthd['hdid']}");
	$merchanthd['pstype'] = $huodong['pstype'];
	$data['merhd'] = $merchanthd;
	$data['merchantgoods'] = $merchantgoods;
	$data['allprice'] = $allprice;
	$data['alllirun'] = $alllirun;
	$data['allordernum'] = $allordernum;
	$data['isdz'] = $_W['siteroot'] == DZDOMAIN ? 1 : 0;
	$this->result(0,"活动详情", $data);
}elseif($operation == 'dodetail'){
	$id = intval($_GPC['id']);
	$data['sharetitle'] = trim($_GPC['sharetitle']);
	$data['sharedes'] = trim($_GPC['sharedes']);
	$data['sharethumb'] = trim($_GPC['sharethumb']);
	$data['yunfei'] = $_GPC['yunfei'];
	$data['manjian'] = $_GPC['manjian'];
	$data['canziti'] = intval($_GPC['canziti']);
	$data['cansonghuo'] = intval($_GPC['cansonghuo']);
	$data['candmfk'] = intval($_GPC['candmfk']);
	$data['daojishi'] = intval($_GPC['daojishi']);
	$data['buydetail'] = intval($_GPC['buydetail']);
	pdo_update(BEST_MERCHANTHD,$data,array('id'=>$id));
	$this->result(0,"更新活动详情成功", $data);
}
?>