<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://1024ok.cn/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);

if (!in_array($do, array('log'))) {
	exit('Access Denied');
}

if($do == 'log') {
	$tid = intval($_GPC['tid']);
	$module = trim($_GPC['module']);
	$type = trim($_GPC['type']);
	$data = pdo_getall('core_cron_record', array('uniacid' => $_W['uniacid'], 'tid' => $tid, 'module' => $module, 'type' => $type));
	if(!empty($data)) {
		foreach($data as &$da) {
			$da['createtime'] = date('Y-m-d H:i:s', $da['createtime']);
		}
		unset($da);
	}
	iajax(0, array('items' => $data));
}
