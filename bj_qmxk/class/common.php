<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Your Name <you@example.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id:$

defined('IN_IA') or exit('Access Denied');
require_once 'dev.php';
//define('BJ_QMXK_VERSION', '20160203X5');
define('BAIJIA_COOKIE_QRCODE', 'qrcode_' . BJ_QMXK_VERSION);
define('BAIJIA_COOKIE_SID', 'sid_' . BJ_QMXK_VERSION);
define('BAIJIA_COOKIE_OPENID', 'openid_' . BJ_QMXK_VERSION);
define('BAIJIA_COOKIE_XOAUHURL', 'xoauhurl_' . BJ_QMXK_VERSION);
define('BAIJIA_COOKIE_CHECKOPENID', 'checkopenid_' . BJ_QMXK_VERSION);
define('BAIJIA_COOKIE_CHECKOPURL', 'checkopurl_' . BJ_QMXK_VERSION);
define('BAIJIA_AUTHKEY', 'bj_qmxkcco1905cmodule');
class bj_qmxkModuleCommon extends WeModuleSite {
	public $table_iptable = 'bj_qmxk_iptable';
	
	public function doWebMemberlevel(){
		global $_GPC, $_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
		    $memberlevel = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_memberlevel')." WHERE weid = '{$_W['weid']}' ORDER BY id asc");
			include $this->template('memberlevel');
			exit();
		} elseif ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)){
		          $memberlevel = pdo_fetch("SELECT * FROM ".tablename('bj_qmxk_memberlevel')." WHERE weid = '{$_W['weid']}' and id=$id ORDER BY id asc");
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['levelname'])) {
					message('抱歉，请输入会员等级名称！');
				}
				$discount=intval($_GPC['discount']);
				if (empty($discount)) {
					message('折扣率请输入数字！');
				}
				
				$data = array(
					'weid' => $_W['weid'],
					'levelname' => $_GPC['levelname'],
					'czje' => intval($_GPC['czje']),
					'discount' => intval($_GPC['discount']),
					'description' =>$_GPC['description']
				);
				if (!empty($id)) {
					pdo_update('bj_qmxk_memberlevel', $data, array('id' => $id));
				} else {
					pdo_insert('bj_qmxk_memberlevel', $data);
				}
				message('更新等级成功！', $this->createWebUrl('memberlevel', array('op' => 'display')), 'success');
			}
			include $this->template('memberlevel');
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$memberlevel = pdo_fetch("SELECT id FROM ".tablename('bj_qmxk_memberlevel')." WHERE id = '$id'");
			if (empty($memberlevel)) {
				message('抱歉，等级不存在或是已经被删除！', $this->createWebUrl('memberlevel', array('op' => 'display')), 'error');
			}
			pdo_delete('bj_qmxk_memberlevel', array('id' => $id));
			message('等级删除成功！', $this->createWebUrl('memberlevel', array('op' => 'display')), 'success');
		}
	}
	
	public function doWebShare() {
    global $_W, $_GPC;

    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display-log';

    if ($operation == 'display-log') {
      $list = pdo_fetchall("SELECT SUM(discount) as discount, COUNT(goodsid) as click, goodsid, title  FROM " . tablename($this->table_iptable) . " WHERE weid=:weid GROUP BY goodsid",
        array(":weid" => $_W['weid']));
    }
    include $this->template('share');
	}
	
	private function getDiscountByOrder($orderid, $openid)
  {
    $ordergoods = pdo_fetchall("SELECT goodsid, total,optionid FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}'", array(), 'goodsid');
    return $this->getDiscountByGoodsIds(array_keys($ordergoods), $openid);
  }
  
	private function setDiscountGivenByOrder($orderid, $begger)
  {
    $ordergoods = pdo_fetchall("SELECT goodsid, total,optionid FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}'", array(), 'goodsid');
    $goodsids = array_keys($ordergoods);
    $discount = $this->getDiscountByGoodsIds($goodsids, $begger);
    $this->setDiscountGivenByGoodsIds($goodsids, $begger);
    return $discount;
  }

  private function setDiscountGivenByGoodsIds($goodsids, $begger)
  {
    global $_W;
    if (count($goodsids) > 0) {
      $allgoodsid_str = join(',', $goodsids);
      pdo_query("UPDATE " . tablename($this->table_iptable)
        . " SET exchangetime=:exchangetime WHERE begger=:begger AND weid=:weid AND goodsid in (" . $allgoodsid_str . ") AND exchangetime IS NULL",
          array(':exchangetime'=>TIMESTAMP, ':begger'=>$begger, ':weid'=>$_W['weid']));
    }
  }
	
	private function sfsxj($mid,$fromuser) {
		return false;
	}
	
  ////////////////////////////////////////////////////////////////////////
  /// 聚友杀部分开始
  ////////////////////////////////////////////////////////////////////////
  public function doMobileKill()
  {
    global $_GPC, $_W;
    $goodsid = intval($_GPC['goodsid']);
    $begger = ($_GPC['begger']);
    $weid = intval($_GPC['weid']);
    $giver = $_GPC['giver'];
    $givername = $_GPC['givername'];

    if (empty($goodsid)) {
      $msg = array(
        'title'=>'星际穿越',
        'msg'=>'您在给哪一件商品杀价呢？小二居然在商品库里没找到！so..杀价失败...',
        'code' => 1
      );
      message($msg,'', 'ajax');
    } else if (empty($begger)) {
      $msg = array(
        'title'=>'星际穿越',
        'msg'=>'您在帮谁杀价呢？小二居然在会员库里没找到他！so..杀价失败...',
        'code' => 1
      );
      message($msg,'', 'ajax');
    }

    /* 1. 确定本次杀价的基本数据：
     *   > 是否还能杀 $killdone
     *   > 杀多少 $discount
     */
    $goods = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_goods') . ' WHERE id=:goodsid AND weid=:weid', array(':goodsid'=>$goodsid, ':weid'=>$weid));
    $title = $goods['title'];
    $discount = $this->randBetween($goods['killmindiscount'], $goods['killdiscount']);

    // 各种检查一番，确认砍价资格
    if ($goods['istime'] == 1) {
      if ($goods['timestart'] > TIMESTAMP) {
        message('活动尚未开始', '', 'ajax');
      } else if ($goods['timeend'] < TIMESTAMP) {
        $msg = array(
          'title'=>'杀价失败',
          'msg'=>'活动已经于'.date('m-d H:i', $goods['timeend']).'结束',
          'code' => 1);
        message($msg,'', 'ajax');
      }
    }
    if ($goods['killenable'] != 1) {
      $msg = array(
        'title'=>'客官别急',
        'msg'=> '别太心急噢，活动还在筹备中，请稍后再试 ^_^',
        'code' => 1);
      message($msg,'', 'ajax');
    }

    $gotten_discount = $this->getDiscountByGoodsId($goodsid, $begger);
    if ($goods['killtotaldiscount'] <= $gotten_discount) {
      $msg = array(
        'title'=>'多谢客官',
        'msg'=> '哎呀，你的朋友人气爆棚，已经提前完成打折目标！省的不是一般多啊~~',
        'code' => 1);
      message($msg,'', 'ajax');
    }
    if ($gotten_discount + $discount > $goods['killtotaldiscount']) {
      $discount = $goods['killtotaldiscount'] - $gotten_discount;
    }
    if ($discount < 0) {
      die("Unexpected discount {$discount}");
    }

    WeUtility::logging("KILLPRICE",
      array('discount'=>$discount, 'gotten'=>$gotten_discount, 'total'=>$goods['killtotaldiscount']));

    // 检查策略：
    // 1. 首先检查是否有cookie，若有则不增加点击
    // 2. 如果有giver id，则只检查是否giver id存在，若存在则不增加点击
    // 3. 如果没有give id，则检查IP地址，如果IP地址已经存在，则不增加点击
    //
    // 4. 无论如何，以上完成后总是设置cookie
    if (true != $this->addClick($_W['weid'], $goodsid, $begger, $giver, $givername, getip(), $title, $discount)) {
      $msg = array(
        'title' => '感谢支持',
        'msg' =>'你已经帮忙杀过价啦，谢谢~~杀了又杀，这才是真爱啊！！',
        'code' => 1);
      message($msg,'', 'ajax');
    }
    if ($goods['killdiscount'] <= 0) {
      $msg = array(
        'title' => '手气不好',
        'msg' =>'居然1分钱都没杀下来，哎！',
        'code' => 0);
        message($msg,'', 'ajax');
    }
    $msg = array(
      'title' => '杀价成功',
      'msg' =>'在你的帮助下，你的朋友本单再省'.$discount.'元! 快保留证据让他请吃饭！！',
      'code' => 0
    );
    message($msg,'', 'ajax');
  }
  
    private function addClick($weid, $goodsid, $begger, $giver, $givername, $ip, $title, $discount)
  {
    global $_W;
    $isOK = false;

    // (1) . 检查cookie
    if (false) {
      $cookie_name = "t2-bj_qmxk-" . $weid . "-" . $goodsid . "-" . $begger;
      if (isset($_COOKIE[$cookie_name])) {
        return false;
      } else {
        setcookie($cookie_name, 'killed', time()+60*60*24*7); // 7天内本订单内的每个商品最多杀一次
      }
    }

    // (2) . 如果有giver id，则只检查是否giver id存在，若存在则不增加点击
    if (!empty($giver)) {
      $history = pdo_fetch("SELECT * FROM "
        . tablename($this->table_iptable)
        . " WHERE weid=:weid AND goodsid=:goodsid AND giver=:giver AND begger=:begger AND TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(createtime), NOW()) < 24 * 7",
          array(":weid"=>$_W['weid'], ":goodsid" => $goodsid, ":giver"=>$giver, ":begger"=>$begger));

      // (3) . 如果没有give id，则检查IP地址，如果IP地址已经存在，则不增加点击
    } else {
      $history = pdo_fetch("SELECT * FROM "
        . tablename($this->table_iptable)
        . " WHERE weid=:weid AND ip=:ip AND goodsid=:goodsid AND begger=:begger AND TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(createtime), NOW()) < 24 * 7",
          array(":weid"=>$_W['weid'], ":ip" => $ip, ":goodsid" => $goodsid, ":begger" => $begger));
    }

    if (false != $history) {
      $isOK = false;
    } else {
      $isOK = true;
      pdo_insert($this->table_iptable,
        array("weid"=>$weid,
        "ip"=>$ip,
        "goodsid" => $goodsid,
        "discount" => $discount,
        "title"=>$title,
        "createtime"=>TIMESTAMP,
        "giver"=>$giver,
        "givername"=>$givername,
        "begger"=>$begger));
    }
    return $isOK;
  }
  
   private function randBetween($a, $b)
  {
    $discount = 0;
    if ($a == $b) {
      $discount = $a;
    } else {
      if ($a > $b) {
        $tmp = $a; $a = $b; $b = $tmp; // swap a, b so that $a < $b
      }
      $fval = floatval(rand() % 100) / 100; //  gen value from 0~1
      WeUtility::logging('fv', $fval);
      $discount = $a + $fval * ($b - $a); // get real discount
      WeUtility::logging('discount', $discount);
      $discount = floatval(intval($discount * 100)) / 100; // keep 2 digits at most for discount
    }
    return $discount;
  }
  
  
  private function getDiscountByGoodsId($goodsid, $openid)
  {
    global $_W;
    $discount = pdo_fetchall("SELECT * FROM " . tablename($this->table_iptable)
        . " WHERE begger=:begger AND weid=:weid AND goodsid=:goodsid AND exchangetime IS NULL",
          array(':begger'=> $openid, ':weid'=>$_W['weid'], ':goodsid'=>$goodsid));
    $totaldiscount = 0;
    foreach ($discount as $d) {
      $totaldiscount += $d['discount'];
    }
    return $totaldiscount;
  }

  private function getDiscountByGoodsIds($goodsids, $openid)
  {
    global $_W;
    $totaldiscount = 0.0;
    if (count($goodsids) > 0) {
      $allgoodsid_str = join(',', $goodsids);
      $discounts = pdo_fetchall("SELECT * FROM " . tablename($this->table_iptable)
        . " WHERE begger=:begger AND weid=:weid AND goodsid in (" . $allgoodsid_str . ") AND exchangetime IS NULL",
          array(':begger'=> $openid, ':weid'=>$_W['weid']));
      foreach ($discounts as $d) {
        $totaldiscount += $d['discount'];
      }
    }
    return $totaldiscount;
  }
  
  
 public function doMobileCz() {
    global $_W, $_GPC;
 
    if( (empty($_W['account']['payment']['alipay']['switch']) || empty($_W['account']['payment']['alipay']['secret'])) &&
			(empty($_W['account']['payment']['wechat']['switch']) || empty($_W['account']['payment']['wechat']['secret']))) {
			message('还没有提供支付方式, 请联系' . $_W['account']['name']);
		}
        if (empty($_GPC['test'])){
		  $from_user = $this->getFromUser();
        } else {
         $from_user="test";
        }
		
		
		$_W['fans'] = pdo_fetch('SELECT follow,nickname,from_user,avatar,credit1,credit2 FROM '.tablename('fans')." WHERE  weid = :weid  AND from_user = :from_user LIMIT 1" , array(':weid' => $_W['weid'],':from_user' => $from_user));
		
		$alipay = $_W['account']['payment']['alipay'];
		if(empty($alipay)) {
			message('还没有提供支付方式, 请联系' . $_W['account']['name']);
		}
		
		if (checksubmit('codsubmit')) {
			message('会员充值无法货到付款');
		}
	
		if(checksubmit()) {
			$fee = floatval($_GPC['fee']);
			if($fee < 10) {
				//message('支付金额不能小于 10 元');
			}
			//关闭余额支付
			$_W['account']['payment']['credit']['switch'] = 0;
			$params['tid'] = TIMESTAMP.random(10, 1);
			$params['user'] = $_W['fans']['from_user'];
			$params['fee'] = $fee;
			$params['title'] = $_W['account']['name'] . "用户充值{$fee}";
			$czcookie = "bj_qmxkcz".$_W['weid'];
			setcookie($czcookie,$params['tid'], time()+3600*0.15);
			include $this->template('pay');
			exit;
		}
    $sql="select * from ".tablename("bj_qmxk_memberlevel")." where weid=".$_W['weid']." order by czje asc ";
    $memberlevel = pdo_fetchall($sql);
    
    include $this->template('cz');
 }
   	
  

 public function doMobileShareDetail() {
    global $_W, $_GPC;
    $begger = $_GPC['begger'];
    $beggerinfo=array();
    if (empty($_GPC['test'])){
    if (isset($_GPC['gz'])){
      $beggerinfo = pdo_fetch("SELECT nickname,avatar FROM " . tablename('fans') . " WHERE from_user = :id", array(':id' => $begger));
    }
    
    $from_user = $this->getFromUser();
    $day_cookies = 15;
    $shareid = 'bj_qmxk_sid08'.$_W['weid'];
    if((($_GPC['mid']!=$_COOKIE[$shareid]) && !empty($_GPC['mid'])))
    {
      $this->shareClick($_GPC['mid']);
      setcookie($shareid, $this->getShareId(), time()+3600*24*$day_cookies);
    }

    
    if (empty($from_user)){
    	message("没有用户名");
    }
    
    $profile =fans_search($from_user, array('nickname'));
    $giver = $from_user;
    $givername = $profile['nickname'];
    }  else {
    $from_user="test";
    }
    $goodsid = intval($_GPC['id']);
    $goods = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(':id' => $goodsid));

    if (empty($goods)) {
      message('抱歉，商品不存在或是已经被删除！');
    }
    if ($goods['totalcnf']!=2 && empty($goods['total'])) {
      message('抱歉，商品库存不足！');;
    }
    if ($goods['istime'] == 1) {
      if (time() < $goods['timestart']) {
        message('抱歉，还未到购买时间, 暂时无法购物哦~', referer(), "error");
      }
      if (time() > $goods['timeend']) {
        message('抱歉，商品限购时间已到，不能购买了哦~', referer(), "error");
      }
    }

    //浏览量
    pdo_query("update " . tablename('bj_qmxk_goods') . " set viewcount=viewcount+1 where id=:id and weid='{$_W['weid']}' ", array(":id" => $goodsid));
    $piclist = array(array("attachment" => $goods['thumb']));
    if ($goods['thumb_url'] != 'N;') {
      $urls = unserialize($goods['thumb_url']);
      if (is_array($urls)) {
        $piclist = array_merge($piclist, $urls);
      }
    }

    $marketprice = $goods['marketprice'];
    $productprice= $goods['productprice'];
    $stock = $goods['total'];


  
    $openid = $_GPC['begger'];
    if (!empty($openid)) {
      $juinfo = $this->getClickInfo($goodsid, $openid);
    }
    
    $signPackage=$this->getSignPackage("",'list');

    include $this->template('share_detail');
  }


 private function getClickInfo($goodsid, $begger)
  {
    global $_W;
    $click_info = pdo_fetchall('SELECT * FROM ' . tablename($this->table_iptable) . ' WHERE begger=:begger AND goodsid=:goodsid AND weid=:weid AND exchangetime is NULL',
      array(':begger'=>$begger, ':goodsid'=>$goodsid, ':weid'=>$_W['weid']));
    return $click_info;
  }
  ////////////////////////////////////////////////////////////////////////
  /// 聚友杀部分结束
  ////////////////////////////////////////////////////////////////////////
	
    public function __web($f_name) {
        global $_W, $_GPC;
        checklogin();
        $this->doWebAuth();
        include_once 'web/' . strtolower(substr($f_name, 5)) . '.php';
    }
    public function doWebStatistics() {
        $this->__web(__FUNCTION__);
    }
    public function doWebCategory() {
        $this->__web(__FUNCTION__);
    }
    public function doWebSetGoodsProperty() {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array(
            'new',
            'hot',
            'recommand',
        	'recommandcategory',
            'discount',
            'status',
            'sendfree'
        ))) {
            die(json_encode(array(
                "result" => 0
            )));
        }
        if ($_GPC['type'] == 'status') {
            pdo_update("bj_qmxk_goods", array(
                $type => $data
            ) , array(
                "id" => $id,
                "weid" => $_W['weid']
            ));
        } else {
            pdo_update("bj_qmxk_goods", array(
                "is" . $type => $data
            ) , array(
                "id" => $id,
                "weid" => $_W['weid']
            ));
        }
        die(json_encode(array(
            "result" => 1,
            "data" => $data
        )));
    }
    public function doWebSetArticleProperty() {
        global $_GPC;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        $data == 1 ? $data = -1 : $data = 1;
        if ($_GPC['type'] == 'recommend') {
            pdo_update("bj_qmxk_article", array(
            $type => $data
            ) , array(
            "id" => $id,
            ));
        }
        die(json_encode(array(
            "result" => 1,
            "data" => $data
        )));
    }
    public function doWebSetColumnProperty() {
        global $_GPC;
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        $data == 1 ? $data = -1 : $data = 1;
        if ($_GPC['type'] == 'isopen') {
            pdo_update("bj_qmxk_article_column", array(
            $type => $data
            ) , array(
            "id" => $id,
            ));
        }
        die(json_encode(array(
            "result" => 1,
            "data" => $data
        )));
    }
    public function doWebGoods() {
        $this->__web(__FUNCTION__);
    }
	public function doWebSalesman() {
        $this->__web(__FUNCTION__);
    }
	public function doWebProfit() {
        $this->__web(__FUNCTION__);
    }
    public function doWebOrder() {
        $this->__web(__FUNCTION__);
    }
    public function doWebOrdermy() {
        $this->__web(__FUNCTION__);
    }
    public function doWebNotice() {
        $this->__web(__FUNCTION__);
    }
    public function doWebArticle() {
        $this->__web(__FUNCTION__);
    }
    public function doWebArticle_column() {
        $this->__web(__FUNCTION__);
    }
    public function doWebArticle_share() {
        $this->__web(__FUNCTION__);
    }
    public function doWebVoucher() {
        $this->__web(__FUNCTION__);
    }
    public function doWebVoucher_receive() {
        $this->__web(__FUNCTION__);
    }
    public function doWebPoints_record() {
        $this->__web(__FUNCTION__);
    }
    public function getCartTotal() {
        global $_W;
        $from_user = $this->getFromUser();
        $cartotal = pdo_fetchcolumn("select sum(total) from " . tablename('bj_qmxk_cart') . " where weid = '{$_W['weid']}' and from_user='" . $from_user . "'");
        return empty($cartotal) ? 0 : $cartotal;
    }
    public function getFeedbackType($type) {
        $types = array(
            1 => '维权',
            2 => '告警'
        );
        return $types[intval($type) ];
    }
    public function getFeedbackStatus($status) {
        $statuses = array(
            '未解决',
            '用户同意',
            '用户拒绝'
        );
        return $statuses[intval($status) ];
    }
    public function doWebPhbmedal() {
        $this->__web(__FUNCTION__);
    }
    public function doWebfansmanager() {
        $this->__web(__FUNCTION__);
    }
    public function doWebCommission() {
        $this->__web(__FUNCTION__);
    }
    public function doWebSalary() {
    	$this->__web(__FUNCTION__);
    }
    public function doWebOutCommission() {
        $this->__web(__FUNCTION__);
    }
    public function doWebRules() {
        $this->__web(__FUNCTION__);
    }
    public function doWebSpread() {
        $this->__web(__FUNCTION__);
    }
    public function doWebExpress() {
        $this->__web(__FUNCTION__);
    }
    public function doWebDispatch() {
        $this->__web(__FUNCTION__);
    }
    public function doWebAdv() {
        $this->__web(__FUNCTION__);
    }
    public function doWebAward() {
        $this->__web(__FUNCTION__);
    }
    public function doWebCredit() {
        $this->__web(__FUNCTION__);
    }
    public function doWebZhifu() {
        $this->__web(__FUNCTION__);
    }
    public function doWebCharge() {
        $this->__web(__FUNCTION__);
    }
    public function setOrderStock($id = '', $minus = true) {
        $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,g.total as goodstotal,o.total,o.optionid,g.sales FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$id}'");
        foreach ($goods as $item) {
            if ($minus) {
                if (!empty($item['optionid'])) {
                    pdo_query("update " . tablename('bj_qmxk_goods_option') . " set stock=stock-:stock where id=:id", array(
                        ":stock" => $item['total'],
                        ":id" => $item['optionid']
                    ));
                }
                $data = array();
                if (!empty($item['goodstotal']) && $item['goodstotal'] != - 1) {
                    $data['total'] = $item['goodstotal'] - $item['total'];
                }
                $data['sales'] = $item['sales'] + $item['total'];
                pdo_update('bj_qmxk_goods', $data, array(
                    'id' => $item['id']
                ));
            } else {
                if (!empty($item['optionid'])) {
                    pdo_query("update " . tablename('bj_qmxk_goods_option') . " set stock=stock+:stock where id=:id", array(
                        ":stock" => $item['total'],
                        ":id" => $item['optionid']
                    ));
                }
                $data = array();
                if (!empty($item['goodstotal']) && $item['goodstotal'] != - 1) {
                    $data['total'] = $item['goodstotal'] + $item['total'];
                }
                $data['sales'] = $item['sales'] - $item['total'];
                pdo_update('bj_qmxk_goods', $data, array(
                    'id' => $item['id']
                ));
            }
        }
    }
    public function sendgmsptz($ordersn, $orderprice, $agentname, $to_from_user) {
    	global $_W;
    	//:TODO根据$to_from_user查询member中的openid和realname
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
    	
//         if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
//             return;
//         }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'gmsptz'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message1 = str_replace("{order_price}", $orderprice, $tmsgtemplate['template']);
            $message2 = str_replace("{order_sn}", $ordersn, $message1);
            $message3 = str_replace("{agent_name}", $agentname, $message2);
            $message = str_replace("{time}", $time, $message3);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function sendtjrtz($agentname, $to_from_user) {
    	global $_W;
    	//:TODO根据$to_from_user查询member中的openid和realname
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
        if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
            $this->sendcustomMsg($member_info["openid"], "很高兴通知您(".$member_info["realname"].")，【" . $agentname . "】用户点击了您的链接。");
            return;
        }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'tjrtz'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message2 = str_replace("{agent_name}", $agentname, $tmsgtemplate['template']);
            $message = str_replace("{time}", $time, $message2);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function sendtjrtzewm($agentname, $to_from_user) {
    	global $_W;
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
        if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
            $this->sendcustomMsg($member_info["openid"], "很高兴通知您(".$member_info["realname"].")，(" . $agentname . ")扫描了您的二维码。");
            return;
        }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'tjrtzewm'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message2 = str_replace("{agent_name}", $agentname, $tmsgtemplate['template']);
            $message = str_replace("{time}", $time, $message2);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function sendtjrtzdl($agentname, $to_from_user) {
    	global $_W;
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
        if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
            $this->sendcustomMsg($member_info["openid"], "很高兴通知您(".$member_info["realname"].")，" . $agentname . "已经成为您的代理.");
            return;
        }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'tjrtzdl'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message2 = str_replace("{agent_name}", $agentname, $tmsgtemplate['template']);
            $message = str_replace("{time}", $time, $message2);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function sendxjdlshtz($ordersn, $orderprice, $agentname, $to_from_user) {
    	global $_W;
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
        if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
            return;
        }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'xjdlshtz'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message1 = str_replace("{order_price}", $orderprice, $tmsgtemplate['template']);
            $message2 = str_replace("{order_sn}", $ordersn, $message1);
            $message3 = str_replace("{agent_name}", $agentname, $message2);
            $message = str_replace("{time}", $time, $message3);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function sendyjsqtz($agent_money, $agentname, $to_from_user) {
    	global $_W;
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
        if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
            return;
        }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'yjsqtz'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message1 = str_replace("{agent_money}", $agent_money, $tmsgtemplate['template']);
            $message2 = str_replace("{agent_name}", $agentname, $message1);
            $message = str_replace("{time}", $time, $message2);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function sendsjytktz($agent_money, $agent_level, $to_from_user) {
    	global $_W;
    	//根据$from_user获取openid  2015-12-10(贺磊)
    	$member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
    			':from_user' => $to_from_user,
    			':weid' => $_W['weid']
    	));
        if (CUSTOMER_CODE != '002XM' || CUSTOMER_CODE != '001HEML') {
            return;
        }
        $time = date('Y-m-d H:i:s');
        $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
            ':weid' => $_W['weid'],
            ':key' => 'sjytktz'
        ));
        if (!empty($tmsgtemplate['id']) && !empty($tmsgtemplate['template']) && $tmsgtemplate['tenable'] == 1) {
            $message1 = str_replace("{agent_money}", $agent_money, $tmsgtemplate['template']);
            $message2 = str_replace("{agent_level}", $agent_level, $message1);
            $message = str_replace("{time}", $time, $message2);
            $this->sendcustomMsg($member_info["openid"], $message);
        }
    }
    public function doWebOption() {
        $tag = random(32);
        global $_GPC;
        include $this->template('option');
    }
    public function doWebSpec() {
        global $_GPC;
        $spec = array(
            "id" => random(32) ,
            "title" => $_GPC['title']
        );
        include $this->template('spec');
    }
    public function doWebSpecItem() {
        global $_GPC;
        $spec = array(
            "id" => $_GPC['specid']
        );
        $specitem = array(
            "id" => random(32) ,
            "title" => $_GPC['title'],
            "show" => 1
        );
        include $this->template('spec_item');
    }
    public function doWebParam() {
        $tag = random(32);
        global $_GPC;
        include $this->template('param');
    }
    public function __mobile($f_name) {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $profile = $this->getProfile();
        $signPackage = $this->getSignPackage();
        include_once 'mobile/' . strtolower(substr($f_name, 8)) . '.php';
    }
    public function doMobilePhb() {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $profile = $this->getProfile();
        $signPackage = $this->getSignPackage();
        $list = pdo_fetchall("SELECT member.*,(select avatar from " . tablename('fans') . " fs 
        		where fs.from_user=member.from_user and avatar<>'' limit 1) avatar,(select count(his.sharemid) 
        		from " . tablename('bj_qmxk_share_history') . " his where his.sharemid=member.id) 
        		fanscount FROM " . tablename('bj_qmxk_member') . " member  WHERE member.weid = :weid  
        		
        		order by fanscount desc limit 20", array(
            ':weid' => $_W['weid']
        ));
        include $this->template('phb');
    }
    public function doMobileFansIndex() {
        $this->__mobile(__FUNCTION__);
    }

