<?php
global $_W, $_GPC;
$member = $this->Mcheckmember();
$merchant = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$member['openid']}'");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	include $this->template('merchantteam');
}elseif($operation == 'xiaji'){
	if($merchant['istz'] == 0){
		$tuanzhang = pdo_fetch("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND openid = '{$merchant['fopenid']}'");
		$tuanzhangmember = pdo_fetch("SELECT * FROM ".tablename(BEST_MEMBER)." WHERE openid = '{$tuanzhang['openid']}'");
		$fopenid = $tuanzhang['openid'];
	}else{
		$fopenid = $merchant['openid'];
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND fopenid = '{$fopenid}' AND fopenid != ''");
	$psize = 10;
	$allpage = ceil($total/$psize)+1;
	$page = intval($_GPC["page"]);
	$pindex = max(1, $page);
	$branchlist = pdo_fetchall("SELECT * FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND fopenid = '{$fopenid}' AND fopenid != '' ORDER BY addtime DESC LIMIT ".($pindex - 1)*$psize.",".$psize);
	foreach($branchlist as $k=>$v){
		$branchlist[$k]['member'] = pdo_fetch("SELECT * FROM ".tablename(BEST_MEMBER)." WHERE openid = '{$v['openid']}'");
	}
	$isajax = intval($_GPC['isajax']);
	if($isajax == 1){
		$html = '';
		foreach($branchlist as $k=>$v){
			$html .= '<div class="item">
						<div class="img"><img src="'.$v['member']['avatar'].'" /></div>
						<div class="text">
							<div class="nickname">'.$v['realname'].'（'.$v['member']['nickname'].'）</div>
							<div class="time">加入时间：'.date("Y-m-d H:i:s",$v['addtime']).'</div>
						</div>
					</div>';
		}
		echo $html;
		exit;
	}
	include $this->template('merchantxiaji');
}elseif($operation == 'haibao'){
	$this->mkdirs(HB_ROOT_TD);
	$qrcodename = HB_ROOT_TD."{$member['openid']}.png";
	if(!file_exists($qrcodename)){
		include ROOT_PATH.'qrcode.class.php';    
		$value = $_W['siteroot'].'app/'.str_replace('./','',$this->createMobileUrl('merchant',array('op'=>'tuandui','fopenid'=>$member['openid']))); //二维码内容   
		$errorCorrectionLevel = 'L';//容错级别   
		$matrixPointSize = 6;//生成图片大小   
		//生成二维码图片
		QRcode::png($value,$qrcodename,$errorCorrectionLevel,$matrixPointSize,2); 
	}
	
	$filename = HB_ROOT_TD."{$member['openid']}-hb.jpg";
	if(!file_exists($filename)){
		$config = array(
		  'image'=>array(
			array(
			  'url'=>$qrcodename,     //二维码资源
			  'stream'=>0,			  
			  'left'=>$this->module['config']['tdleft'],
			  'top'=>-$this->module['config']['tdbottom'],
			  'right'=>0,
			  'bottom'=>0,
			  'width'=>180,
			  'height'=>180,
			  'opacity'=>100
			)
		  ),
		  'background'=>tomedia($this->module['config']['tdthumb']),         //背景图
		);
		
		$this->createPoster($config,$filename);
	}
	echo '<img src="'.$filename.'" />';
	exit;
}elseif($operation == 'paihang'){
	$message = "程序猿小伙伴正在日夜兼程赶工哈~~";
	include $this->template('error');
}
?>