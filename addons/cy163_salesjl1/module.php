<?php
/**
 * 模块定义
 * 
 */
defined('IN_IA') or exit('Access Denied');

class Cy163_salesjlModule extends WeModule {

    public function settingsDisplay($settings) {  
        global $_GPC,$_FILES, $_W;  
        
        if (checksubmit()) {
            $cfg = array(
				'iszfjlsh'=>intval($_GPC['iszfjlsh']),
				'agentreg'=>intval($_GPC['agentreg']),
				'agentregtype'=>intval($_GPC['agentregtype']),
				'isagentshow'=>intval($_GPC['isagentshow']),
				'temcolor' => $_GPC['temcolor'],
				
				'kms'=>$_GPC['kms'],
				'mapkey'=>$_GPC['mapkey'],
				
				'dltxhour' => $_GPC['dltxhour'],
				'dltkhour' => $_GPC['dltkhour'],

				'zftxhour' => $_GPC['zftxhour'],
				
				'fbthumb' => $_GPC['fbthumb'],
				'loadthumb' => $_GPC['loadthumb'],
				
				'certify_api' => $_GPC['certify_api'],
				'certify_key' => $_GPC['certify_key'],
                'mchid' => $_GPC['mchid'],
                'appid' => $_GPC['appid'],
                'key' => $_GPC['key'],
                'ip' => $_GPC['ip'],
				
				'indextitle' => $_GPC['indextitle'],
				'sharetitle' => $_GPC['sharetitle'],
				'sharethumb' => $_GPC['sharethumb'],
				'sharedes' => $_GPC['sharedes'],
				
				'istplon'=>intval($_GPC['istplon']),
				'nt_order_new' => $_GPC['nt_order_new'],// 订单消息通知
				'tpl_or_fahuo' => $_GPC['tpl_or_fahuo'],// 发货通知
				'agent_tz' => $_GPC['agent_tz'],// 
				'huodong_tz' => $_GPC['huodong_tz'],// 
				'rutuan_tz' => $_GPC['rutuan_tz'],
				'daohuo_tz' => $_GPC['daohuo_tz'],
				
				'present_money' => $_GPC['present_money'],
                'present_money_end' => $_GPC['present_money_end'],
                'txdisaccount' => $_GPC['txdisaccount'],
				'usertxdisaccount' => $_GPC['usertxdisaccount'],
				'refundstock' => $_GPC['refundstock'],
				
				'kdnid' => $_GPC['kdnid'],
				'kdnkey' => $_GPC['kdnkey'],
				
				'gerenfee' => $_GPC['gerenfee'],
				'qiyefee' => $_GPC['qiyefee'],
				'renzhengsm' => $_GPC['renzhengsm'],
				'rztixian'=>intval($_GPC['rztixian']),
				
				'tdtitle' => $_GPC['tdtitle'],
				'tddes' => $_GPC['tddes'],
				'tuanzhangfee' => $_GPC['tuanzhangfee'],
				
				'advon1'=>intval($_GPC['advon1']),
				'advon2'=>intval($_GPC['advon2']),
				'advon3'=>intval($_GPC['advon3']),
				'adv1'=>trim($_GPC['adv1']),
				'adv2'=>trim($_GPC['adv2']),
				'adv3'=>trim($_GPC['adv3']),
				'adv1url'=>trim($_GPC['adv1url']),
				'adv2url'=>trim($_GPC['adv2url']),
				'adv3url'=>trim($_GPC['adv3url']),
				'adv1appid'=>trim($_GPC['adv1appid']),
				'adv2appid'=>trim($_GPC['adv2appid']),
				'adv3appid'=>trim($_GPC['adv3appid']),
				
				'gqzfjlzs'=>intval($_GPC['gqzfjlzs']),
				
				'qrshhour1'=>intval($_GPC['qrshhour1']),
				'qrshhour2'=>intval($_GPC['qrshhour2']),
				
				'dodatahf'=>trim($_GPC['dodatahf']),
				
				'basicjlnum'=>intval($_GPC['basicjlnum']),
				'basicinpoeple'=>intval($_GPC['basicinpoeple']),
				'basicviews'=>intval($_GPC['basicviews']),
				
				'tdthumb' => $_GPC['tdthumb'],
				'tdleft' => $_GPC['tdleft'],
				'tdbottom' => $_GPC['tdbottom'],
				
				'zfjlthumb' => $_GPC['zfjlthumb'],
				'zfjlleft' => $_GPC['zfjlleft'],
                'zfjlbottom' => $_GPC['zfjlbottom'],
                'dianming' => $_GPC['dianming'],//店名
                'sckaiqi' => $_GPC['sckaiqi'],  //商城是否在前端显示字段
            );
			
			if($_W['siteroot'] == 'https://www.aaa.com/'){
				$cfg['teshumid'] = intval($_GPC['teshumid']);
			}
			
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
        load()->func('tpl');
		include $this->template('setting');
    }
}