//     //显示某人的二维码页面(old)
//     public function doMobileQrCode(){
//     	global $_W, $_GPC;
//     	//某人的用户id
//     	$id = $_GPC['mid'];
//     	$signPackage = $this->getSignPackage("QrCode",array("mid"=>$id));
//     	include $this->template('homeerwema');
//     }
	/**
	 * 创建关注二维码
	 */
    public function doMobileCreateQrCode(){
    	$from_user=$this->getFromUser();
    	$this->memberQrcodeNew($from_user);
    }
    
    //显示某人的二维码页面(new)
    public function doMobileQrCode(){
    	global $_W, $_GPC;
    	//某人的用户id
    	$id = $_GPC['mid'];
    	$media_id = $_GPC['media_id'];
    	$qmjf_qr = pdo_fetch("select * from " . tablename('bj_qmxk_qr') . " WHERE weid=:weid and media_id=:media_id
    			 limit 1", array(
    			':weid' => $_W['weid'],
    			 ':media_id'=>$media_id
    	));
    	$signPackage = $this->getSignPackage("QrCode",array("mid"=>$id,"media_id"=>$media_id));
    	include $this->template('homeerwema');
    }
    
    public function doMobileErwema() {
        global $_W, $_GPC;
        //=$from_user = $this->getFromUser();
       $signPackage = $this->getSignPackage();
        $profile = $this->getProfile();
	//	$this->memberQrcode($from_user);
        $weid = $_W['weid'];
        $id = $profile['id'];
        include $this->template('homeerwema');
    }
    public function doMobileRegister() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileCommission() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileProfit() {
        $this->__mobile(__FUNCTION__);
    }
	public function doMobileSalesman(){ 
		$this->__mobile(__FUNCTION__); 
	}
    public function doMobileBankcard() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileFansorder() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileRule() {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $weid = $_W['weid'];
        $op = $_GPC['op'] ? $_GPC['op'] : 'display';
        $rule = pdo_fetchcolumn('SELECT rule FROM ' . tablename('bj_qmxk_rules') . " WHERE weid = :weid", array(
            ':weid' => $_W['weid']
        ));
        $id = pdo_fetchcolumn('SELECT id FROM ' . tablename('bj_qmxk_member') . " WHERE weid = :weid AND from_user = :from_user", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        $signPackage = $this->getSignPackage();
        include $this->template('rule');
    }
    public function doMobilelist() {
        global $_GPC, $_W;
        include_once 'mobile/list.php';
    }
    /**
     * 我的子账号
     */
    public function doMobilechildren() {
    	global $_GPC, $_W;
    	$signPackage = $this->getSignPackage();
    	include_once 'mobile/children.php';
    }
    /**
     * 我的勋章
     */
    public function doMobilemedal() {
    	global $_GPC, $_W;
    	include_once 'mobile/medal.php';
    }
    /**
     * 我的消费记录
     */
    public function doMobilepurchaseHistory() {
    	global $_GPC, $_W;
    	include_once 'mobile/purchaseHistory.php';
    }
    /**
     * 勋章管理
     */
    public function doWebmedal() {
    	global $_GPC, $_W;
    	include_once 'web/medal.php';
    }
    /**
     * 关注分钱管理
     */
    public function doWebsubscribe() {
    	global $_GPC, $_W;
    	include_once 'web/subscribe.php';
    }
    /**
     * 推广订单
     */
    public function doWebexpandorder(){
    	global $_GPC, $_W;
    	include_once 'web/expandorder.php';
    }
    /**
     * 自由余额转账
     */
    public function doMobiletransfer(){
    	global $_GPC, $_W;
     	// include $this->template('transfer');
    	$this->__mobile(__FUNCTION__);
    }
    public function doMobilelistmore_rec() {
        global $_GPC, $_W;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 6;
        $condition = ' and isrecommand=1 ';
        $list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}'  and deleted=0 AND status = '1' $condition ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $signPackage = $this->getSignPackage();
        include $this->template('list_more');
    }
    public function doMobilelistmore() {
        global $_GPC, $_W;
        $signPackage = $this->getSignPackage();
        $pindex = max(1, intval($_GPC['page']));
        $psize = 6;
        $condition = '';
        if (!empty($_GPC['ccate'])) {
            $cid = intval($_GPC['ccate']);
            $condition.= " AND ccate = '{$cid}'";
            $_GPC['pcate'] = pdo_fetchcolumn("SELECT parentid FROM " . tablename('bj_qmxk_category') . " WHERE id = :id", array(
                ':id' => intval($_GPC['ccate'])
            ));
        } elseif (!empty($_GPC['pcate'])) {
            $cid = intval($_GPC['pcate']);
            $condition.= " AND pcate = '{$cid}'";
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        include $this->template('list_more');
    }
    public function doMobilelist2() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobilelistCategory() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobiletuiguang() {
        global $_GPC, $_W;
        $carttotal = $this->getCartTotal();
        $share = BAIJIA_COOKIE_QRCODE . $_W['weid'];
        $gid = $_GPC['gid'];
        $from_user = $this->getFromUser();
        $goods = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(
            ':id' => $gid
        ));
        $rule = pdo_fetchcolumn('SELECT rule FROM ' . tablename('bj_qmxk_rules') . " WHERE weid = :weid", array(
            ':weid' => $_W['weid']
        ));
        $profile = $this->getProfile();
        $id = $profile['id'];
        $signPackage = $this->getSignPackage();
        $this->memberQrcode($from_user);
        if (intval($profile['id']) && $profile['status'] == 0) {
            include $this->template('forbidden');
            exit;
        }
        if (empty($profile)) {
            $rule = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE `weid` = :weid ", array(
                ':weid' => $_W['weid']
            ));
            include $this->template('register');
            exit;
        }
        $cfg = $this->module['config'];
        $logo = $cfg['logo'];
        $description = $cfg['description'];
		
		$share = BAIJIA_COOKIE_QRCODE . $_W['weid'];
