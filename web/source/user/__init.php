<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://1024ok.cn/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('registerset'))) {
	define('FRAME', 'site');
} else {
	define('FRAME', 'system');
}
