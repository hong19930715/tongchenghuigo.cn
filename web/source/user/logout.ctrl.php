<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://1024ok.cn/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
isetcookie('__session', '', -10000);
isetcookie('__switch', '', -10000);

$forward = $_GPC['forward'];
if (empty($forward)) {
	$forward = './?refersh';
}

header('Location:' . $_W['siteroot']);