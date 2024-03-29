<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://1024ok.cn/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('article');
load()->model('user');

$dos = array( 'detail', 'list');
$do = in_array($do, $dos) ? $do : 'list';

if($do == 'detail') {
	$id = intval($_GPC['id']);
	$notice = article_notice_info($id);
	if(is_error($notice)) {
		itoast('公告不存在或已删除', referer(), 'error');
	}
	$_W['page']['title'] = $notice['title'] . '-公告列表';
	
	pdo_update('article_notice', array('click +=' => 1), array('id' => $id));

	if(!empty($_W['uid'])) {
		pdo_update('article_unread_notice', array('is_new' => 0), array('notice_id' => $id, 'uid' => $_W['uid']));
	}
	$title = $notice['title'];
}

if($do == 'list') {
	$_W['page']['title'] = '-新闻列表';
	$categroys = article_categorys('notice');
	$categroys[0] = array('title' => '所有公告');

	$cateid = intval($_GPC['cateid']);
	$_W['page']['title'] = $categroys[$cateid]['title'] . '-公告列表';

	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$filter = array('cateid' => $cateid);
	$notices = article_notice_all($filter, $pindex, $psize);
	$total = intval($notices['total']);
	$data = $notices['notice'];
	$pager = pagination($total, $pindex, $psize);
}

template('article/notice-show');