<?php

//decode by http://www.yunlu99.com/
define("ROOT_PATH", IA_ROOT . "/addons/cy163_salesjl/");
define("HB_ROOT_TD", "../addons/cy163_salesjl/haibao/tuandui/");
define("HB_ROOT_ZFJL", "../addons/cy163_salesjl/haibao/zfjl/");
define("BEST_GOODS", "cy163salesjl_goods");
define("BEST_GOODSOPTION", "cy163salesjl_goods_option");
define("BEST_ORDER", "cy163salesjl_order");
define("BEST_ORDERGOODS", "cy163salesjl_order_goods");
define("BEST_MERCHANT", "cy163salesjl_merchant");
define("BEST_MERCHANTACCOUNT", "cy163salesjl_merchantaccount");
define("BEST_MEMBER", "cy163salesjl_member");
define("BEST_TIXIAN", "cy163salesjl_tixian");
define("BEST_HUODONG", "cy163salesjl_huodong");
define("BEST_HUODONGGOODS", "cy163salesjl_huodonggoods");
define("BEST_MERCHANTHD", "cy163salesjl_merchanthd");
define("BEST_MERCHANTHDGOODS", "cy163salesjl_merchanthdgoods");
define("BEST_YUNFEI", "cy163salesjl_yunfei");
define("BEST_YUNFEISHENG", "cy163salesjl_yfsheng");
define("BEST_MEMBERGOODS", "cy163salesjl_membergoods");
define("BEST_MEMBERGOODSJIETI", "cy163salesjl_membergoods_jieti");
define("BEST_MEMBERHUODONG", "cy163salesjl_memberhuodong");
define("BEST_CART", "cy163salesjl_cart");
define("BEST_MEMBERACCOUNT", "cy163salesjl_memberaccount");
define("BEST_CITY", "cy163salesjl_city");
define("BEST_ZITIDIAN", "cy163salesjl_zitidian");
define("BEST_REFUND", "cy163salesjl_refund");
define("BEST_MEMBERRZ", "cy163salesjl_memberrz");
define("BEST_HEXIAOYUAN", "cy163salesjl_hexiaoyuan");
define("BEST_HUODONGTEAMJIANG", "cy163salesjl_huodong_teamjiang");
define("BEST_TZORDER", "cy163salesjl_tzorder");
define("BEST_FORMID", "cy163salesjl_formid");
define("DZDOMAIN", "https://www.lshhwl.com/");
define('BEST_youhuijuan', 'cy163salesjl_youhuijuan');
define('BEST_fist_comment', 'cy163salesjl_jlfist_comment');
define('BEST_getCoupon', 'cy163salesjl_get_coupon');
define('BEST_shopcart', 'cy163salesjl_shopcart');
define('BEST_shoporder', 'cy163salesjl_shoporder');
define('BEST_shopaddr', 'cy163salesjl_shopaddr');
define('BEST_two_comment', 'cy163salesjl_two_comment');
define('BEST_dianzan', 'cy163salesjl_dianzan');
class Cy163_salesjlModuleWxapp extends WeModuleWxapp
{
	public function __construct()
	{
	}
	
	//生成核销码
	public function doPagehxm()
	{
	    global $_W, $_GPC;
	    $account_api = WeAccount::create();
	    $access_token = $account_api->getAccessToken();
	    
	    $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
	    $data = array(
	        "scene"=>$_GPC['scene'],
	        "page"=>"cy163_salesjl/pages/shop/hexiao/hexiao",
	        "width"=>430,
	    );
	    $data = json_encode($data);
	    $response = $this->send_post($url,$data);

	    $result=$this->data_uri($response,'image/png');
	    $this->result(0,'核销码！', $result);
	}
	
	
	
	//立即核销
	public function doPagehexiaos()
	{
	    global $_W, $_GPC;
	    $order_id = $_GPC['orderid'];
	    $openid = $_GPC['openid'];
	    
	    $hxyuan = pdo_fetch("SELECT * FROM " . tablename(BEST_HEXIAOYUAN) . " WHERE openid = '".$openid."'");
	    if(empty($hxyuan)){
	        $this->result(0,'很抱歉，你不是核销员！请联系核销员核销,谢谢配合!', 1);
	    }
	    
	    $res = pdo_fetch("SELECT * FROM " . tablename(BEST_shoporder) . " WHERE id = '{$order_id}' AND weid = {$_W["uniacid"]}");
	    if($res){
	        if(!empty($res['nums'])){
	            $result = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '{$res['pro_id']}' AND weid = {$_W["uniacid"]}");
	            $num = $result['total'] - $res['nums'];
	            $data = array("total" => $num);
	            pdo_update(BEST_GOODS, $data, array("id" => $res['pro_id']));
	        }else {
	            if(!empty($res['jsCartInfo'])){
	                $jsCartInfo = json_decode(htmlspecialchars_decode($res['jsCartInfo']),true);
	                foreach ($jsCartInfo as $k4 => $v4)
	                {
	                    if($v4['pro_id'] != ''){
	                        $result = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '{$v4['pro_id']}' AND weid = {$_W["uniacid"]}");
	                        $num = $result['total'] - $v4['nums'];
	                        $data = array("total" => $num);
	                        pdo_update(BEST_GOODS, $data, array("id" => $res['pro_id']));
	                    }
	                    if($v4['attr_id'] != 0){
	                        $attrvalue = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = '".$v4['attr_id']."'");
	                        $num = $attrvalue['stock'] - $v4['nums'];
	                        $data = array("stock" => $num);
	                        pdo_update(BEST_GOODSOPTION, $data, array("id" => $v4['attr_id']));
	                    }
	                }
	            }
	        }
	    }else {
	        $this->result(0,'没有这个核销订单!', 0);
	    }

	    $datas = array("state" => 4);
	    pdo_update(BEST_shoporder, $datas, array("id" => $order_id));
	    
	    $this->result(0,'核销成功!', 2);
	}
	
	
	
	public function doPageMessiopenid()
	{
		global $_W, $_GPC;
		$userinfo = file_get_contents("https://api.weixin.qq.com/sns/jscode2session?appid={$_W["account"]["key"]}&secret={$_W["account"]["secret"]}&js_code={$_GPC["code"]}&grant_type=authorization_code");
		$userinfo = json_decode($userinfo, true);
		$openid = $userinfo["openid"];
		$nickname = trim($_GPC["nickname"]);
		$avatar = trim($_GPC["avatar"]);
		$gender = intval($_GPC["gender"]);
		$gender = $gender == 1 ? "男" : "女";
		$has = pdo_fetch("SELECT id FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if (empty($has)) {
			$data = array("openid" => $openid, "nickname" => $nickname, "avatar" => $avatar, "gender" => $gender, "weid" => $_W["uniacid"], "isxcx" => 1, "regtime" => TIMESTAMP);
			pdo_insert(BEST_MEMBER, $data);
		}
		$this->result(0, "用户信息", $userinfo);
	}
	public function doPageAdv()
	{
		global $_W, $_GPC;
		if ($this->module["config"]["advon1"] == 1) {
			$data[] = array("thumb" => tomedia($this->module["config"]["adv1"]), "url" => $this->module["config"]["adv1url"], "appid" => $this->module["config"]["adv1appid"]);
		}
		if ($this->module["config"]["advon2"] == 1) {
			$data[] = array("thumb" => tomedia($this->module["config"]["adv2"]), "url" => $this->module["config"]["adv2url"], "appid" => $this->module["config"]["adv2appid"]);
		}
		if ($this->module["config"]["advon3"] == 1) {
			$data[] = array("thumb" => tomedia($this->module["config"]["adv3"]), "url" => $this->module["config"]["adv3url"], "appid" => $this->module["config"]["adv3appid"]);
		}
		$this->result(0, "幻灯片列表", $data);
	}
	
	
	//接龙列表接口
	public function doPageIndex()
	{
		global $_W, $_GPC;

		$conditions = "weid = {$_W["uniacid"]} AND status = 1 AND owndel = 0 AND admindel = 0";
		if ($this->module["config"]["gqzfjlzs"] == 1) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE {$conditions}");
		} else {
			if ($this->module["config"]["gqzfjlzs"] == 2) {
				$total = 0;
			} else {
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE {$conditions} AND endtime > " . TIMESTAMP);
			}
		}
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		if ($this->module["config"]["gqzfjlzs"] == 1) {
			$jielonglist = pdo_fetchall("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE {$conditions} ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		} else {
			if ($this->module["config"]["gqzfjlzs"] == 2) {
				$jielonglist = '';
			} else {
				$jielonglist = pdo_fetchall("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE {$conditions} AND endtime > " . TIMESTAMP . " ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
			}
		}
		foreach ($jielonglist as $k => $v) {
			$jielonglist[$k]["time"] = date("m-d", $v["time"]);
			if ($v["starttime"] > TIMESTAMP) {
				$jielonglist[$k]["status"] = "未开始";
				$jielonglist[$k]["color"] = "hui";
			} else {
				if ($v["endtime"] < TIMESTAMP) {
					$jielonglist[$k]["status"] = "已结束";
					$jielonglist[$k]["color"] = "hui";
				} else {
					$jielonglist[$k]["status"] = "进行中";
					$jielonglist[$k]["color"] = "buhui";
				}
			}
			$thumbs = unserialize($v["thumbs"]);
			$jielonglist[$k]["thumb1"] = tomedia($thumbs[0]);
			$jielonglist[$k]["thumb2"] = tomedia($thumbs[1]);
			$jielonglist[$k]["thumb3"] = tomedia($thumbs[2]);
		}

		//获取点赞的用户信息列表
		foreach ($jielonglist as $k => $v)
		{
		    $res = pdo_fetchall("SELECT * FROM " . tablename(BEST_dianzan) ."where state = 0 and jlId =".$v['id']." ORDER BY addtime DESC");
		    if(!empty($res)){
		        foreach ($res as $k1 => $v1){ 
		            $data = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBER) . " WHERE id = {$v1['uid']}");
		            $res[$k1]['userinfo'] = $data;
		        }
		    }
            $jielonglist[$k]['dianzaninfo'] = $res;
		}
		
		$data["jielongs"] = $jielonglist;
		$data["allpage"] = $allpage;
		$share["sharetitle"] = $this->module["config"]["sharetitle"];
		$share["sharethumb"] = tomedia($this->module["config"]["sharethumb"]);
		$data["share"] = $share;
		$data["title"] = $this->module["config"]["indextitle"];
		$data["temstyle"] = $_W["siteroot"] == DZDOMAIN ? 1 : 0;
		
