<?php

//decode by http://www.yunlu99.com/
defined('IN_IA') or exit('Access Denied');
define('STATIC_ROOT', '../addons/cy163_salesjl/static');
define('HB_ROOT_TD', '../addons/cy163_salesjl/haibao/tuandui/');
define('HB_ROOT_ZFJL', '../addons/cy163_salesjl/haibao/zfjl/');
define('ROOT_PATH', IA_ROOT . '/addons/cy163_salesjl/');
define('BEST_DOMAINSF', 'http://we7.qiumipai.com/shengfen.php');
define('BEST_DOMAINCS', 'http://we7.qiumipai.com/chengshi.php');
define('BEST_DOMAINQX', 'http://we7.qiumipai.com/quxian.php');
define('BEST_GOODS', 'cy163salesjl_goods');
define('BEST_GOODSOPTION', 'cy163salesjl_goods_option');
define('BEST_ORDER', 'cy163salesjl_order');
define('BEST_ORDERGOODS', 'cy163salesjl_order_goods');
define('BEST_MERCHANT', 'cy163salesjl_merchant');
define('BEST_MERCHANTACCOUNT', 'cy163salesjl_merchantaccount');
define('BEST_MEMBER', 'cy163salesjl_member');
define('BEST_TIXIAN', 'cy163salesjl_tixian');
define('BEST_HUODONG', 'cy163salesjl_huodong');
define('BEST_HUODONGGOODS', 'cy163salesjl_huodonggoods');
define('BEST_MERCHANTHD', 'cy163salesjl_merchanthd');
define('BEST_MERCHANTHDGOODS', 'cy163salesjl_merchanthdgoods');
define('BEST_YUNFEI', 'cy163salesjl_yunfei');
define('BEST_YUNFEISHENG', 'cy163salesjl_yfsheng');
define('BEST_MEMBERGOODS', 'cy163salesjl_membergoods');
define('BEST_MEMBERGOODSJIETI', 'cy163salesjl_membergoods_jieti');
define('BEST_MEMBERHUODONG', 'cy163salesjl_memberhuodong');
define('BEST_CART', 'cy163salesjl_cart');
define('BEST_MEMBERACCOUNT', 'cy163salesjl_memberaccount');
define('BEST_CITY', 'cy163salesjl_city');
define('BEST_ZITIDIAN', 'cy163salesjl_zitidian');
define('BEST_REFUND', 'cy163salesjl_refund');
define('BEST_MEMBERRZ', 'cy163salesjl_memberrz');
define('BEST_HEXIAOYUAN', 'cy163salesjl_hexiaoyuan');
define('BEST_HUODONGTEAMJIANG', 'cy163salesjl_huodong_teamjiang');
define('BEST_TZORDER', 'cy163salesjl_tzorder');
define('BEST_FORMID', 'cy163salesjl_formid');
define('BEST_youhuijuan', 'cy163salesjl_youhuijuan');
define('BEST_pro_comment', 'cy163salesjl_pro_comment');
define('BEST_youhuijuan', 'cy163salesjl_youhuijuan');
define('BEST_fist_comment', 'cy163salesjl_jlfist_comment');
define('BEST_getCoupon', 'cy163salesjl_get_coupon');
define('BEST_shopcart', 'cy163salesjl_shopcart');
define('BEST_shoporder', 'cy163salesjl_shoporder');
define('BEST_shopaddr', 'cy163salesjl_shopaddr');
define('BEST_two_comment', 'cy163salesjl_two_comment');
define('BEST_dianzan', 'cy163salesjl_dianzan');

function timediff($begin_time, $end_time)
{
	if ($begin_time < $end_time) {
		$starttime = $begin_time;
		$endtime = $end_time;
	} else {
		$starttime = $end_time;
		$endtime = $begin_time;
	}
	$timediff = $endtime - $starttime;
	$days = intval($timediff / 86400);
	$remain = $timediff % 86400;
	$hours = intval($remain / 3600);
	$remain = $remain % 3600;
	$mins = intval($remain / 60);
	$secs = $remain % 60;
	$res = array("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs);
	return $res;
}

function returnError($message, $data = "", $status = 0, $type = "")
{
	global $_W;
	if ($_W['isajax'] || $type == 'ajax') {
		header('Content-Type:application/json; charset=utf-8');
		$ret = array("status" => $status, "info" => $message, "data" => $data);
		die(json_encode($ret));
	} else {
		return message($message, $data, 'error');
	}
}
function returnSuccess($message, $data = "", $status = 1, $type = "")
{
	global $_W;
	if ($_W['isajax'] || $type == 'ajax') {
		header('Content-Type:application/json; charset=utf-8');
		$ret = array("status" => $status, "info" => $message, "data" => $data);
		die(json_encode($ret));
	} else {
		return message($message, $data, 'success');
	}
}
class Cy163_salesjlModuleSite extends WeModuleSite
{
    
    
    //企业付款到零钱  (提现)
	protected function transferByPay($transfer)
	{
		global $_W;
		$api = array("mchid" => $this->module['config']['mchid'], "appid" => $this->module['config']['appid'], "ip" => $this->module['config']['ip'], "key" => $this->module['config']['key']);
		$ret = array();
		$amount = $transfer['amount'];
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		$pars = array();
		$pars['mch_appid'] = $api['appid'];
		$pars['mchid'] = $api['mchid'];
		$pars['nonce_str'] = random(32);
		$pars['partner_trade_no'] = time() . random(4, true);
		$pars['openid'] = empty($transfer['openid']) ? $_W['openid'] : $transfer['openid'];
		$pars['check_name'] = 'NO_CHECK';
		$pars['amount'] = $amount;
		$pars['desc'] = $transfer['desc'];
		$pars['spbill_create_ip'] = $api['ip'];
		ksort($pars, SORT_STRING);
		$string1 = '';
		foreach ($pars as $k => $v) {
			$string1 .= "{$k}={$v}&";
		}
		$string1 .= "key={$api['key']}";
		$pars['sign'] = strtoupper(md5($string1));
		//对xml数据进行过滤
		$xml = array2xml($pars);
		$extras = array();
		$extras['CURLOPT_CAINFO'] = '../addons/cy163_salesjl/' . $_W['uniacid'] . '/rootca.pem';
		$extras['CURLOPT_SSLCERT'] = '../addons/cy163_salesjl/' . $_W['uniacid'] . '/apiclient_cert.pem';
		$extras['CURLOPT_SSLKEY'] = '../addons/cy163_salesjl/' . $_W['uniacid'] . '/apiclient_key.pem';
		load()->func('communication');
		$procResult = null;
		$resp = ihttp_request($url, $xml, $extras);
		if (is_error($resp)) {
			return error(-1, $resp['message']);
		} else {
			$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
			//php函数   创建一个存放xml的对象
			$dom = new DOMDocument();
			//loadXML 导入指定字符串的XML文档 
			if ($dom->loadXML($xml)) {
			    //DOMXPath快速解析xml
				$xpath = new DOMXPath($dom);
				//获取xml里面的状态值
				$code = $xpath->evaluate('string(//xml/return_code)');
				$result = $xpath->evaluate('string(//xml/result_code)');
				if (strtolower($code) == 'success' && strtolower($result) == 'success') {
					$partner_trade_no = $xpath->evaluate('string(//xml/partner_trade_no)');
					$payment_no = $xpath->evaluate('string(//xml/payment_no)');
					$payment_time = $xpath->evaluate('string(//xml/payment_time)');
					return array("partner_trade_no" => $partner_trade_no, "payment_no" => $payment_no, "payment_time" => $payment_time);
				} else {
					$error = $xpath->evaluate('string(//xml/err_code_des)');
					return error(-2, $error);
				}
			} else {
				return error(-3, 'error response');
			}
		}
	}
	
	
	//微信退款接口
	protected function refundByPay($params)
	{
		global $_W;
		$data = array("appid" => $this->module['config']['appid'], "mch_id" => $this->module['config']['mchid'], "nonce_str" => random(32), "out_refund_no" => random(22, true), "refund_fee" => floatval($params['refund_fee']) * 100, "total_fee" => floatval($params['total_fee']) * 100, "transaction_id" => $params['transaction_id']);
		$xml_data = '<xml>';
		foreach ($data as $k => $v) {
			$xml_data .= "<{$k}>{$v}</{$k}>";
		}
		ksort($data);
		$data_str = '';
		foreach ($data as $k => $v) {
			if ($v != '' && $k != 'sign') {
				$data_str .= "{$k}={$v}&";
			}
		}
		$data_str .= 'key=' . $this->module['config']['key'];
		$sign = strtoupper(md5($data_str));
		$xml_data .= "<sign>{$sign}</sign>";
		$xml_data .= '</xml>';
		$headers = array();
		$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		$headers['CURLOPT_SSL_VERIFYPEER'] = false;
		$headers['CURLOPT_SSL_VERIFYHOST'] = false;
		$headers['CURLOPT_SSLCERTTYPE'] = 'PEM';
		$headers['CURLOPT_SSLCERT'] = '../addons/cy163_salesjl/' . $_W['uniacid'] . '/apiclient_cert.pem';
		$headers['CURLOPT_SSLKEYTYPE'] = 'PEM';
		$headers['CURLOPT_SSLKEY'] = '../addons/cy163_salesjl/' . $_W['uniacid'] . '/apiclient_key.pem';
		$headers['CURLOPT_CAINFO'] = '../addons/cy163_salesjl/' . $_W['uniacid'] . '/rootca.pem';
		$response = ihttp_request('https://api.mch.weixin.qq.com/secapi/pay/refund', $xml_data, $headers);
		if ($response == '') {
			return error(-1, $response['message']);
		}
		$response = $response['content'];
		$xml = @simplexml_load_string($response);
		if (empty($xml)) {
			return error(-2, '[wxpay-api:refund] parse xml NULL');
		}
		$return_code = $xml->return_code ? (string) $xml->return_code : '';
		$return_msg = $xml->return_msg ? (string) $xml->return_msg : '';
		$result_code = $xml->result_code ? (string) $xml->result_code : '';
		$err_code = $xml->err_code ? (string) $xml->err_code : '';
		$err_code_des = $xml->err_code_des ? (string) $xml->err_code_des : '';
		if ($return_code == 'SUCCESS' && $result_code == 'SUCCESS') {
			$ret = array("success" => true, "refund_id" => (string) $xml->refund_id, "out_refund_no" => (string) $xml->out_refund_no);
			return $ret;
		} else {
			return error(-3, $return_code . ':' . $return_msg . ',' . $err_code . ':' . $err_code_des);
		}
	}

	
	public function Mcheckmember()
	{
		global $_W, $_GPC;
		if (empty($_W['fans']['from_user'])) {
			$message = '请在微信浏览器中打开！';
			include $this->template('error');
			exit;
		}
		$member = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$_W['fans']['from_user']}'");
		if (empty($member)) {
			$account_api = WeAccount::create();
			$info = $account_api->fansQueryInfo($_W['fans']['from_user']);
			if ($info['subscribe'] == 1) {
				$avatar = $info['headimgurl'];
				$nickname = $info['nickname'];
				if ($info['sex'] == 1) {
					$gender = '男';
				} else {
					$gender = '女';
				}
			} else {
				$fan = mc_oauth_userinfo();
				$avatar = $fan['headimgurl'];
				$nickname = $fan['nickname'];
				if ($fan['sex'] == 1) {
					$gender = '男';
				} else {
					$gender = '女';
				}
			}
			$data['weid'] = $_W['uniacid'];
			$data['openid'] = $_W['fans']['from_user'];
			$data['nickname'] = $nickname;
			$data['avatar'] = $avatar;
			$data['gender'] = $gender;
			$data['regtime'] = TIMESTAMP;
			pdo_insert(BEST_MEMBER, $data);
			$member = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$_W['fans']['from_user']}'");
		}
		return $member;
	}
	public function doMobilePytask()
	{
	}
	public function doMobileIndex()
	{
		global $_W, $_GPC;
		$openid = $_W['fans']['from_user'];
		if ($this->module['config']['gqzfjlzs'] == 1) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0");
		} else {
			if ($this->module['config']['gqzfjlzs'] == 2) {
				$total = 0;
			} else {
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND endtime > " . TIMESTAMP);
			}
		}
		$allpage = ceil($total / 10) + 1;
		$page = intval($_GPC['page']);
		$pindex = max(1, $page);
		$psize = 10;
		if ($this->module['config']['gqzfjlzs'] == 1) {
			$hdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		} else {
			if ($this->module['config']['gqzfjlzs'] == 2) {
				$hdlist = '';
			} else {
				$hdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND endtime > " . TIMESTAMP . ' ORDER BY time DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize);
			}
		}
		foreach ($hdlist as $k => $v) {
			$hdlist[$k]['member'] = pdo_fetch('SELECT rztype FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$v['openid']}'");
			$thumbs = unserialize($v['thumbs']);
			$hdlist[$k]['img1'] = tomedia($thumbs[0]);
			$hdlist[$k]['img2'] = tomedia($thumbs[1]);
			$hdlist[$k]['img3'] = tomedia($thumbs[2]);
			if ($v['starttime'] > TIMESTAMP) {
				$hdlist[$k]['status'] = '未开始';
			} else {
				if ($v['endtime'] < TIMESTAMP) {
					$hdlist[$k]['status'] = '已结束';
				} else {
					$hdlist[$k]['status'] = '<span>进行中</span>';
				}
			}
		}
		$isajax = intval($_GPC['isajax']);
		if ($isajax == 1) {
			$html = '';
			foreach ($hdlist as $k => $v) {
				if ($v['member']['rztype'] > 0) {
					$rzhtml = '<a href="' . $this->createMobileUrl('store', array("openid" => $v['openid'])) . '">TA的主页</a>';
				} else {
					$rzhtml = '';
				}
				if ($v['address'] != '') {
					$addhtml = '<div class="address flex">
									<img src="' . STATIC_ROOT . '/dw.png" />
									<div class="address-con textellipsis1">' . $v['address'] . '</div>
								</div>';
				} else {
					$addhtml = '';
				}
				$img1 = $v['img1'] ? '<img src="' . $v['img1'] . '" />' : '';
				$img2 = $v['img2'] ? '<img src="' . $v['img2'] . '" />' : '';
				$img3 = $v['img3'] ? '<img src="' . $v['img3'] . '" />' : '';
				$html .= '<div class="item">
							<div class="top flex">
								<img src="' . $v['avatar'] . '" />
								<div class="nicktime flex">
									<div class="nicknamecon">' . $v['nickname'] . '</div>
									<div class="tostore text-r flex">' . $rzhtml . '</div>
								</div>
							</div>
							<a href="' . $this->createMobileUrl('hddetail', array("id" => $v['id'])) . '">
								<div class="title textellipsis2">' . $v['title'] . '</div>
								<div class="jltj flex">
									<div class="jltjitem">' . $v['views'] . '人浏览， ' . date('m-d', $v['time']) . '</div>
									<div class="jltjitem text-r jlstatus">' . $v['status'] . '</div>
								</div>
								<div class="imgs flex">
									' . $img1 . $img2 . $img3 . '
									<div style="flex:1;"></div>
								</div>
								' . $addhtml . '
							</a>
						</div>';
			}
			echo $html;
			exit;
		}
		$shareurl = $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('index'));
		include $this->template('index');
	}
	public function doMobileFujinjl()
	{
		global $_W, $_GPC;
		$longitude = $_GPC['latitude'];
		$latitude = $_GPC['longitude'];
		$radius = 6378.138;
		$hm = empty($this->module['config']['kms']) ? 0 : $this->module['config']['kms'];
		if ($this->module['config']['gqzfjlzs'] == 1) {
			$allfujin = pdo_fetchall("SELECT *,({$radius}*acos(cos(radians({$latitude}))*cos(radians(jingdu))*cos(radians(weidu)-radians({$longitude}))+sin(radians({$latitude}))*sin(radians(jingdu)))) AS distance FROM " . tablename(BEST_MEMBERHUODONG) . " HAVING distance < {$hm} AND weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 ORDER BY distance");
		} else {
			$allfujin = pdo_fetchall("SELECT *,({$radius}*acos(cos(radians({$latitude}))*cos(radians(jingdu))*cos(radians(weidu)-radians({$longitude}))+sin(radians({$latitude}))*sin(radians(jingdu)))) AS distance FROM " . tablename(BEST_MEMBERHUODONG) . " HAVING distance < {$hm} AND weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND endtime > " . TIMESTAMP . ' ORDER BY distance');
		}
		$total = count($allfujin);
		$allpage = ceil($total / 10) + 1;
		$page = intval($_GPC['page']);
		$pindex = max(1, $page);
		$psize = 10;
		if ($this->module['config']['gqzfjlzs'] == 1) {
			$sql = "SELECT *,({$radius}*acos(cos(radians({$latitude}))*cos(radians(jingdu))*cos(radians(weidu)-radians({$longitude}))+sin(radians({$latitude}))*sin(radians(jingdu)))) AS distance FROM " . tablename(BEST_MEMBERHUODONG) . " HAVING distance < {$hm} AND weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 ORDER BY distance LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
		} else {
			$sql = "SELECT *,({$radius}*acos(cos(radians({$latitude}))*cos(radians(jingdu))*cos(radians(weidu)-radians({$longitude}))+sin(radians({$latitude}))*sin(radians(jingdu)))) AS distance FROM " . tablename(BEST_MEMBERHUODONG) . " HAVING distance < {$hm} AND weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND endtime > " . TIMESTAMP . ' ORDER BY distance LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		}
		$jllist = pdo_fetchall($sql);
		$html = '';
		foreach ($jllist as $k => $v) {
			$memberjl = $hdlist[$k]['member'] = pdo_fetch('SELECT rztype FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$v['openid']}'");
			$thumbs = unserialize($v['thumbs']);
			$img1 = empty($thumbs[0]) ? '' : '<img src="' . tomedia($thumbs[0]) . '" />';
			$img2 = empty($thumbs[1]) ? '' : '<img src="' . tomedia($thumbs[1]) . '" />';
			$img3 = empty($thumbs[2]) ? '' : '<img src="' . tomedia($thumbs[2]) . '" />';
			if ($v['starttime'] > TIMESTAMP) {
				$status = '未开始';
			} else {
				if ($v['endtime'] < TIMESTAMP) {
					$status = '已结束';
				} else {
					$status = '<span>进行中</span>';
				}
			}
			$zhuyehtml = $memberjl['rztype'] > 0 ? '<a href="' . $this->createMobileUrl('store', array("openid" => $v['openid'])) . '">TA的主页</a>' : '';
			if ($v['address'] != '') {
				$addhtml = '<div class="address flex">
								<img src="' . STATIC_ROOT . '/dw.png" />
								<div class="address-con textellipsis1">' . $v['address'] . '</div>
							</div>';
			} else {
				$addhtml = '';
			}
			$html .= '<div class="item">
						<div class="top flex">
							<img src="' . $v['avatar'] . '" />
							<div class="nicktime flex">
								<div class="nicknamecon textellipsis1">' . $v['nickname'] . '</div>
								<div class="tostore text-r flex">
									' . $zhuyehtml . '
								</div>
							</div>
						</div>
						<a href="' . $this->createMobileUrl('hddetail', array("id" => $v['id'])) . '">
							<div class="title textellipsis2">' . $v['title'] . '</div>
							<div class="jltj flex">
								<div class="jltjitem">' . $v['views'] . '人浏览， ' . date('m-d', $v['time']) . '</div>
								<div class="jltjitem text-r jlstatus">' . $status . '</div>
							</div>
							<div class="imgs flex">
								' . $img1 . $img2 . $img3 . '
								<div style="flex:1;"></div>
							</div>
							' . $addhtml . '
						</a>
					</div>';
		}
		$resArr['allpage'] = $allpage;
		$resArr['html'] = $html;
		echo json_encode($resArr);
		exit;
	}
	public function doMobileStore()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC['openid']);
		$member = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$openid}'");
		if ($member['rztype'] <= 0) {
			$message = '该用户未认证！';
			include $this->template('error');
			exit;
		}
		if ($this->module['config']['gqzfjlzs'] == 1) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND openid = '{$openid}'");
		} else {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND openid = '{$openid}' AND endtime > " . TIMESTAMP);
		}
		$allpage = ceil($total / 10) + 1;
		$page = intval($_GPC['page']);
		$pindex = max(1, $page);
		$psize = 10;
		if ($this->module['config']['gqzfjlzs'] == 1) {
			$hdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND openid = '{$openid}' ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		} else {
			$hdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND status = 1 AND owndel = 0 AND admindel = 0 AND openid = '{$openid}' AND endtime > " . TIMESTAMP . ' ORDER BY time DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize);
		}
		foreach ($hdlist as $k => $v) {
			$thumbs = unserialize($v['thumbs']);
			$hdlist[$k]['img1'] = empty($thumbs[0]) ? '<div style="width:1.5rem;"></div>' : '<img src="' . tomedia($thumbs[0]) . '" />';
			$hdlist[$k]['img2'] = empty($thumbs[1]) ? '<div style="width:1.5rem;"></div>' : '<img src="' . tomedia($thumbs[1]) . '" />';
			$hdlist[$k]['img3'] = empty($thumbs[2]) ? '<div style="width:1.5rem;"></div>' : '<img src="' . tomedia($thumbs[2]) . '" />';
			$hdlist[$k]['img4'] = empty($thumbs[3]) ? '<div style="width:1.5rem;"></div>' : '<img src="' . tomedia($thumbs[3]) . '" />';
			if ($v['starttime'] > TIMESTAMP) {
				$hdlist[$k]['status'] = '未开始';
			} else {
				if ($v['endtime'] < TIMESTAMP) {
					$hdlist[$k]['status'] = '已结束';
				} else {
					$hdlist[$k]['status'] = '<span>进行中</span>';
				}
			}
		}
		$isajax = intval($_GPC['isajax']);
		if ($isajax == 1) {
			$html = '';
			foreach ($hdlist as $k => $v) {
				if ($v['address'] != '') {
					$addhtml = '<div class="address">' . $v['address'] . '</div>';
				} else {
					$addhtml = '';
				}
				$html .= '<div class="item">
							<a href="' . $this->createMobileUrl('hddetail', array("id" => $v['id'])) . '">
								<div class="title textellipsis2">' . $v['title'] . '</div>
								<div class="jltj flex">
									<div class="jltjitem">' . $v['views'] . '人浏览， ' . date('m-d', $v['time']) . '</div>
									<div class="jltjitem text-r jlstatus">' . $v['status'] . '</div>
								</div>
								<div class="imgs flex">
									' . $v['img1'] . $v['img2'] . $v['img3'] . $v['img4'] . '
								</div>
								' . $addhtml . '
							</a>
						</div>';
			}
			echo $html;
			exit;
		}
		$shareurl = $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('store', array("openid" => $openid)));
		include $this->template('store');
	}
	public function doMobileFabuchose()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$jlnum = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']}");
		$jlnum = $jlnum + $this->module['config']['basicjlnum'];
		$jlinpeople = pdo_fetchcolumn('SELECT SUM(inpeople) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']}");
		$jlinpeople = $jlinpeople + $this->module['config']['basicinpoeple'];
		$jlviews = pdo_fetchcolumn('SELECT SUM(views) FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']}");
		$jlviews = $jlviews + $this->module['config']['basicviews'];
		$ismerchant = pdo_fetch('SELECT id FROM ' . tablename(BEST_MERCHANT) . " WHERE openid = '{$member['openid']}' AND status = 1");
		include $this->template('fabuchose');
	}
	public function doMobileHddetail()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$id = intval($_GPC['id']);
		$memberhd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$id} AND status = 1");
		$memberhd['inpeople'] = $memberhd['inpeople'] + $memberhd['basicsales'];
		$memberhd['views'] = $memberhd['views'] + $memberhd['basicviews'];
		$hdmember = pdo_fetch('SELECT openid,rztype FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$memberhd['openid']}'");
		if (empty($memberhd)) {
			$message = '不存在该接龙主题！';
			include $this->template('error');
			exit;
		}
		$data['views'] = $memberhd['views'] + 1;
		pdo_update(BEST_MEMBERHUODONG, $data, array("id" => $id));
		$thumbs = unserialize($memberhd['thumbs']);
		$thumbscount = count($thumbs);
		if ($thumbscount >= 4) {
			$yushu = 4 - $thumbscount % 4;
		} else {
			$yushu = 4 - $thumbscount;
		}
		$goodslist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MEMBERGOODS) . " WHERE weid = {$_W['uniacid']} AND hdid = {$id}");
		foreach ($goodslist as $k => $v) {
			$goodsthumbs = unserialize($v['thumbs']);
			$goodslist[$k]['thumb'] = $goodsthumbs[0];
			$goodslist[$k]['count'] = count($goodsthumbs);
			$hasjieti = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERGOODSJIETI) . " WHERE goodsid = {$v['id']} AND jietinumstart <= {$v['inpeople']} AND jietinum >= {$v['inpeople']}");
			if (!empty($hasjieti)) {
				$goodslist[$k]['normalprice'] = $hasjieti['jietiprice'];
			}
			$goodslist[$k]['jietilist'] = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MEMBERGOODSJIETI) . " WHERE goodsid = {$v['id']} ORDER BY jietiprice DESC");
			if ($v['xiangounum'] > 0) {
				$hasbuynum = pdo_fetchcolumn('SELECT SUM(a.total) FROM ' . tablename(BEST_ORDERGOODS) . ' as a,' . tablename(BEST_ORDER) . " as b WHERE a.jlid = {$id} AND a.goodsid = {$v['id']} AND b.status >= 0 AND a.orderid = b.id AND b.from_user = '{$member['openid']}'");
				$leftcanbuy = $v['xiangounum'] - $hasbuynum;
				if ($v['total'] < $leftcanbuy) {
					$goodslist[$k]['canbuynum'] = $v['total'];
				} else {
					$goodslist[$k]['canbuynum'] = $leftcanbuy;
				}
			} else {
				$goodslist[$k]['canbuynum'] = $v['total'];
			}
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 1 AND status >= 1 AND jlid = {$id}");
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC['page']);
		$pindex = max(1, $page);
		$canyulist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 1 AND status >= 1 AND jlid = {$id} ORDER BY createtime LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		$canyunum = 1;
		$canyunum2 = 1;
		$zhantie = '团购名称：' . $memberhd['title'] . '