if($_COOKIE[$share] != $_W['weid']."share".$id."goods".$gid)
{
include IA_ROOT . BJ_QMXK_BASE . "/common/phpqrcode.php";
$value = $_W['siteroot'].$this->createMobileUrl('detail',array('id'=>$gid,'mid'=>$id));
$errorCorrectionLevel = "L";
$matrixPointSize = "4";
$imgname = "share_qrx{$id}goods{$gid}.png";
$imgurl = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname";
QRcode::png($value, $imgurl, $errorCorrectionLevel, $matrixPointSize);
setCookie($share, $_W['weid']."share".$id."goods".$gid, time()+3600*24);
}
		
        include $this->template('tgym');
    }
    public function doMobileMyfans() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileMyfansDetail() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileMyCart() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileConfirm() {
        $this->__mobile(__FUNCTION__);
    }
    public function setOrderCredit($orderid, $weid, $add = true) {
        $order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id limit 1", array(
            ':id' => $orderid
        ));
        if (empty($order['id'])) {
            return;
        }
        $ordergoods = pdo_fetchall("SELECT goodsid FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}'", array() , 'goodsid');
        if (!empty($ordergoods)) {
            $goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total,credit FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
        }
        if (!empty($goods)) {
            $credits = 0;
            foreach ($goods as $g) {
                $credits+= $g['credit'];
            }
            $fans = pdo_fetch('SELECT credit1 FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
                ':weid' => $weid,
                ':from_user' => $order['from_user']
            ));
            if (!empty($fans)) {
                if ($add) {
                    $new_credit = $credits + $fans['credit1'];
                } else {
                    $new_credit = $fans['credit1'] - $credits;
                    if ($new_credit <= 0) {
                        $new_credit = 0;
                    }
                }
                pdo_update('fans', array(
                    "credit1" => $new_credit
                ) , array(
                    'from_user' => $order['from_user'],
                    'weid' => $weid
                ));
            }
        }
    }
    public function doMobilePay() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileContactUs() {
        global $_W;
        $cfg = $this->module['config'];
        $signPackage = $this->getSignPackage();
        include $this->template('contactus');
    }
    public function doMobileMyOrder() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileDetail() {
        global $_W, $_GPC;
        include_once 'mobile/detail.php';
    }
    public function doMobileCheck() {
    }
    public function doMobileAddress() {
        $this->__mobile(__FUNCTION__);
    }
    public function doMobileAjaxdelete() {
        global $_GPC;
        $delurl = $_GPC['pic'];
        if (file_delete($delurl)) {
            echo 1;
        } else {
            echo 0;
        }
    }
    public function doMobileAward() {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $award_list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_credit_award') . " WHERE weid = '{$_W['weid']}' and NOW() < deadline and amount > 0");
        $profile = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        $signPackage = $this->getSignPackage();
        include $this->template('credit_new');
    }
    public function doMobileFillInfo() {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $award_id = intval($_GPC['award_id']);
        $profile = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        $signPackage = $this->getSignPackage();
        $award_info = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_credit_award') . " WHERE award_id = $award_id AND weid = '{$_W['weid']}'");
        include $this->template('credit_fillinfo_new');
    }
    public function doMobileCredit() {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $signPackage = $this->getSignPackage();
        $award_id = intval($_GPC['award_id']);
        if (!empty($_GPC['award_id'])) {
            $fans = pdo_fetch('SELECT credit1 FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
                ':weid' => $_W['weid'],
                ':from_user' => $from_user
            ));
            $award_info = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_credit_award') . " WHERE award_id = $award_id AND weid = '{$_W['weid']}'");
            if ($fans['credit1'] >= $award_info['credit_cost'] && $award_info['amount'] > 0) {
                $data = array(
                    'amount' => $award_info['amount'] - 1
                );
                pdo_update('bj_qmxk_credit_award', $data, array(
                    'weid' => $_W['weid'],
                    'award_id' => $award_id
                ));
                $data = array(
                    'weid' => $_W['weid'],
                    'from_user' => $from_user,
                    'award_id' => $award_id,
                    'createtime' => TIMESTAMP
                );
                pdo_insert('bj_qmxk_credit_request', $data);
                $data = array(
                    'realname' => $_GPC['realname'],
                    'mobile' => $_GPC['mobile'],
                    'credit1' => $fans['credit1'] - $award_info['credit_cost'],
                    'residedist' => $_GPC['residedist'],
                );
                pdo_update('fans', $data, array(
                    'from_user' => $from_user,
                    'weid' => $_W['weid']
                ));
                message('积分兑换成功！', create_url('mobile/module/mycredit', array(
                    'weid' => $_W['weid'],
                    'name' => 'bj_qmxk',
                    'do' => 'mycredit',
                    'op' => 'display'
                )) , 'success');
            } else {
                message('积分不足或商品已经兑空，请重新选择商品！<br>当前商品所需积分:' . $award_info['credit_cost'] . '<br>您的积分:' . $fans['credit1'] . '. 商品剩余数量:' . $award_info['amount'] . '<br><br>小提示：<br>每日签到，在线订票，宾馆预订可以赚取积分', create_url('mobile/module/award', array(
                    'weid' => $_W['weid'],
                    'name' => 'bj_qmxk'
                )) , 'error');
            }
        } else {
            message('请选择要兑换的商品！', create_url('mobile/module/award', array(
                'weid' => $_W['weid'],
                'name' => 'bj_qmxk'
            )) , 'error');
        }
    }
    public function doMobileSearch() {
        global $_GPC, $_W;
        $keyword = $_GPC['keyword'];
        $url = $this->createMobileUrl('list2', array(
            'name' => 'bj_qmxk',
            'weid' => $_W['weid'],
            'keyword' => $keyword,
            'sort' => 1
        ));
        header("location:$url");
        exit;
    }
    public function doMobileMycredit() {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $signPackage = $this->getSignPackage();
        $award_list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_credit_award') . " as t1," . tablename('bj_qmxk_credit_request') . "as t2 WHERE t1.award_id=t2.award_id AND from_user='" . $from_user . "' AND t1.weid = '{$_W['weid']}' ORDER BY t2.createtime DESC");
        $profile = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        $user = $this->getProfile();
        include $this->template('credit_mycredit_new');
    }
    public function doMobileZhifu() {
        global $_GPC, $_W;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 30;
        $weid = $_W['weid'];
        $from_user = $this->getFromUser();
        $cfg = $this->module['config'];
        $zhifucommission = $cfg['zhifuCommission'];
        $signPackage = $this->getSignPackage();
        $profile = $this->getProfile();
        $id = $profile['id'];
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('paylog') . " WHERE  openid='" . $from_user . "' AND type='zhifu' AND `weid` = " . $_W['weid']);
        $pager = pagination($total, $pindex, $psize);
        $list = pdo_fetchall("SELECT * FROM " . tablename('paylog') . " WHERE openid='" . $from_user . "' AND type='zhifu' AND weid=" . $_W['weid'] . " ORDER BY plid DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
        $profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND from_user = :from_user", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        $id = $profile['id'];
        include $this->template('dakuan');
    }
    public function doMobileArticle_column() {
        $this->__mobile(__FUNCTION__);
    }
    /**
     * 添加分享记录
     */
    public function doMobileSharerecord(){
    	$this->__mobile(__FUNCTION__);
    }
    /**
     * 新增文章阅读
     */
    public function doMobileArticle() {
    	global $_W, $_GPC;
    	//某人的用户id
    	$current_mid = $_GPC['mid'];
    	$article_id= $_GPC['article_id'];
    	$signPackage = $this->getSignPackage("article",array("mid"=>$current_mid,"article_id"=>$article_id));
    	$profile = $this->getProfile();
    	
        $this->__mobile(__FUNCTION__);
    }
    /**
     * 新增每日分享
     */
    public function doWebDailyshare() {
    	global $_W, $_GPC;
    	$this->__web(__FUNCTION__);
    }
    /**
     * 我的代金券
     */
    public function doMobileMycard(){
    	global $_W, $_GPC;
    	$this->__mobile(__FUNCTION__);
    }
    /**
     * 商品评论
     */
    public function doMobileComment(){
    	global $_W, $_GPC;
    	$this->__mobile(__FUNCTION__);
    }
    /**
     * 商品评论
     */
    public function doWebComment(){
    	global $_W, $_GPC;
    	$this->__web(__FUNCTION__);
    }
    /**
     *测试分类
     */
    public function doMobileNewlistcategory(){
    	global $_W, $_GPC;
   	 $this->__mobile(__FUNCTION__);
    }
    
    public function doInsertTestData($testData){
    	 $data = array(
                    'tt' => $testData    
          );
    	 pdo_begin();
         pdo_insert('test', $data);
         pdo_commit();
    }
}
?>