		$this->result(0, "首页", $data);
	}


	

	public function doPageIndexdz()
	{
		global $_W, $_GPC;
		$conditions = "weid = {$_W["uniacid"]} AND merchant_id = {$this->module["config"]["teshumid"]}";
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MERCHANTHD) . " WHERE {$conditions}");
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$jielonglist = pdo_fetchall("SELECT * FROM " . tablename(BEST_MERCHANTHD) . " WHERE {$conditions} ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		$jielonglists = array();
		$i = 0;
		$nowtime = TIMESTAMP;
		foreach ($jielonglist as $k => $v) {
			$hdres = pdo_fetch("SELECT endtime FROM " . tablename(BEST_HUODONG) . " WHERE id = {$v["hdid"]}");
			if ($hdres["endtime"] > $nowtime) {
				$hdgoods = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANTHDGOODS) . " WHERE mhdid = {$v["id"]} AND optionid = 0");
				if (!empty($hdgoods)) {
					$jielonglist[$k]["endtime"] = $hdres["endtime"];
					$jielonglist[$k]["countDownDay"] = "00";
					$jielonglist[$k]["countDownHour"] = "00";
					$jielonglist[$k]["countDownMinute"] = "00";
					$jielonglist[$k]["countDownSecond"] = "00";
					$jielonglist[$k]["time"] = date("m-d", $v["time"]);
					$goods = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = {$hdgoods["goodsid"]}");
					if (!empty($goods)) {
						$goods["sales"] = pdo_fetchcolumn("SELECT SUM(total) FROM " . tablename(BEST_ORDERGOODS) . " WHERE goodsid = {$goods["id"]} AND mhdid = {$v["id"]}");
						$goods["sales"] = empty($goods["sales"]) ? 0 : $goods["sales"];
						$jielonglist[$k]["thumb"] = tomedia($goods["thumb"]);
						$jielonglist[$k]["goods"] = $goods;
						$jielonglist[$k]["hdgoods"] = $hdgoods;
						$cylist = pdo_fetchall("SELECT from_user FROM " . tablename(BEST_ORDER) . " WHERE mhdid = {$v["id"]} AND status = 4 ORDER BY createtime DESC LIMIT 3");
						foreach ($cylist as $kk => $vv) {
							$cylist[$kk]["member"] = pdo_fetch("SELECT nickname,avatar FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$vv["from_user"]}' AND weid = {$_W["uniacid"]}");
						}
						$jielonglist[$k]["cylist"] = $cylist;
						$jielonglists[$i] = $jielonglist[$k];
						$i++;
					}
				}
			}
		}
		$data["jielongs"] = $jielonglists;
		$data["allpage"] = $allpage;
		$share["sharetitle"] = $this->module["config"]["sharetitle"];
		$share["sharethumb"] = tomedia($this->module["config"]["sharethumb"]);
		$data["share"] = $share;
		$data["title"] = $this->module["config"]["indextitle"];
		$this->result(0, "首页", $data);
	}
	

	//商城首页
	public function doPagescList()
	{
	    global $_W, $_GPC;
	    //后台设置是否开启商城
	    $kaiguan = $this->module["config"]["sckaiqi"];
	    if($kaiguan){
	        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_GOODS));
	        $psize = 10;
	        $allpage = ceil($total / $psize);
	        
	        $page = intval($_GPC["page"]);
	        $pindex = max(1, $page);
	        $list = pdo_fetchall("SELECT * FROM " . tablename(BEST_GOODS) ." ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);

	        foreach ($list as $k => $v){
	            $resop = pdo_fetchall("SELECT * FROM " . tablename(BEST_GOODSOPTION) ."where goodsid =".$v['id']);
	            $list[$k]['attr'] = $resop;
	            $list[$k]['thumb'] = $this->getImg($v['thumb']);
	          
	            $thumbs = unserialize($v['thumbs']);
	            if(!empty($thumbs)){
	                foreach ($thumbs as $k2 => $v2){

	                    $list[$k]['thumbss'][$k2] = $this->getImg($v2);
	                }
	            }
	            
	        }
	        $res["product"] = $list;
	        $res["dianming"] = $this->module['config']['dianming'];
	        $res["allpage"] = $allpage;
	        $this->result(0, "商品列表", $res);
	        
	    }else {
	        $this->result(0, "商店打烊了!", 0);
	    }

	}
	
	
	//商品详情
	public function doPagepro_detail()
	{
	    global $_W, $_GPC;
	    
	    $pro_id = $_GPC['pro_id'];
	    $list = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '{$pro_id}' AND weid = {$_W["uniacid"]}");
	    $resop = pdo_fetchall("SELECT * FROM " . tablename(BEST_GOODSOPTION) ."where goodsid =".$pro_id);
	    $list['attr'] = $resop;
	    
	    $list['thumb'] = $this->getImg($list['thumb']);
	    $thumbs = unserialize($list['thumbs']);
	    foreach ($thumbs as $k =>$v ){
	        $thumbs[$k] =  $this->getImg($v);
	    }
	    $list['thumbs'] = $thumbs;
	    $this->result(0, "商品详情", $list);
	    
	}
	
	
	

	//优惠卷列表
	public function doPageCouponList()
	{
	    global $_W, $_GPC;
	    
	    $uid = $_GPC['uid'];
	    $list = pdo_fetch("SELECT * FROM " . tablename(BEST_getCoupon) . " WHERE uid = '{$uid}' AND weid = {$_W["uniacid"]}");
	    if($list){
	        $this->result(0, "还有未使用的优惠卷，暂时不可领取!",0);
	    }else {
	        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_youhuijuan));
	        $psize = 10;
	        $allpage = ceil($total / $psize);
	        $page = intval($_GPC["page"]);
	        $pindex = max(1, $page);
	        $list = pdo_fetchall("SELECT * FROM " . tablename(BEST_youhuijuan) ." ORDER BY addtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
	        
	        $this->result(0, "优惠卷列表",array('list'=>$list,'allpage'=>$allpage));
	    }

	}
	

	//用户领取优惠价接口
	public function doPageGetCoupon()
	{
	    global $_W, $_GPC;

	    $data = array("state" => 0,"CouponId" => $_GPC["CouponId"], "uid" => $_GPC["uid"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	    
	    $res = pdo_insert(BEST_getCoupon, $data);
	    
	    if($res){
	        $this->result(0, "领取成功!", 1);
	    }else {
	        $this->result(0, "领取失败!", 0);
	    }

	}
	
	
	//用户的优惠卷列表
	public function doPageUserGetCoupon()
	{
	    global $_W, $_GPC;
	    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_getCoupon));
	    $psize = 10;
	    $allpage = ceil($total / $psize);
	    $page = intval($_GPC["page"]);
	    $pindex = max(1, $page);
	    $list = pdo_fetchall("SELECT * FROM " . tablename(BEST_getCoupon) ." ORDER BY addtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
	    
	    foreach ($list as $k => $v){
	        switch ($v['state']) {
	            case 0:
	                $v['state'] = '未使用';
	                break;
	            case 1:
	                $v['state'] = '已使用';
	                break;
	        }
	        $list[$k]['state'] = $v['state'];
	    }

	    foreach ($list as $k =>$v){
	        $res = pdo_fetch("SELECT * FROM " . tablename(BEST_youhuijuan) . " WHERE id = ".$v['CouponId']);
	        $list[$k]['detail'] = $res;
	    }
	    
	    $this->result(0, "获取成功!",array('list'=>$list,'allpage'=>$allpage));
	    
	}
	
	
	
	//加入购物车接口ljbuy
	public function doPageCart()
	{
	    global $_W, $_GPC;

	    $data = array("price" => $_GPC["price"],"proId" => $_GPC["proId"],"nums" => $_GPC["nums"],"uid" => $_GPC["uid"], "attrId" => $_GPC["attrId"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	    
	    $res = pdo_insert(BEST_shopcart, $data);
	    
	    if($res){
	        $this->result(0, "加入购物车成功!", 1);
	    }else {
	        $this->result(0, "加入购物车失败!", 0);
	    }
	    
	}
	
	
	//立即购买接口
	public function doPageljbuy()
	{
	    global $_W, $_GPC;
	    $price = $_GPC["price"];
	    $nums= $_GPC["nums"];
	    
	    $allmony = $price *$nums;

	    $data = array("ordersn" =>date("YmdHis"),"allmoney" => $allmony,"price" => $_GPC["price"],"state" =>0,"pro_id" => $_GPC["pro_id"],"nums" => $_GPC["nums"],"uid" => $_GPC["uid"], "attr_id" => $_GPC["attr_id"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	    
	    $res = pdo_insert(BEST_shoporder, $data);
	    $resId = pdo_insertid();

	    $result = pdo_fetch("SELECT * FROM " . tablename(BEST_shoporder) . " WHERE id = ".$resId);
	    
	    $result['product'] = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = ".$result['pro_id']);
	    $result['product']['thumb'] = $this->getImg($result['product']['thumb']);
	    $thumbs = unserialize($result['product']['thumbs']);
	
	    if(!empty($thumbs)){
	        foreach ($thumbs as $k => $v){
	            $result['product']['thumbs'] = $this->getImg($v);
	        }
	    }
	    //关联属性
	    if(!empty($result['attr_id'])){
	        $result['attrvalue'] = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = ".$result['attr_id']);
	       
	    }
	    
	    if($result){
	        $this->result(0, "成功", $result);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	

	
	//提交订单接口
	public function doPageSuborder()
	{
	    global $_W, $_GPC;
	    
	    $ordersn =  $_GPC["ordersn"];
	    $addrId =  $_GPC["addrId"];
	    $allmoney =  $_GPC["allmoney"];
	    $uid =  $_GPC["uid"];
	    
	    $data = array("uid" => $uid,"aid" => $addrId,"allmoney" => $allmoney,"weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	    $resss = pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	    
	    if($resss){
	        $this->result(0, "成功", 1);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	
	
	//订单详情接口
	public function doPageshopOrderdetail()
	{
	    global $_W, $_GPC;
	    
	    $ordersn =  $_GPC["ordersn"];
	    $res = pdo_fetch("SELECT * FROM " . tablename(BEST_shoporder) . " WHERE ordersn = ".$ordersn);
	    
	    $res['attrvalue'] = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = '".$res['attr_id']."'");

	    $res['product'] = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '".$res['pro_id']."'");
	    
	    $res['product']['thumb'] = $this->getImg($res['product']['thumb']);
	    
	    $thumbs = unserialize($res['product']['thumbs']);

	    if(!empty($thumbs)){
	        foreach ($thumbs as $k =>$v){
	            $res['product']['thumbs'] = $this->getImg($v);
	        }
	    }
	    
	    //多产品进入
	    if(!empty($res['jsCartInfo'])){
	        
	        $jsCartInfo = json_decode(htmlspecialchars_decode($res['jsCartInfo']),true);
	        
	        foreach ($jsCartInfo as $k4 => $v4)
	        {
	            if($v4['attr_id'] != 0){
	                $attrvalue = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = '".$v4['attr_id']."'");
	                $jsCartInfo[$k4]['attrvalue'] = $attrvalue;
	            }
	            if($v4['pro_id'] != ''){
	                $pro = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '".$v4['pro_id']."'");
	                $pro['thumb'] = $this->getImg($pro['thumb']);
	                $jsCartInfo[$k4]['product'] = $pro;
	            }
	            
	        }
	        $shopaddr = pdo_fetch("SELECT * FROM " . tablename(BEST_shopaddr) . " WHERE id = '".$res['aid']."'");
	        $res['jsCartInfo'] = $jsCartInfo;
	        $res['shopaddr'] = $shopaddr;
	    }
	    
	    
	    
	    
	    if($res){
	        $this->result(0, "成功", $res);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	
	//单个删除购物车接口
	public function doPagedelCart()
	{
	    global $_W, $_GPC;
	    
	    $catId =  $_GPC["catId"];
	    $result = pdo_delete(BEST_shopcart, array("id" => $catId));
	    if($result){
	        $this->result(0, "成功", 1);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	
	
	//地址接口
	public function doPagemyaddrs()
	{
	    global $_W, $_GPC;
	    
	    $openid =  $_GPC["openid"];
	    $res = pdo_fetch("SELECT * FROM " . tablename(BEST_shopaddr) . " WHERE openid = "."'".$_GPC["openid"]."'");
	    if($res){
	        $this->result(0, "成功", $res);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	

	
	//商城支付接口
	public function doPagePayshop()
	{
	    global $_GPC, $_W;
	    
	    $ordersn = trim($_GPC["ordersn"]);
	    $sfmoney = trim($_GPC["sfmoney"]);
	    $uid = trim($_GPC["uid"]);
	    $useryhj = pdo_fetch("SELECT * FROM " . tablename(BEST_getCoupon) . " WHERE uid = ".$uid);
	    //判断是否有优惠卷
	    if(!empty($useryhj)){
	        $info = pdo_fetch("SELECT * FROM " . tablename(BEST_youhuijuan) . " WHERE id = ".$useryhj['CouponId']);
	        if(!empty($info)){
	            if($sfmoney >= $info['manPrice']){
	                $fuprice = $sfmoney - $info['jianPrice'];
	                $order = array("tid" => $ordersn, "fee" => $fuprice, "title" => "订单支付");
	                $pay_params = $this->pay($order);
	            }else{
	                $order = array("tid" => $ordersn, "fee" => $sfmoney, "title" => "订单支付");
	                $pay_params = $this->pay($order);
	            }
	            if (is_error($pay_params)) {
	                pdo_update("core_paylog", array("openid" => $_GPC["openid"]), array("tid" => $ordersn, "module" => "cy163_salesjl", "type" => "wxapp"));
	                $pay_params = $this->pay($order);
// 	                $data = array("state" => 1);
// 	                pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	                return $this->result(0, '', $pay_params);
	            }else {
// 	                $data = array("state" => 1);
// 	                pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	                return $this->result(0, '', $pay_params);
	            }
	        }
	    }else {
	        $order = array("tid" => $ordersn, "fee" => $sfmoney, "title" => "订单支付");
	        $pay_params = $this->pay($order);
	        if (is_error($pay_params)) {
	            pdo_update("core_paylog", array("openid" => $_GPC["openid"]), array("tid" => $ordersn, "module" => "cy163_salesjl", "type" => "wxapp"));
	            $pay_params = $this->pay($order);
// 	            $data = array("state" => 1);
// 	            pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	            return $this->result(0, '', $pay_params);
	        }else {
// 	            $data = array("state" => 1);
// 	            pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	            return $this->result(0, '', $pay_params);
	        }

	    }

	}
	
	//支付成功状态修改
	public function doPagestatus()
	{
	    global $_GPC, $_W;
	    $ordersn = $_GPC["ordersn"];
	    
	    $data = array("state" => 1);
	    $res =  pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	    
	    if($res){
	        $this->result(0, "成功", 1);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	

	
	
// 	//支付接口
// 	public function doPagePay()
// 	{
// 	    global $_GPC, $_W;
// 	    $ordersn = trim($_GPC["ordersn"]);
// 	    $operation = trim($_GPC["operation"]);
// 	    if ($operation == "renzheng") {
// 	        $orderres = pdo_fetch("SELECT rzprice FROM " . tablename(BEST_MEMBERRZ) . " WHERE rzordersn = '{$ordersn}'");
// 	        $orderres["price"] = $orderres["rzprice"];
// 	    } else {
// 	        $orderres = pdo_fetch("SELECT price FROM " . tablename(BEST_ORDER) . " WHERE ordersn = '{$ordersn}'");
// 	    }
// 	    $order = array("tid" => $ordersn, "fee" => $orderres["price"], "title" => "订单支付");
// 	    $pay_params = $this->pay($order);
// 	    if (is_error($pay_params)) {
// 	        pdo_update("core_paylog", array("openid" => $_GPC["openid"]), array("tid" => $ordersn, "module" => "cy163_salesjl", "type" => "wxapp"));
// 	        $pay_params = $this->pay($order);
// 	    }
// 	    if (is_error($pay_params)) {
// 	        return $this->result($pay_params["errno"], $pay_params["message"]);
// 	    }
// 	    return $this->result(0, '', $pay_params);
// 	}
	
	
	//确认订单接口
	public function doPageQRorder()
	{
	    global $_W, $_GPC;
	    $ordersn =  $_GPC["ordersn"];
	    
	    $res = pdo_fetch("SELECT * FROM " . tablename(BEST_shoporder) . " WHERE ordersn = ".$ordersn);
	    $yunfei = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = ".$res['pro_id']);
	    $res['yunfei'] = $yunfei['psprice'];
	    $res['sfmoney'] = $res['allmoney'] - $yunfei['psprice'];
	    
	    if($res){
	        $this->result(0, "成功", $res);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	
	
	//多商品结算接口BEST_dianzan
	public function doPagejsCart()
	{
	    global $_W, $_GPC;
	    $allmoney =  $_GPC["allmoney"];
	    $jsCartInfo =  $_GPC["jsCartInfo"];
	    $data = array("ordersn" =>date("YmdHis"),"state" =>0,"allmoney" => $allmoney,"jsCartInfo" => $jsCartInfo,"uid" => $_GPC["uid"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	    $res = pdo_insert(BEST_shoporder, $data);
	    $orderid = pdo_insertid();
	    $result = pdo_fetch("SELECT * FROM " . tablename(BEST_shoporder) . " WHERE id = ".$orderid);
	    
	    if($res){
	        $this->result(0, "成功", $result['ordersn']);
	    }else {
	        $this->result(0, "失败", 0);
	    }
	    
	}
	
	
	
	//点赞接口
	public function doPagedianzan()
	{
	    global $_W, $_GPC;
	    
	    $data = array("jlid" => $_GPC["jlid"],"uid" => $_GPC["uid"], "state" => 0, "addtime" => TIMESTAMP);
	    
	    $res = pdo_insert(BEST_dianzan, $data);
	    
	    if($res){
	        $this->result(0, "点赞成功", 1);
	    }else {
	        $this->result(0, "点赞失败", 0);
	    }
	    
	}
	
	
	//取消点赞接口
	public function doPageqxdianzan()
	{
	    global $_W, $_GPC;
	    
	    $res = array("jlid" => $_GPC["jlid"],"uid" => $_GPC["uid"]);
	    $data = array("state" => 1);

	    $result = pdo_update(BEST_dianzan, $data, $res);
	    
	    if($result){
	        $this->result(0, "取消点赞成功", 1);
	    }else {
	        $this->result(0, "已经取消过了", 0);
	    }
	    
	}
	
	
	//取消订单接口
	public function doPageqxorder()
	{
	    global $_W, $_GPC;
	    $ordersn = $_GPC["ordersn"];
	    $data = array("state" => 2);
	    $result = pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));

	    if($result){
	        $this->result(0, "取消成功", 1);
	    }else {
	        $this->result(0, "已经取消过了", 0);
	    }
	    
	}
	
	
	//确认收货接口
	public function doPageqrenorder()
	{
	    global $_W, $_GPC;
	    
	    $ordersn = $_GPC["ordersn"];
	    
	    $data = array("state" => 5);
	    $result = pdo_update(BEST_shoporder, $data, array("ordersn" => $ordersn));
	    
	    if($result){
	        $this->result(0, "确认成功", 1);
	    }else {
	        $this->result(0, "已经确认过了", 0);
	    }
	    
	}
	
	
	
	//接龙评论接口
	public function doPagejlFistComment()
	{
	    global $_W, $_GPC;

	    $data = array("content" => $_GPC["content"],"jlid" => $_GPC["jlid"],"formId" => $_GPC["formId"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);

	    $res = pdo_insert(BEST_fist_comment, $data);
	    
	    if($res){
	        $this->result(0, "评论成功", 1);
	    }else {
	        $this->result(0, "评论失败", 0);
	    }
	    
	}
	
	
	//搭桥评论
	public function doPagejlTwoComment()
	{
	    global $_W, $_GPC;
	    
	    $data = array("content" => $_GPC["content"],"jlfcId" => $_GPC["jlfcId"],"toId" => $_GPC["toId"],"formId" => $_GPC["formId"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	    $res = pdo_insert(BEST_two_comment, $data);
	    
	    if($res){
	        $this->result(0, "评论别人成功", 1);
	    }else {
	        $this->result(0, "评论失败", 0);
	    }
	    
	}
	
	

	//用户的订单列表接口
	public function doPageUserShopOrderList()
	{
	    global $_W, $_GPC;
	    $uid =  $_GPC["uid"];
	    
	    $condition = "where aid != '' ";
	    
	    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_shoporder).$condition." and uid =".$uid);

	    $psize = 10;
	    $allpage = ceil($total / $psize);
	    $page = intval($_GPC["page"]);
	    $pindex = max(1, $page);
	    $list = pdo_fetchall("SELECT * FROM " . tablename(BEST_shoporder) .$condition."and uid =".$uid." ORDER BY addtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
	    foreach ($list as $k => $v){
	        if(!empty($v['pro_id'])){
	            $res  = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = ".$v['pro_id']);
	            $res['thumb'] = $this->getImg($res['thumb']);
	            $list[$k]['proinfo'] = $res;
	            $thumbs = unserialize($res['thumbs']);
	            if(!empty($thumbs)){
	                foreach ($thumbs as $k2 => $v2){
	                    $list[$k]['proinfo']['thumbss'][$k2] = $this->getImg($v2);
	                }
	            } 
	        }

	        //多产品进入
	        if(!empty($v['jsCartInfo'])){
	            $jsCartInfo = json_decode(htmlspecialchars_decode($v['jsCartInfo']),true);
	            
	            foreach ($jsCartInfo as $k4 => $v4)
	            {
	                if($v4['attr_id'] != 0){
	                    $attrvalue = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = '".$v4['attr_id']."'");
	                    $jsCartInfo[$k4]['attrvalue'] = $attrvalue;
	                }

	                $pros = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = '".$v4['pro_id']."'");
	                $pros['thumb'] = $this->getImg($pros['thumb']);
	                $jsCartInfo[$k4]['product'] = $pros;       
	                
	            }
	            $shopaddr = pdo_fetch("SELECT * FROM " . tablename(BEST_shopaddr) . " WHERE id = '".$v['aid']."'");
	            $list[$k]['jsCartInfo'] = $jsCartInfo;
	            $list[$k]['shopaddr'] = $shopaddr;
	        }

	    }
	    if($list){
	        $this->result(0, "成功", $list);
	    }else {
	        $this->result(0, "请填写地址", 0);
	    }
	    
	}
	
	   
	
	//保存收货地址
	public function doPagesaveAddr()
	{
	    global $_W, $_GPC;
	    
	    $uid = $_GPC['uid'];
	    $result = pdo_fetch("SELECT * FROM " . tablename(BEST_shopaddr) . " WHERE uid = ".$uid);
	    
	    if($result){
	        $data = array("openid" => $_GPC["openid"],"detailaddr" => $_GPC["detailaddr"],"uname" => $_GPC["uname"],"uid" => $_GPC["uid"],"phone" => $_GPC["phone"],"province" => $_GPC["province"],"area" => $_GPC["area"], "city" => $_GPC["city"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	        $resss = pdo_update(BEST_shopaddr, $data, array("uid" => $uid));
	        if($resss){
	            $this->result(0, "保存成功", 1);
	        }else {
	            $this->result(0, "保存失败", 0);
	        }
	    }else {
	        $data = array("openid" => $_GPC["openid"],"detailaddr" => $_GPC["detailaddr"],"uname" => $_GPC["uname"],"uid" => $_GPC["uid"],"phone" => $_GPC["phone"],"province" => $_GPC["province"],"area" => $_GPC["area"], "city" => $_GPC["city"], "weid" => $_W["uniacid"], "addtime" => TIMESTAMP);
	        $res = pdo_insert(BEST_shopaddr, $data);
	        if($res){
	            $this->result(0, "保存成功", 1);
	        }else {
	            $this->result(0, "保存失败", 0);
	        }
	    }

	}

	
	
	
	//购物车列表
	public function doPageCartList()
	{
	    global $_W, $_GPC;

	    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_shopcart));
	    $psize = 10;
	    $allpage = ceil($total / $psize);
	    
	    $page = intval($_GPC["page"]);
	    $pindex = max(1, $page);
	    $list = pdo_fetchall("SELECT * FROM " . tablename(BEST_shopcart) ." ORDER BY addtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
	    
	    foreach ($list as $k => $v){
	        if(!empty($v['proId'])){
	            
	            $res = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = ".$v['proId']);
	            $res['thumb'] = $this->getImg($res['thumb']);
	            
	            $thumbs = unserialize($res['thumbs']);
	            if(!empty($thumbs)){
	                foreach ($thumbs as $k2 =>$v2 ){
	                    $list[$k]['thumbss'][$k2] = $this->getImg($v2);
	                }
	            }
	            $list[$k]['product'] = $res;
	        }
	        if(!empty($v['attrId'])){
	             $res = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = ".$v['attrId']);
	             $list[$k]['attr'] = $res['title'];
	        }
	      
	    }
	    if($list){
	        $this->result(0, "购物车列表获取成功!", array('list'=>$list,'allpage'=>$allpage));
	    }else {
	        $this->result(0, "购物车列表获取失败!", 0);
	    }
	    
	}
	
	
	
	
	
	
	public function doPageFujin()
	{
		include_once ROOT_PATH . "inc/wxapp/fujin.php";
	}
	
	public function doPageHddetail()
	{
		include_once ROOT_PATH . "inc/wxapp/hddetail.php";
	}
	public function doPageHddetailschb()
	{
		global $_W, $_GPC;
		$id = intval($_GPC["id"]);
		$account_api = WeAccount::create();
		$access_token = $account_api->getAccessToken();
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
		$data = array("scene" => $_GPC["scene"], "page" => "cy163_salesjl/pages/detail/detail", "width" => 430);
		$data = json_encode($data);
		$response = $this->send_post($url, $data);
		$this->mkdirs(HB_ROOT_ZFJL);
		$qrcodename = HB_ROOT_ZFJL . $_GPC["scene"] . ".png";
		file_put_contents($qrcodename, $response);
		$filename = HB_ROOT_ZFJL . $_GPC["scene"] . "-hb.jpg";
		if (!file_exists($filename)) {
			$config = array("image" => array(array("url" => $qrcodename, "stream" => 0, "left" => $this->module["config"]["zfjlleft"], "top" => -$this->module["config"]["zfjlbottom"], "right" => 0, "bottom" => 0, "width" => 180, "height" => 180, "opacity" => 100)), "background" => tomedia($this->module["config"]["zfjlthumb"]));
			$this->createPoster($config, $filename);
		}
		$resarr["filename"] = $_W["siteroot"] . "/addons/cy163_salesjl/haibao/zfjl/" . $_GPC["scene"] . "-hb.jpg";
		$resarr["qrcodename"] = $_W["siteroot"] . "/addons/cy163_salesjl/haibao/zfjl/" . $_GPC["scene"] . ".png";
		$this->result(0, "自发接龙海报！", $resarr);
	}
	public function doPageDomembersub()
	{
		include_once ROOT_PATH . "inc/wxapp/domembersub.php";
	}
	public function doPageMyhxy()
	{
		include_once ROOT_PATH . "inc/wxapp/myhxy.php";
	}
	public function doPageMerchanthxy()
	{
		include_once ROOT_PATH . "inc/wxapp/merchanthxy.php";
	}
	public function doPageRenzheng()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$memberrz = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if (!empty($memberrz)) {
			$data["idcard11"] = tomedia($memberrz["idcard1"]);
			$data["idcard22"] = tomedia($memberrz["idcard2"]);
			$data["rzsanzheng11"] = tomedia($memberrz["rzsanzheng"]);
		}
		$data["dspay"] = 0;
		if (empty($memberrz)) {
			$data["canrz"] = 1;
			$data["btntext"] = "支付认证费用" . $this->module["config"]["gerenfee"] . "元";
			$data["btntext2"] = "支付认证费用" . $this->module["config"]["qiyefee"] . "元";
		} else {
			$data["canrz"] = 0;
			if ($memberrz["rzstatus"] == 1) {
				$data["btntext"] = $memberrz["isjujue"] == 1 ? "重新提交" : "您已提交认证资料，请等待审核";
				$data["btntext2"] = $memberrz["isjujue"] == 1 ? "重新提交" : "您已提交认证资料，请等待审核";
			} else {
				$data["btntext"] = "支付认证费用" . $memberrz["rzprice"] . "元";
				$data["btntext2"] = "支付认证费用" . $memberrz["rzprice"] . "元";
				$data["dspay"] = 1;
			}
		}
		$data["memberrz"] = $memberrz;
		$data["rzsm"] = $this->module["config"]["renzhengsm"];
		$data["temstyle"] = $_W["siteroot"] == DZDOMAIN ? 1 : 0;
		$this->result(0, "结算结果", $data);
	}
	public function doPageGerenrz()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		if (empty($_GPC["rzrealname"])) {
			$this->result(1, "请输入姓名！", '');
		}
		$isidcard = $this->validation_filter_id_card($_GPC["rzidcard"]);
		if (empty($isidcard)) {
			$this->result(1, "请输入正确的身份证号！", '');
		}
		if (empty($_GPC["idcard1"])) {
			$this->result(1, "请上传身份证正面照！", '');
		}
		if (empty($_GPC["idcard2"])) {
			$this->result(1, "请上传身份证反面照！", '');
		}
		if (empty($_GPC["rztelphone"])) {
			$this->result(1, "请输入手机号！", '');
		}
		$memberrz = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if (!empty($memberrz)) {
			$data = array("rzrealname" => $_GPC["rzrealname"], "rzidcard" => $_GPC["rzidcard"], "idcard1" => $_GPC["idcard1"], "idcard2" => $_GPC["idcard2"], "rztelphone" => $_GPC["rztelphone"], "isjujue" => 0);
			pdo_update(BEST_MEMBERRZ, $data, array("openid" => $openid));
			$resArr["price"] = '';
			$resArr["ordersn"] = '';
			$this->result(0, "重新提交资料成功！", $resArr);
		} else {
			if ($this->module["config"]["gerenfee"] <= 0) {
				$rzstatus = 1;
			} else {
				$rzstatus = 0;
			}
			$data = array("weid" => $_W["uniacid"], "openid" => $openid, "rzrealname" => $_GPC["rzrealname"], "rzidcard" => $_GPC["rzidcard"], "idcard1" => $_GPC["idcard1"], "idcard2" => $_GPC["idcard2"], "rztelphone" => $_GPC["rztelphone"], "rztype" => 1, "rzordersn" => date("Ymd") . random(12, 1), "rztime" => TIMESTAMP, "rzprice" => $this->module["config"]["gerenfee"], "rzstatus" => $rzstatus);
			pdo_insert(BEST_MEMBERRZ, $data);
			$resArr["price"] = $this->module["config"]["gerenfee"];
			$resArr["ordersn"] = $data["rzordersn"];
			$this->result(0, "申请个人认证成功！", $resArr);
		}
	}
	public function doPageQiyerz()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		if (empty($_GPC["rzqiyename"])) {
			$this->result(1, "请输入企业名称！", '');
		}
		if (empty($_GPC["rzsanzheng"])) {
			$this->result(1, "请上传三合一营业许可证！", '');
		}
		if (empty($_GPC["rzrealname"])) {
			$this->result(1, "请输入法人姓名！", '');
		}
		$isidcard = $this->validation_filter_id_card($_GPC["rzidcard"]);
		if (empty($isidcard)) {
			$this->result(1, "请输入正确的身份证号！", '');
		}
		if (empty($_GPC["idcard1"])) {
			$this->result(1, "请上传身份证正面照！", '');
		}
		if (empty($_GPC["idcard2"])) {
			$this->result(1, "请上传身份证反面照！", '');
		}
		if (empty($_GPC["rztelphone"])) {
			$this->result(1, "请输入手机号！", '');
		}
		$memberrz = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERRZ) . " WHERE openid = '{$openid}'");
		if (!empty($memberrz)) {
			$data = array("rzqiyename" => $_GPC["rzqiyename"], "rzsanzheng" => $_GPC["rzsanzheng"], "rzrealname" => $_GPC["rzrealname"], "rzidcard" => $_GPC["rzidcard"], "idcard1" => $_GPC["idcard1"], "idcard2" => $_GPC["idcard2"], "rztelphone" => $_GPC["rztelphone"], "isjujue" => 0);
			pdo_update(BEST_MEMBERRZ, $data, array("openid" => $openid));
			$resArr["price"] = '';
			$resArr["ordersn"] = '';
			$this->result(0, "重新提交资料成功！", $resArr);
		} else {
			if ($this->module["config"]["qiyefee"] <= 0) {
				$rzstatus = 1;
			} else {
				$rzstatus = 0;
			}
			$data = array("weid" => $_W["uniacid"], "openid" => $openid, "rzqiyename" => $_GPC["rzqiyename"], "rzsanzheng" => $_GPC["rzsanzheng"], "rzrealname" => $_GPC["rzrealname"], "rzidcard" => $_GPC["rzidcard"], "idcard1" => $_GPC["idcard1"], "idcard2" => $_GPC["idcard2"], "rztelphone" => $_GPC["rztelphone"], "rztype" => 2, "rzordersn" => date("Ymd") . random(12, 1), "rztime" => TIMESTAMP, "rzprice" => $this->module["config"]["qiyefee"], "rzstatus" => $rzstatus);
			pdo_insert(BEST_MEMBERRZ, $data);
			$resArr["price"] = $this->module["config"]["qiyefee"];
			$resArr["ordersn"] = $data["rzordersn"];
			$this->result(0, "申请企业认证成功！", $resArr);
		}
	}
	public function doPageCartmsg()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$member = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if ($member["shcity"] != '') {
			$member["shcity"] = explode(",", $member["shcity"]);
		}
		$jlid = intval($_GPC["jlid"]);
		$list = pdo_fetchall("SELECT * FROM " . tablename(BEST_CART) . " WHERE weid = {$_W["uniacid"]} AND jlid = {$jlid} AND openid = '{$member["openid"]}'");
		foreach ($list as $k => $v) {
			$list[$k]["goodsname"] = $v["goodsname"];
			$list[$k]["price"] = $v["price"];
			$list[$k]["total"] = $v["total"];
		}
		$allprice = pdo_fetchcolumn("SELECT SUM(allprice) FROM " . tablename(BEST_CART) . " WHERE weid = {$_W["uniacid"]} AND jlid = {$jlid} AND openid = '{$member["openid"]}'");
		$jielong = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$jlid}");
		if ($allprice >= $jielong["manjian"]) {
			$jielong["yunfei"] = 0;
		}
		if ($jielong["canziti"] == 1 && $jielong["cansh"] == 0) {
			$jielong["ztclass1"] = "now";
			$jielong["ztclass2"] = '';
			$jielong["ztclass3"] = '';
			$jielong["ztclass4"] = "hide";
			$jielong["pstype"] = 1;
		} else {
			$jielong["ztclass1"] = '';
			$jielong["ztclass2"] = "hide";
			$jielong["ztclass3"] = "now";
			$jielong["ztclass4"] = '';
			$jielong["pstype"] = 0;
		}
		$ztdlist = pdo_fetchall("SELECT * FROM " . tablename(BEST_ZITIDIAN) . " WHERE openid = '{$jielong["openid"]}' AND weid = {$_W["uniacid"]} AND ztdtype = 0");
		if (empty($ztdlist)) {
			$ztdlist[0]["id"] = 0;
		}
		$jielong["allprice"] = $allprice;
		$jielong["allprice2"] = $allprice + $jielong["yunfei"];
		$data["member"] = $member;
		$data["jielong"] = $jielong;
		$data["goodslist"] = $list;
		$data["ztdlist"] = $ztdlist;
		$this->result(0, "结算结果", $data);
	}
	public function doPageMembersub()
	{
		include_once ROOT_PATH . "inc/wxapp/membersub.php";
	}
	public function doPageFabu()
	{
		global $_W, $_GPC;
		$data["jlnum"] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W["uniacid"]}");
		$data["jlnum"] = $data["jlnum"] + $this->module["config"]["basicjlnum"];
		$data["cynum"] = pdo_fetchcolumn("SELECT SUM(inpeople) FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W["uniacid"]}");
		$data["cynum"] = $data["cynum"] + $this->module["config"]["basicinpoeple"];
		$data["views"] = pdo_fetchcolumn("SELECT SUM(views) FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W["uniacid"]}");
		$data["views"] = $data["views"] + $this->module["config"]["basicviews"];
		$data["fbthumb"] = tomedia($this->module["config"]["fbthumb"]);
		$data["isagentshow"] = $this->module["config"]["isagentshow"];
		$this->result(0, "发布页面数量", $data);
	}
	
	
	
	//我的
	public function doPageMycenter()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$allmoney = pdo_fetchcolumn("SELECT SUM(money) as allmoney FROM " . tablename(BEST_MEMBERACCOUNT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		
		$data["allmoney"] = empty($allmoney) ? "0.00" : $allmoney;
		$member = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if ($member["shcity"] != '') {
			$member["shcity"] = explode(" ", $member["shcity"]);
		} else {
			$member["shcity"] = '';
		}
		if ($this->module["config"]["isagentshow"] == 1) {
			$member["showmerchant"] = 1;
		} else {
			$member["showmerchant"] = 0;
		}
		$merchant = pdo_fetch("SELECT id FROM " . tablename(BEST_MERCHANT) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$openid}' AND openid != '' AND status = 1");
		$data["member"] = $member;
		$data["merchant"] = $merchant;
		$this->result(0, "个人中心", $data);
	}
	
	
	
	
	public function doPageDzmycenter()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$merchant = pdo_fetch("SELECT id FROM " . tablename(BEST_MERCHANT) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$openid}' AND openid != '' AND status = 1");
		if ($this->module["config"]["isagentshow"] == 1) {
			$data["showmerchant"] = 1;
		} else {
			$data["showmerchant"] = 0;
		}
		$member = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		$data["member"] = $member;
		$data["merchant"] = $merchant;
		$this->result(0, "个人中心", $data);
	}
	public function doPageMerchantcenter()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]} AND status = 1");
		if (!empty($merchant)) {
			$merchant["avatar"] = tomedia($merchant["avatar"]);
			$merchant["total1"] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]} AND status = 0");
			$merchant["total2"] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]} AND status = 1");
			$merchant["total3"] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]} AND status = 2");
			$merchant["total4"] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]} AND status = 4");
			$merchant["tdtitle"] = $this->module["config"]["tdtitle"];
			$merchant["title"] = $_W["siteroot"] == DZDOMAIN ? "团长中心" : "代理中心";
			$this->result(0, "代理商中心", $merchant);
		} else {
			$this->result(0, "请先注册成为代理商！", 0);
		}
	}
	public function doPageMerchantreg()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		$merchant["avatar2"] = $merchant["avatar"];
		$merchant["avatar"] = tomedia($merchant["avatar"]);
		$merchant["agentreg"] = $this->module["config"]["agentreg"];
		$this->result(0, "代理商申请！", $merchant);
	}
	
	
	
	
	
	
	public function doPageDomerreg()
	{
		global $_W, $_GPC;
		if ($this->module["config"]["agentreg"] == 0) {
			$this->result(1, "平台已关闭代理商入驻！", '');
		}
		$openid = trim($_GPC["openid"]);
		if (empty($openid)) {
			$this->result(1, "获取您的信息失败！", '');
		}
		$has = pdo_fetch("SELECT id FROM " . tablename(BEST_MERCHANT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if (!empty($has)) {
			$this->result(1, "您已提交申请，请勿重复操作！", '');
		}
		if (empty($_GPC["realname"])) {
			$this->result(1, "请填写您的真实姓名！", '');
		}
		if (!$this->isMobile($_GPC["telphone"])) {
			$this->result(1, "请填写正确的手机号码！", '');
		}
		$isidcard = $this->validation_filter_id_card($_GPC["idcard"]);
		if (empty($isidcard)) {
			$this->result(1, "请输入正确的身份证号！", '');
		}
		if (empty($_GPC["name"])) {
			$this->result(1, "请填写代理商名称！", '');
		}
		if (empty($_GPC["address"])) {
			$this->result(1, "请填写代理商地址！", '');
		}
		$data = array("weid" => $_W["uniacid"], "realname" => trim($_GPC["realname"]), "telphone" => trim($_GPC["telphone"]), "idcard" => trim($_GPC["idcard"]), "name" => trim($_GPC["name"]), "address" => trim($_GPC["address"]), "addtime" => TIMESTAMP, "openid" => $openid);
		pdo_insert(BEST_MERCHANT, $data);
		$this->result(0, "申请开通资料提交成功，请等待管理员审核！", 0);
	}
	
	
	
	
	
	
	
	
	public function validation_filter_id_card($id_card)
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
				$idcard = substr($idcard, 0, 6) . "18" . substr($idcard, 6, 9);
			} else {
				$idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9);
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
	public function doPageAccount()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$allmoney = pdo_fetchcolumn("SELECT SUM(money) as allmoney FROM " . tablename(BEST_MEMBERACCOUNT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		$allmoney = empty($allmoney) ? "0.00" : $allmoney;
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MEMBERACCOUNT) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$openid}'");
		$allpage = ceil($total / 10) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$psize = 10;
		$moneylist = pdo_fetchall("SELECT * FROM " . tablename(BEST_MEMBERACCOUNT) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$openid}' ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		foreach ($moneylist as $k => $v) {
			$data[$k]["time"] = date("Y-m-d H:i:s", $v["time"]);
			$data[$k]["explain"] = $v["explain"];
			$data[$k]["jiajian"] = $v["money"] > 0 ? "jia" : "jian";
			$data[$k]["money"] = $v["money"];
		}
		$res["accountlist"] = $data;
		$res["allmoney"] = $allmoney;
		$res["allpage"] = $allpage;
		$res["sxf"] = $this->module["config"]["usertxdisaccount"];
		$this->result(0, "个人账户", $res);
	}
	public function doPageMeraccount()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		$allmoney = pdo_fetchcolumn("SELECT SUM(money) FROM " . tablename(BEST_MERCHANTACCOUNT) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]} AND istking = 0 AND candotime < " . TIMESTAMP);
		$allmoney = empty($allmoney) ? "0.00" : $allmoney;
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MERCHANTACCOUNT) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]}");
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$moneylist = pdo_fetchall("SELECT * FROM " . tablename(BEST_MERCHANTACCOUNT) . " WHERE merchant_id = {$merchant["id"]} AND weid = {$_W["uniacid"]} ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		foreach ($moneylist as $k => $v) {
			$data[$k]["time"] = date("Y-m-d H:i:s", $v["time"]);
			$data[$k]["explain"] = $v["explain"];
			$data[$k]["jiajian"] = $v["money"] > 0 ? "jia" : "jian";
			$data[$k]["money"] = $v["money"];
			if ($v["money"] > 0 && $v["candotime"] > TIMESTAMP) {
				$data[$k]["showjs"] = 1;
			} else {
				$data[$k]["showjs"] = 0;
			}
		}
		$res["accountlist"] = $data;
		$res["allmoney"] = $allmoney;
		$res["allpage"] = $allpage;
		if ($merchant["usetx"] == 1) {
			$txdisaccount = $merchant["txdisaccount"];
		} else {
			$txdisaccount = $this->module["config"]["txdisaccount"];
		}
		$res["sxf"] = $txdisaccount;
		$this->result(0, "代理商账户", $res);
	}
	public function doPageProfile()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$data["shname"] = trim($_GPC["shname"]);
		if ($data["shname"] == '') {
			$this->result(1, "请填写收货人姓名！", '');
		}
		$data["shphone"] = trim($_GPC["shphone"]);
		if (!$this->isMobile($data["shphone"])) {
			$this->result(1, "请填写正确的收货人手机号码！", '');
		}
		$shcity = trim($_GPC["shcity"]);
		$shcity = str_replace(",", " ", $shcity);
		if (empty($shcity)) {
			$this->result(1, "请选择地区！", '');
		}
		$shaddress = trim($_GPC["shaddress"]);
		if (empty($shaddress)) {
			$this->result(1, "请填写详细地址！", '');
		}
		$data["shcity"] = $shcity;
		$data["shaddress"] = $shaddress;
		pdo_update(BEST_MEMBER, $data, array("openid" => $openid));
		$this->result(0, "修改个人资料成功！", '');
	}
	public function doPageMerprofile()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MERCHANT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if (empty($_GPC["realname"])) {
			$this->result(1, "请填写您的真实姓名！", '');
		}
		if (!$this->isMobile($_GPC["telphone"])) {
			$this->result(1, "请填写正确的手机号码！", '');
		}
		$isidcard = $this->validation_filter_id_card($_GPC["idcard"]);
		if (empty($isidcard)) {
			$this->result(1, "请输入正确的身份证号！", '');
		}
		if (empty($_GPC["address"])) {
			$this->result(1, "请填写商家地址！", '');
		}
		if (empty($_GPC["avatar"])) {
			$this->result(1, "请上传代理商Logo！", '');
		}
		$data = array("realname" => trim($_GPC["realname"]), "telphone" => trim($_GPC["telphone"]), "idcard" => trim($_GPC["idcard"]), "address" => trim($_GPC["address"]), "avatar" => $_GPC["avatar"]);
		pdo_update(BEST_MERCHANT, $data, array("id" => $merchant["id"]));
		$this->result(0, "修改资料成功！", '');
	}
	public function doPageAddjl()
	{
		include_once ROOT_PATH . "inc/wxapp/addjl.php";
	}
	public function messistr2array($str)
	{
		$str = htmlspecialchars_decode($str);
		$marr = json_decode($str, true);
		return $marr;
	}
	
	
	
	
	//提现
	public function doPageTixian()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$member = pdo_fetch("SELECT rztype FROM " . tablename(BEST_MEMBER) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$openid}'");
		if (empty($member)) {
			$this->result(1, "未获取到您的信息！", '');
		}
		$allmoney = pdo_fetchcolumn("SELECT SUM(money) as allmoney FROM " . tablename(BEST_MEMBERACCOUNT) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		$allmoney = empty($allmoney) ? "0.00" : $allmoney;
		if ($this->module["config"]["rztixian"] == 1 && $member["rztype"] == 0) {
			$this->result(1, "实名认证后才能提现！", '');
		}
		$money = $_GPC["money"];
		if ($money <= 0) {
			$this->result(1, "请输入正确的提现金额！", '');
		}
		if ($money > $allmoney) {
			$this->result(1, "您的余额不足！", '');
		}
		if ($money < $this->module["config"]["present_money"] || $money > $this->module["config"]["present_money_end"]) {
			$this->result(1, "提现金额必须在" . $this->module["config"]["present_money"] . "元 ~ " . $this->module["config"]["present_money_end"] . "元之间！", '');
		}
		$shouxufei = abs($money) * $this->module["config"]["usertxdisaccount"] / 100;
		$shouxufei = sprintf("%.2f", $shouxufei);
		$shidao = abs($money) - $shouxufei;
		$data = array("weid" => $_W["uniacid"], "openid" => $openid, "money" => -$money, "time" => TIMESTAMP, "explain" => "提现", "feilv" => $this->module["config"]["usertxdisaccount"], "dzprice" => $shidao, "membertype" => 1);
		pdo_insert(BEST_TIXIAN, $data);
		$data2 = array("weid" => $_W["uniacid"], "openid" => $openid, "money" => -$money, "time" => TIMESTAMP, "explain" => "提现", "candotime" => TIMESTAMP);
		pdo_insert(BEST_MEMBERACCOUNT, $data2);
		$this->result(0, "提现成功！", '非常ok');
	}
	
	
	
	
	
	public function doPageMertixian()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$merchant = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$openid}' AND weid = {$_W["uniacid"]}");
		if (empty($merchant)) {
			$this->result(1, "未获取到您的信息！", '');
		}
		$allmoney = pdo_fetchcolumn("SELECT SUM(money) FROM " . tablename(BEST_MERCHANTACCOUNT) . " WHERE weid = {$_W["uniacid"]} AND merchant_id = {$merchant["id"]} AND istking = 0 AND candotime < " . TIMESTAMP);
		$allmoney = empty($allmoney) ? "0.00" : $allmoney;
		$money = $_GPC["money"];
		if ($money <= 0) {
			$this->result(1, "请输入正确的提现金额！", '');
		}
		if ($money > $allmoney) {
			$this->result(1, "您的余额不足！", '');
		}
		if ($money < $this->module["config"]["present_money"] || $money > $this->module["config"]["present_money_end"]) {
			$this->result(1, "提现金额必须在" . $this->module["config"]["present_money"] . "元 ~ " . $this->module["config"]["present_money_end"] . "元之间！", '');
		}
		if ($merchant["usetx"] == 1) {
			$txdisaccount = $merchant["txdisaccount"];
		} else {
			$txdisaccount = $this->module["config"]["txdisaccount"];
		}
		$shouxufei = abs($money) * $txdisaccount / 100;
		$shouxufei = sprintf("%.2f", $shouxufei);
		$shidao = abs($money) - $shouxufei;
		$data = array("weid" => $_W["uniacid"], "openid" => $merchant["openid"], "money" => -$money, "time" => TIMESTAMP, "explain" => "提现", "feilv" => $txdisaccount, "dzprice" => $shidao, "membertype" => 2);
		pdo_insert(BEST_TIXIAN, $data);
		$data2 = array("weid" => $_W["uniacid"], "merchant_id" => $merchant["id"], "money" => -$money, "time" => TIMESTAMP, "explain" => "提现", "candotime" => TIMESTAMP);
		pdo_insert(BEST_MERCHANTACCOUNT, $data2);
		$this->result(0, "申请提现成功！", '');
	}
	
	public function doPageMyorder()
	{
		include_once ROOT_PATH . "inc/wxapp/myorder.php";
	}
	public function doPageMyinjl()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$orstatus = intval($_GPC["orstatus"]);
		if ($orstatus == 200) {
			$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = -1";
		} else {
			if ($orstatus == 300) {
				$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = 0";
			} else {
				if ($orstatus == 1) {
					$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = 1";
				} else {
					if ($orstatus == 2) {
						$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = 2";
					} else {
						if ($orstatus == 4) {
							$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = 4";
						} else {
							if ($orstatus == -2) {
								$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = -2";
							} else {
								if ($orstatus == -3) {
									$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1 AND status = -3";
								} else {
									$conditions = "weid = {$_W["uniacid"]} AND from_user = '{$openid}' AND isjl = 1";
								}
							}
						}
					}
				}
			}
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE " . $conditions);
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$list = pdo_fetchall("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE " . $conditions . " ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		foreach ($list as $k => $v) {
			$huodong = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$v["jlid"]}");
			$data[$k]["title"] = $huodong["title"];
			$data[$k]["avatar"] = $huodong["avatar"];
			$data[$k]["time"] = date("Y-m-d H:i:s", $v["createtime"]);
			$data[$k]["price"] = $v["price"];
			$data[$k]["id"] = $v["id"];
			if ($v["status"] == -1) {
				$status = "已取消";
			}
			if ($v["status"] == 0) {
				$status = "待付款";
			}
			if ($v["status"] == 1) {
				if ($v["pstype"] == 0) {
					$status = "待发货";
				} else {
					$status = "待核销";
				}
			}
			if ($v["status"] == 2) {
				$status = "待收货";
			}
			if ($v["status"] == 4) {
				$status = "已完成";
			}
			if ($v["status"] == -2) {
				$status = "退款中";
			}
			if ($v["status"] == -3) {
				$status = "已退款";
			}
			$data[$k]["status"] = $status;
		}
		$res["injielongs"] = $data;
		$res["allpage"] = $allpage;
		$this->result(0, "个人账户", $res);
	}
	public function doPageMyinjltz()
	{
		global $_W, $_GPC;
		$pid = trim($_GPC["pid"]);
		$pidarr = explode("=", $pid);
		$ordersn = trim($_GPC["ordersn"]);
		$orderres = pdo_fetch("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE weid = {$_W["uniacid"]} AND ordersn = '{$ordersn}' AND isjl = 1");
		if ($this->module["config"]["istplon"] == 1) {
			$temvalue = array("keyword1" => array("value" => $orderres["ordersn"], "color" => "#4a4a4a"), "keyword2" => array("value" => $orderres["price"], "color" => "#9b9b9b"), "keyword3" => array("value" => date("Y-m-d H:i:s", $orderres["createtime"]), "color" => "#9b9b9b"), "keyword4" => array("value" => $orderres["address"], "color" => "#9b9b9b"));
			$account_api = WeAccount::create();
			$access_token = $account_api->getAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $access_token;
			$dd = array();
			$dd["touser"] = $orderres["jlopenid"];
			$dd["template_id"] = $this->module["config"]["nt_order_new"];
			$dd["page"] = "cy163_salesjl/pages/fborderdetaildetail/fborderdetaildetail?id=" . $orderres["id"];
			$dd["form_id"] = $pidarr[1];
			$dd["data"] = $temvalue;
			$dd["color"] = '';
			$dd["emphasis_keyword"] = '';
			$result = $this->https_curl_json($url, $dd, "json");
		}
	}
	public function doPageMyfbjlorders()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$id = intval($_GPC["id"]);
		$orstatus = intval($_GPC["orstatus"]);
		if ($orstatus == 200) {
			$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = -1";
		} else {
			if ($orstatus == 300) {
				$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 0";
			} else {
				if ($orstatus == 1) {
					$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 1";
				} else {
					if ($orstatus == 2) {
						$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 2";
					} else {
						if ($orstatus == 4) {
							$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 4";
						} else {
							if ($orstatus == -2) {
								$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = -2";
							} else {
								if ($orstatus == -3) {
									$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = -3";
								} else {
									$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1";
								}
							}
						}
					}
				}
			}
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE " . $conditions);
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$list = pdo_fetchall("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE " . $conditions . " ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		foreach ($list as $k => $v) {
			$huodong = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$v["jlid"]}");
			$ordermember = pdo_fetch("SELECT avatar FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$v["from_user"]}' AND weid = {$_W["uniacid"]}");
			$data[$k]["title"] = $huodong["title"];
			$data[$k]["avatar"] = $ordermember["avatar"];
			$data[$k]["time"] = date("Y-m-d H:i:s", $v["createtime"]);
			$data[$k]["price"] = $v["price"];
			$data[$k]["id"] = $v["id"];
			if ($v["status"] == -1) {
				$status = "已取消";
			}
			if ($v["status"] == 0) {
				$status = "待付款";
			}
			if ($v["status"] == 1) {
				if ($v["pstype"] == 0) {
					$status = "待发货";
				} else {
					$status = "待核销";
				}
			}
			if ($v["status"] == 2) {
				$status = "待收货";
			}
			if ($v["status"] == 4) {
				$status = "已完成";
			}
			if ($v["status"] == -2) {
				$status = "退款中";
			}
			if ($v["status"] == -3) {
				$status = "已退款";
			}
			$data[$k]["status"] = $status;
			$shrs = explode("|", $v["address"]);
			$data[$k]["shr0"] = $shrs[0];
			$data[$k]["shr1"] = $shrs[1];
			$data[$k]["shr2"] = $shrs[2];
			$data[$k]["shr3"] = $shrs[3];
		}
		$res["jielongorders"] = $data;
		$res["allpage"] = $allpage;
		$this->result(0, "个人账户", $res);
	}
	public function doPageMyfbjlorderssearch()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$id = intval($_GPC["id"]);
		$orstatus = intval($_GPC["orstatus"]);
		if ($orstatus == 200) {
			$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = -1";
		} else {
			if ($orstatus == 300) {
				$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 0";
			} else {
				if ($orstatus == 1) {
					$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 1";
				} else {
					if ($orstatus == 2) {
						$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 2";
					} else {
						if ($orstatus == 4) {
							$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = 4";
						} else {
							if ($orstatus == -2) {
								$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = -2";
							} else {
								if ($orstatus == -3) {
									$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1 AND status = -3";
								} else {
									$conditions = "weid = {$_W["uniacid"]} AND jlid = {$id} AND isjl = 1";
								}
							}
						}
					}
				}
			}
		}
		$keyword = trim($_GPC["keyword"]);
		if (empty($keyword)) {
			$this->result(1, "请输入订单号或下单人信息查询！", "请输入订单号或下单人信息查询！");
		} else {
			$conditions .= " AND (ordersn like '%{$keyword}%' OR address like '%{$keyword}%')";
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_ORDER) . " WHERE " . $conditions);
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$list = pdo_fetchall("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE " . $conditions . " ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		foreach ($list as $k => $v) {
			$huodong = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$v["jlid"]}");
			$ordermember = pdo_fetch("SELECT avatar FROM " . tablename(BEST_MEMBER) . " WHERE openid = '{$v["from_user"]}' AND weid = {$_W["uniacid"]}");
			$data[$k]["title"] = $huodong["title"];
			$data[$k]["avatar"] = $ordermember["avatar"];
			$data[$k]["time"] = date("Y-m-d H:i:s", $v["createtime"]);
			$data[$k]["price"] = $v["price"];
			$data[$k]["id"] = $v["id"];
			if ($v["status"] == -1) {
				$status = "已取消";
			}
			if ($v["status"] == 0) {
				$status = "待付款";
			}
			if ($v["status"] == 1) {
				if ($v["pstype"] == 0) {
					$status = "待发货";
				} else {
					$status = "待核销";
				}
			}
			if ($v["status"] == 2) {
				$status = "待收货";
			}
			if ($v["status"] == 4) {
				$status = "已完成";
			}
			if ($v["status"] == -2) {
				$status = "退款中";
			}
			if ($v["status"] == -3) {
				$status = "已退款";
			}
			$data[$k]["status"] = $status;
			$shrs = explode("|", $v["address"]);
			$data[$k]["shr0"] = $shrs[0];
			$data[$k]["shr1"] = $shrs[1];
			$data[$k]["shr2"] = $shrs[2];
			$data[$k]["shr3"] = $shrs[3];
		}
		$res["jielongorders"] = $data;
		$res["allpage"] = $allpage;
		$this->result(0, "个人账户", $res);
	}
	public function doPageMyinjldetail()
	{
		include_once ROOT_PATH . "inc/wxapp/myinjldetail.php";
	}
	public function doPageMyfbjldetail()
	{
		include_once ROOT_PATH . "inc/wxapp/myfbjldetail.php";
	}
	public function doPageMerorder()
	{
		include_once ROOT_PATH . "inc/wxapp/merorder.php";
	}
	public function doPageMerhuodong()
	{
		include_once ROOT_PATH . "inc/wxapp/merhuodong.php";
	}
	public function doPageHuodong()
	{
		include_once ROOT_PATH . "inc/wxapp/huodong.php";
	}
	public function doPageHuodongdz()
	{
		include_once ROOT_PATH . "inc/wxapp/huodongdz.php";
	}
	public function doPageGeterjiquyu()
	{
		global $_W, $_GPC;
		$_W["uniacid"] = $this->Getweid();
		$fcode = trim($_GPC["fcode"]);
		$citys = pdo_fetchall("SELECT name,code FROM " . tablename(BEST_CITY) . " WHERE fcode = '{$fcode}' AND type = 2");
		$this->result(0, "二级区域", $citys);
	}
	public function doPageGetsanjiquyu()
	{
		global $_W, $_GPC;
		$_W["uniacid"] = $this->Getweid();
		$fcode = trim($_GPC["fcode"]);
		$districts = pdo_fetchall("SELECT name,code FROM " . tablename(BEST_CITY) . " WHERE fcode = '{$fcode}' AND type = 3");
		$this->result(0, "三级区域", $districts);
	}
	public function doPageZfhexiao()
	{
		global $_W, $_GPC;
		$member["openid"] = trim($_GPC["openid"]);
		$orderid = intval($_GPC["orderid"]);
		$orderres = pdo_fetch("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND weid = {$_W["uniacid"]} AND status = 1");
		if (empty($orderres)) {
			$this->result(0, "核销详情", "抱歉，没有该订单信息！");
		}
		if ($member["openid"] != $orderres["jlopenid"]) {
			$hexiaoyuan = pdo_fetch("SELECT * FROM " . tablename(BEST_HEXIAOYUAN) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$member["openid"]}' AND fopenid = '{$orderres["jlopenid"]}'");
			if (empty($hexiaoyuan)) {
				$this->result(0, "核销详情", "抱歉，你不是核销员！");
			}
		}
		$data["status"] = 4;
		pdo_update(BEST_ORDER, $data, array("id" => $orderid));
		$hasmemberaccount = pdo_fetch("SELECT id FROM " . tablename(BEST_MEMBERACCOUNT) . " WHERE openid = '{$orderres["jlopenid"]}' AND orderid = {$orderres["id"]}");
		if (empty($hasmemberaccount)) {
			$datamoney["weid"] = $_W["uniacid"];
			$datamoney["openid"] = $orderres["jlopenid"];
			$datamoney["money"] = $orderres["price"];
			$datamoney["time"] = TIMESTAMP;
			$datamoney["orderid"] = $orderres["id"];
			$datamoney["explain"] = "接龙订单";
			$datamoney["candotime"] = TIMESTAMP + $this->module["config"]["zftxhour"] * 3600;
			pdo_insert(BEST_MEMBERACCOUNT, $datamoney);
		}
		$this->result(0, "核销成功", 1);
	}
	public function doPageMerhexiao()
	{
		global $_W, $_GPC;
		$member["openid"] = trim($_GPC["openid"]);
		$orderid = intval($_GPC["orderid"]);
		$orderres = pdo_fetch("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE id = {$orderid} AND weid = {$_W["uniacid"]} AND status = 1");
		if (empty($orderres)) {
			$this->result(0, "核销详情", "抱歉，没有该订单信息！");
		}
		$merchant = pdo_fetch("SELECT id,openid FROM " . tablename(BEST_MERCHANT) . " WHERE id = {$orderres["merchant_id"]}");
		if ($member["openid"] != $merchant["openid"]) {
			$hexiaoyuan = pdo_fetch("SELECT * FROM " . tablename(BEST_HEXIAOYUAN) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$member["openid"]}' AND merchant_id = {$merchant["id"]}");
			if (empty($hexiaoyuan)) {
				$this->result(0, "核销详情", "抱歉，你不是核销员！");
			}
		}
		$data["status"] = 4;
		pdo_update(BEST_ORDER, $data, array("id" => $orderid));
		if ($orderres["isdmfk"] == 0) {
			$hasmerchantaccount = pdo_fetch("SELECT id FROM " . tablename(BEST_MERCHANTACCOUNT) . " WHERE merchant_id = {$orderres["merchant_id"]} AND orderid = {$orderres["id"]}");
			if (empty($hasmerchantaccount)) {
				$datamerchant["weid"] = $_W["uniacid"];
				$datamerchant["merchant_id"] = $orderres["merchant_id"];
				$datamerchant["money"] = $orderres["alllirun"];
				$datamerchant["time"] = TIMESTAMP;
				$datamerchant["explain"] = "代理团结算";
				$datamerchant["orderid"] = $orderres["id"];
				$datamerchant["candotime"] = TIMESTAMP + $this->module["config"]["dltxhour"] * 3600;
				pdo_insert(BEST_MERCHANTACCOUNT, $datamerchant);
			}
		}
		$this->result(0, "核销成功", 1);
	}
	public function doPageZitidian()
	{
		include_once ROOT_PATH . "inc/wxapp/zitidian.php";
	}
	public function doPageMerchantztd()
	{
		include_once ROOT_PATH . "inc/wxapp/merchantztd.php";
	}
	public function doPageMyfbjl()
	{
		global $_W, $_GPC;
		$hdstatus = trim($_GPC["hdstatus"]);
		$openid = trim($_GPC["openid"]);
		if ($hdstatus == "status") {
			$conditions = "weid = {$_W["uniacid"]} AND openid = '{$openid}' AND status = 0";
		} else {
			if ($hdstatus == "isxiugai") {
				$conditions = "weid = {$_W["uniacid"]} AND openid = '{$openid}' AND isxiugai = 1";
			} else {
				if ($hdstatus == "owndel") {
					$conditions = "weid = {$_W["uniacid"]} AND openid = '{$openid}' AND owndel = 1";
				} else {
					if ($hdstatus == "admindel") {
						$conditions = "weid = {$_W["uniacid"]} AND openid = '{$openid}' AND admindel = 1";
					} else {
						if ($hdstatus == "ing") {
							$conditions = "weid = {$_W["uniacid"]} AND openid = '{$openid}' AND owndel = 0 AND admindel = 0 AND status = 1 AND isxiugai = 0";
						} else {
							$conditions = "weid = {$_W["uniacid"]} AND openid = '{$openid}'";
						}
					}
				}
			}
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE " . $conditions);
		$psize = 10;
		$allpage = ceil($total / $psize) + 1;
		$page = intval($_GPC["page"]);
		$pindex = max(1, $page);
		$list = pdo_fetchall("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE " . $conditions . " ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
		foreach ($list as $k => $v) {
			$data[$k]["id"] = $v["id"];
			$data[$k]["title"] = $v["title"];
			$data[$k]["time"] = date("Y-m-d", $v["time"]);
			$data[$k]["price"] = $v["price"];
			$data[$k]["inpeople"] = $v["inpeople"];
			if ($v["status"] == 0) {
				$data[$k]["color"] = "red";
				$status = "审核中";
			} else {
				if ($v["isxiugai"] == 1) {
					$data[$k]["color"] = "red";
					$status = "修改中";
				} else {
					if ($v["owndel"] == 1) {
						$data[$k]["color"] = "red";
						$status = "提前终止";
					} else {
						if ($v["admindel"] == 1) {
							$data[$k]["color"] = "red";
							$status = "平台终止";
						} else {
							$data[$k]["color"] = "green";
							$status = "进行中";
						}
					}
				}
			}
			$data[$k]["status"] = $status;
		}
		$res["fbjielongs"] = $data;
		$res["allpage"] = $allpage;
		$this->result(0, "我发布的接龙", $res);
	}
	public function doPageJiesujl()
	{
		global $_W, $_GPC;
		$openid = trim($_GPC["openid"]);
		$id = intval($_GPC["id"]);
		$memberhd = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE weid = {$_W["uniacid"]} AND openid = '{$openid}' AND id = {$id}");
		if (empty($memberhd)) {
			$resArr["error"] = 1;
			$resArr["message"] = "不存在该接龙！";
			$this->result(0, "不存在该接龙！", $resArr);
		}
		$data["owndel"] = 1;
		pdo_update(BEST_MEMBERHUODONG, $data, array("id" => $id));
		$data2["status"] = -1;
		pdo_update(BEST_ORDER, $data2, array("jlid" => $id, "status" => 0));
		$resArr["message"] = "结束接龙成功！";
		$resArr["error"] = 0;
		$this->result(0, "结束接龙成功！", $resArr);
	}
	
	
	//退款
	public function jlorderrefund($jlid)
	{
		global $_W, $_GPC;
		$lastorder = pdo_fetch("SELECT price,yunfei FROM " . tablename(BEST_ORDER) . " WHERE jlid = {$jlid} AND status >= 1 AND status < 4 ORDER BY price ASC");
		$zuidiprice = $lastorder["price"] - $lastorder["yunfei"];
		$allorder = pdo_fetchall("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE jlid = {$jlid} AND status >= 1 AND status < 4");
		foreach ($allorder as $k => $v) {
			$sspp = $v["price"] - $v["yunfei"];
			if ($sspp > $zuidiprice) {
				$data = array();
				$data["jltuiprice"] = $sspp - $zuidiprice;
				$data["price"] = $zuidiprice;
				pdo_update(BEST_ORDER, $data, array("id" => $v["id"]));
				$datamoney = array();
				$datamoney["weid"] = $_W["uniacid"];
				$datamoney["openid"] = $v["from_user"];
				$datamoney["money"] = $data["jltuiprice"];
				$datamoney["time"] = TIMESTAMP;
				$datamoney["orderid"] = $v["id"];
				$datamoney["explain"] = "接龙订单退差价";
				$datamoney["candotime"] = TIMESTAMP;
				pdo_insert(BEST_MEMBERACCOUNT, $datamoney);
			}
		}
	}
	
	
	
	//上传图片
	public function doPageJlupload()
	{
		global $_W, $_GPC;
		$url = $_FILES["imgs"]["tmp_name"];
		$updir = "../attachment/images/" . $_W["uniacid"] . "/" . date("Y", time()) . "/" . date("m", time()) . "/";
		if (!file_exists($updir)) {
			mkdir($updir, 0777, true);
		}
		$randimgurl = "images/" . $_W["uniacid"] . "/" . date("Y", time()) . "/" . date("m", time()) . "/" . date("YmdHis") . rand(1000, 9999) . ".jpg";
		$targetName = "../attachment/" . $randimgurl;
		move_uploaded_file($url, $targetName);
		if (file_exists($targetName)) {
			$resarr["error"] = 0;
			$this->mkThumbnail($targetName, 640, 0, $targetName);
			if (!empty($_W["setting"]["remote"]["type"])) {
				load()->func("file");
				$remotestatus = file_remote_upload($randimgurl, true);
				if (is_error($remotestatus)) {
					$resarr["error"] = 1;
					$resarr["message"] = "远程附件上传失败，请检查配置并重新上传";
				} else {
					$resarr["imgurl"] = $randimgurl;
					$resarr["message"] = "上传成功";
				}
			}
			$resarr["imgurl"] = $randimgurl;
			$resarr["message"] = "上传成功";
		} else {
			$resarr["error"] = 1;
			$resarr["message"] = "上传文件失败";
		}
		echo json_encode($resarr);
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
		$src_mime = $size["mime"];
		switch ($src_type) {
			case 1:
				$img_type = "gif";
				break;
			case 2:
				$img_type = "jpeg";
				break;
			case 3:
				$img_type = "png";
				break;
			case 15:
				$img_type = "wbmp";
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
		$imagecreatefunc = "imagecreatefrom" . $img_type;
		$src_img = $imagecreatefunc($src);
		$dest_img = imagecreatetruecolor($width, $height);
		imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);
		$imagefunc = "image" . $img_type;
		if ($filename) {
			$imagefunc($dest_img, $filename);
		} else {
			header("Content-Type: " . $src_mime);
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
		return preg_match("/^1[2345789]{1}\\d{9}\$/", $mobile) ? true : false;
	}
	
	
	//支付接口
	public function doPagePay()
	{
		global $_GPC, $_W;
		$ordersn = trim($_GPC["ordersn"]);
		$operation = trim($_GPC["operation"]);
		if ($operation == "renzheng") {
			$orderres = pdo_fetch("SELECT rzprice FROM " . tablename(BEST_MEMBERRZ) . " WHERE rzordersn = '{$ordersn}'");
			$orderres["price"] = $orderres["rzprice"];
		} else {
			$orderres = pdo_fetch("SELECT price FROM " . tablename(BEST_ORDER) . " WHERE ordersn = '{$ordersn}'");
		}
		$order = array("tid" => $ordersn, "fee" => $orderres["price"], "title" => "订单支付");
		$pay_params = $this->pay($order);
		if (is_error($pay_params)) {
			pdo_update("core_paylog", array("openid" => $_GPC["openid"]), array("tid" => $ordersn, "module" => "cy163_salesjl", "type" => "wxapp"));
			$pay_params = $this->pay($order);
		}
		if (is_error($pay_params)) {
			return $this->result($pay_params["errno"], $pay_params["message"]);
		}
		return $this->result(0, '', $pay_params);
	}

	
	public function payResult($params)
	{
		global $_GPC, $_W;
		if ($params["result"] == "success" && $params["from"] == "notify") {
			$sql = "SELECT * FROM " . tablename("core_paylog") . " WHERE `tid`=:tid";
			$pars = array();
			$pars[":tid"] = $params["tid"];
			$log = pdo_fetch($sql, $pars);
			$paydetail = $log["tag"];
			$logtag = unserialize($log["tag"]);
			$ordersnlen = strlen($params["tid"]);
			if ($ordersnlen == 20) {
				pdo_update(BEST_MEMBERRZ, array("rzstatus" => 1, "rztransid" => $logtag["transaction_id"], "rzpaydetail" => $paydetail), array("rzordersn" => $params["tid"]));
			}
			if ($ordersnlen == 21) {
				pdo_update(BEST_ORDER, array("status" => 1, "transid" => $logtag["transaction_id"], "paydetail" => $paydetail), array("ordersn" => $params["tid"]));
				$orderres = pdo_fetch("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE ordersn = '{$params["tid"]}' AND weid = {$_W["uniacid"]}");
				$ordergoods = pdo_fetchall("SELECT * FROM " . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$orderres["id"]}");
				foreach ($ordergoods as $k => $v) {
					$goodsres = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODS) . " WHERE id = {$v["goodsid"]} AND weid = {$_W["uniacid"]}");
					$datagoods["total"] = $goodsres["total"] - $v["total"];
					pdo_update(BEST_GOODS, $datagoods, array("id" => $v["goodsid"]));
					if ($v["optionid"] > 0) {
						$goodsoptionres = pdo_fetch("SELECT * FROM " . tablename(BEST_GOODSOPTION) . " WHERE id = {$v["optionid"]}");
						$datagoodsoption["stock"] = $goodsoptionres["stock"] - $v["total"];
						pdo_update(BEST_GOODSOPTION, $datagoodsoption, array("id" => $v["optionid"]));
					}
				}
			}
			if ($ordersnlen == 22) {
				pdo_update(BEST_ORDER, array("status" => 1, "transid" => $logtag["transaction_id"], "paydetail" => $paydetail), array("ordersn" => $params["tid"]));
				$orderres = pdo_fetch("SELECT * FROM " . tablename(BEST_ORDER) . " WHERE ordersn = '{$params["tid"]}' AND weid = {$_W["uniacid"]}");
				$memberhd = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERHUODONG) . " WHERE id = {$orderres["jlid"]} AND weid = {$_W["uniacid"]}");
				$datamemberhd["inpeople"] = $memberhd["inpeople"] + 1;
				pdo_update(BEST_MEMBERHUODONG, $datamemberhd, array("id" => $orderres["jlid"]));
				$ordergoods = pdo_fetchall("SELECT * FROM " . tablename(BEST_ORDERGOODS) . " WHERE orderid = {$orderres["id"]}");
				foreach ($ordergoods as $k => $v) {
					$goodsres = pdo_fetch("SELECT * FROM " . tablename(BEST_MEMBERGOODS) . " WHERE id = {$v["goodsid"]} AND weid = {$_W["uniacid"]}");
					$datagoods["total"] = $goodsres["total"] - $v["total"];
					$datagoods["inpeople"] = $goodsres["inpeople"] + 1;
					pdo_update(BEST_MEMBERGOODS, $datagoods, array("id" => $v["goodsid"]));
				}
			}
		}
		if ($params["from"] == "return") {
			if ($params["result"] == "success") {
				$this->result(0, "支付成功！");
			} else {
				$this->result(1, "支付失败！");
			}
		}
	}
	public function data_uri($contents, $mime)
	{
		$base64 = base64_encode($contents);
		return "data:" . $mime . ";base64," . $base64;
	}
	public function send_post($url, $post_data)
	{
		$options = array("http" => array("method" => "POST", "header" => "Content-type:application/json", "content" => $post_data, "timeout" => 60));
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
	public function https_curl_json($url, $data, $type)
	{
		if ($type == "json") {
			$headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache");
			$data = json_encode($data);
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$output = curl_exec($curl);
		if (curl_errno($curl)) {
			echo "Errno" . curl_error($curl);
		}
		curl_close($curl);
		return $output;
	}
	public function submitEOrder($requestData)
	{
		$datas = array("EBusinessID" => $this->module["config"]["kdnid"], "RequestType" => "1007", "RequestData" => urlencode($requestData), "DataType" => "2");
		$datas["DataSign"] = $this->encrypt($requestData, $this->module["config"]["kdnkey"]);
		$result = $this->sendPost("http://api.kdniao.cc/api/Eorderservice", $datas);
		return $result;
	}
	public function sendPost($url, $datas)
	{
		$temps = array();
		foreach ($datas as $key => $value) {
			$temps[] = sprintf("%s=%s", $key, $value);
		}
		$post_data = implode("&", $temps);
		$url_info = parse_url($url);
		if (empty($url_info["port"])) {
			$url_info["port"] = 80;
		}
		$httpheader = "POST " . $url_info["path"] . " HTTP/1.0\r\n";
		$httpheader .= "Host:" . $url_info["host"] . "\r\n";
		$httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
		$httpheader .= "Connection:close\r\n\r\n";
		$httpheader .= $post_data;
		$fd = fsockopen($url_info["host"], $url_info["port"]);
		fwrite($fd, $httpheader);
		$gets = '';
		$headerFlag = true;
		yV940:
		if (!feof($fd)) {
			if (!($header = @fgets($fd)) || $header != "\r\n" && $header != "\n") {
				goto yV940;
			}
		}
		while (!feof($fd)) {
			$gets .= fread($fd, 128);
		}
		fclose($fd);
		return $gets;
	}
	public function encrypt($data, $appkey)
	{
		return urlencode(base64_encode(md5($data . $appkey)));
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
	public function createPoster($config = array(), $filename = '')
	{
		if (empty($filename)) {
			header("content-type: image/png");
		}
		$imageDefault = array("left" => 0, "top" => 0, "right" => 0, "bottom" => 0, "width" => 100, "height" => 100, "opacity" => 100);
		$textDefault = array("text" => '', "left" => 0, "top" => 0, "fontSize" => 32, "fontColor" => "255,255,255", "angle" => 0);
		$background = $config["background"];
		$backgroundInfo = getimagesize($background);
		$backgroundFun = "imagecreatefrom" . image_type_to_extension($backgroundInfo[2], false);
		$background = $backgroundFun($background);
		$backgroundWidth = imagesx($background);
		$backgroundHeight = imagesy($background);
		$imageRes = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
		$color = imagecolorallocate($imageRes, 0, 0, 0);
		imagefill($imageRes, 0, 0, $color);
		imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
		if (!empty($config["image"])) {
			foreach ($config["image"] as $key => $val) {
				$val = array_merge($imageDefault, $val);
				$info = getimagesize($val["url"]);
				$function = "imagecreatefrom" . image_type_to_extension($info[2], false);
				if ($val["stream"]) {
					$info = getimagesizefromstring($val["url"]);
					$function = "imagecreatefromstring";
				}
				$res = $function($val["url"]);
				$resWidth = $info[0];
				$resHeight = $info[1];
				$canvas = imagecreatetruecolor($val["width"], $val["height"]);
				imagefill($canvas, 0, 0, $color);
				imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val["width"], $val["height"], $resWidth, $resHeight);
				$val["left"] = $val["left"] < 0 ? $backgroundWidth - abs($val["left"]) - $val["width"] : $val["left"];
				$val["top"] = $val["top"] < 0 ? $backgroundHeight - abs($val["top"]) - $val["height"] : $val["top"];
				imagecopymerge($imageRes, $canvas, $val["left"], $val["top"], $val["right"], $val["bottom"], $val["width"], $val["height"], $val["opacity"]);
			}
		}
		if (!empty($config["text"])) {
			foreach ($config["text"] as $key => $val) {
				$val = array_merge($textDefault, $val);
				list($R, $G, $B) = explode(",", $val["fontColor"]);
				$fontColor = imagecolorallocate($imageRes, $R, $G, $B);
				$val["left"] = $val["left"] < 0 ? $backgroundWidth - abs($val["left"]) : $val["left"];
				$val["top"] = $val["top"] < 0 ? $backgroundHeight - abs($val["top"]) : $val["top"];
				imagettftext($imageRes, $val["fontSize"], $val["angle"], $val["left"], $val["top"], $fontColor, $val["fontPath"], $val["text"]);
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
	
	
	
	private function getImg($img)
	{
	    global $_W;
	    return count(explode("http", $img)) > 1 ? $img : $_W["attachurl"] . $img;
	}
	
	
	
	
	
}