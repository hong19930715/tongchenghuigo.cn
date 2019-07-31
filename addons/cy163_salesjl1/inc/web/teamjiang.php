<?php
global $_GPC, $_W;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	$huodonglist = pdo_fetchall("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} ORDER BY hasteamjiang DESC,time DESC");
	$hdid = intval($_GPC['hdid']);
	if($hdid > 0){
		$huodongres = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} AND id = {$hdid}");
		$teamlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND istz = 1 AND tztime < {$huodongres['endtime']}");
		foreach($teamlist as $k=>$v){
			$merchanthd = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$v['id']} AND hdid = {$hdid}");
			$mhdidarr[] = $merchanthd['id'];
			$xiajilist = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND fopenid = '{$v['openid']}'");
			foreach($xiajilist as $kk=>$vv){
				$merchanthd2 = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$vv['id']} AND hdid = {$hdid}");
				if(!empty($merchanthd2)){
					$mhdidarr[] = $merchanthd2['id'];
				}
			}
			$teamorder = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE weid = {$_W['uniacid']} AND mhdid in (".implode($mhdidarr).")");
			$alldailiprice = $allsalesprice = 0;
			foreach($teamorder as $kkk=>$vvv){
				$alldailiprice += $vvv['dlprice']*$vvv['total'];
				$allsalesprice += $vvv['price']*$vvv['total'];
			}
			$teamlist[$k]['alldailiprice'] = $alldailiprice;
			$teamlist[$k]['allsalesprice'] = $allsalesprice;
		}
	}
	include $this->template('web/teamjiang');
}elseif($operation == 'detail') {
	$hdid = intval($_GPC['hdid']);
	$fopenid = trim($_GPC['fopenid']);
	$teamlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND (fopenid = '{$fopenid}' OR openid = '{$fopenid}')");
	
	$alldailiprice = $allsaleprice = 0;
	foreach($teamlist as $k=>$v){
		$merchanthd = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND merchant_id = {$v['id']} AND hdid = {$hdid}");
		$teamorder = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE weid = {$_W['uniacid']} AND mhdid = {$merchanthd['id']}");
		$dailiprice = $saleprice = 0;
		foreach($teamorder as $kkk=>$vvv){
			$dailiprice += $vvv['dlprice']*$vvv['total'];
			$saleprice += $vvv['price']*$vvv['total'];
			$alldailiprice += $vvv['dlprice']*$vvv['total'];
			$allsaleprice += $vvv['price']*$vvv['total'];
		}
		$teamlist[$k]['dailiprice'] = $dailiprice;
		$teamlist[$k]['saleprice'] = $saleprice;
	}
	$teamjiang = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONGTEAMJIANG)." WHERE startmoney < {$alldailiprice} AND endmoney > {$alldailiprice} AND hdid = {$hdid} ORDER BY displayerorder ASC");
	if(empty($teamjiang)){
		$bili = 0;
	}else{
		$bili = $teamjiang['jiangli'];
	}
	
	$teamjiang2 = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONGTEAMJIANG)." WHERE startmoney < {$allsaleprice} AND endmoney > {$allsaleprice} AND hdid = {$hdid} ORDER BY displayerorder ASC");
	if(empty($teamjiang2)){
		$bili2 = 0;
	}else{
		$bili2 = $teamjiang2['jiangli'];
	}
	
	$allyj1 = ($alldailiprice*$bili)/100;
	$allyj2 = ($allsaleprice*$bili2)/100;
	
	$allyj11 = $allyj22 = 0;
	foreach($teamlist as $k=>$v){
		if($v['openid'] != $fopenid){
			$teamlist[$k]['jiang'] = ($v['dailiprice']*$bili)/100;
			$teamlist[$k]['jiang2'] = ($v['saleprice']*$bili2)/100;
			$allyj11 += $teamlist[$k]['jiang'];
			$allyj22 += $teamlist[$k]['jiang2'];
		}
	}
	
	$tzyj1 = $allyj1-$allyj11;
	$tzyj2 = $allyj2-$allyj22;
	include $this->template('web/teamjiang');
}
?>