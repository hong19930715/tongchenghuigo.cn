<?php
global $_W,$_GPC;
$openid = trim($_GPC['openid']);

$datahd['weid'] = $_W['uniacid'];
$datahd['time'] = TIMESTAMP;
$datahd['openid'] = $openid;
$datahd['nickname'] = trim($_GPC['nickname']);
$datahd['avatar'] = trim($_GPC['avatar']);
$datahd['title'] = $_GPC['title'];
$datahd['canziti'] = intval($_GPC['canziti']);
$datahd['cansh'] = intval($_GPC['cansh']);

if(empty($datahd['title'])){
	$this->result(1,"请填写接龙主题名称！", '');
}
$datahd['des'] = $_GPC['des'];
if(empty($datahd['des'])){
	$this->result(1,"请填写接龙主题介绍！", '');
}

$thumbs = $this->messistr2array($_GPC['thumbs']);
if(empty($thumbs)){
	$this->result(1,"请上传接龙主题图片！", '');
}


$thumbss = array();
foreach($thumbs as $k=>$v){
	$thumbss[] = $v['realpath'];
}


$datahd['thumbs'] = serialize($thumbss);
$datahd['starttime'] = strtotime($_GPC['starttime']);
$datahd['endtime'] = strtotime($_GPC['endtime']);
$datahd['telphone'] = $_GPC['telphone'];
$datahd['yunfei'] = $_GPC['yunfei'];
$datahd['manjian'] = $_GPC['manjian'];
if(empty($datahd['starttime']) || empty($datahd['endtime'])){	
	$this->result(1,"请选择接龙主题开始时间和截止时间！", '');
}
if($datahd['starttime'] > $datahd['endtime']){
	$this->result(1,"接龙主题开始时间不能大于截止时间！", '');
}
if(empty($datahd['telphone'])){
	$this->result(1,"请填写联系方式！", '');
}
if($datahd['canziti'] == 0 && $datahd['cansh'] == 0){	
	$this->result(1,"自提与送货必须选择至少选择其中一项！", '');
}


if($datahd['canziti'] == 1){
	$zitidian = pdo_fetch("SELECT id FROM ".tablename(BEST_ZITIDIAN)." WHERE openid = '{$openid}' AND weid = {$_W['uniacid']} AND ztdtype = 0");
	if(empty($zitidian)){
		$this->result(0,"您还没有创建自提点，不能选择支持自提。请至个人中心添加自提点！", 1);
	}
}

$goods = $this->messistr2array($_GPC['goods']);
foreach($goods as $k=>$v){
	if(empty($v['goodsname'])){
		$this->result(1,"请填写商品名称！".$v['total'], '');
	}
	if(empty($v['imgs'])){
		$this->result(1,"请上传商品图片！", '');
	}
	if($v['normalprice'] <= 0){
		$this->result(1,"请填写正确的商品价格！", '');
	}	

	$lastshuliang = 0;
	$lastprice = 0;
	foreach($v['jieti'] as $kk=>$vv){
		if(empty($vv['jietiprice']) || empty($vv['jietinum'])){			
			$this->result(1,"请填写完整的阶梯价格和阶梯数量！", '');
		}
		if($vv['jietinumstart'] != 0 && $kk == 0){
			$this->result(1,"第一个阶梯起始数量应该为0！", '');
		}
		if($vv['jietinumstart'] <= $lastshuliang){
			if($lastshuliang != 0){
				$this->result(1,"阶梯起始数量不能小于上一个阶梯的结束数量！", '');
			}
		}
		if($vv['jietiprice'] >= $lastprice && $lastshuliang != 0){
			$this->result(1,"阶梯价格不能大于等于上一个阶梯的价格！", '');
		}
		$lastshuliang = $vv['jietinum'];
		$lastprice = $vv['jietiprice'];
	}
	$total = $v['total'];
	if(empty($total)){
		$this->result(1,"请填写商品库存！", '');
	}
}
if($this->module['config']['iszfjlsh'] == 0){
	$datahd['status'] = 1;
}
$datahd['address'] = $_GPC['address'];
$datahd['jingdu'] = $_GPC['jingdu'];
$datahd['weidu'] = $_GPC['weidu'];
pdo_insert(BEST_MEMBERHUODONG,$datahd);
$hdid = pdo_insertid();
foreach($goods as $k=>$v){
	$data['weid'] = $_W['uniacid'];
	$data['openid'] = $openid;
	$data['title'] = $v['goodsname'];
	$data['normalprice'] = $v['normalprice'];
	$data['total'] = $v['total'];
	$data['optionname'] = $v['optionname'];
	$data['xiangounum'] = $v['xiangounum'];
	$data['createtime'] = TIMESTAMP;
	$data['hdid'] = $hdid;
	$gthumbs = array();
	foreach($v['imgs'] as $kk=>$vv){
		$gthumbs[] = $vv['realpath'];
	}
	$data['thumbs'] = serialize($gthumbs);
	pdo_insert(BEST_MEMBERGOODS,$data);
	$goodsid = pdo_insertid();

	foreach($v['jieti'] as $kkk=>$vvv){
		$datajt['goodsid'] = $goodsid;
		$datajt['jietiprice'] = $vvv['jietiprice'];
		$datajt['jietinumstart'] = $vvv['jietinumstart'];
		$datajt['jietinum'] = $vvv['jietinum'];
		$datajt['displayorder'] = $kkk;
		pdo_insert(BEST_MEMBERGOODSJIETI,$datajt);
	}
}
$this->result(0,"添加成功！", '');
?>