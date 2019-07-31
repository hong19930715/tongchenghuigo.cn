<?php
global $_W, $_GPC;
$express = array(
	99=>array(
		'pinyin'=>'ZFPS',
		'value'=>'自发配送',
	),
	0=>array(
		'pinyin'=>'SF',
		'value'=>'顺丰速运',
	),
	1=>array(
		'pinyin'=>'HTKY',
		'value'=>'百世快递',
	),
	2=>array(
		'pinyin'=>'ZTO',
		'value'=>'中通快递',
	),
	3=>array(
		'pinyin'=>'STO',
		'value'=>'申通快递',
	),
	4=>array(
		'pinyin'=>'YTO',
		'value'=>'圆通速递',
	),
	5=>array(
		'pinyin'=>'YD',
		'value'=>'韵达速递',
	),
	6=>array(
		'pinyin'=>'YZPY',
		'value'=>'邮政快递包裹',
	),
	7=>array(
		'pinyin'=>'EMS',
		'value'=>'EMS',
	),
	8=>array(
		'pinyin'=>'HHTT',
		'value'=>'天天快递',
	),
	9=>array(
		'pinyin'=>'JD',
		'value'=>'京东物流',
	),
	10=>array(
		'pinyin'=>'QFKD',
		'value'=>'全峰快递',
	),
	11=>array(
		'pinyin'=>'GTO',
		'value'=>'国通快递',
	),
	12=>array(
		'pinyin'=>'UC',
		'value'=>'优速快递',
	),
	13=>array(
		'pinyin'=>'DBL',
		'value'=>'德邦',
	),
	14=>array(
		'pinyin'=>'FAST',
		'value'=>'快捷快递',
	),
	15=>array(
		'pinyin'=>'ZJS',
		'value'=>'宅急送',
	),
);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$status = $_GPC['status'];
	if($status == ''){
		$status = 100;
	}

	$condition = "weid = {$_W['uniacid']} and aid !='' ";
	
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND ordersn LIKE '%{$_GPC['keyword']}%'";
	}
	if ($status != 100) {
		$condition .= " AND status = {$status}";
	}
	$sql = 'SELECT COUNT(*) FROM '.tablename(BEST_shoporder).' WHERE '.$condition;
	$total = pdo_fetchcolumn($sql);

	if ($total > 0) {
		if ($_GPC['export'] == '') {
			$limit = ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		}else{
			$limit = '';
		}
		$sql = 'SELECT * FROM ' . tablename(BEST_shoporder) . ' WHERE ' . $condition . ' ORDER BY `addtime` DESC ' . $limit;
		$list = pdo_fetchall($sql);

		foreach ($list as $k => $v){
		    
		    $adds = pdo_fetch("SELECT * FROM ".tablename(BEST_shopaddr)." WHERE id ='".$v['aid']."'");

		    $province = $adds['province'];
		    $city = $adds['city'];
		    $area= $adds['area'];
		    $detailaddr = $adds['detailaddr'];
		    $address = $province .$city . $area .$detailaddr;
		    
		    $list[$k]['address'] = $address;
		    $list[$k]['phone'] = $adds['phone'];
		    
	   
		    //多产品进入
		    if(!empty($v['jsCartInfo'])){
		        
		        $jsCartInfo = json_decode(htmlspecialchars_decode($v['jsCartInfo']),true);
		        
		        foreach ($jsCartInfo as $k4 => $v4)
		        {
		            if($v4['attr_id'] != 0){
		                $attrvalue = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = '".$v4['attr_id']."'");
		                $jsCartInfo[$k4]['attrvalue'] = $attrvalue;
		            }
		            if($v4['pro_id'] != ''){
		                $pro = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '".$v4['pro_id']."'");
		                $jsCartInfo[$k4]['product'] = $pro;
		            }
		            
		        }
		        $shopaddr = pdo_fetch("SELECT * FROM " . tablename(BEST_shopaddr) . " WHERE id = '".$res['aid']."'");
		        $list[$k]['jsCartInfo'] = $jsCartInfo;
		        $list[$k]['shopaddr'] = $shopaddr;
		    }

		    
		    
		}
		
        //多商品处理
		
		$pager = pagination($total, $pindex, $psize);

// 		if ($_GPC['export'] == 'export') {
// 			/* 输入到CSV文件 */
// 			$html = "\xEF\xBB\xBF";
// 			/* 输出表头 */
// 			$filter = array('订单号','姓名','电话','商品','数量','单价','总价','下单时间','收货地址');
// 			foreach ($filter as $key => $title) {
// 				$html .= $title . "\t,";
// 			}
// 			$html .= "\n";

// 			foreach ($list as $k => $v) {
			    
// 			    $html .= $v['ordersn']. "\t, ";
// 			    $uinfo =  pdo_fetch("SELECT * FROM ".tablename(BEST_MEMBER)." WHERE id = {$v['uid']} AND weid = {$_W['uniacid']}");
// 			    $html .= $uinfo['nickname']. "\t, ";
// 			    $html .= $v['phone']. "\t, ";
// 			    if(!empty($v['pro_id'])){
// 			        $product = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE id = {$v['pro_id']} AND weid = {$_W['uniacid']}");
// 			    }
			  
// 			    $html .= $product['title']. "\t, ";
// 			    $html .= $v['nums']. "\t, ";
// 			    $html .= $product['normalprice']. "\t, ";
// 			    $html .= $v['allmoney']. "\t, ";
			    
// 			    $addtime = date('Y-m-d H:i:s',$v['addtime']);
// 			    $html .= $addtime. "\t, ";
// 			    $html .= $v['address']. "\t, ";
// 				$html .= "\n";
			    
// 			}
// 			/* 输出CSV文件 */
// 			header("Content-type:text/csv");
// 			header("Content-Disposition:attachment; filename=商城订单数据.csv");
// 			echo $html;
// 			exit();
// 		}
		
	}	
}elseif ($operation == 'jiesuanall') {
	if (!empty($_GPC['id'])) {
		foreach ($_GPC['id'] as $key => $id) {
			$plorder = pdo_fetch("SELECT * FROM ".tablename(BEST_ORDER)." WHERE id = {$id} AND status = 2 AND isjl = 1");
			if(!empty($plorder)){
				pdo_update(BEST_ORDER, array('status' => 4), array('id' => $plorder['id']));
				if($plorder['isdmfk'] == 0){					
					$hasmemberaccount = pdo_fetch("SELECT id FROM ".tablename(BEST_MEMBERACCOUNT)." WHERE openid = '{$plorder['jlopenid']}' AND orderid = {$plorder['id']}");
					if(empty($hasmemberaccount)){
						$datamoney['weid'] = $_W['uniacid'];
						$datamoney['openid'] = $plorder['jlopenid'];
						$datamoney['money'] = $plorder['price'];
						$datamoney['time'] = TIMESTAMP;
						$datamoney['orderid'] = $plorder['id'];
						$datamoney['explain'] = "接龙订单";
						$datamoney['candotime'] = TIMESTAMP + ($this->module['config']['zftxhour'])*3600;
						pdo_insert(BEST_MEMBERACCOUNT,$datamoney);
					}
				}
			}
		}
		message('批量结算订单成功！', referer(), 'success');
	}else{
		message('请选择订单进行操作！');
	}
} elseif ($operation == 'docancel') {
	if (!empty($_GPC['id'])) {
		foreach ($_GPC['id'] as $key => $id) {
			$plorder = pdo_fetch("SELECT id FROM ".tablename(BEST_ORDER)." WHERE id = {$id} AND status = 0 AND isjl = 1");
			if(!empty($plorder)){
				pdo_update(BEST_ORDER, array('status' => -1), array('id' => $plorder['id']));
			}
		}
		message('批量取消订单成功！', referer(), 'success');
	}else{
		message('请选择订单进行操作！');
	}
}elseif ($operation == 'dodelete') {
	if (!empty($_GPC['id'])) {
		foreach ($_GPC['id'] as $key => $id) {
			$plorder = pdo_fetch("SELECT id FROM ".tablename(BEST_ORDER)." WHERE id = {$id} AND status = -1 AND isjl = 1");
			if(!empty($plorder)){
				pdo_delete(BEST_ORDER, array('id' => $plorder['id']));
				pdo_delete(BEST_ORDERGOODS, array('orderid' => $plorder['id']));
			}
		}
		message('批量删除订单成功！', referer(), 'success');
	}else{
		message('请选择订单进行操作！');
	}
}elseif ($operation == 'detail') {
    
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM ".tablename(BEST_shoporder)." WHERE id = {$id} AND weid = {$_W['uniacid']}");
	if (empty($item)) {
		message("抱歉，订单不存在!", referer(), "error");
	}	

     if(!empty($item['pro_id'])){
	    $item['product'] = pdo_fetch("SELECT * FROM ".tablename(BEST_GOODS)." WHERE id = {$item['pro_id']} AND weid = {$_W['uniacid']}");
     }
     
     if(!empty($item['jsCartInfo'])){
         $jsCartInfo = json_decode(htmlspecialchars_decode($item['jsCartInfo']),true);
         
         foreach ($jsCartInfo as $k4 => $v4)
         {
             if($v4['attr_id'] != 0){
                 $attrvalue = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = '".$v4['attr_id']."'");
                 $jsCartInfo[$k4]['attrvalue'] = $attrvalue;
             }
             
             $pros = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '".$v4['pro_id']."'");
            
             $jsCartInfo[$k4]['product'] = $pros;
             
         }
         $shopaddr = pdo_fetch("SELECT * FROM " . tablename(BEST_shopaddr) . " WHERE id = '".$v['aid']."'");
         $item['jsCartInfo'] = $jsCartInfo;
         $item['shopaddr'] = $shopaddr;
     }
     

	
	$item['user'] =  pdo_fetch("SELECT * FROM ".tablename(BEST_MEMBER)." WHERE id = {$item['uid']} AND weid = {$_W['uniacid']}");
	$item['address'] =  pdo_fetch("SELECT * FROM ".tablename(BEST_shopaddr)." WHERE id = {$item['aid']} AND weid = {$_W['uniacid']}");

	
}elseif ($operation == 'yifahuo'){
    
    $id = intval($_GPC['id']);
    
    $res = pdo_update(BEST_shoporder, array('state' => 3), array('id' => $id));
    if($res){
        message('发货成功！', referer(), 'success');
    }
    
}

include $this->template('web/scorder');
?>