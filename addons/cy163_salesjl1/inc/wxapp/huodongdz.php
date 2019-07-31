<?php
global $_W,$_GPC;
$mhdid = intval($_GPC['id']);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	$merchanthd = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND id = {$mhdid}");
	$hdres = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} AND id = {$merchanthd['hdid']}");
	$hdres['sharedes'] = $merchanthd["sharedes"];
	if($merchanthd['sharetitle']){
		$hdres['title'] = $merchanthd['sharetitle'];
	}
	$hdres['sharethumb'] = tomedia($merchanthd['sharethumb']);

	$hdgoods = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHDGOODS)." WHERE mhdid = {$mhdid} AND weid = {$_W['uniacid']}");
	$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} AND id = {$hdgoods['goodsid']}");
	$goodsres['sales'] = pdo_fetchcolumn("SELECT SUM(total) FROM ".tablename(BEST_ORDERGOODS)." WHERE goodsid = {$goodsres['id']} AND mhdid = {$mhdid}");
	$goodsres['sales'] = empty($goodsres['sales']) ? 0 : $goodsres['sales'];
	$goodsres['thumb'] = tomedia($goodsres['thumb']);
	
	$thumbs = unserialize($goodsres['thumbs']);
	$thumbss = array();
	foreach($thumbs as $k=>$v){
		$thumbss[] = tomedia($v);
	}

	$buylistthree = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDER)." WHERE mhdid = {$merchanthd['id']} AND isjl = 0 AND status >= 1 ORDER BY createtime DESC LIMIT 10");
	foreach($buylistthree as $k=>$v){
		$buylistthree[$k]['member'] = pdo_fetch("SELECT avatar,nickname FROM ".tablename(BEST_MEMBER)." WHERE openid = '{$v['from_user']}'");
		$buylistthree[$k]['goods'] = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE orderid = {$v['id']}");
		$buylistthree[$k]['time'] = date("Y-m-d H:i:s",$v['createtime']);
		$buylistthree[$k]['allprice'] = $v['price']*$v['total'];
	}
	
	$hdres['mhdid'] = $mhdid;
	$res['hdres'] = $hdres;
	$res['buylistthree'] = $buylistthree;
	$res['goodsres'] = $goodsres;
	$res['thumbss'] = $thumbss;
	$this->result(0,"活动页", $res);
}elseif($operation == 'getsub'){
	$mhdid = intval($_GPC['mhdid']);
	$merchanthd = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND id = {$mhdid}");
	$goodsid = intval($_GPC['goodsid']);
	$goodsres = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE weid = {$_W['uniacid']} AND id = {$goodsid}");
	$goodsres['thumb'] = tomedia($goodsres['thumb']);
	$goodsres['nownum'] = 0;
	
	$openid = trim($_GPC['openid']);
	$member = pdo_fetch("SELECT * FROM ".tablename(BEST_MEMBER)." WHERE openid = '{$openid}'");
	if($member['shcity'] != ''){
		$member['shcity'] = explode(",",$member['shcity']);
	}
	
	$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND id = {$merchanthd['merchant_id']}");	
	$ztdlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_ZITIDIAN)." WHERE openid = '{$merchant['openid']}' AND ztdtype = 1");
	
	$hdres = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} AND id = {$merchanthd['hdid']}");
	if($hdres['pstype'] == 1){
		$hdres['cansonghuo'] = $merchanthd['cansonghuo'];
		$hdres['canziti'] = $merchanthd['canziti'];
		$hdres['manjian'] = $merchanthd['manjian'];
		$hdres['candmfk'] = $merchanthd['candmfk'];
	}
	if($hdres['canziti'] == 1 && $hdres['cansonghuo'] == 0){
		$hdres['ztclass1'] = 'now';
		$hdres['ztclass2'] = '';
		$hdres['ztclass3'] = '';
		$hdres['ztclass4'] = 'hide';
		$hdres['pstype'] = 1;
	}else{
		$hdres['ztclass1'] = '';
		$hdres['ztclass2'] = 'hide';
		$hdres['ztclass3'] = 'now';
		$hdres['ztclass4'] = '';
		$hdres['pstype'] = 0;
	}
	if($hdres['cansonghuo'] == 1){
		$hdres['shzt'] = '满'.$hdres['manjian'].'元免运费';
	}else{
		$hdres['shzt'] = '免运费';
	}
	$hdres['iserror'] = 0;
	if($hdres['tqjs'] == 1){
		$hdres['iserror'] = 1;
		$hdres['errormessage'] = '活动已经结束';
	}
	if($hdres['starttime'] > TIMESTAMP){
		$hdres['iserror'] = 1;
		$hdres['errormessage'] = '活动还未开始';
	}
	if($hdres['endtime'] < TIMESTAMP){
		$hdres['iserror'] = 1;
		$hdres['errormessage'] = '活动已经结束';
	}
	
	$res['hdres'] = $hdres;
	$res['member'] = $member;
	$res['ztdlist'] = $ztdlist;
	$res['goodsres'] = $goodsres;
	$res['merchanthd'] = $merchanthd;
	$this->result(0,"活动页", $res);
}elseif($operation == 'sub'){
	$member['openid'] = trim($_GPC['openid']);
	$allnum = 0;
	$allprice = 0;
	$alllirun = 0;
	$hdres = pdo_fetch("SELECT * FROM ".tablename(BEST_HUODONG)." WHERE weid = {$_W['uniacid']} AND id = {$_GPC['hdid']}");
	if($hdres['tqjs'] == 1){
		$this->result(1,"活动已经提前结束！", "");
	}
	if($hdres['starttime'] > TIMESTAMP){
		$this->result(1,"活动还未开始！", "");
	}
	if($hdres['endtime'] < TIMESTAMP){
		$this->result(1,"活动已经结束！", "");
	}
	$pstype = intval($_GPC['pstype']);
	if($pstype != 0 && $pstype != 1){
		$this->result(1,"请选择配送方式！", "");
	}
	
	$goodsres = $this->messistr2array($_GPC['goods']);
	if($goodsres['nownum'] > 0){
		if($goodsres['nownum'] > $goodsres['total']){
			$this->result(1,"商品".$goodsres['title']."库存不足！", "");
		}
		$allprice += $goodsres['normalprice']*$goodsres['nownum'];
		$alllirun += ($goodsres['normalprice']-$goodsres['dailiprice'])*$goodsres['nownum'];
		$allnum += $goodsres['nownum'];
	}
	if($allnum <= 0){		
		$this->result(1,"请选择需要购买的商品！", "");
	}
	if($allprice <= 0){
		$this->result(1,"订单总金额不得少于0元！", "");
	}
	$data['price'] = $allprice;
	$data['alllirun'] = $alllirun;
	if($pstype == 0){
		$shname = trim($_GPC['shname']);
		$shphone = trim($_GPC['shphone']);
		$shcity = trim($_GPC['shcity']);
		$shaddress = trim($_GPC['shaddress']);
		if(empty($shname)){
			$this->result(1,"请填写收货人姓名！", "");
		}
		if(!$this->isMobile($shphone)){
			$this->result(1,"请填写正确的收货人手机号码！", "");
		}
		if(empty($shcity)){
			$this->result(1,"请选择地区！", "");
		}
		if(empty($shaddress)){
			$this->result(1,"请填写详细地址！", "");
		}
		if($hdres['pstype'] == 0){
	
			$diquarr = explode(",",$shcity);
			$sheng = $diquarr[0];
			$shi = $diquarr[1];
			$xian = $diquarr[2];
			$yfsheng1 = pdo_fetch("SELECT * FROM ".tablename(BEST_YUNFEISHENG)." WHERE yfid = {$hdres['yfid']} AND diqutype = 3 AND name = '{$sheng}' AND city = '{$shi}' AND xian = '{$xian}'");
			$yfsheng2 = pdo_fetch("SELECT * FROM ".tablename(BEST_YUNFEISHENG)." WHERE yfid = {$hdres['yfid']} AND diqutype = 2 AND name = '{$sheng}' AND city = '{$shi}' AND xian = ''");
			$yfsheng3 = pdo_fetch("SELECT * FROM ".tablename(BEST_YUNFEISHENG)." WHERE yfid = {$hdres['yfid']} AND diqutype = 1 AND name = '{$sheng}' AND city = '' AND xian = ''");
			if(empty($yfsheng1) && empty($yfsheng2) && empty($yfsheng3)){
				$this->result(1,"不在活动售卖区域不能提交订单！", "");
			}

			if($data['price'] >= $hdres['manjian']){
				$data['yunfei'] = 0;
			}else{
				if(!empty($yfsheng1)){
					$data['yunfei'] = $yfsheng1['money'];
				}
				if(!empty($yfsheng2) && empty($yfsheng1)){
					$data['yunfei'] = $yfsheng2['money'];
				}
				if(!empty($yfsheng3) && empty($yfsheng1) && empty($yfsheng2)){
					$data['yunfei'] = $yfsheng3['money'];
				}
			}
			$data['remark'] = $_GPC['remark'];
		}else{
			$merchanthd = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANTHD)." WHERE weid = {$_W['uniacid']} AND id = {$_GPC['mhdid']}");
			if($data['price'] >= $merchanthd['manjian']){
				$data['yunfei'] = 0;
			}else{
				$data['yunfei'] = $merchanthd['yunfei'];
			}
			$pstype = 3;
			$data['alllirun'] = $data['alllirun'] + $data['yunfei'];
			$data['remark'] = $_GPC['remark2'];
		}
		$datam['shname'] = $shname;
		$datam['shphone'] = $shphone;
		$datam['shcity'] = $shcity;
		$datam['shaddress'] = $shaddress;
		$data['address'] = $shname."|".$shphone."|".$shcity."|".$shaddress;
		$data['price'] = $data['price']+$data['yunfei'];
		$data['isdmfk'] = intval($_GPC['isdmfk']);
		if($data['isdmfk'] == 1){
			$data['status'] = 2;
		}
	}else{
		$shphone = trim($_GPC['shphone2']);
		if(!$this->isMobile($shphone)){
			$this->result(1,"请填写自提所需的手机号码！", "");
		}
		$ztdid = intval($_GPC['ztdid']);
		if(empty($ztdid)){
			$this->result(1,"请选择自提点！", "");
		}
		$data['ztdid'] = $ztdid;
		$data['address'] = $datam['shphone'] = $shphone;
		$data['yunfei'] = 0;
		if($hdres['pstype'] == 1){
			$pstype = 4;
		}
		$data['isdmfk'] = intval($_GPC['isdmfk']);
		if($data['isdmfk'] == 1){
			$data['status'] = 1;
		}
	}
	if($hdres['autofield'] == 1){
		$isidcard = $this->validation_filter_id_card($_GPC['idcard']);
		if(empty($isidcard)){
			$this->result(1,"请填写正确的身份证号！", "");
		}
		$data['otheraddress'] = $_GPC['idcard']."(身份证)";
	}
	if($hdres['autofield'] == 2){
		if(empty($_GPC['wxcode'])){
			$this->result(1,"请填写微信号！", "");
		}
		$data['otheraddress'] = $_GPC['wxcode']."(微信号)";
	}	
	$data['weid'] = $_W['uniacid'];
	$data['pstype'] = $pstype;
	$data['from_user'] = $member['openid'];
	$data['ordersn'] = date('Ymd').random(13,1);
	$data['merchant_id'] = intval($_GPC['merchant_id']);
	$data['createtime'] = TIMESTAMP;
	$data['hdid'] = intval($_GPC['hdid']);
	$data['mhdid'] = intval($_GPC['mhdid']);
	if(isset($this->module['config']['dltkhour'])){
		$data['cantktime'] = $hdres['endtime'] - ($this->module['config']['dltkhour'])*3600;
	}
	pdo_insert(BEST_ORDER, $data);
	$orderid = pdo_insertid();

	pdo_update(BEST_MEMBER,$datam,array('openid'=>$member['openid']));	
	if($goodsres['nownum'] > 0){
		$datao['weid'] = $_W['uniacid'];
		$datao['total'] = $goodsres['nownum'];
		$datao['price'] = $goodsres['normalprice'];
		$datao['cbprice'] = $goodsres['chengbenprice'];
		$datao['dlprice'] = $goodsres['dailiprice'];
		$datao['goodsid'] = $goodsres['id'];
		$datao['createtime'] = TIMESTAMP;
		$datao['orderid'] = $orderid;
		$datao['goodsname'] = $goodsres['title'];
		$datao['lirun'] = ($goodsres['normalprice']-$goodsres['dailiprice'])*$goodsres['nownum'];
		$datao['hdid'] = intval($_GPC['hdid']);
		$datao['mhdid'] = intval($_GPC['mhdid']);
		pdo_insert(BEST_ORDERGOODS,$datao);
	}
	if($data['isdmfk'] == 1){
		$this->result(0,"提交订单成功！", '');
	}else{
		$this->result(0,"提交订单成功！", $data['ordersn']);
	}
}elseif($operation == 'tongzhi'){
	$pid = trim($_GPC['pid']);
	$pidarr = explode('=',$pid);
	$ordersn = trim($_GPC['ordersn']);
	$orderres = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE weid = {$_W['uniacid']} AND ordersn = '{$ordersn}' AND isjl = 0");
	$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND id = {$orderres['merchant_id']}");
	if($this->module['config']['istplon'] == 1 && !empty($this->module['config']['nt_order_new'])){
		$temvalue = array(
			"keyword1"=>array(
				"value"=>$orderres['ordersn'],
				"color"=>"#4a4a4a"
			),
			"keyword2"=>array(
				"value"=>$orderres['price'],
				"color"=>"#9b9b9b"
			),
			"keyword3"=>array(
				"value"=>date("Y-m-d H:i:s",$orderres['createtime']),
				"color"=>"#9b9b9b"
			),
			"keyword4"=>array(
				"value"=>$orderres['address'],
				"color"=>"#9b9b9b"
			)
		);
		
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;
		$dd = array();
		$dd['touser'] = $merchant['openid'];
		$dd['template_id'] = $this->module['config']['nt_order_new'];
		$dd['page'] = 'cy163_salesjl/pages/merorderdetail/merorderdetail?id='.$orderres['id']; //点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,该字段不填则模板无跳转。
		$dd['form_id'] = $pidarr[1];
		$dd['data'] = $temvalue;                        //模板内容，不填则下发空模板
		$dd['color'] = '';                        //模板内容字体的颜色，不填默认黑色
		$dd['emphasis_keyword'] = '';    //模板需要放大的关键词，不填则默认无放大
		//$dd['emphasis_keyword']='keyword1.DATA';
		//$send = json_encode($dd);   //二维数组转换成json对象
		
		/* curl_post()进行POST方式调用api： api.weixin.qq.com*/
		$result = $this->https_curl_json($url,$dd,'json');
		/*if($result){
			echo json_encode(array('state'=>5,'msg'=>$result));
		}else{
			echo json_encode(array('state'=>5,'msg'=>$result));
		}*/
	}
	$this->result(0,"发送成功！", '');
}
?>