团购详情：' . $memberhd['des'] . '
接龙详情：
';
		$canyulist2 = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 1 AND status >= 1 AND jlid = {$id} ORDER BY createtime");
		foreach ($canyulist2 as $k => $v) {
			$ztmember = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$v['from_user']}'");
			$zttotal = pdo_fetchcolumn('SELECT SUM(total) FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$v['id']}");
			$ordergoods = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$v['id']}");
			$zhantie .= $canyunum2 . '、' . $ztmember['nickname'] . '(' . $ordergoods['goodsname'] . '... 接力 × ' . $zttotal . ')
';
			$canyunum2++;
		}
		$zhantie .= '去参团：' . $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('hddetail', array("id" => $id)));
		foreach ($canyulist as $k => $v) {
			$canyulist[$k]['canyunum'] = $canyunum;
			$canyulist[$k]['member'] = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$v['from_user']}'");
			$canyulist[$k]['total'] = pdo_fetchcolumn('SELECT SUM(total) FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$v['id']}");
			$canyunum++;
		}
		$jlallprice = pdo_fetchcolumn('SELECT SUM(price) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 1 AND status >= 1 AND jlid = {$id}");
		$jlallprice = empty($jlallprice) ? '0.00' : round($jlallprice, 2);
		$jlallprice = $jlallprice + $jlallprice * $memberhd['basicsales'];
		$shareurl = $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('hddetail', array("id" => $id)));
		$sharethumb = !empty($memberhd['sharethumb']) ? tomedia($memberhd['sharethumb']) : tomedia($thumbs[0]);
		include $this->template('hddetail');
	}
	public function doMobileAjaxjlpeople()
	{
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 1 AND status = 1 AND jlid = {$id}");
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC['page']);
		$pindex = max(1, $page);
		$canyulist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 1 AND status = 1 AND jlid = {$id} ORDER BY createtime LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		$canyunum = $psize * ($page - 1) + 1;
		$html = '';
		foreach ($canyulist as $k => $v) {
			$member = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$v['from_user']}'");
			$gtotal = pdo_fetchcolumn('SELECT SUM(total) FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$v['id']}");
			$html .= '<div class="item flex">
						<div class="number">No.' . $canyunum . '</div>
						<img src="' . $member['avatar'] . '" />
						<div class="nickname textellipsis1">' . $member['nickname'] . '</div>
						<div class="num">+' . $gtotal . '</div>
						<div class="time">' . date('Y-m-d H:i', $v['createtime']) . '</div>
					</div>';
			$canyunum++;
		}
		echo $html;
	}
	public function doMobileGetgoodstbigimg()
	{
		global $_W, $_GPC;
		$goodsid = intval($_GPC['goodsid']);
		$goods = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERGOODS) . " WHERE id = {$goodsid} AND weid = {$_W['uniacid']}");
		$imglist = unserialize($goods['thumbs']);
		if (!empty($imglist)) {
			$imglistval = '';
			foreach ($imglist as $k => $v) {
				$imglistval .= tomedia($v) . ',';
			}
			$imglistval = substr($imglistval, 0, -1);
			$resArr['error'] = 0;
			$resArr['message'] = $imglistval;
			echo json_encode($resArr);
			exit;
		} else {
			$resArr['error'] = 1;
			$resArr['message'] = '';
			echo json_encode($resArr);
			exit;
		}
	}
	public function doMobileGetgoodstbigimgdl()
	{
		global $_W, $_GPC;
		$goodsid = intval($_GPC['goodsid']);
		$goods = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE id = {$goodsid} AND weid = {$_W['uniacid']}");
		$imglist = unserialize($goods['thumbs']);
		if (!empty($imglist)) {
			$imglistval = tomedia($goods['thumb']) . ',';
			foreach ($imglist as $k => $v) {
				$imglistval .= tomedia($v) . ',';
			}
			$imglistval = substr($imglistval, 0, -1);
			$resArr['error'] = 0;
			$resArr['message'] = $imglistval;
			echo json_encode($resArr);
			exit;
		} else {
			$imglistval = tomedia($goods['thumb']);
			$resArr['error'] = 0;
			$resArr['message'] = $imglistval;
			echo json_encode($resArr);
			exit;
		}
	}
	public function doMobileGethuodongbigimg()
	{
		global $_W, $_GPC;
		$hdid = intval($_GPC['hdid']);
		$imgsrc = trim($_GPC['imgsrc']);
		$hd = pdo_fetch('SELECT thumbs FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$hdid} AND weid = {$_W['uniacid']}");
		$imglist = unserialize($hd['thumbs']);
		if (!empty($imglist)) {
			$imglistval = '';
			$nowindex = 0;
			foreach ($imglist as $k => $v) {
				$v = tomedia($v);
				if ($v == $imgsrc) {
					$nowindex = $k;
				}
				$imglistval .= $v . ',';
			}
			$imglistval = substr($imglistval, 0, -1);
			$resArr['error'] = 0;
			$resArr['message'] = $imglistval;
			$resArr['index'] = $nowindex;
			echo json_encode($resArr);
			exit;
		} else {
			$resArr['error'] = 1;
			$resArr['message'] = '';
			echo json_encode($resArr);
			exit;
		}
	}
	public function doMobileMembersub()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$jlid = intval($_GPC['jlid']);
		$memberhd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$jlid}");
		if (empty($memberhd)) {
			$resArr['error'] = 1;
			$resArr['message'] = '不存在该接龙主题！';
			echo json_encode($resArr);
			exit;
		}
		if ($member['openid'] == $memberhd['openid']) {
			$resArr['error'] = 1;
			$resArr['message'] = '不能参与自己发布的接龙！';
			echo json_encode($resArr);
			exit;
		}
		if ($memberhd['owndel'] == 1 || $memberhd['admindel'] == 1) {
			$resArr['error'] = 1;
			$resArr['message'] = '接龙主题已经结束！';
			echo json_encode($resArr);
			exit;
		}
		if ($memberhd['isxiugai'] == 1) {
			$resArr['error'] = 1;
			$resArr['message'] = '接龙主题正在修改中，暂不能下单！';
			echo json_encode($resArr);
			exit;
		}
		pdo_delete(BEST_CART, array("jlid" => $jlid, "openid" => $member['openid']));
		if ($memberhd['starttime'] > TIMESTAMP) {
			$resArr['error'] = 1;
			$resArr['message'] = '接龙主题还未开始！';
			echo json_encode($resArr);
			exit;
		}
		if ($memberhd['endtime'] < TIMESTAMP) {
			$resArr['error'] = 1;
			$resArr['message'] = '接龙主题已经结束！';
			echo json_encode($resArr);
			exit;
		}
		$goodsid = $_GPC['goodsid'];
		foreach ($goodsid as $k => $v) {
			$total = $_GPC['total'][$k];
			if ($total > 0) {
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERGOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$v} AND hdid = {$jlid}");
				if (empty($goodsres)) {
					$resArr['error'] = 1;
					$resArr['message'] = '有商品不属于该接龙主题！';
					echo json_encode($resArr);
					exit;
				}
				if ($total > $goodsres['total']) {
					$resArr['error'] = 1;
					$resArr['message'] = '商品' . $goodsres['title'] . '库存不足！';
					echo json_encode($resArr);
					exit;
				}
				$hasbuytotal = pdo_fetchcolumn('SELECT SUM(a.total) FROM ' . tablename(BEST_ORDERGOODS) . ' as a,' . tablename(BEST_ORDER) . " as b WHERE a.jlid = {$jlid} AND a.goodsid = {$v} AND b.status >= 0 AND a.orderid = b.id AND b.from_user = '{$member['openid']}'");
				$latertotal = $hasbuytotal + $total;
				if ($goodsres['xiangounum'] > 0 && $latertotal > $goodsres['xiangounum']) {
					$resArr['error'] = 1;
					$resArr['message'] = '商品' . $goodsres['title'] . '每人限购' . $goodsres['xiangounum'] . $goodsres['optionname'] . '！您已购买了' . $hasbuytotal . $goodsres['optionname'];
					echo json_encode($resArr);
					exit;
				}
			}
		}
		foreach ($goodsid as $k => $v) {
			$total = $_GPC['total'][$k];
			if ($total > 0) {
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERGOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$v} AND hdid = {$jlid}");
				$hasjieti = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERGOODSJIETI) . " WHERE goodsid = {$v} AND jietinumstart <= {$goodsres['inpeople']} AND jietinum >= {$goodsres['inpeople']}");
				if (!empty($hasjieti)) {
					$data['price'] = $hasjieti['jietiprice'];
				} else {
					$data['price'] = $goodsres['normalprice'];
				}
				$data['weid'] = $_W['uniacid'];
				$data['openid'] = $member['openid'];
				$data['jlid'] = $jlid;
				$data['goodsid'] = $v;
				$data['goodsname'] = $goodsres['title'];
				$data['total'] = $total;
				$data['allprice'] = $data['total'] * $data['price'];
				pdo_insert(BEST_CART, $data);
			}
		}
		$resArr['error'] = 0;
		$resArr['message'] = '提交成功！';
		echo json_encode($resArr);
		exit;
	}
	public function doMobileMemberordersub()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$jlid = intval($_GPC['jlid']);
		$list = pdo_fetchall('SELECT * FROM ' . tablename(BEST_CART) . " WHERE weid = {$_W['uniacid']} AND jlid = {$jlid} AND openid = '{$member['openid']}'");
		if (empty($list)) {
			header('Location:' . $this->createMobileUrl('my'));
		}
		$allprice = pdo_fetchcolumn('SELECT SUM(allprice) FROM ' . tablename(BEST_CART) . " WHERE weid = {$_W['uniacid']} AND jlid = {$jlid} AND openid = '{$member['openid']}'");
		$jielong = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$jlid}");
		if ($allprice >= $jielong['manjian']) {
			$yunfei = 0;
		} else {
			$yunfei = $jielong['yunfei'];
		}
		if ($jielong['cansh'] == 0) {
			$yunfei = 0;
		}
		$allprice2 = $allprice + $yunfei;
		$ztdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ZITIDIAN) . " WHERE openid = '{$jielong['openid']}' AND ztdtype = 0");
		include $this->template('memberordersub');
	}
	public function doMobileDomemberordersub()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$jlid = intval($_GPC['jlid']);
		$memberjl = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$jlid}");
		$list = pdo_fetchall('SELECT * FROM ' . tablename(BEST_CART) . " WHERE weid = {$_W['uniacid']} AND jlid = {$jlid} AND openid = '{$member['openid']}'");
		if (empty($list)) {
			$resArr['error'] = 1;
			$resArr['message'] = '没有商品可以结算！';
			echo json_encode($resArr);
			exit;
		}
		$allprice = pdo_fetchcolumn('SELECT SUM(allprice) FROM ' . tablename(BEST_CART) . " WHERE weid = {$_W['uniacid']} AND jlid = {$jlid} AND openid = '{$member['openid']}'");
		if ($allprice >= $memberjl['manjian']) {
			$yunfei = 0;
		} else {
			$yunfei = $memberjl['yunfei'];
		}
		$pstype = intval($_GPC['pstype']);
		if ($pstype == 1) {
			$yunfei = 0;
			$shname = $shcity = $shaddress = '';
			$shphone = trim($_GPC['shphone2']);
			if (!$this->isMobile($shphone)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写正确的手机号码！';
				echo json_encode($resArr);
				exit;
			}
			$ztdid = intval($_GPC['ztdid']);
			$ztdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ZITIDIAN) . " WHERE id = {$ztdid}");
			if (empty($ztdres)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请选择自提点！';
				echo json_encode($resArr);
				exit;
			}
			$data['remark'] = $_GPC['remark2'];
		} else {
			$shname = trim($_GPC['shname']);
			$shphone = trim($_GPC['shphone']);
			$shcity = trim($_GPC['shcity']);
			$shaddress = trim($_GPC['shaddress']);
			if (empty($shname)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写收货人姓名！';
				echo json_encode($resArr);
				exit;
			}
			if (!$this->isMobile($shphone)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写正确的收货人手机号码！';
				echo json_encode($resArr);
				exit;
			}
			if (empty($shcity)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请选择省市！';
				echo json_encode($resArr);
				exit;
			}
			if (empty($shaddress)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写详细地址！';
				echo json_encode($resArr);
				exit;
			}
			$data['remark'] = $_GPC['remark'];
		}
		$allprice2 = $allprice + $yunfei;
		if ($pstype == 0) {
			$data['address'] = $shname . '|' . $shphone . '|' . $shcity . '|' . $shaddress;
			$datam['shname'] = $shname;
			$datam['shphone'] = $shphone;
			$datam['shcity'] = $shcity;
			$datam['shaddress'] = $shaddress;
			pdo_update(BEST_MEMBER, $datam, array("openid" => $member['openid']));
		} else {
			$data['address'] = $shphone;
			$data['ztdid'] = $ztdid;
			$data['ztdaddress'] = $ztdres['address'];
			$data['ztdjingdu'] = $ztdres['jingdu'];
			$data['ztdweidu'] = $ztdres['weidu'];
		}
		$data['price'] = $allprice2;
		$data['yunfei'] = $yunfei;
		$data['weid'] = $_W['uniacid'];
		$data['from_user'] = $member['openid'];
		$data['ordersn'] = date('Ymd') . random(14, 1);
		$data['createtime'] = TIMESTAMP;
		$data['jlid'] = $jlid;
		$data['isjl'] = 1;
		$data['jlopenid'] = $memberjl['openid'];
		$data['pstype'] = $pstype;
		pdo_insert(BEST_ORDER, $data);
		$orderid = pdo_insertid();
		foreach ($list as $k => $v) {
			$datao['weid'] = $_W['uniacid'];
			$datao['total'] = $v['total'];
			$datao['price'] = $v['price'];
			$datao['goodsid'] = $v['goodsid'];
			$datao['createtime'] = TIMESTAMP;
			$datao['orderid'] = $orderid;
			$datao['goodsname'] = $v['goodsname'];
			$datao['jlid'] = $jlid;
			pdo_insert(BEST_ORDERGOODS, $datao);
		}
		pdo_delete(BEST_CART, array("jlid" => $jlid, "openid" => $member['openid']));
		$resArr['error'] = 0;
		$resArr['fee'] = $allprice2;
		$resArr['ordertid'] = $data['ordersn'];
		$resArr['message'] = '提交订单成功！';
		echo json_encode($resArr);
		exit;
	}
	public function doMobileMy()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$allmoney = pdo_fetchcolumn('SELECT SUM(money) as allmoney FROM ' . tablename(BEST_MEMBERACCOUNT) . " WHERE openid = '{$member['openid']}' AND weid = {$_W['uniacid']}");
		$allmoney = empty($allmoney) ? '0.00' : $allmoney;
		$ismerchant = pdo_fetch('SELECT id FROM ' . tablename(BEST_MERCHANT) . " WHERE openid = '{$member['openid']}' AND status = 1");
		include $this->template('my');
	}
	public function isCard($id_card)
	{
		if (strlen($id_card) == 18) {
			return $this->idcard_checksum18($id_card);
		} else {
			if (strlen($id_card) == 15) {
				$id_card = $this->idcard_15to18($id_card);
				return $this->idcard_checksum18($id_card);
			} else {
				return false;
			}
		}
	}
	public function idcard_verify_number($idcard_base)
	{
		if (strlen($idcard_base) != 17) {
			return false;
		}
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		$verify_number_list = array("1", "0", "X", "9", "8", "7", "6", "5", "4", "3", "2");
		$checksum = 0;
		$i = 0;
		while ($i < strlen($idcard_base)) {
			$checksum += substr($idcard_base, $i, 1) * $factor[$i];
			$i++;
		}
		$mod = $checksum % 11;
		$verify_number = $verify_number_list[$mod];
		return $verify_number;
	}
	public function idcard_15to18($idcard)
	{
		if (strlen($idcard) != 15) {
			return false;
		} else {
			if (array_search(substr($idcard, 12, 3), array("996", "997", "998", "999")) !== false) {
				$idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
			} else {
				$idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
			}
		}
		$idcard = $this->idcard_verify_number($idcard);
		return $idcard;
	}
	public function idcard_checksum18($idcard)
	{
		if (strlen($idcard) != 18) {
			return false;
		}
		$idcard_base = substr($idcard, 0, 17);
		if ($this->idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
			return false;
		} else {
			return true;
		}
	}
	public function doMobileRenzheng()
	{
		$member = $this->Mcheckmember();
		if ($member['rztype'] > 0) {
			$message = '您已认证通过！';
			include $this->template('error');
			exit;
		}
		include $this->template('renzheng');
	}
	public function doMobileGerenrz()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		if ($member['rztype'] > 0) {
			$message = '您已认证通过！';
			include $this->template('error');
			exit;
		}
		$memberrz = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$member['openid']}'");
		if ($memberrz['rztype'] == 2) {
			header('Location:' . $this->createMobileUrl('qiyerz'));
		}
		include $this->template('gerenrz');
	}
	public function doMobileDogerenrz()
	{
		global $_W, $_GPC;
		if (!checksubmit('submit')) {
			exit;
		}
		$member = $this->Mcheckmember();
		if (empty($_GPC['rzrealname'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入姓名！';
			echo json_encode($resArr);
			exit;
		}
		$isidcard = $this->isCard($_GPC['rzidcard']);
		if (empty($isidcard)) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入正确的身份证号！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['idcard1'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请上传身份证正面照！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['idcard2'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请上传身份证反面照！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['rztelphone'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入手机号！';
			echo json_encode($resArr);
			exit;
		}
		$memberrz = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$member['openid']}'");
		if (!empty($memberrz)) {
			$data = array("rzrealname" => $_GPC['rzrealname'], "rzidcard" => $_GPC['rzidcard'], "idcard1" => $_GPC['idcard1'], "idcard2" => $_GPC['idcard2'], "rztelphone" => $_GPC['rztelphone'], "isjujue" => 0);
			pdo_update(BEST_MEMBERRZ, $data, array("openid" => $member['openid']));
			$resArr['rzprice'] = 0;
			$resArr['error'] = 0;
			$resArr['message'] = '重新提交资料成功！';
			echo json_encode($resArr);
			exit;
		} else {
			if ($this->module['config']['gerenfee'] <= 0) {
				$rzstatus = 1;
			} else {
				$rzstatus = 0;
			}
			$data = array("weid" => $_W['uniacid'], "openid" => $member['openid'], "rzrealname" => $_GPC['rzrealname'], "rzidcard" => $_GPC['rzidcard'], "idcard1" => $_GPC['idcard1'], "idcard2" => $_GPC['idcard2'], "rztelphone" => $_GPC['rztelphone'], "rztype" => 1, "rzordersn" => date('Ymd') . random(12, 1), "rztime" => TIMESTAMP, "rzprice" => $this->module['config']['gerenfee'], "rzstatus" => $rzstatus);
			pdo_insert(BEST_MEMBERRZ, $data);
			$resArr['rzprice'] = $this->module['config']['gerenfee'];
			$resArr['error'] = 0;
			$resArr['message'] = '申请个人认证成功！';
			echo json_encode($resArr);
			exit;
		}
	}
	public function doMobileQiyerz()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		if ($member['rztype'] > 0) {
			$message = '您已认证通过！';
			include $this->template('error');
			exit;
		}
		$memberrz = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$member['openid']}'");
		if ($memberrz['rztype'] == 1) {
			header('Location:' . $this->createMobileUrl('gerenrz'));
		}
		include $this->template('qiyerz');
	}
	public function doMobileDoqiyerz()
	{
		global $_W, $_GPC;
		if (!checksubmit('submit')) {
			exit;
		}
		$member = $this->Mcheckmember();
		if (empty($_GPC['rzqiyename'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入企业名称！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['rzsanzheng'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请上传三合一营业许可证！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['rzrealname'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入法人姓名！';
			echo json_encode($resArr);
			exit;
		}
		$isidcard = $this->isCard($_GPC['rzidcard']);
		if (empty($isidcard)) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入正确的身份证号！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['idcard1'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请上传身份证正面照！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['idcard2'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请上传身份证反面照！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['rztelphone'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请输入手机号！';
			echo json_encode($resArr);
			exit;
		}
		$memberrz = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$member['openid']}'");
		if (!empty($memberrz)) {
			$data = array("rzqiyename" => $_GPC['rzqiyename'], "rzsanzheng" => $_GPC['rzsanzheng'], "rzrealname" => $_GPC['rzrealname'], "rzidcard" => $_GPC['rzidcard'], "idcard1" => $_GPC['idcard1'], "idcard2" => $_GPC['idcard2'], "rztelphone" => $_GPC['rztelphone'], "isjujue" => 0);
			pdo_update(BEST_MEMBERRZ, $data, array("openid" => $member['openid']));
			$resArr['rzprice'] = 0;
			$resArr['error'] = 0;
			$resArr['message'] = '重新提交资料成功！';
			echo json_encode($resArr);
			exit;
		} else {
			if ($this->module['config']['qiyefee'] <= 0) {
				$rzstatus = 1;
			} else {
				$rzstatus = 0;
			}
			$data = array("weid" => $_W['uniacid'], "openid" => $member['openid'], "rzqiyename" => $_GPC['rzqiyename'], "rzsanzheng" => $_GPC['rzsanzheng'], "rzrealname" => $_GPC['rzrealname'], "rzidcard" => $_GPC['rzidcard'], "idcard1" => $_GPC['idcard1'], "idcard2" => $_GPC['idcard2'], "rztelphone" => $_GPC['rztelphone'], "rztype" => 2, "rzordersn" => date('Ymd') . random(12, 1), "rztime" => TIMESTAMP, "rzprice" => $this->module['config']['qiyefee'], "rzstatus" => $rzstatus);
			pdo_insert(BEST_MEMBERRZ, $data);
			$resArr['rzprice'] = $this->module['config']['qiyefee'];
			$resArr['error'] = 0;
			$resArr['message'] = '申请企业认证成功！';
			echo json_encode($resArr);
			exit;
		}
	}
	
	public function doMobileRenzhengpay()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$memberrz = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$member['openid']}'");
		if ($memberrz['rzstatus'] == 1) {
			$message = '您已认证通过！';
			include $this->template('error');
			exit;
		}
		include $this->template('renzhengpay');
	}
	public function doMobileSqtzpay()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$tzorder = pdo_fetch('SELECT * FROM ' . tablename(BEST_TZORDER) . " WHERE openid = '{$member['openid']}'");
		if ($tzorder['status'] == 1) {
			$message = '您已付款成功！';
			include $this->template('error');
			exit;
		}
		include $this->template('sqtzpay');
	}
	public function doMobileMyinjl()
	{
		include_once ROOT_PATH . 'inc/mobile/myinjl.php';
	}
	public function doMobileMyfbjl()
	{
		include_once ROOT_PATH . 'inc/mobile/myfbjl.php';
	}
	

	//退款
	public function jlorderrefund($jlid)
	{
		global $_W, $_GPC;
		$lastorder = pdo_fetch('SELECT price,yunfei FROM ' . tablename(BEST_ORDER) . " WHERE jlid = {$jlid} AND status >= 1 AND status < 4 ORDER BY price ASC");
		$zuidiprice = $lastorder['price'] - $lastorder['yunfei'];
		$allorder = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE jlid = {$jlid} AND status >= 1 AND status < 4");
		foreach ($allorder as $k => $v) {
			$sspp = $v['price'] - $v['yunfei'];
			if ($sspp > $zuidiprice) {
				$data = array();
				$data['jltuiprice'] = $sspp - $zuidiprice;
				$data['price'] = $zuidiprice;
				pdo_update(BEST_ORDER, $data, array("id" => $v['id']));
				$datamoney = array();
				$datamoney['weid'] = $_W['uniacid'];
				$datamoney['openid'] = $v['from_user'];
				$datamoney['money'] = $data['jltuiprice'];
				$datamoney['time'] = TIMESTAMP;
				$datamoney['orderid'] = $v['id'];
				$datamoney['explain'] = '接龙订单退差价';
				$datamoney['candotime'] = TIMESTAMP;
				pdo_insert(BEST_MEMBERACCOUNT, $datamoney);
			}
		}
	}
	
	
	
	
	public function doMobileAccount()
	{
		include_once ROOT_PATH . 'inc/mobile/account.php';
	}
	public function doMobileProfile()
	{
		include_once ROOT_PATH . 'inc/mobile/profile.php';
	}
	public function doMobileMyyunfei()
	{
		include_once ROOT_PATH . 'inc/mobile/myyunfei.php';
	}
	public function doMobileZitidian()
	{
		include_once ROOT_PATH . 'inc/mobile/zitidian.php';
	}
	public function doMobileHexiaoyuan()
	{
		include_once ROOT_PATH . 'inc/mobile/hexiaoyuan.php';
	}
	public function doMobileFabu()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$xiangouarr = array();
		$i = 0;
		while ($i <= 100) {
			if ($i == 0) {
				$xiangouarr[] = array("title" => "无上限", "value" => $i);
			} else {
				$xiangouarr[] = array("title" => '每人最多' . $i, "value" => $i);
			}
			$i++;
		}
		$xiangoujson = json_encode($xiangouarr);
		include $this->template('fabu');
	}
	public function doMobileDofabu()
	{
		global $_W, $_GPC;
		if (!checksubmit('submit')) {
			exit;
		}
		$member = $this->Mcheckmember();
		$datahd['weid'] = $_W['uniacid'];
		$datahd['time'] = TIMESTAMP;
		$datahd['openid'] = $member['openid'];
		$datahd['nickname'] = $member['nickname'];
		$datahd['avatar'] = $member['avatar'];
		$datahd['title'] = $_GPC['title'];
		$datahd['canziti'] = intval($_GPC['canziti']);
		$datahd['cansh'] = intval($_GPC['cansh']);
		if (empty($datahd['title'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请填写接龙主题名称！';
			echo json_encode($resArr);
			exit;
		}
		$datahd['des'] = $_GPC['des'];
		if (empty($datahd['des'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请填写接龙主题介绍！';
			echo json_encode($resArr);
			exit;
		}
		if (empty($_GPC['thumbs'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请上传接龙主题图片介绍！';
			echo json_encode($resArr);
			exit;
		}
		$datahd['thumbs'] = serialize($_GPC['thumbs']);
		$datahd['starttime'] = strtotime($_GPC['starttime']);
		$datahd['endtime'] = strtotime($_GPC['endtime']);
		$datahd['yunfei'] = $_GPC['yunfei'];
		$datahd['manjian'] = $_GPC['manjian'];
		if (empty($datahd['starttime']) || empty($datahd['endtime'])) {
			$resArr['error'] = 1;
			$resArr['message'] = '请选择接龙主题开始时间和截止时间！';
			echo json_encode($resArr);
			exit;
		}
		if ($datahd['starttime'] > $datahd['endtime']) {
			$resArr['error'] = 1;
			$resArr['message'] = '接龙主题开始时间不能大于截止时间！';
			echo json_encode($resArr);
			exit;
		}
		if ($datahd['canziti'] == 0 && $datahd['cansh'] == 0) {
			$resArr['error'] = 1;
			$resArr['message'] = '自提与送货必须选择至少选择其中一项！';
			echo json_encode($resArr);
			exit;
		}
		if ($datahd['canziti'] == 1) {
			$zitidian = pdo_fetch('SELECT id FROM ' . tablename(BEST_ZITIDIAN) . " WHERE openid = '{$member['openid']}'");
			if (empty($zitidian)) {
				$resArr['error'] = 1;
				$resArr['message'] = '您还没有创建自提点，不能选择支持自提。请至个人中心添加自提点！';
				echo json_encode($resArr);
				exit;
			}
		}
		$goodsname = $_GPC['goodsname'];
		if (empty($goodsname)) {
			$resArr['error'] = 1;
			$resArr['message'] = '请填写商品名称！';
			echo json_encode($resArr);
			exit;
		}
		foreach ($goodsname as $k => $v) {
			$data['title'] = $v;
			if (empty($data['title'])) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写商品名称！';
				echo json_encode($resArr);
				exit;
			}
			$data['normalprice'] = $_GPC['normalprice'][$k];
			if (empty($data['normalprice'])) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写商品价格！';
				echo json_encode($resArr);
				exit;
			}
			$data['total'] = $_GPC['total'][$k];
			if (empty($data['total'])) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写商品库存！';
				echo json_encode($resArr);
				exit;
			}
			$goodsthumbskey = 'goodsthumbs' . $k;
			if (empty($_GPC[$goodsthumbskey])) {
				$resArr['error'] = 1;
				$resArr['message'] = '请上传商品图片！';
				echo json_encode($resArr);
				exit;
			}
			$jietipricekey = 'jietiprice' . $k;
			$jietinumstartkey = 'jietinumstart' . $k;
			$jietinumkey = 'jietinum' . $k;
			$lastshuliang = 0;
			$lastprice = 0;
			foreach ($_GPC[$jietipricekey] as $kk => $vv) {
				$datajt['jietiprice'] = $vv;
				$datajt['jietinumstart'] = $_GPC[$jietinumstartkey][$kk];
				$datajt['jietinum'] = $_GPC[$jietinumkey][$kk];
				if (empty($datajt['jietiprice']) || empty($datajt['jietinum'])) {
					$resArr['error'] = 1;
					$resArr['message'] = '请填写完整的阶梯价格和阶梯数量！';
					echo json_encode($resArr);
					exit;
				}
				if ($_GPC[$jietinumstartkey][0] != 0) {
					$resArr['error'] = 1;
					$resArr['message'] = '第一个阶梯起始数量应该为0！';
					echo json_encode($resArr);
					exit;
				}
				if ($_GPC[$jietinumstartkey][$kk] <= $lastshuliang) {
					if ($lastshuliang != 0) {
						$resArr['error'] = 1;
						$resArr['message'] = '阶梯起始数量不能小于上一个阶梯的结束数量！';
						echo json_encode($resArr);
						exit;
					}
				}
				if ($vv >= $lastprice) {
					if ($lastshuliang != 0) {
						$resArr['error'] = 1;
						$resArr['message'] = '阶梯价格不能大于等于上一个阶梯的价格！';
						echo json_encode($resArr);
						exit;
					}
				}
				$lastshuliang = $_GPC[$jietinumkey][$kk];
				$lastprice = $vv;
			}
		}
		if ($this->module['config']['iszfjlsh'] == 0) {
			$datahd['status'] = 1;
		}
		$datahd['address'] = $_GPC['address'];
		$datahd['jingdu'] = $_GPC['jingdu'];
		$datahd['weidu'] = $_GPC['weidu'];
		pdo_insert(BEST_MEMBERHUODONG, $datahd);
		$hdid = pdo_insertid();
		foreach ($goodsname as $k => $v) {
			$data['weid'] = $_W['uniacid'];
			$data['openid'] = $member['openid'];
			$data['title'] = $v;
			$data['normalprice'] = $_GPC['normalprice'][$k];
			$data['total'] = $_GPC['total'][$k];
			$data['optionname'] = $_GPC['optionname'][$k];
			$data['xiangounum'] = $_GPC['xiangounum'][$k];
			$data['createtime'] = TIMESTAMP;
			$data['hdid'] = $hdid;
			$goodsthumbskey = 'goodsthumbs' . $k;
			$data['thumbs'] = serialize($_GPC[$goodsthumbskey]);
			pdo_insert(BEST_MEMBERGOODS, $data);
			$goodsid = pdo_insertid();
			$jietipricekey = 'jietiprice' . $k;
			$jietinumstartkey = 'jietinumstart' . $k;
			$jietinumkey = 'jietinum' . $k;
			foreach ($_GPC[$jietipricekey] as $kk => $vv) {
				$datajt['goodsid'] = $goodsid;
				$datajt['jietiprice'] = $vv;
				$datajt['jietinumstart'] = $_GPC[$jietinumstartkey][$kk];
				$datajt['jietinum'] = $_GPC[$jietinumkey][$kk];
				$datajt['displayorder'] = $kk;
				pdo_insert(BEST_MEMBERGOODSJIETI, $datajt);
			}
		}
		$resArr['error'] = 0;
		$resArr['message'] = '添加成功！';
		echo json_encode($resArr);
		exit;
	}
	public function doMobileMerchant()
	{
		include_once ROOT_PATH . 'inc/mobile/merchantcenter.php';
	}
	public function doMobileMerchantteam()
	{
		include_once ROOT_PATH . 'inc/mobile/merchantteam.php';
	}
	public function doMobileSqtz()
	{
		include_once ROOT_PATH . 'inc/mobile/sqtz.php';
	}
	public function doMobileMerchantorder()
	{
		include_once ROOT_PATH . 'inc/mobile/merchantorder.php';
	}
	public function doMobileMerchantsaomahxy()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$merchant_id = intval($_GPC['merchant_id']);
		if (empty($merchant_id)) {
			$message = '参数错误！';
			include $this->template('error');
			exit;
		}
		$has = pdo_fetch('SELECT id FROM ' . tablename(BEST_HEXIAOYUAN) . " WHERE merchant_id = {$merchant_id} AND openid = '{$member['openid']}'");
		if (!empty($has)) {
			$message = '你已绑定成为核销员！';
			include $this->template('error');
			exit;
		}
		$data = array("weid" => intval($_W['uniacid']), "name" => $member['nickname'], "openid" => $member['openid'], "merchant_id" => $merchant_id);
		pdo_insert(BEST_HEXIAOYUAN, $data);
		$message = '绑定成为核销员成功！';
		include $this->template('error');
		exit;
	}
	public function doMobileSaomahxy()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$toopenid = trim($_GPC['toopenid']);
		if (empty($toopenid)) {
			$message = '参数错误！';
			include $this->template('error');
			exit;
		}
		$has = pdo_fetch('SELECT id FROM ' . tablename(BEST_HEXIAOYUAN) . " WHERE fopenid = '{$toopenid}' AND openid = '{$member['openid']}'");
		if (!empty($has)) {
			$message = '你已绑定成为核销员！';
			include $this->template('error');
			exit;
		}
		$data = array("weid" => intval($_W['uniacid']), "name" => $member['nickname'], "openid" => $member['openid'], "fopenid" => $toopenid);
		pdo_insert(BEST_HEXIAOYUAN, $data);
		$message = '绑定成为核销员成功！';
		include $this->template('error');
		exit;
	}
	public function doMobileJlhexiao()
	{
		include_once ROOT_PATH . 'inc/mobile/jlhexiao.php';
	}
	public function doMobileMerchanthexiao()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$op = trim($_GPC['op']);
		if ($op == '') {
			$hexiaoyuan = pdo_fetch('SELECT * FROM ' . tablename(BEST_HEXIAOYUAN) . " WHERE openid = '{$member['openid']}' AND merchant_id > 0");
			if (empty($hexiaoyuan)) {
				$message = '抱歉，你不是核销员！';
				include $this->template('error');
				exit;
			}
			$orderid = intval($_GPC['id']);
			$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND merchant_id = {$hexiaoyuan['merchant_id']} AND weid = {$_W['uniacid']} AND status = 1 AND ztdid > 0");
			$ordermember = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$orderres['from_user']}'");
			if (empty($orderres)) {
				$message = '抱歉，没有该订单信息或订单已经核销！';
				include $this->template('error');
				exit;
			}
			$allgoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$orderid} AND hexiaonum != total");
			foreach ($allgoods as $k => $v) {
				$allgoods[$k]['left'] = $v['total'] - $v['hexiaonum'];
			}
			include $this->template('merchanthexiao');
		}
		if ($op == 'do') {
			if (!checksubmit('submit')) {
				exit;
			}
			$hexiaoyuan = pdo_fetch('SELECT * FROM ' . tablename(BEST_HEXIAOYUAN) . " WHERE openid = '{$member['openid']}' AND merchant_id > 0");
			if (empty($hexiaoyuan)) {
				$resArr['error'] = 1;
				$resArr['message'] = '抱歉，你不是核销员！';
				echo json_encode($resArr);
				exit;
			}
			$orderid = intval($_GPC['orderid']);
			$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND merchant_id = {$hexiaoyuan['merchant_id']} AND weid = {$_W['uniacid']} AND status = 1 AND ztdid > 0");
			if (empty($orderres)) {
				$resArr['error'] = 1;
				$resArr['message'] = '抱歉，没有该订单信息！';
				echo json_encode($resArr);
				exit;
			}
			foreach ($_GPC['ordergoodsid'] as $k => $v) {
				$hxnum = intval($_GPC['num'][$k]);
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE id = {$v}");
				if ($hxnum > 0) {
					if ($hxnum > $goodsres['total'] - $goodsres['hexiaonum']) {
						$resArr['error'] = 1;
						$tipmsg = $goodsres['optionname'] == '' ? '[' . $goodsres['goodsname'] . ']的核销数量超过限制！' : '[' . $goodsres['goodsname'] . '（' . $goodsres['optionname'] . '）]的核销数量超过限制！';
						$resArr['message'] = $tipmsg;
						echo json_encode($resArr);
						exit;
					}
				} else {
					$resArr['error'] = 1;
					$tipmsg = $goodsres['optionname'] == '' ? '[' . $goodsres['goodsname'] . ']的核销数量必须大于0！' : '[' . $goodsres['goodsname'] . '（' . $goodsres['optionname'] . '）]的核销数量必须大于0！';
					$resArr['message'] = $tipmsg;
					echo json_encode($resArr);
					exit;
				}
			}
			foreach ($_GPC['ordergoodsid'] as $k => $v) {
				$hxnum = intval($_GPC['num'][$k]);
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE id = {$v}");
				if ($hxnum > 0) {
					$data['hexiaonum'] = $goodsres['hexiaonum'] + $hxnum;
					pdo_update(BEST_ORDERGOODS, $data, array("id" => $v));
				}
			}
			$isallhx = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE weid = {$_W['uniacid']} AND orderid = {$orderid} AND total != hexiaonum");
			if (empty($isallhx)) {
				$data2['status'] = 4;
				pdo_update(BEST_ORDER, $data2, array("id" => $orderid));
				if ($orderres['isdmfk'] == 0) {
					$hasmerchantaccount = pdo_fetch('SELECT id FROM ' . tablename(BEST_MERCHANTACCOUNT) . " WHERE merchant_id = {$orderres['merchant_id']} AND orderid = {$orderres['id']}");
					if (empty($hasmerchantaccount)) {
						$datamerchant['weid'] = $_W['uniacid'];
						$datamerchant['merchant_id'] = $orderres['merchant_id'];
						$datamerchant['money'] = $orderres['alllirun'];
						$datamerchant['time'] = TIMESTAMP;
						$datamerchant['explain'] = '代理团结算';
						$datamerchant['orderid'] = $orderres['id'];
						$datamerchant['candotime'] = TIMESTAMP + $this->module['config']['dltxhour'] * 3600;
						pdo_insert(BEST_MERCHANTACCOUNT, $datamerchant);
					}
				}
				$resArr['error'] = 0;
				$resArr['message'] = '核销订单成功[全部]！';
				echo json_encode($resArr);
				exit;
			} else {
				$resArr['error'] = 0;
				$resArr['message'] = '核销订单成功[部分]！';
				echo json_encode($resArr);
				exit;
			}
		}
	}
	public function doMobileMerchantaccount()
	{
		include_once ROOT_PATH . 'inc/mobile/merchantaccount.php';
	}
	public function doMobileMerchantprofile()
	{
		include_once ROOT_PATH . 'inc/mobile/merchantprofile.php';
	}
	public function doMobileMerchantztd()
	{
		include_once ROOT_PATH . 'inc/mobile/merchantztd.php';
	}
	public function doMobileMerchanthxy()
	{
		include_once ROOT_PATH . 'inc/mobile/merchanthxy.php';
	}
	public function doMobileMerchanthd()
	{
		global $_W, $_GPC;
		$merchant = $this->checkmergentauth();
		$hdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} ORDER BY endtime DESC");
		$hascj = $weicj = array();
		foreach ($hdlist as $k => $v) {
			$merchanthd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANTHD) . " WHERE hdid = {$v['id']} AND merchant_id = {$merchant['id']}");
			if (empty($merchanthd)) {
				$weicj[$k] = $v;
			} else {
				$hascj[$k] = $v;
				$hascj[$k]['merchanthdid'] = $merchanthd['id'];
			}
		}
		include $this->template('merchanthd');
	}
	public function doMobileSeegoods()
	{
		global $_W, $_GPC;
		$hdid = intval($_GPC['id']);
		$hdgoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_HUODONGGOODS) . " WHERE weid = {$_W['uniacid']} AND hdid = {$hdid}");
		$goodsarr = array();
		$nowindex = 0;
		foreach ($hdgoods as $k => $v) {
			$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
			if ($goodsres['hasoption'] == 1) {
				$goodsoptions = pdo_fetchall('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE goodsid = {$goodsres['id']}");
				foreach ($goodsoptions as $kk => $vv) {
					$goodsarr[$nowindex]['goods'] = $goodsres;
					$goodsarr[$nowindex]['optionname'] = $vv['title'];
					$goodsarr[$nowindex]['optionid'] = $vv['id'];
					$goodsarr[$nowindex]['option'] = $vv;
					$nowindex++;
				}
			} else {
				$goodsarr[$nowindex]['goods'] = $goodsres;
				$goodsarr[$nowindex]['optionname'] = '';
				$goodsarr[$nowindex]['optionid'] = 0;
				$nowindex++;
			}
		}
		include $this->template('seegoods');
	}
	public function doMobileDohd()
	{
		global $_W, $_GPC;
		$merchant = $this->checkmergentauth();
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$hdid = intval($_GPC['id']);
			$hdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$hdid}");
			if (empty($hdres)) {
				$message = '不存在该活动！';
				include $this->template('error');
				exit;
			}
			if ($hdres['endtime'] < TIMESTAMP) {
				$message = '活动已经结束，不能参加！';
				include $this->template('error');
				exit;
			}
			$merchanthd = pdo_fetch('SELECT id FROM ' . tablename(BEST_MERCHANTHD) . " WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND hdid = {$hdid}");
			if (!empty($merchanthd)) {
				$url = $this->createMobileUrl('myhddetail', array("id" => $merchanthd['id']));
				header('Location:' . $url);
			}
			$hdgoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_HUODONGGOODS) . " WHERE weid = {$_W['uniacid']} AND hdid = {$hdid}");
			$goodsarr = array();
			$nowindex = 0;
			foreach ($hdgoods as $k => $v) {
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
				if ($goodsres['hasoption'] == 1) {
					$goodsoptions = pdo_fetchall('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE goodsid = {$goodsres['id']}");
					foreach ($goodsoptions as $kk => $vv) {
						$goodsarr[$nowindex]['goods'] = $goodsres;
						$goodsarr[$nowindex]['optionname'] = $vv['title'];
						$goodsarr[$nowindex]['optionid'] = $vv['id'];
						$goodsarr[$nowindex]['option'] = $vv;
						$nowindex++;
					}
				} else {
					$goodsarr[$nowindex]['goods'] = $goodsres;
					$goodsarr[$nowindex]['optionname'] = '';
					$goodsarr[$nowindex]['optionid'] = 0;
					$nowindex++;
				}
			}
			include $this->template('dohd');
		} else {
			if ($operation == 'do') {
				if (!checksubmit('submit')) {
					exit;
				}
				$hdid = intval($_GPC['id']);
				$hdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$hdid}");
				if (empty($hdres)) {
					$resArr['error'] = 1;
					$resArr['message'] = '不存在该活动！';
					echo json_encode($resArr);
					exit;
				}
				if ($merchant['id'] == 0) {
					$resArr['error'] = 1;
					$resArr['message'] = '请不要重复提交！';
					echo json_encode($resArr);
					exit;
				}
				if ($hdres['endtime'] < TIMESTAMP) {
					$resArr['error'] = 1;
					$resArr['message'] = '活动已经结束，不能参加！';
					echo json_encode($resArr);
					exit;
				}
				$goodsidarr = $_GPC['goodsid'];
				if (!empty($goodsidarr)) {
					$hasmerchanthd = pdo_fetch('SELECT id FROM ' . tablename(BEST_MERCHANTHD) . " WHERE merchant_id = {$merchant['id']} AND hdid = {$hdid}");
					if (empty($hasmerchanthd)) {
						$data2['weid'] = $_W['uniacid'];
						$data2['hdid'] = $hdid;
						$data2['merchant_id'] = $merchant['id'];
						$data2['time'] = TIMESTAMP;
						$data2['sharetitle'] = $hdres['sharetitle'];
						$data2['sharethumb'] = $hdres['sharethumb'];
						$data2['sharedes'] = $hdres['sharedes'];
						$data2['canziti'] = 1;
						pdo_insert(BEST_MERCHANTHD, $data2);
						$mhdid = pdo_insertid();
						foreach ($goodsidarr as $k => $v) {
							$goodsoptionid = explode('-', $v);
							$data['weid'] = $_W['uniacid'];
							$data['mhdid'] = $mhdid;
							$data['optionid'] = $goodsoptionid[1];
							$data['goodsid'] = $goodsoptionid[0];
							$data['time'] = TIMESTAMP;
							pdo_insert(BEST_MERCHANTHDGOODS, $data);
						}
						$resArr['error'] = 0;
						$resArr['message'] = '参加活动成功！';
						echo json_encode($resArr);
						exit;
					}
				} else {
					$resArr['error'] = 1;
					$resArr['message'] = '没有选择任何商品，不能参加活动！';
					echo json_encode($resArr);
					exit;
				}
			}
		}
	}
	public function doMobileMyhddetail()
	{
		global $_W, $_GPC;
		$merchant = $this->checkmergentauth();
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$id = intval($_GPC['id']);
			$merchanthd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANTHD) . " WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND id = {$id}");
			if (empty($merchanthd)) {
				$message = '不存在该活动！';
				include $this->template('error');
				exit;
			}
			$hdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$merchanthd['hdid']}");
			$merchantgoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MERCHANTHDGOODS) . " WHERE mhdid = {$id} AND weid = {$_W['uniacid']}");
			foreach ($merchantgoods as $k => $v) {
				$merchantgoods[$k]['goods'] = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
				if ($v['optionid'] > 0) {
					$merchantgoods[$k]['option'] = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE id = {$v['optionid']}");
					$merchantgoods[$k]['sales'] = pdo_fetchcolumn('SELECT SUM(a.total),b.id FROM ' . tablename(BEST_ORDERGOODS) . ' as a,' . tablename(BEST_ORDER) . " as b WHERE a.mhdid = {$id} AND a.optionid = {$v['optionid']} AND b.status >= 1 AND a.orderid = b.id");
					$merchantgoods[$k]['sales'] = empty($merchantgoods[$k]['sales']) ? 0 : $merchantgoods[$k]['sales'];
				} else {
					$merchantgoods[$k]['sales'] = pdo_fetchcolumn('SELECT SUM(a.total),b.id FROM ' . tablename(BEST_ORDERGOODS) . ' as a,' . tablename(BEST_ORDER) . " as b WHERE a.mhdid = {$id} AND a.goodsid = {$v['goodsid']} AND b.status >= 1 AND a.orderid = b.id");
					$merchantgoods[$k]['sales'] = empty($merchantgoods[$k]['sales']) ? 0 : $merchantgoods[$k]['sales'];
				}
			}
			$allprice = pdo_fetchcolumn('SELECT SUM(price) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND mhdid = {$id} AND status >= 1");
			$allprice = empty($allprice) ? '0.00' : $allprice;
			$alllirun = pdo_fetchcolumn('SELECT SUM(alllirun) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND mhdid = {$id} AND status >= 1");
			$alllirun = empty($alllirun) ? '0.00' : $alllirun;
			$allordernum = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND merchant_id = {$merchant['id']} AND mhdid = {$id} AND status >= 1");
			include $this->template('myhddetail');
		} else {
			if ($operation == 'do') {
				$id = intval($_GPC['id']);
				$data['sharetitle'] = trim($_GPC['sharetitle']);
				$data['sharedes'] = trim($_GPC['sharedes']);
				$data['sharethumb'] = trim($_GPC['sharethumb']);
				$data['yunfei'] = $_GPC['yunfei'];
				$data['manjian'] = $_GPC['manjian'];
				$data['canziti'] = intval($_GPC['canziti']);
				$data['cansonghuo'] = intval($_GPC['cansonghuo']);
				$data['candmfk'] = intval($_GPC['candmfk']);
				$data['daojishi'] = intval($_GPC['daojishi']);
				$data['buydetail'] = intval($_GPC['buydetail']);
				pdo_update(BEST_MERCHANTHD, $data, array("id" => $id));
				foreach ($_GPC['merhdgid'] as $k => $v) {
					if ($_GPC['dlprice'][$k] > 0) {
						pdo_update(BEST_MERCHANTHDGOODS, array("dlprice" => $_GPC['dlprice'][$k]), array("id" => $v));
					}
				}
				$resArr['error'] = 0;
				$resArr['message'] = '更新活动信息成功！';
				echo json_encode($resArr);
				exit;
			}
		}
	}
	public function doMobileHdpeihuodan()
	{
		include_once ROOT_PATH . 'inc/mobile/hdpeihuodan.php';
	}
	public function doMobileDaohuotz()
	{
		include_once ROOT_PATH . 'inc/mobile/daohuotz.php';
	}
	private function checkmergentauth()
	{
		global $_W;
		$merchant = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANT) . " WHERE openid = '{$_W['fans']['from_user']}' AND weid = {$_W['uniacid']}");
		if (empty($merchant) || $merchant['status'] == 0) {
			header('Location:' . $this->createMobileUrl('merchantcenter'));
		}
		return $merchant;
	}
	public function doMobileHuodong()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$type = intval($_GPC['type']);
		$mhdid = intval($_GPC['id']);
		$merchanthd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANTHD) . " WHERE weid = {$_W['uniacid']} AND id = {$mhdid}");
		$hdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$merchanthd['hdid']}");
		if ($hdres['pstype'] == 1) {
			$cansonghuo = $merchanthd['cansonghuo'];
			$canziti = $merchanthd['canziti'];
			$manjian = $merchanthd['manjian'];
			$hdres['candmfk'] = $merchanthd['candmfk'];
		} else {
			$cansonghuo = $hdres['cansonghuo'];
			$canziti = $hdres['canziti'];
			$manjian = $hdres['manjian'];
		}
		if ($hdres['tqjs'] == 1) {
			$iserror = 1;
			$errormessage = '活动已经结束';
		}
		if ($hdres['starttime'] > TIMESTAMP) {
			$iserror = 1;
			$errormessage = '活动还未开始';
		}
		if ($hdres['endtime'] < TIMESTAMP) {
			$iserror = 1;
			$errormessage = '活动已经结束';
		}
		$merchant = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANT) . " WHERE weid = {$_W['uniacid']} AND id = {$merchanthd['merchant_id']}");
		$ztdlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ZITIDIAN) . " WHERE openid = '{$merchant['openid']}' AND weid = {$_W['uniacid']} AND ztdtype = 1");
		$goodslist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_MERCHANTHDGOODS) . " WHERE mhdid = {$mhdid}");
		foreach ($goodslist as $k => $v) {
			$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$v['goodsid']}");
			if ($v['optionid'] > 0) {
				$option = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE id = {$v['optionid']}");
				if ($v['dlprice'] >= $option['dailiprice']) {
					$goodslist[$k]['saleprice'] = $v['dlprice'];
				} else {
					$goodslist[$k]['saleprice'] = $option['normalprice'];
				}
				$goodslist[$k]['option'] = $option;
			} else {
				if ($v['dlprice'] >= $goodsres['dailiprice']) {
					$goodslist[$k]['saleprice'] = $v['dlprice'];
				} else {
					$goodslist[$k]['saleprice'] = $goodsres['normalprice'];
				}
			}
			$thumbs = unserialize($goodsres['thumbs']);
			$goodslist[$k]['goods'] = $goodsres;
			if (empty($thumbs)) {
				$goodslist[$k]['count'] = 1;
			} else {
				$goodslist[$k]['count'] = count($thumbs) + 1;
			}
		}
		if ($merchanthd['daojishi'] == 1) {
			if ($hdres['starttime'] > TIMESTAMP) {
				$djs = '距离开抢还有';
				$djstime = $hdres['starttime'];
				$meikaishi = 1;
			}
			if ($hdres['starttime'] < TIMESTAMP && TIMESTAMP < $hdres['endtime']) {
				$djs = '后结束';
				$djstime = $hdres['endtime'];
				$meikaishi = 0;
			}
		} else {
			$djstime = 0;
		}
		if ($merchanthd['buydetail'] == 1) {
			$buylistthree = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE mhdid = {$merchanthd['id']} AND isjl = 0 AND status >= 1 ORDER BY createtime DESC LIMIT 10");
			foreach ($buylistthree as $k => $v) {
				$buylistthree[$k]['member'] = pdo_fetch('SELECT avatar,nickname FROM ' . tablename(BEST_MEMBER) . " WHERE openid = '{$v['from_user']}'");
				$buylistthree[$k]['goods'] = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$v['id']}");
			}
		}
		$color = empty($this->module['config']['temcolor']) ? '#E64340' : $this->module['config']['temcolor'];
		$shareurl = $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('huodong', array("id" => $mhdid, "type" => $type)));
		$shenglist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_CITY) . ' WHERE type = 1');
		include $this->template('huodong');
	}
	public function doMobileGetcitys2()
	{
		global $_W, $_GPC;
		$fcode = trim($_GPC['fcode']);
		$citys = pdo_fetchall('SELECT name,code FROM ' . tablename(BEST_CITY) . " WHERE fcode = '{$fcode}' AND type = 2");
		foreach ($citys as $k => $v) {
			$html .= '<div class="messi-liandong-item-item erjiquyu textellipsis1" data-code="' . $v['code'] . '">' . $v['name'] . '</div>';
		}
		echo $html;
		exit;
	}
	public function doMobileGetdistricts2()
	{
		global $_W, $_GPC;
		$fcode = trim($_GPC['fcode']);
		$districts = pdo_fetchall('SELECT name FROM ' . tablename(BEST_CITY) . " WHERE fcode = '{$fcode}' AND type = 3");
		foreach ($districts as $k => $v) {
			$html .= '<div class="messi-liandong-item-item sanjiquyu textellipsis1" data-code="' . $v['code'] . '">' . $v['name'] . '</div>';
		}
		echo $html;
		exit;
	}
	public function doMobileGdetail()
	{
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$goods = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE id = {$id} AND weid = {$_W['uniacid']}");
		include $this->template('gdetail');
	}
	public function doMobileMyorder()
	{
		global $_W, $_GPC;
		$member = $this->Mcheckmember();
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 0 AND from_user = '{$member['openid']}' AND status >= 0");
			$allpage = ceil($total / 10) + 1;
			$page = intval($_GPC['page']);
			$pindex = max(1, $page);
			$psize = 10;
			$orderlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND isjl = 0 AND from_user = '{$member['openid']}' AND status >= 0 ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			foreach ($orderlist as $k => $v) {
				$merchant = pdo_fetch('SELECT name FROM ' . tablename(BEST_MERCHANT) . " WHERE weid = {$_W['uniacid']} AND id = {$v['merchant_id']}");
				$orderlist[$k]['merchantname'] = $merchant['name'];
			}
			$isajax = intval($_GPC['isajax']);
			if ($isajax == 1) {
				$html = '';
				foreach ($orderlist as $k => $v) {
					if ($v['status'] == 0) {
						$statustext = '待付款';
					}
					if ($v['status'] == 1) {
						$statustext = $v['pstype'] == 0 || $v['pstype'] == 3 ? '待发货' : '待自提';
					}
					if ($v['status'] == 2) {
						$statustext = '待收货';
					}
					if ($v['status'] == 4) {
						$statustext = '已完成';
					}
					$html .= '<div class="orderitem">
								<div class="name flex">
									<div class="storename">店铺：' . $v['merchantname'] . '</div>
									<div class="orderstatus text-r">' . $statustext . '</div>
								</div>
								<div class="ordersn">订单编号：' . $v['ordersn'] . '</div>
								<div class="price flex">
									<div class="orderprice">合计：<span>￥' . $v['price'] . '</span></div>
									<div class="orderbutton text-r"><a href="' . $this->createMobileUrl('myorder', array("op" => "detail", "ordersn" => $v['ordersn'])) . '">订单详情</a></div>
								</div>
							</div>';
				}
				echo $html;
				exit;
			} else {
				include $this->template('myorder');
			}
		} else {
			if ($operation == 'search') {
				$keyword = trim($_GPC['keyword']);
				if ($keyword == '') {
					$resArr['error'] = 1;
					$resArr['message'] = '输入订单号搜索！';
					echo json_encode($resArr);
					exit;
				}
				$orderlist = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE weid = {$_W['uniacid']} AND from_user = '{$member['openid']}' AND ordersn like '%{$keyword}%' ORDER BY createtime DESC");
				foreach ($orderlist as $k => $v) {
					$merchant = pdo_fetch('SELECT name FROM ' . tablename(BEST_MERCHANT) . " WHERE weid = {$_W['uniacid']} AND id = {$v['merchant_id']}");
					$orderlist[$k]['merchantname'] = $merchant['name'];
				}
				if (empty($orderlist)) {
					$html = '<div class="nodata text-c">
							<div class="iconfont">&#xe67c;</div>
							<div class="text">( ⊙ o ⊙ )啊哦，没有搜索记录啦~</div>
						</div>';
				} else {
					$html = '';
					foreach ($orderlist as $k => $v) {
						if ($v['status'] == 0) {
							$statustext = '待付款';
						}
						if ($v['status'] == 1) {
							$statustext = $v['pstype'] == 0 || $v['pstype'] == 3 ? '待发货' : '待自提';
						}
						if ($v['status'] == 2) {
							$statustext = '待收货';
						}
						if ($v['status'] == 4) {
							$statustext = '已完成';
						}
						$html .= '<div class="orderitem">
								<div class="name flex">
									<div class="storename">店铺：' . $v['merchantname'] . '</div>
									<div class="orderstatus text-r">' . $statustext . '</div>
								</div>
								<div class="ordersn">订单编号：' . $v['ordersn'] . '</div>
								<div class="price flex">
									<div class="orderprice">合计：<span>￥' . $v['price'] . '</span></div>
									<div class="orderbutton text-r"><a href="' . $this->createMobileUrl('myorder', array("op" => "detail", "id" => $v['id'])) . '">订单详情</a></div>
								</div>
							</div>';
					}
				}
				$resArr['error'] = 0;
				$resArr['html'] = $html;
				echo json_encode($resArr);
				exit;
			} else {
				if ($operation == 'detail') {
					$ordersn = trim($_GPC['ordersn']);
					$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE ordersn = '{$ordersn}' AND from_user = '{$member['openid']}'");
					if (empty($orderres)) {
						$message = '没有该订单！';
						include $this->template('error');
						exit;
					}
					$huodongres = pdo_fetch('SELECT endtime FROM ' . tablename(BEST_HUODONG) . " WHERE id = {$orderres['hdid']}");
					if ($huodongres['endtime'] < TIMESTAMP) {
						$orderres['canfukuan'] = 0;
					} else {
						$orderres['canfukuan'] = 1;
					}
					$ordergoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$orderres['id']}");
					if ($orderres['status'] == 0 && ($orderres['canfukuan'] = 1)) {
						foreach ($ordergoods as $k => $v) {
							$goodsres = pdo_fetch('SELECT total FROM ' . tablename(BEST_GOODS) . " WHERE id = {$v['goodsid']}");
							if ($goodsres['total'] < $v['total']) {
								$orderres['canfukuan'] = 0;
							}
						}
					} else {
						$orderres['canfukuan'] = 0;
					}
					if ($orderres['pstype'] == 0 || $orderres['pstype'] == 3) {
						$address = explode('|', $orderres['address']);
					}
					if ($orderres['expresscom'] != 'ZFPS' && $orderres['expresscom'] != 'SF' && $orderres['expresscom'] != '') {
						include_once ROOT_PATH . 'Express.class.php';
						$idkdn = $this->module['config']['kdnid'];
						$keykdn = $this->module['config']['kdnkey'];
						$urlkdn = 'http://api.kdniao.cc/api/dist';
						$shipperCode = $orderres['expresscom'];
						$logisticCode = $orderres['expresssn'];
						$a = new Express();
						$logisticResult = $a->getOrderTracesByJson($idkdn, $keykdn, $urlkdn, $shipperCode, $logisticCode);
						$data = json_decode($logisticResult, true);
						if ($data['State'] != '0') {
							$expressres = $data['Traces'];
							rsort($expressres);
						}
					}
					$hdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE id = {$orderres['hdid']}");
					include $this->template('myorderdetail');
				} else {
					if ($operation == 'shouhuo') {
						$orderid = intval($_GPC['orderid']);
						$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND status = 2 AND from_user = '{$member['openid']}'");
						if (empty($orderres)) {
							$resArr['error'] = 1;
							$resArr['message'] = '没有该订单！';
							echo json_encode($resArr);
							exit;
						}
						$data['status'] = 4;
						pdo_update(BEST_ORDER, $data, array("id" => $orderres['id']));
						if ($orderres['isdmfk'] == 0) {
							$hasmerchantaccount = pdo_fetch('SELECT id FROM ' . tablename(BEST_MERCHANTACCOUNT) . " WHERE merchant_id = {$orderres['merchant_id']} AND orderid = {$orderres['id']}");
							if (empty($hasmerchantaccount)) {
								$datamerchant['weid'] = $_W['uniacid'];
								$datamerchant['merchant_id'] = $orderres['merchant_id'];
								$datamerchant['money'] = $orderres['alllirun'];
								$datamerchant['time'] = TIMESTAMP;
								$datamerchant['explain'] = '代理团结算';
								$datamerchant['orderid'] = $orderres['id'];
								$datamerchant['candotime'] = TIMESTAMP + $this->module['config']['dltxhour'] * 3600;
								pdo_insert(BEST_MERCHANTACCOUNT, $datamerchant);
							}
						}
						$resArr['error'] = 0;
						$resArr['message'] = '确认收货成功！';
						echo json_encode($resArr);
						exit;
					} else {
						if ($operation == 'refund') {
							$orderid = intval($_GPC['orderid']);
							$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND (status = 1 OR status = 2) AND from_user = '{$member['openid']}'");
							if (empty($orderres)) {
								$message = '没有该订单！';
								include $this->template('error');
								exit;
							}
							if ($orderres['isdmfk'] == 1) {
								$message = '该订单不能退款！';
								include $this->template('error');
								exit;
							}
							if ($orderres['cantktime'] < TIMESTAMP) {
								$message = '已经超过允许的退款时间！';
								include $this->template('error');
								exit;
							}
							$refund_price = $orderres['price'] - $orderres['yunfei'];
							include $this->template('myorderrefund');
						} else {
							if ($operation == 'dorefund') {
								$orderid = intval($_GPC['orderid']);
								$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND (status = 1 OR status = 2) AND from_user = '{$member['openid']}'");
								if (empty($orderres)) {
									$resArr['error'] = 1;
									$resArr['message'] = '没有该订单！';
									echo json_encode($resArr);
									exit;
								}
								if ($orderres['isdmfk'] == 1) {
									$resArr['error'] = 1;
									$resArr['message'] = '该订单不能退款！';
									echo json_encode($resArr);
									exit;
								}
								if ($orderres['cantktime'] < TIMESTAMP) {
									$resArr['error'] = 1;
									$resArr['message'] = '已经超过允许的退款时间！';
									echo json_encode($resArr);
									exit;
								}
								$refund_price = $_GPC['refund_price'];
								if ($refund_price <= 0) {
									$resArr['error'] = 1;
									$resArr['message'] = '请填写退款金额！';
									echo json_encode($resArr);
									exit;
								}
								$can_refund_price = $orderres['price'] - $orderres['yunfei'];
								if ($refund_price > $can_refund_price) {
									$resArr['error'] = 1;
									$resArr['message'] = '退款金额超限！';
									echo json_encode($resArr);
									exit;
								}
								$refund_desc = trim($_GPC['refund_desc']);
								if (empty($refund_desc)) {
									$resArr['error'] = 1;
									$resArr['message'] = '请填写退款原因！';
									echo json_encode($resArr);
									exit;
								}
								$data['refund_desc'] = $refund_desc;
								$data['tktime'] = TIMESTAMP;
								$data['refund_price'] = $refund_price;
								$data['status'] = -2;
								pdo_update(BEST_ORDER, $data, array("id" => $orderres['id']));
								$resArr['error'] = 0;
								$resArr['message'] = '申请退款成功！';
								echo json_encode($resArr);
								exit;
							} else {
								if ($operation == 'cancelorder') {
									$ordersn = trim($_GPC['ordersn']);
									$order = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE ordersn = '{$ordersn}' AND status = 0 AND from_user = '{$member['openid']}'");
									if (empty($order)) {
										$resArr['error'] = 1;
										$resArr['message'] = '不存在该订单！';
										echo json_encode($resArr);
										exit;
									} else {
										$datacancel['status'] = -1;
										pdo_update(BEST_ORDER, $datacancel, array("id" => $order['id']));
										$resArr['error'] = 0;
										$resArr['message'] = '取消订单成功！';
										echo json_encode($resArr);
										exit;
									}
								} else {
									$message = '请求方式不存在！';
									include $this->template('error');
									exit;
								}
							}
						}
					}
				}
			}
		}
	}
	public function doMobileSuborder()
	{
		global $_W, $_GPC;
		if (!checksubmit('submit')) {
			exit;
		}
		$member = $this->Mcheckmember();
		$gunms = $_GPC['gnum'];
		$allnum = 0;
		$allprice = 0;
		$alllirun = 0;
		$hdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$_GPC['hdid']}");
		if ($hdres['tqjs'] == 1) {
			$resArr['error'] = 1;
			$resArr['message'] = '活动已经提前结束！';
			echo json_encode($resArr);
			exit;
		}
		if ($hdres['starttime'] > TIMESTAMP) {
			$resArr['error'] = 1;
			$resArr['message'] = '活动还未开始！';
			echo json_encode($resArr);
			exit;
		}
		if ($hdres['endtime'] < TIMESTAMP) {
			$resArr['error'] = 1;
			$resArr['message'] = '活动已经结束！';
			echo json_encode($resArr);
			exit;
		}
		$pstype = intval($_GPC['pstype']);
		if ($pstype != 0 && $pstype != 1) {
			$resArr['error'] = 1;
			$resArr['message'] = '请选择配送方式！';
			echo json_encode($resArr);
			exit;
		}
		foreach ($gunms as $k => $v) {
			$goodsid = intval($_GPC['goodsid'][$k]);
			$optionid = intval($_GPC['optionid'][$k]);
			if ($v > 0) {
				$merchanthdgoods = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANTHDGOODS) . " WHERE goodsid = {$goodsid} AND optionid = {$optionid} AND mhdid = {$_GPC['mhdid']}");
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$goodsid}");
				$optionres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE goodsid = {$goodsid} AND id = {$optionid}");
				if (!empty($optionres)) {
					if ($v > $optionres['stock']) {
						$resArr['error'] = 1;
						$resArr['message'] = '商品' . $goodsres['title'] . '[' . $optionres['title'] . ']' . '库存不足！';
						echo json_encode($resArr);
						exit;
					}
					if ($merchanthdgoods['dlprice'] >= $optionres['dailiprice']) {
						$allprice += $merchanthdgoods['dlprice'] * $v;
						$alllirun += ($merchanthdgoods['dlprice'] - $optionres['dailiprice']) * $v;
					} else {
						$allprice += $optionres['normalprice'] * $v;
						$alllirun += ($optionres['normalprice'] - $optionres['dailiprice']) * $v;
					}
				} else {
					if ($v > $goodsres['total']) {
						$resArr['error'] = 1;
						$resArr['message'] = '商品' . $goodsres['title'] . '库存不足！';
						echo json_encode($resArr);
						exit;
					}
					if ($merchanthdgoods['dlprice'] >= $goodsres['dailiprice']) {
						$allprice += $merchanthdgoods['dlprice'] * $v;
						$alllirun += ($merchanthdgoods['dlprice'] - $goodsres['dailiprice']) * $v;
					} else {
						$allprice += $goodsres['normalprice'] * $v;
						$alllirun += ($goodsres['normalprice'] - $goodsres['dailiprice']) * $v;
					}
				}
			}
			$allnum += $v;
		}
		if ($allnum <= 0) {
			$resArr['error'] = 1;
			$resArr['message'] = '请选择需要购买的商品！';
			echo json_encode($resArr);
			exit;
		}
		if ($allprice <= 0) {
			$resArr['error'] = 1;
			$resArr['message'] = '订单总金额不得少于0元！';
			echo json_encode($resArr);
			exit;
		}
		$data['price'] = $allprice;
		$data['alllirun'] = $alllirun;
		if ($pstype == 0) {
			$shname = trim($_GPC['shname']);
			$shphone = trim($_GPC['shphone']);
			$shcity = trim($_GPC['shcity']);
			$shaddress = trim($_GPC['shaddress']);
			if (empty($shname)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写收货人姓名！';
				echo json_encode($resArr);
				exit;
			}
			if (!$this->isMobile($shphone)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写正确的收货人手机号码！';
				echo json_encode($resArr);
				exit;
			}
			if (empty($shcity)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请选择区域！';
				echo json_encode($resArr);
				exit;
			}
			if (empty($shaddress)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写详细地址！';
				echo json_encode($resArr);
				exit;
			}
			if ($hdres['pstype'] == 0) {
				$diquarr = explode(' ', $shcity);
				$sheng = $diquarr[0];
				$shi = $diquarr[1];
				$xian = $diquarr[2];
				$yfsheng1 = pdo_fetch('SELECT * FROM ' . tablename(BEST_YUNFEISHENG) . " WHERE yfid = {$hdres['yfid']} AND diqutype = 3 AND name = '{$sheng}' AND city = '{$shi}' AND xian = '{$xian}'");
				$yfsheng2 = pdo_fetch('SELECT * FROM ' . tablename(BEST_YUNFEISHENG) . " WHERE yfid = {$hdres['yfid']} AND diqutype = 2 AND name = '{$sheng}' AND city = '{$shi}' AND xian = ''");
				$yfsheng3 = pdo_fetch('SELECT * FROM ' . tablename(BEST_YUNFEISHENG) . " WHERE yfid = {$hdres['yfid']} AND diqutype = 1 AND name = '{$sheng}' AND city = '' AND xian = ''");
				if (empty($yfsheng1) && empty($yfsheng2) && empty($yfsheng3)) {
					$resArr['error'] = 1;
					$resArr['message'] = '不在活动售卖区域不能提交订单！';
					echo json_encode($resArr);
					exit;
				}
				if ($data['price'] >= $hdres['manjian']) {
					$data['yunfei'] = 0;
				} else {
					if (!empty($yfsheng1)) {
						$data['yunfei'] = $yfsheng1['money'];
					}
					if (!empty($yfsheng2) && empty($yfsheng1)) {
						$data['yunfei'] = $yfsheng2['money'];
					}
					if (!empty($yfsheng3) && empty($yfsheng1) && empty($yfsheng2)) {
						$data['yunfei'] = $yfsheng3['money'];
					}
				}
				$data['remark'] = $_GPC['remark'];
			} else {
				$merchanthd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANTHD) . " WHERE weid = {$_W['uniacid']} AND id = {$_GPC['mhdid']}");
				if ($data['price'] >= $merchanthd['manjian']) {
					$data['yunfei'] = 0;
				} else {
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
			$data['address'] = $shname . '|' . $shphone . '|' . $shcity . '|' . $shaddress;
			$data['price'] = $data['price'] + $data['yunfei'];
			$data['isdmfk'] = intval($_GPC['isdmfk']);
			if ($data['isdmfk'] == 1) {
				$data['status'] = 2;
			}
		} else {
			$shphone = trim($_GPC['shphone2']);
			if (!$this->isMobile($shphone)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写自提所需的手机号码！';
				echo json_encode($resArr);
				exit;
			}
			$ztdid = intval($_GPC['ztdid']);
			$ztdres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ZITIDIAN) . " WHERE id = {$ztdid}");
			if (empty($ztdres)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请选择自提点！';
				echo json_encode($resArr);
				exit;
			}
			$data['ztdid'] = $ztdid;
			$data['ztdaddress'] = $ztdres['address'];
			$data['ztdjingdu'] = $ztdres['jingdu'];
			$data['ztdweidu'] = $ztdres['weidu'];
			$data['address'] = $datam['shphone'] = $shphone;
			$data['yunfei'] = 0;
			if ($hdres['pstype'] == 1) {
				$pstype = 4;
			}
			$data['isdmfk'] = intval($_GPC['isdmfk']);
			if ($data['isdmfk'] == 1) {
				$data['status'] = 1;
			}
		}
		if ($hdres['autofield'] == 1) {
			$isidcard = $this->isCard($_GPC['idcard']);
			if (empty($isidcard)) {
				$resArr['error'] = 1;
				$resArr['message'] = '请输入正确的身份证号！';
				echo json_encode($resArr);
				exit;
			}
			$data['otheraddress'] = $_GPC['idcard'] . '(身份证)';
		}
		if ($hdres['autofield'] == 2) {
			if (empty($_GPC['wxcode'])) {
				$resArr['error'] = 1;
				$resArr['message'] = '请填写微信号！';
				echo json_encode($resArr);
				exit;
			}
			$data['otheraddress'] = $_GPC['wxcode'] . '(微信号)';
		}
		$data['weid'] = $_W['uniacid'];
		$data['pstype'] = $pstype;
		$data['from_user'] = $member['openid'];
		$data['ordersn'] = date('Ymd') . random(13, 1);
		$data['merchant_id'] = intval($_GPC['merchant_id']);
		$data['createtime'] = TIMESTAMP;
		$data['hdid'] = intval($_GPC['hdid']);
		$data['mhdid'] = intval($_GPC['mhdid']);
		if (isset($this->module['config']['dltkhour'])) {
			$data['cantktime'] = $hdres['endtime'] - $this->module['config']['dltkhour'] * 3600;
		}
		pdo_insert(BEST_ORDER, $data);
		$orderid = pdo_insertid();
		pdo_update(BEST_MEMBER, $datam, array("openid" => $member['openid']));
		foreach ($gunms as $k => $v) {
			$goodsid = intval($_GPC['goodsid'][$k]);
			$optionid = intval($_GPC['optionid'][$k]);
			if ($v > 0) {
				$merchanthdgoods = pdo_fetch('SELECT * FROM ' . tablename(BEST_MERCHANTHDGOODS) . " WHERE goodsid = {$goodsid} AND optionid = {$optionid} AND mhdid = {$_GPC['mhdid']}");
				$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE weid = {$_W['uniacid']} AND id = {$goodsid}");
				$optionres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE goodsid = {$goodsid} AND id = {$optionid}");
				if (!empty($optionres)) {
					if ($merchanthdgoods['dlprice'] >= $optionres['dailiprice']) {
						$datao['price'] = $merchanthdgoods['dlprice'];
						$datao['lirun'] = ($merchanthdgoods['dlprice'] - $optionres['dailiprice']) * $v;
					} else {
						$datao['price'] = $optionres['normalprice'];
						$datao['lirun'] = ($optionres['normalprice'] - $optionres['dailiprice']) * $v;
					}
					$datao['weid'] = $_W['uniacid'];
					$datao['optionid'] = $optionid;
					$datao['total'] = $v;
					$datao['cbprice'] = $optionres['chengbenprice'];
					$datao['dlprice'] = $optionres['dailiprice'];
					$datao['goodsid'] = $goodsid;
					$datao['createtime'] = TIMESTAMP;
					$datao['orderid'] = $orderid;
					$datao['goodsname'] = $goodsres['title'];
					$datao['optionname'] = $optionres['title'];
				} else {
					if ($merchanthdgoods['dlprice'] >= $goodsres['dailiprice']) {
						$datao['price'] = $merchanthdgoods['dlprice'];
						$datao['lirun'] = ($merchanthdgoods['dlprice'] - $goodsres['dailiprice']) * $v;
					} else {
						$datao['price'] = $goodsres['normalprice'];
						$datao['lirun'] = ($goodsres['normalprice'] - $goodsres['dailiprice']) * $v;
					}
					$datao['weid'] = $_W['uniacid'];
					$datao['optionid'] = 0;
					$datao['total'] = $v;
					$datao['cbprice'] = $goodsres['chengbenprice'];
					$datao['dlprice'] = $goodsres['dailiprice'];
					$datao['goodsid'] = $goodsid;
					$datao['createtime'] = TIMESTAMP;
					$datao['orderid'] = $orderid;
					$datao['goodsname'] = $goodsres['title'];
					$datao['optionname'] = '';
				}
				$datao['hdid'] = intval($_GPC['hdid']);
				$datao['mhdid'] = intval($_GPC['mhdid']);
				pdo_insert(BEST_ORDERGOODS, $datao);
			}
		}
		$resArr['error'] = 0;
		$resArr['status'] = $data['status'];
		$resArr['fee'] = $allprice + $data['yunfei'];
		$resArr['ordertid'] = $data['ordersn'];
		$resArr['message'] = '提交订单成功！';
		echo json_encode($resArr);
		exit;
	}
	public function payResult($params)
	{
		global $_W;
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `tid`=:tid';
			$pars = array();
			$pars[':tid'] = $params['tid'];
			$log = pdo_fetch($sql, $pars);
			$paydetail = $log['tag'];
			$logtag = unserialize($log['tag']);
			$ordersnlen = strlen($params['tid']);
			if ($ordersnlen == 19) {
				pdo_update(BEST_TZORDER, array("status" => 1, "transid" => $logtag['transaction_id'], "paydetail" => $paydetail), array("ordersn" => $params['tid']));
				$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_TZORDER) . " WHERE ordersn = '{$params['tid']}' AND weid = {$_W['uniacid']}");
				$data['istz'] = 1;
				$data['tztime'] = TIMESTAMP;
				$data['tzintype'] = 1;
				pdo_update(BEST_MERCHANT, $data, array("openid" => $orderres['openid']));
			}
			if ($ordersnlen == 20) {
				pdo_update(BEST_MEMBERRZ, array("rzstatus" => 1, "rztransid" => $logtag['transaction_id'], "rzpaydetail" => $paydetail), array("rzordersn" => $params['tid']));
			}
			if ($ordersnlen == 21) {
				pdo_update(BEST_ORDER, array("status" => 1, "transid" => $logtag['transaction_id'], "paydetail" => $paydetail), array("ordersn" => $params['tid']));
				$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE ordersn = '{$params['tid']}' AND weid = {$_W['uniacid']}");
				$ordergoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$orderres['id']}");
				foreach ($ordergoods as $k => $v) {
					$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE id = {$v['goodsid']} AND weid = {$_W['uniacid']}");
					$datagoods['total'] = $goodsres['total'] - $v['total'];
					pdo_update(BEST_GOODS, $datagoods, array("id" => $v['goodsid']));
					if ($v['optionid'] > 0) {
						$goodsoptionres = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODSOPTION) . " WHERE id = {$v['optionid']}");
						$datagoodsoption['stock'] = $goodsoptionres['stock'] - $v['total'];
						pdo_update(BEST_GOODSOPTION, $datagoodsoption, array("id" => $v['optionid']));
					}
				}
				if ($this->module['config']['istplon'] == 1) {
					if ($orderres['pstype'] == 0 || $orderres['pstype'] == 3) {
						$address_tplarr = explode('|', $orderres['address']);
						$realname_tpl = $address_tplarr[0];
						$phone_tpl = $address_tplarr[1];
						$address_tpl = $address_tplarr[2] . $address_tplarr[3];
					} else {
						$realname_tpl = $address_tpl = '自提订单无需信息';
						$phone_tpl = $orderres['address'];
					}
					$merchant = pdo_fetch('SELECT openid FROM ' . tablename(BEST_MERCHANT) . " WHERE weid = {$_W['uniacid']} AND id = {$orderres['merchant_id']}");
					$or_paysuccess_redirect = $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('merchantorder', array("op" => "detail", "id" => $orderres['id'])));
					$postdata = array("first" => array("value" => "用户下单通知！", "color" => "#ff510"), "keyword1" => array("value" => $orderres['ordersn'], "color" => "#ff510"), "keyword2" => array("value" => $orderres['price'], "color" => "#ff510"), "keyword3" => array("value" => $realname_tpl, "color" => "#ff510"), "keyword4" => array("value" => $phone_tpl, "color" => "#ff510"), "keyword5" => array("value" => $address_tpl, "color" => "#ff510"), "Remark" => array("value" => "", "color" => "#ff510"));
					$account_api = WeAccount::create();
					$account_api->sendTplNotice($merchant['openid'], $this->module['config']['nt_order_new'], $postdata, $or_paysuccess_redirect, '#FF5454');
				}
			}
			if ($ordersnlen == 22) {
				pdo_update(BEST_ORDER, array("status" => 1, "transid" => $logtag['transaction_id'], "paydetail" => $paydetail), array("ordersn" => $params['tid']));
				$orderres = pdo_fetch('SELECT * FROM ' . tablename(BEST_ORDER) . " WHERE ordersn = '{$params['tid']}' AND weid = {$_W['uniacid']}");
				$memberhd = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$orderres['jlid']} AND weid = {$_W['uniacid']}");
				$datamemberhd['inpeople'] = $memberhd['inpeople'] + 1;
				pdo_update(BEST_MEMBERHUODONG, $datamemberhd, array("id" => $orderres['jlid']));
				$ordergoods = pdo_fetchall('SELECT * FROM ' . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$orderres['id']}");
				foreach ($ordergoods as $k => $v) {
					$goodsres = pdo_fetch('SELECT * FROM ' . tablename(BEST_MEMBERGOODS) . " WHERE id = {$v['goodsid']} AND weid = {$_W['uniacid']}");
					$datagoods['total'] = $goodsres['total'] - $v['total'];
					$datagoods['inpeople'] = $goodsres['inpeople'] + 1;
					pdo_update(BEST_MEMBERGOODS, $datagoods, array("id" => $v['goodsid']));
				}
				if ($this->module['config']['istplon'] == 1) {
					if ($orderres['pstype'] == 0 || $orderres['pstype'] == 3) {
						$address_tplarr = explode('|', $orderres['address']);
						$realname_tpl = $address_tplarr[0];
						$phone_tpl = $address_tplarr[1];
						$address_tpl = $address_tplarr[2] . $address_tplarr[3];
					} else {
						$realname_tpl = $address_tpl = '自提订单无需信息';
						$phone_tpl = $orderres['address'];
					}
					$or_paysuccess_redirect2 = $_W['siteroot'] . 'app/' . str_replace('./', '', $this->createMobileUrl('myfbjl', array("op" => "orderdetail", "orderid" => $orderres['id'])));
					$postdata2 = array("first" => array("value" => "用户下单通知！", "color" => "#ff510"), "keyword1" => array("value" => $orderres['ordersn'], "color" => "#ff510"), "keyword2" => array("value" => $orderres['price'], "color" => "#ff510"), "keyword3" => array("value" => $realname_tpl, "color" => "#ff510"), "keyword4" => array("value" => $phone_tpl, "color" => "#ff510"), "keyword5" => array("value" => $address_tpl, "color" => "#ff510"), "Remark" => array("value" => "", "color" => "#ff510"));
					$account_api = WeAccount::create();
					$account_api->sendTplNotice($memberhd['openid'], $this->module['config']['nt_order_new'], $postdata2, $or_paysuccess_redirect2, '#FF5454');
				}
			}
		}
		if ($params['from'] == 'return') {
			if ($params['result'] == 'success') {
				message('支付成功！', referer(), 'success');
			} else {
				message('支付失败！', referer(), 'error');
			}
		}
	}
	public function doWebOrder()
	{
		include_once ROOT_PATH . 'inc/web/order.php';
	}
	public function doWebTongbu()
	{
		include_once ROOT_PATH . 'inc/web/tongbu.php';
	}
	public function doWebHuodong()
	{
		include_once ROOT_PATH . 'inc/web/huodong.php';
	}
	public function doWebQkhaibao()
	{
		global $_GPC, $_W;
		$path = HB_ROOT_TD;
		$this->deldir($path);
		$path2 = HB_ROOT_ZFJL;
		$this->deldir($path2);
		message('操作成功！', '', 'success');
	}
	public function deldir($path)
	{
		if (is_dir($path)) {
			$p = scandir($path);
			foreach ($p as $val) {
				if ($val != '.' && $val != '..') {
					if (is_dir($path . $val)) {
						$this->deldir($path . $val . '/');
						@rmdir($path . $val . '/');
					} else {
						unlink($path . $val);
					}
				}
			}
		}
	}
	
	//优惠卷
	public function doWebyouhuijuan()
	{
	    global $_GPC, $_W;
	    
	    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	    if ($operation == 'post') {
	        $id = intval($_GPC['id']);

	        if (!empty($id)) {
	            $item = pdo_fetch('SELECT * FROM ' . tablename(BEST_youhuijuan) . " WHERE id = {$id}");
	            if (empty($item)) {
	                message('抱歉，优惠卷不存在或是已经删除！', '', 'error');
	            }

	        }
	        if (checksubmit('submit')) {
	            if (empty($_GPC['title'])) {
	                message('请输入优惠卷名称！');
	            }
	            if (empty($_GPC['starttime'])) {
	                message('请选择开始时间!');
	            }
	            if (empty($_GPC['endtime'])) {
	                message('请选择结束时间!');
	            }
	            if (empty($_GPC['manPrice'])) {
	                message('请填写满多少钱!');
	            }
	            if (empty($_GPC['jianPrice'])) {
	                message('请填写减多少钱!');
	            }

	            $data = array("weid" => intval($_W['uniacid']),"manPrice" => $_GPC['manPrice'], "jianPrice" => $_GPC['jianPrice'], "title" => $_GPC['title'], "endtime" => $_GPC['endtime'],  "starttime" => $_GPC['starttime'], "addtime" => TIMESTAMP);

	            if (empty($id)) {
	                pdo_insert(BEST_youhuijuan, $data);
	                $id = pdo_insertid();
	            } else {
	                unset($data['addtime']);
	                pdo_update(BEST_youhuijuan, $data, array("id" => $id));
	            }
	           
	            message('操作成功！', $this->createWebUrl('youhuijuan', array("op" => "display", "id" => $id)), 'success');
	        }
	    } else {
	        if ($operation == 'display') {
	            $pindex = max(1, intval($_GPC['page']));
	            $psize = 10;
	            $condition = ' WHERE weid = :weid';
	            $params = array(":weid" => $_W['uniacid']);
	            if (!empty($_GPC['keyword'])) {
	                $condition .= ' AND `title` LIKE :title';
	                $params[':title'] = '%' . trim($_GPC['keyword']) . '%';
	            }
	            $sql = 'SELECT COUNT(*) FROM ' . tablename(BEST_youhuijuan) . $condition;
	            $total = pdo_fetchcolumn($sql, $params);
	            if (!empty($total)) {
	                $sql = 'SELECT * FROM ' . tablename(BEST_youhuijuan) . $condition . ' ORDER BY addtime DESC,
						`id` DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
	                $list = pdo_fetchall($sql, $params);
	                $pager = pagination($total, $pindex, $psize);
	            }
	        } else {
	            if ($operation == 'delete') {
	                $id = intval($_GPC['id']);
	                pdo_delete(BEST_youhuijuan, array("id" => $id));
	                message('操作成功！', $this->createWebUrl('youhuijuan', array("op" => "display")), 'success');
	            }
	        }
	    }
	    

	    include $this->template('web/youhuijuan');
	}	
	
	
	
	
	
	
	public function doWebGoods()
	{
		global $_GPC, $_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch('SELECT * FROM ' . tablename(BEST_GOODS) . " WHERE id = {$id}");
				if (empty($item)) {
					message('抱歉，商品不存在或是已经删除！', '', 'error');
				}
				$options = pdo_fetchall('select * from ' . tablename(BEST_GOODSOPTION) . " where goodsid={$id} order by displayorder ASC");
				$piclist = unserialize($item['thumbs']);
			}
			if (checksubmit('submit')) {
                if (empty($_GPC['from'])) {
                    message('请输入商品来源！');
                }
				if (empty($_GPC['title'])) {
					message('请输入商品名称！');
				}
				if (empty($_GPC['ftitle'])) {
					message('请输入商品简称！');
				}
				if (empty($_GPC['thumb'])) {
					message('请上传商品图片！');
				}

				$data = array("weid" => intval($_W['uniacid']),
                    "psprice" => $_GPC['psprice'],
                    "starttime" => date("Y-m-d",time()).' 00:00',//$_GPC['starttime'],
                    "endtime" => date("Y-m-d",time()).' 23:00',//$_GPC['endtime'],
                    "topTitle" => $_GPC['topTitle'],
                    "leixin" => $_GPC['leixin'],
                    "title" => $_GPC['title'],
                    "ftitle" => $_GPC['ftitle'],
                    "from" => $_GPC['from'],
                    "sales" => $_GPC['sales'],
                    "describer" => $_GPC['describer'],
                    "thumb" => $_GPC['thumb'], "createtime" => TIMESTAMP,
                    "total" => intval($_GPC['total']), "normalprice" => $_GPC['normalprice'],
                    "chengbenprice" => $_GPC['chengbenprice'], "dailiprice" => $_GPC['dailiprice'],
                    "tiaoma" => $_GPC['tiaoma'], "des" => $_GPC['des']);
				if ($data['total'] === -1) {
					$data['total'] = 0;
				}
				if (empty($_GPC['thumbs'])) {
					$_GPC['thumbs'] = array();
				}
				if (is_array($_GPC['thumbs'])) {
					$data['thumbs'] = serialize($_GPC['thumbs']);
				}
				if (empty($id)) {
					pdo_insert(BEST_GOODS, $data);
					$id = pdo_insertid();
				} else {
					unset($data['createtime']);
					pdo_update(BEST_GOODS, $data, array("id" => $id));
				}
				$totalstocks = 0;
				$option_ids = $_POST['option_id'];
				$option_titles = $_POST['option_title'];
				$option_normalprices = $_POST['option_normalprice'];
				$option_chengbenprices = $_POST['option_chengbenprice'];
				$option_dailiprices = $_POST['option_dailiprice'];
				$option_stocks = $_POST['option_stock'];
				$len = count($option_ids);
				$optionids = array();
				$k = 0;
				while ($k < $len) {
					$option_id = '';
					$get_option_id = $option_ids[$k];
					$a = array("title" => $option_titles[$k], "normalprice" => $option_normalprices[$k], "chengbenprice" => $option_chengbenprices[$k], "dailiprice" => $option_dailiprices[$k], "stock" => $option_stocks[$k], "displayorder" => $k, "goodsid" => $id);
					if (!is_numeric($get_option_id)) {
						pdo_insert(BEST_GOODSOPTION, $a);
						$option_id = pdo_insertid();
					} else {
						pdo_update(BEST_GOODSOPTION, $a, array("id" => $get_option_id));
						$option_id = $get_option_id;
					}
					$optionids[] = $option_id;
					$totalstocks += $option_stocks[$k];
					$k++;
				}
				if (count($optionids) > 0) {
					pdo_query('delete from ' . tablename(BEST_GOODSOPTION) . " where goodsid = {$id} and id not in ( " . implode(',', $optionids) . ')');
					pdo_update(BEST_GOODS, array("total" => $totalstocks, "hasoption" => 1), array("id" => $id));
				} else {
					pdo_query('delete from ' . tablename(BEST_GOODSOPTION) . " where goodsid = {$id}");
					pdo_update(BEST_GOODS, array("hasoption" => 0), array("id" => $id));
				}
				message('操作成功！', $this->createWebUrl('goods', array("op" => "display", "id" => $id)), 'success');
			}
		} else {
			if ($operation == 'display') {
				$pindex = max(1, intval($_GPC['page']));
				$psize = 10;
				$condition = ' WHERE weid = :weid';
				$params = array(":weid" => $_W['uniacid']);
				if (!empty($_GPC['keyword'])) {
					$condition .= ' AND `title` LIKE :title';
					$params[':title'] = '%' . trim($_GPC['keyword']) . '%';
				}
				$sql = 'SELECT COUNT(*) FROM ' . tablename(BEST_GOODS) . $condition;
				$total = pdo_fetchcolumn($sql, $params);
				if (!empty($total)) {
					$sql = 'SELECT * FROM ' . tablename(BEST_GOODS) . $condition . ' ORDER BY createtime DESC,
						`id` DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
					$list = pdo_fetchall($sql, $params);
					foreach ($list as $k=>$item){
                        $totals = pdo_fetchcolumn("SELECT sum(nums) FROM " . tablename(BEST_shoporder)." where pro_id=".$item['id']." and weid = {$_W['uniacid']} and state=1");
                        $list[$k]['all']= $totals?$totals:0;
                        $list[$k]['activity'] = time()>strtotime($item['endtime']) ? 0:1;
					}
					$pager = pagination($total, $pindex, $psize);
				}
			} else if ($operation == 'delete') {
					$id = intval($_GPC['id']);
					$huodonggoods = pdo_fetchall('SELECT hdid FROM ' . tablename(BEST_HUODONGGOODS) . " WHERE weid = {$_W['uniacid']} AND goodsid = {$id}");
					foreach ($huodonggoods as $k => $v) {
						$huodong = pdo_fetch('SELECT title,endtime FROM ' . tablename(BEST_HUODONG) . " WHERE weid = {$_W['uniacid']} AND id = {$v['hdid']}");
						if ($huodong['endtime'] > TIMESTAMP) {
							message('该商品参与的活动：' . $huodong['title'] . '。还未结束，不能删除！', $this->createWebUrl('goods', array("op" => "display", "id" => $id)), 'error');
						}
					}
					pdo_delete(BEST_GOODS, array("id" => $id));
					pdo_delete(BEST_GOODSOPTION, array("goodsid" => $id));
					message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');
				}else  if ($_GPC['submit']==='批量上架') {
                    if (!empty($_GPC['id'])) {
                        foreach ($_GPC['id'] as $key => $id) {
                            pdo_update(BEST_GOODS,array('status'=>1),array('id'=>$id,'weid' => $_W['uniacid']));
                        }
                    }
                    message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');

            }else  if ($_GPC['submit']==='批量下架') {
                if (!empty($_GPC['id'])) {
//                    printf(json_encode($_GPC['id']));
//                    die();
                    foreach ($_GPC['id'] as $key => $id) {
                        pdo_update(BEST_GOODS,array('status'=>0),array('id'=>$id,'weid' => $_W['uniacid']));
                    }
                }
                message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');

            }else  if ($_GPC['submit']==='批量开始活动') {
                if (!empty($_GPC['id'])) {
//                    printf(json_encode($_GPC['id']));
//                    die();
                    $data = array(
                        "starttime" => date("Y-m-d",time()).' 00:00',//$_GPC['starttime'],
                        "endtime" => date("Y-m-d",time()).' 23:00',//$_GPC['endtime'],
                        'status'=>1
                    );
                    foreach ($_GPC['id'] as $key => $id) {
                        pdo_update(BEST_GOODS,$data,array('id'=>$id,'weid' => $_W['uniacid']));
                    }
                }
                message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');

            }else  if ($operation == 'sigleupload') {
                if (!empty($_GPC['id'])) {

                        pdo_update(BEST_GOODS,array('status'=>1),array('id'=>$_GPC['id'],'weid' => $_W['uniacid']));

                }
                message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');

            }else  if ($operation == 'sigleudown') {
                if (!empty($_GPC['id'])) {
                    pdo_update(BEST_GOODS,array('status'=>0),array('id'=>$_GPC['id'],'weid' => $_W['uniacid']));
                }
                message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');

            }else  if ($operation == 'start') {
                if (!empty($_GPC['id'])) {
                    $data = array(
                        "starttime" => date("Y-m-d",time()).' 00:00',//$_GPC['starttime'],
                        "endtime" => date("Y-m-d",time()).' 23:00',//$_GPC['endtime'],
                       );
                    pdo_update(BEST_GOODS,$data,array('id'=>$_GPC['id'],'weid' => $_W['uniacid']));
                }
                message('操作成功！', $this->createWebUrl('goods', array("op" => "display")), 'success');

            }
		}
		include $this->template('web/goods');
	}
	
	public function doWebOption()
	{
		$tag = random(32);
		global $_GPC;
		include $this->template('web/option');
	}
	public function doWebMember()
	{
		include_once ROOT_PATH . 'inc/web/member.php';
	}
	public function doWebMerchant()
	{
		include_once ROOT_PATH . 'inc/web/merchant.php';
	}
	public function doWebTixian()
	{
		include_once ROOT_PATH . 'inc/web/tixian.php';
	}
	public function doWebYunfei()
	{
		include_once ROOT_PATH . 'inc/web/yunfei.php';
	}
	public function doWebJielong()
	{
		include_once ROOT_PATH . 'inc/web/jielong.php';
	}
	public function doWebJlorder()
	{
		include_once ROOT_PATH . 'inc/web/jlorder.php';
	}
	public function doWebscorder()
	{
	    include_once ROOT_PATH . 'inc/web/scorder.php';
	}
	public function doWebJiesuan()
	{
		include_once ROOT_PATH . 'inc/web/jiesuan.php';
	}
	
	
	public function doWebRefund()
	{
		include_once ROOT_PATH . 'inc/web/refund.php';
	}
	
	
	
	public function doWebRenzheng()
	{
		include_once ROOT_PATH . 'inc/web/renzheng.php';
	}
	public function doWebTeamjiang()
	{
		include_once ROOT_PATH . 'inc/web/teamjiang.php';
	}
	public function doMobileGetmedia()
	{
		global $_W, $_GPC;
		include_once ROOT_PATH . 'ImageCrop.class.php';
		$access_token = WeAccount::token();
		$media_id = $_GPC['media_id'];
		$url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=' . $access_token . '&media_id=' . $media_id;
		$response = ihttp_get($url);
		if (is_error($response)) {
			$resarr['error'] = 1;
			$resarr['message'] = "访问公众平台接口失败, 错误: {$response['message']}";
			die(json_encode($resarr));
		}
		$result = @json_decode($response['content'], true);
		if (!empty($result['errcode'])) {
			$resarr['error'] = 1;
			$resarr['message'] = "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}";
			die(json_encode($resarr));
		}
		$updir = ATTACHMENT_ROOT . 'images/' . $_W['uniacid'] . '/' . date('Y', time()) . '/' . date('m', time()) . '/';
		if (!file_exists($updir)) {
			mkdir($updir, 0777, true);
		}
		$randimgurl = 'images/' . $_W['uniacid'] . '/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('YmdHis') . rand(1000, 9999) . '.jpg';
		$targetName = ATTACHMENT_ROOT . $randimgurl;
		$fp = @fopen($targetName, 'wb');
		@fwrite($fp, $response['content']);
		@fclose($fp);
		if (file_exists($targetName)) {
			$resarr['error'] = 0;
			$img_info = getimagesize($targetName);
			$uptype = intval($_GPC['uptype']);
			if ($uptype > 0) {
				$percent = intval($_GPC['percent']);
				$tarwidth = $img_info[0] * $percent / 100;
				$tarheight = $img_info[1] * $percent / 100;
				$this->mkThumbnail($targetName, $tarwidth, $tarheight, $targetName);
			} else {
				if ($img_info[0] >= 640) {
					$tarwidth = intval($_GPC['tarwidth']);
					$tarheight = intval($_GPC['tarheight']);
					$ic = new ImageCrop($targetName, $targetName);
					$ic->Crop($tarwidth, $tarheight, 2);
					$ic->SaveImage();
					$ic->destory();
				}
			}
			if (!empty($_W['setting']['remote']['type'])) {
				load()->func('file');
				$remotestatus = file_remote_upload($randimgurl, true);
				if (is_error($remotestatus)) {
					$resarr['error'] = 1;
					$resarr['message'] = '远程附件上传失败，请检查配置并重新上传';
					file_delete($randimgurl);
					die(json_encode($resarr));
				} else {
					file_delete($randimgurl);
					$resarr['realimgurl'] = $randimgurl;
					$resarr['imgurl'] = tomedia($randimgurl);
					$resarr['message'] = '上传成功';
					die(json_encode($resarr));
				}
			}
			$resarr['realimgurl'] = $randimgurl;
			$resarr['imgurl'] = tomedia($randimgurl);
			$resarr['message'] = '上传成功';
		} else {
			$resarr['error'] = 1;
			$resarr['message'] = '上传失败';
		}
		echo json_encode($resarr, true);
		exit;
	}
	public function mkThumbnail($src, $width = null, $height = null, $filename = null)
	{
		if (!isset($width) && !isset($height)) {
			return false;
		}
		if (isset($width) && $width <= 0) {
			return false;
		}
		if (isset($height) && $height <= 0) {
			return false;
		}
		$size = getimagesize($src);
		if (!$size) {
			return false;
		}
		list($src_w, $src_h, $src_type) = $size;
		$src_mime = $size['mime'];
		switch ($src_type) {
			case 1:
				$img_type = 'gif';
				break;
			case 2:
				$img_type = 'jpeg';
				break;
			case 3:
				$img_type = 'png';
				break;
			case 15:
				$img_type = 'wbmp';
				break;
			default:
				return false;
		}
		if (!isset($width)) {
			$width = $src_w * ($height / $src_h);
		}
		if (!isset($height)) {
			$height = $src_h * ($width / $src_w);
		}
		$imagecreatefunc = 'imagecreatefrom' . $img_type;
		$src_img = $imagecreatefunc($src);
		$dest_img = imagecreatetruecolor($width, $height);
		imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);
		$imagefunc = 'image' . $img_type;
		if ($filename) {
			$imagefunc($dest_img, $filename);
		} else {
			header('Content-Type: ' . $src_mime);
			$imagefunc($dest_img);
		}
		imagedestroy($src_img);
		imagedestroy($dest_img);
		return true;
	}
	public function isMobile($mobile)
	{
		if (!is_numeric($mobile)) {
			return false;
		}
		return preg_match('/^1[2345789]{1}\\d{9}$/', $mobile) ? true : false;
	}
	public function createPoster($config = array(), $filename = "")
	{
		if (empty($filename)) {
			header('content-type: image/png');
		}
		$imageDefault = array("left" => 0, "top" => 0, "right" => 0, "bottom" => 0, "width" => 100, "height" => 100, "opacity" => 100);
		$textDefault = array("text" => "", "left" => 0, "top" => 0, "fontSize" => 32, "fontColor" => "255,255,255", "angle" => 0);
		$background = $config['background'];
		$backgroundInfo = getimagesize($background);
		$backgroundFun = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
		$background = $backgroundFun($background);
		$backgroundWidth = imagesx($background);
		$backgroundHeight = imagesy($background);
		$imageRes = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
		$color = imagecolorallocate($imageRes, 0, 0, 0);
		imagefill($imageRes, 0, 0, $color);
		imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
		if (!empty($config['image'])) {
			foreach ($config['image'] as $key => $val) {
				$val = array_merge($imageDefault, $val);
				$info = getimagesize($val['url']);
				$function = 'imagecreatefrom' . image_type_to_extension($info[2], false);
				if ($val['stream']) {
					$info = getimagesizefromstring($val['url']);
					$function = 'imagecreatefromstring';
				}
				$res = $function($val['url']);
				$resWidth = $info[0];
				$resHeight = $info[1];
				$canvas = imagecreatetruecolor($val['width'], $val['height']);
				imagefill($canvas, 0, 0, $color);
				imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'], $resWidth, $resHeight);
				$val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
				$val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
				imagecopymerge($imageRes, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height'], $val['opacity']);
			}
		}
		if (!empty($config['text'])) {
			foreach ($config['text'] as $key => $val) {
				$val = array_merge($textDefault, $val);
				list($R, $G, $B) = explode(',', $val['fontColor']);
				$fontColor = imagecolorallocate($imageRes, $R, $G, $B);
				$val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
				$val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
				imagettftext($imageRes, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $val['fontPath'], $val['text']);
			}
		}
		if (!empty($filename)) {
			$res = imagejpeg($imageRes, $filename, 90);
			imagedestroy($imageRes);
			if (!$res) {
				return false;
			}
			return $filename;
		} else {
			imagejpeg($imageRes);
			imagedestroy($imageRes);
		}
	}
	public function mkdirs($dir, $mode = 0777)
	{
		if (is_dir($dir) || @mkdir($dir, $mode)) {
			return TRUE;
		}
		if (!$this->mkdirs(dirname($dir), $mode)) {
			return FALSE;
		}
		return @mkdir($dir, $mode);
	}
	public function get_url_content($url)
	{
		if (function_exists('curl_init')) {
			$ch = curl_init();
			$timeout = 30;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		} else {
			$is_auf = ini_get('allow_url_fopen') ? true : false;
			if ($is_auf) {
				$file_contents = file_get_contents($url);
			}
		}
		return $file_contents;
	}
	public function checkmain()
	{
		global $_GPC, $_W;
		$con1 = $this->get_url_content(BEST_DOMAINSF);
		$con2 = $this->get_url_content(BEST_DOMAINCS);
		$con3 = $this->get_url_content(BEST_DOMAINQX);
		$resarr['con1'] = json_decode($con1, true);
		$resarr['con2'] = json_decode($con2, true);
		$resarr['con3'] = json_decode($con3, true);
		return $resarr;
	}
}