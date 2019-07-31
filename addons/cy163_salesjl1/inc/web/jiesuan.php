<?php
global $_GPC, $_W;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {	
	$keyword = trim($_GPC['keyword']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$starttime  = $_GPC['starttime'];
	$endtime  = $_GPC['endtime'];
	if (empty($starttime)) {
		$starttime = strtotime('-1 year');
	}else{
		$starttime = strtotime($_GPC['starttime']);
	}
	if (empty($endtime)) {
		$endtime = TIMESTAMP;
	}else{
		$endtime = strtotime($_GPC['endtime']);
	}
	$status = $_GPC['status'];
	if($status == ''){
		$status = 100;
	}
	if($status == 100){
		$condition = "weid = {$_W['uniacid']} AND isjl = 0 AND createtime >= {$starttime} AND createtime <= {$endtime} AND status >= 0 ";
	}else{
		$condition = "weid = {$_W['uniacid']} AND isjl = 0 AND createtime >= {$starttime} AND createtime <= {$endtime} AND status = {$status} ";
	}
	if (!empty($_GPC['merkeyword'])) {
		$merchant = pdo_fetch("SELECT id FROM ".tablename(BEST_MERCHANT)." WHERE weid = {$_W['uniacid']} AND (name like '%{$_GPC['merkeyword']}%' OR realname like '%{$_GPC['merkeyword']}%' OR telphone like '%{$_GPC['merkeyword']}%')");
		$condition .= " AND merchant_id = {$merchant['id']}";
	}

	$alllirun = pdo_fetchcolumn("SELECT SUM(alllirun) FROM ".tablename(BEST_ORDER)." WHERE ".$condition);
	$alllirun = empty($alllirun) ? 0 : $alllirun;
	$allprice = pdo_fetchcolumn("SELECT SUM(price) FROM ".tablename(BEST_ORDER)." WHERE ".$condition);
	$allprice = empty($allprice) ? 0 : $allprice;
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(BEST_ORDER)." WHERE ".$condition);
	$total = empty($total) ? 0 : $total;
	if ($total > 0) {
		if ($_GPC['export'] == '') {
			$limit = ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		}else{
			$limit = '';
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDER)." WHERE ".$condition." ORDER BY createtime DESC".$limit);
		foreach($list as $k=>$v){
			$list[$k]['goodslist'] = pdo_fetchall("SELECT * FROM ".tablename(BEST_ORDERGOODS)." WHERE orderid = {$v['id']}");
			$list[$k]['merchant'] = pdo_fetch("SELECT name FROM ".tablename(BEST_MERCHANT)." WHERE id = {$v['merchant_id']}");
		}
		$pager = pagination($total, $pindex, $psize);
		if ($_GPC['export'] == 'export') {
			/* 输入到CSV文件 */
			$html = "\xEF\xBB\xBF";
			/* 输出表头 */
			$filter = array('订单号','商品名称','数量','成本价','代理价','销售价','利润','佣金','订单状态','下单时间','代理商家');
			foreach ($filter as $key => $title) {
				$html .= $title . "\t,";
			}
			$html .= "\n";
			$alltotal = 0;
			$allcbprice = 0;
			$alldlprice = 0;
			$allprice = 0;
			$alllirun = 0;
			$allyongjin = 0;
			foreach ($list as $k => $v) {
				foreach($v['goodslist'] as $kk=>$vv){
					$alltotal += $vv['total'];
					$allcbprice += $vv['cbprice']*$vv['total'];
					$alldlprice += $vv['dlprice']*$vv['total'];
					$allprice += $vv['price']*$vv['total'];
					$alllirun += $vv['dlprice']*$vv['total']-$vv['cbprice']*$vv['total'];
					$allyongjin += $vv['lirun'];
				
					$html .= $v['ordersn']. "\t, ";
					$html .= empty($vv['optionname']) ? $vv['goodsname']. "\t, " : $vv['goodsname']."[".$vv['optionname']."]". "\t, ";
					$html .= $vv['total']. "\t, ";
					$html .= $vv['cbprice']*$vv['total']. "\t, ";
					$html .= $vv['dlprice']*$vv['total']. "\t, ";
					$html .= $vv['price']*$vv['total']. "\t, ";
					$html .= $vv['dlprice']*$vv['total']-$vv['cbprice']*$vv['total']. "\t, ";
					$html .= $vv['lirun']. "\t, ";
					if($v['status'] == 0){
						$html .= "未付款". "\t, ";
					}
					if($v['status'] == 1){
						$html .= "已付款". "\t, ";
					}
					if($v['status'] == 2){
						$html .= "待收货". "\t, ";
					}
					if($v['status'] == 4){
						$html .= "已完成". "\t, ";
					}
					$html .= date("Y-m-d H:i:s",$v['createtime']). "\t, ";
					$html .= $v['merchant']['name']. "\t, ";
					$html .= "\n";
				}
			}
			$html .= "". "\t, ";
			$html .= "". "\t, ";
			$html .= $alltotal. "\t, ";
			$html .= $allcbprice. "\t, ";
			$html .= $alldlprice. "\t, ";
			$html .= $allprice. "\t, ";
			$html .= $alllirun. "\t, ";
			$html .= $allyongjin. "\t, ";
			$html .= "". "\t, ";
			$html .= "". "\t, ";
			$html .= "". "\t, ";
			$html .= "\n";
			/* 输出CSV文件 */
			header("Content-type:text/csv");
			header("Content-Disposition:attachment; filename=结算数据.csv");
			echo $html;
			exit();
		}		
	}
	include $this->template('web/jiesuan');
}
?>