<?php
defined('IN_IA') or exit('Access Denied');
define('BAIJIA_DEVELOPMENT', false);
require_once 'common.php';
class bj_qmxkModuleCore extends bj_qmxkModuleCommon {
    public function doWebAuth() {
		return;
        if (BAIJIA_DEVELOPMENT == true) {
            return true;
        }
        global $_W, $_GPC;
        $authortxt = " 请联系作者重新授权";
        $modulename = 'bj_qmxkV2';
        $key = BAIJIA_AUTHKEY;
        $sendapi = 'http://www.lhsoft.net/';
        $do = $_GPC['do'];
        $authorinfo = $authortxt;
        $updateurl = create_url('site/module/' . $do, array(
            'name' => $modulename,
            'op' => 'doauth'
        ));
        $op = $_GPC['op'];
        if ($op == 'doauth') {
            $authhost = $_SERVER['SERVER_NAME'];
            $authmodule = $modulename;
            $sendapi = $sendapi . '/authcode.php?act=authcode&authhost=' . $authhost . '&authmodule=' . $authmodule;
            $response = ihttp_request($sendapi, json_encode($send));
            if (!$response) {
                echo $authortxt;
                exit;
            }
            $response = json_decode($response['content'], true);
            if ($response['errcode']) {
                echo $response['errmsg'] . $authorinfo;
                exit;
            }
            if (!empty($response['content'])) {
                $data = array(
                    'url' => $response['content']
                );
                pdo_update('modules', $data, array(
                    'name' => 'bj_qmxk'
                ));
                message('更新授权成功', referer() , 'success');
            }
        }
        $module = pdo_fetch("SELECT mid, name,url FROM " . tablename('modules') . " WHERE name = :name", array(
            ':name' => 'bj_qmxk'
        ));
        if ($module == false) {
            message("参数错误!" . $authorinfo, '', 'error');
        }
        if (empty($module['url'])) {
            message("验证信息为空!" . $authorinfo, '', 'error');
        }
        $ident_arr = authcode(base64_decode($module['url']) , 'DECODE', $key);
        if (!$ident_arr) {
            message("验证参数出错!" . $authorinfo, '', 'error');
        }
        $ident_arr = explode('#', $ident_arr);
        if ($ident_arr[0] != $modulename) {
            message("验证参数出错!" . $authorinfo, '', 'error');
        }
        if ($ident_arr[1] != $_SERVER['SERVER_NAME']) {
            message("服务器域名不符合!" . $authorinfo, '', 'error');
        }
    }
    public function changeWechatSend($id, $status, $msg = '') {
        global $_W;
        $paylog = pdo_fetch("SELECT plid, openid, tag FROM " . tablename('paylog') . " WHERE tid = '{$id}' AND status = 1 AND type = 'wechat'");
        if (!empty($paylog['openid'])) {
            $paylog['tag'] = iunserializer($paylog['tag']);
            $send = array(
                'appid' => $_W['account']['payment']['wechat']['appid'],
                'openid' => $paylog['openid'],
                'transid' => $paylog['tag']['transaction_id'],
                'out_trade_no' => $paylog['plid'],
                'deliver_timestamp' => TIMESTAMP,
                'deliver_status' => $status,
                'deliver_msg' => $msg,
            );
            $sign = $send;
            if ($_W['account']['payment']['wechat']['version'] == '2') {
                return true;
            }
            $sign['appkey'] = $_W['account']['payment']['wechat']['signkey'];
            ksort($sign);
            foreach ($sign as $key => $v) {
                $key = strtolower($key);
                $string.= "{$key}={$v}&";
            }
            $send['app_signature'] = sha1(rtrim($string, '&'));
            $send['sign_method'] = 'sha1';
            $token = $this->get_weixin_token();
            $sendapi = 'https://api.weixin.qq.com/pay/delivernotify?access_token=' . $token;
            $response = ihttp_request($sendapi, json_encode($send));
            $response = json_decode($response['content'], true);
            if (empty($response)) {
                message('发货失败，请检查您的公众号权限或是公众号AppId和公众号AppSecret！');
            }
            if (!empty($response['errcode'])) {
                message($response['errmsg']);
            }
        }
    }
	
	public function payResultCz($params) {
	  global $_W;
	  $fee = floatval($params['fee']);

	  $chargenum=intval($fee);
      if($chargenum)
      { 
        pdo_query("update ".tablename('fans')." SET credit2=credit2+".$chargenum." WHERE from_user='".$params['user']."' AND  weid=".$_W['weid']."  ");
        $paylog=array( 'type'=>'charge', 'weid'=>$params['weid'], 'openid'=>$params['user'], 'tid'=>date('Y-m-d H:i:s'), 'fee'=>$chargenum, 'module'=>'bj_qmxk', 'tag'=>' 前台充值'.$chargenum.'元' );
        pdo_insert('paylog',$paylog);
        $sql="select czje,id from ".tablename("bj_qmxk_memberlevel")." where weid=".$params['weid']." and czje<=".$chargenum." order by czje desc limit 1";
        $memberlevel = pdo_fetch($sql);
        if (!empty($memberlevel['czje'])){
        	$cursql="select czje,id from ".tablename("bj_qmxk_memberlevel").
            " where id in (select memberlevel from ".tablename("bj_qmxk_member").
            " where weid=".$params['weid']." and from_user='".$params['user']."')";
            $curlevel = pdo_fetch($cursql);
            if (empty($curlevel['czje']) || $memberlevel['czje']>$curlevel['czje'] ){
             pdo_query("update ".tablename('bj_qmxk_member')." SET memberlevel=".$memberlevel['id']." WHERE from_user='".$params['user']."' AND  weid=".$params['weid']);
            }
        }
      }
      
		
	
		message('支付成功！', '../../' . $this->createMobileUrl('fansindex'), 'success');
	  
	}
	
    public function payResult($params) {
    	global $_W, $_GPC;
    	$profile = $this->getProfile();
		$param = array();
		$czcookie = "bj_qmxkcz".$params['weid'];
		$tid=$_COOKIE[$czcookie];
		$sql="select status from ".tablename("bj_qmxk_order") . " where id='".$params['tid']."'";
		 
		$oldstatus=pdo_fetchcolumn($sql);
		if($oldstatus>1) {
		    $open=fopen("./payerror.txt","a" );
		    fwrite($open,"\n==payment==".json_encode($params)."====status===".$oldstatus."==\n");
		    fclose($open);
			 echo "success";
		    exit;
		    return;
		}
		if($params['from'] == 'return' && $tid==$params['tid']) {
			//充值
		  $this->payResultCz($params);
		}

        $fee = intval($params['fee']);
        $data = array(
            'status' => $params['result'] == 'success' ? 1 : 0
        );
		
        if ($params['type'] == 'wechat') {
            $data['transid'] = $params['tag']['transaction_id'];
        }
        pdo_update('bj_qmxk_order', $data, array(
            'id' => $params['tid']
        ));
        $cfg = $this->module['config'];
		if($data["status"]==1){
			//:TODO判断该订单是否是该用户的第一单（如果是则修改 special (where status>=1)）
			$is_special=pdo_fetchcolumn("select count(*)  from ".tablename('bj_qmxk_order')." where id <> ".$params['tid']." and status>=1");
			if(!$is_special){
				pdo_update("bj_qmxk_order",array(
					'special'=>1
				), array(
	            	'id' => $params['tid']
	        	));
				//:TODO修改身份
				$this->checkisAgent($profile['from_user'], $profile,true);
			}
			//支付成功====》购买增加余额处理
			//$this->buyDealCredit($params['tid']);
			//减余额
			if(empty($params["is_already_subtractalance"])){
				$this->balancePaid(array("used_credit2"=>$params['used_credit2'],"used_credit3"=>$params['used_credit3'],"vochuer_money"=>$params["vochuer_money"],"voucher_record_ids"=>$params["voucher_record_ids"]));
			}
			//:TODO先模板是写死的以后放入模板中
			$this->sendXjMessage($params['tid']);
		}
        
        
        
		$ismessage = pdo_fetchcolumn("select ismessage from ".tablename("bj_qmxk_order") . " where id='".$params['tid']."'");
        if (($params['from'] == 'return')&&empty($ismessage)) {
        
            $order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = '{$params['tid']}'");
//             if (!empty($this->module['config']['noticeemail'])) {
//                 $ordergoods = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$params['tid']}'", array() , 'goodsid');
//                 $goods = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
//                 $address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(
//                     ':id' => $order['addressid']
//                 ));
//                 $body = "<h3>购买商品清单</h3> <br />";
//                 if (!empty($goods)) {
//                     foreach ($goods as $row) {
//                         $body.= "名称：{$row['title']}，规格：{$ordergoods[$row['id']]['optionname']}  ，数量：{$ordergoods[$row['id']]['total']} <br />";
//                     }
//                 }
// 				$body.= "<br />订单号：{$order['ordersn']}<br />";
// 				$body.= "<br />总金额：{$order['price']}元 <br />";
//                 $body.= "<br />余额支付：（购物余额）{$order['used_credit2']}元 ；（可提余额）{$order['used_credit3']}元<br />";
//                 $body.= "<br />线上支付：{$order['non_payment']}元 <br />";
//                 $body.= "<h3>购买用户详情</h3> <br />";
//                 $body.= "真实姓名：{$address['realname']} <br />";
//                 $body.= "地区：{$address['province']} - {$address['city']} - {$address['area']}<br />";
//                 $body.= "详细地址：{$address['address']} <br />";
//                 $body.= "手机：{$address['mobile']} <br />";
//                 ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
//             }
//             echo "select ismessage from ".tablename("bj_qmxk_order") . " where id=".$params['tid'];
//             exit();
            $tagent = $this->getMember($this->getShareId());
            
         //   $this->sendgmsptz($order['ordersn'], $order['price'], $profile['realname'], $tagent['from_user']);
       //     $this->sendMobilePayMsg($order, $goods, "在线付款", $ordergoods);
         
			pdo_update('bj_qmxk_order', array(
				'ismessage'=>1
			), array(
            	'id' => $params['tid']
        	));
			
            if ($params['type'] == 'credit2') {
                message('支付成功！', $this->createMobileUrl('myorder') , 'success');
            } else {
                message('支付成功！', '../../' . $this->createMobileUrl('myorder') , 'success');
            }
        }
    }
    /**
     * 发送下级下单（已付款）提醒
     */
    public function sendXjMessage($orderId){
    	global $_W, $_GPC;
    	$cfg = $this->module['config'];
    	//根据订单获取上级信息
    	$orderInfo = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id =".$orderId);
    		
    	$share_id_arr=array();
    	$commission = $this->module['config']['globalCommission'];
    	$commission2 = $this->module['config']['globalCommission2'];
    	$commission3 = $this->module['config']['globalCommission3'];
    	//:TODO如果每个商品里面设置了佣金比例？？？？？？？
    	
    	if(!empty($orderInfo["shareid"])){
    		$share_id_arr[0]["shareid"]=$orderInfo["shareid"];
    		$share_id_arr[0]["level"]=1;
    		$share_id_arr[0]["commission"]=$commission;
    	}
    	if(!empty($orderInfo["shareid2"])){
    		$share_id_arr[1]["shareid"]=$orderInfo["shareid2"];
    		$share_id_arr[1]["level"]=2;
    		$share_id_arr[1]["commission"]=$commission2;
    	}
    	if(!empty($orderInfo["shareid3"])){
    		$share_id_arr[2]["shareid"]=$orderInfo["shareid3"];
    		$share_id_arr[2]["level"]=3;
    		$share_id_arr[2]["commission"]=$commission3;
    	}
    	$main_member_info=$this->getProfile();
    	$ordergoods = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid =".$orderId, array() , 'goodsid');
    	$goods = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
    	$body="";
    	if (!empty($goods)) {
    		foreach ($goods as $row) {
    			if($ordergoods[$row['id']]['optionname']){
    				$guige="规格：".$ordergoods[$row['id']]['optionname'].",\n";
    			}
    			$body.= "名称：<a href='".$this->createMobileUrl('detail', array('id' => $row['id']))."'>{$row['title']}</a>,\n".$guige."数量：{$ordergoods[$row['id']]['total']}\n\t";
    		}
    	}
    	for($i=0;$i<count($share_id_arr);$i++){
    		//获取公众号名称$_W["account"]["name"]
    		$share_member_info=pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE id =".$share_id_arr[$i]["shareid"]);
    		$message="    尊敬的".$share_member_info['realname']."会员：\n十分高兴的恭喜您，您的".$share_id_arr[$i]["level"]."级分店店主".$main_member_info["realname"]."刚刚成功付款购买了".$orderInfo['price']."元的商品，订单编号为（".$orderInfo['ordersn']."）。\n\t清单如下:\n".$body."    当前您已获得该订单价值（不含运费）".$share_id_arr[$i]['commission']."%的“待确定积分”，请您及时提醒他确认收货，以便您的“待确定积分”尽早被确定。\n分享卓品二维码！\n晋升高等级会员！\n享商城每日红利！\n";
    		$this->sendcustomMsg($share_member_info["from_user"], $message);
    	}
    	
    }
    /**
     * 余额支付
     */
    public  function balancePaid($ps){
    	global $_W, $_GPC;
    	
    	if($ps['vochuer_money'] && $ps['voucher_record_ids']){
    		//这里执行添加代金券操作
    		$sql = 'UPDATE ' . tablename('bj_qmxk_voucher_receive') . " SET `state`=2,`use_time`='".date('Y-m-d H:i:s')."'
		 	 WHERE  id  in(".$ps['voucher_record_ids'].") ";
    		pdo_query($sql);
    		 
    	}
    	$pars = array(':from_user' => $_W['fans']['from_user'], ':weid' => $_W['weid']);
    	$used_credit2= floatval($ps['used_credit2']);
    	$used_credit3 = floatval($ps['used_credit3']);
    	$sql = 'UPDATE ' . tablename('fans') . " SET `credit2`=`credit2`-{$used_credit2},`credit3`=`credit3`-{$used_credit3}   WHERE from_user = :from_user AND weid = :weid";//2015/05/14 修改
    	if(pdo_query($sql, $pars) == 1) {
    		return true;
    	}
    }
	//2015/6/11 修改
	public function getShareId($from_user = '', $level = 1) {
        global $_W, $_GPC;
        if (empty($from_user)) {
            $from_user = $this->getFromUser();
        }
        $profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND from_user = :from_user", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        if (empty($profile['shareid'])) {
            return 0;
        } else {
            if ($level == 1) {
                return $profile['shareid'];
            }
            if ($level == 2 || $level == 3) {
                $profile2 = pdo_fetch('SELECT shareid FROM ' . tablename('bj_qmxk_member') . " WHERE  id=:sid", array(
                    ':sid' => $profile['shareid']
                ));
                if (empty($profile2['shareid'])) {
                    return 0;
                }
                if ($level == 2) {
                    return $profile2['shareid'];
                }
            }
            if ($level == 3) {
                $profile3 = pdo_fetch('SELECT shareid FROM ' . tablename('bj_qmxk_member') . " WHERE  id=:sid", array(
                    ':sid' => $profile2['shareid']
                ));
                if (empty($profile3['shareid'])) {
                    return 0;
                }
                return $profile3['shareid'];
            }
            return 0;
        }
    } 
	
    public function time_tran($the_time) {
        $timediff = $the_time - time();
        $days = intval($timediff / 86400);
        if (strlen($days) <= 1) {
            $days = "0" . $days;
        }
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);;
        if (strlen($hours) <= 1) {
            $hours = "0" . $hours;
        }
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        if (strlen($mins) <= 1) {
            $mins = "0" . $mins;
        }
        $secs = $remain % 60;
        if (strlen($secs) <= 1) {
            $secs = "0" . $secs;
        }
        $ret = "";
        if ($days > 0) {
            $ret.= $days . " 天 ";
        }
        if ($hours > 0) {
            $ret.= $hours . ":";
        }
        if ($mins > 0) {
            $ret.= $mins . ":";
        }
        $ret.= $secs;
        return array(
            "倒计时 " . $ret,
            $timediff
        );
    }
    public function bjpay($params = array() , $paytype) {
        global $_W;
        if ($params['fee'] <= 0) {
            message('支付错误, 金额小于0');
        }
        $params['module'] = $this->module['name'];
        $sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `weid`=:weid AND `module`=:module AND `tid`=:tid';
        $pars = array();
        $pars[':weid'] = $_W['weid'];
        $pars[':module'] = $params['module'];
        $pars[':tid'] = $params['tid'];
        $log = pdo_fetch($sql, $pars);
        if (!empty($log) && $log['status'] == '1') {
            message('这个订单已经支付成功, 不需要重复支付.');
        }
        include $this->template('bjpay');
    }
    
    /**
     * 扫二维码
     * @param unknown $mid
     * @param number $joinway
     */
    public function shareClick($mid, $joinway = 0) {
        global $_W, $_GPC;
        //pdo_insert("test",array("tt"=>12344));
		if (empty($joinway)) {
            $joinway = 0;
        }
        $fromuser = $this->getFromUser();
        $share = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_share_history') . " WHERE from_user=:from_user and weid=:weid", array(
            ':from_user' => $fromuser,
            ':weid' => $_W['weid']
        ));
        //如果已经有分享记录，无需处理
        if(!empty($share['sharemid'])){ 
        	return;
        }
        //$member 分享者    , $users 扫码者
        $member = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = '{$_W['weid']}' AND id = '{$mid}'");
        $users = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = '{$_W['weid']}' AND from_user = '{$fromuser}' limit 1");
        if (empty($share['sharemid']) && !empty($mid) && empty($users['id'])) {
            if (!empty($member['id'])) {
                $data = array(
                    'weid' => $_W['weid'],
                    'from_user' => $fromuser,
                    'sharemid' => $mid,
                    'joinway' => $joinway
                );
                pdo_insert('bj_qmxk_share_history', $data);
                pdo_update('bj_qmxk_member', array(
                    'clickcount' => $member['clickcount'] + 1
                ) , array(
                    'id' => $mid
                ));
                $theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
                    ':weid' => $_W['weid']
                ));
                $joinfans = pdo_fetch('SELECT from_user,nickname,id FROM ' . tablename('fans') . " WHERE `weid` = :weid AND from_user=:from_user limit 1", array(
                    ':weid' => $_W['weid'],
                    ':from_user' => $fromuser
                ));
                $clickNickname = "";
                if (!empty($joinfans['nickname'])) {
                    $clickNickname = $joinfans['nickname'];
                }else{
                	//从微信中获取微信昵称
                	$clickNickname=$this->getWXUserInfo($joinfans["id"]);
                }
                if ($joinway == 0) {

                    $this->sendtjrtz($clickNickname, $member['from_user']);
                } else {
                    $this->sendtjrtzewm($clickNickname, $member['from_user']);
                }
                if ((!empty($theone['clickcredit']))) {
                    $fans = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid and from_user=:from_user", array(
                        ':weid' => $_W['weid'],
                        ':from_user' => $member['from_user']
                    ));
                    if ((!empty($fans)) && ($member["flag"]==1)) {
                        pdo_update('fans', array(
                            'credit1' => $fans['credit1'] + $theone['clickcredit']
                        ) , array(
                            'id' => $fans['id'],
                            'weid' => $_W['weid']
                        ));
                        //添加积分记录
                        $integral["remark"]="扫码成功，积分+".$theone['clickcredit'];
                        $integral["points"]=$theone['clickcredit'];
                        $integral["type"]=4;
                        $integral["from_user"]=$member['from_user'];
                        $integral["balance"]=1;
                        $integral["addtime"]=date("Y-m-d H:i:s");
                        pdo_insert("bj_qmxk_points_record",$integral);
			$this->sendcustomMsg($member['from_user'], $integral["remark"]);
                    }
                }
            }
        }
    }
	 public function shareClick1($from_user, $mid, $joinway = 0) {
        global $_W, $_GPC;
      
      //  $this->sendcustomMsg($from_user, "1111");
      //  pdo_insert("test",array("tt"=>12344));
		if (empty($joinway)) {
            $joinway = 0;
        }
        $fromuser = $from_user;
        $share = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_share_history') . " WHERE from_user=:from_user and weid=:weid", array(
            ':from_user' => $fromuser,
            ':weid' => $_W['weid']
        ));
        $member = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = '{$_W['weid']}' AND id = '{$mid}'");
        $users = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = '{$_W['weid']}' AND from_user = '{$fromuser}' limit 1");
        $this->sendcustomMsg($member['from_user'], "以前是否有关系(".$share['sharemid'].")===上级id".$mid."====扫码者memberID(".$users['id'].")");
        if (empty($share['sharemid']) && !empty($mid) && empty($users['id'])) {
            if (!empty($member['id'])) {
                $data = array(
                    'weid' => $_W['weid'],
                    'from_user' => $fromuser,
                    'sharemid' => $mid,
                    'joinway' => $joinway
                );
                pdo_insert('bj_qmxk_share_history', $data);
                pdo_update('bj_qmxk_member', array(
                    'clickcount' => $member['clickcount'] + 1
                ) , array(
                    'id' => $mid
                ));
                $theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
                    ':weid' => $_W['weid']
                ));
                $joinfans = pdo_fetch('SELECT from_user,nickname,id  FROM ' . tablename('fans') . " WHERE `weid` = :weid AND from_user=:from_user limit 1", array(
                    ':weid' => $_W['weid'],
                    ':from_user' => $fromuser
                ));
                $clickNickname = "";
                if (!empty($joinfans['nickname'])) {
                    $clickNickname = $joinfans['nickname'];
                }else{
                	$clickNickname=$this->getWXUserInfo($joinfans["id"]);
                }
                if ($joinway == 0) {
                    $this->sendtjrtz($clickNickname, $member['from_user']);
                } else {
                    $this->sendtjrtzewm($clickNickname, $member['from_user']);
                }
                if ((!empty($theone['clickcredit']))) {
                    $fans = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid and from_user=:from_user", array(
                        ':weid' => $_W['weid'],
                        ':from_user' => $member['from_user']
                    ));
                     if ((!empty($fans)) && ($member["flag"]==1)) {
                    	pdo_update('fans', array(
                    	'credit1' => $fans['credit1'] + $theone['clickcredit']
                    	) , array(
                    	'id' => $fans['id'],
                    	'weid' => $_W['weid']
                    	));
                    	$this->createMobileUrl();
                    	//添加积分记录
                    	$integral["remark"]="扫码成功，积分+".$theone['clickcredit'];
                    	$integral["points"]=$theone['clickcredit'];
                    	$integral["type"]=4;
                    	$integral["from_user"]=$member['from_user'];
                    	$integral["balance"]=1;
                    	$integral["addtime"]=date("Y-m-d H:i:s");
                    	pdo_insert("bj_qmxk_points_record",$integral);
                    	$this->sendcustomMsg($member['from_user'], $integral["remark"]);
                    }
               
                }
            }
        }
    }
	
    public function sendMobilePayMsg($order, $goods, $paytype, $ordergoods) {
        global $_W;
        $address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(
            ':id' => $order['addressid']
        ));
        $cfg = $this->module['config'];
        $template_id = $cfg['paymsgTemplateid'];
        if (empty($template_id)) {
            if (BAIJIA_AGENT_ALL == true) {
                $template_id = BAIJIA_TEMPLATESID;
            }
        }
        if (!empty($template_id)) {
            include IA_ROOT . BJ_QMXK_BASE . '/messagetemplate/pay.php';
            $this->sendtempmsg($template_id, $_W['siteroot'] . $this->createMobileUrl('myorder', array(
                'orderid' => $order['id'],
                'op' => 'detail'
            )) , $data, '#FF0000');
        }
    }
    /**
     * 获取（修改）指定粉丝的微信昵称
     * @param unknown $fansId
     */
    public function getWXUserInfo($fansId){
    	global $_W, $_GPC;
    	$fanslist = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE  weid=:weid and nickname='' and realname='' and id=:id", array(
    			':weid' => $_W['weid'],
    			':id'=>$fansId
    	));
    	$access_token = $this->get_weixin_token();
    	$oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $fanslist['from_user'] . "&lang=zh_CN";
    	$content = ihttp_get($oauth2_url);
    	$info = @json_decode($content['content'], true);
    	if (!empty($info["nickname"])) {
    		$row = array(
    				'nickname' => $info["nickname"],
    				'realname' => $info["nickname"],
    				'gender' => $info['sex'],
    				'avatar' => $info["headimgurl"]
    		);
    		pdo_update('fans', $row, array(
    		'id' => $fansId
    		));
    	}
    	return $info["nickname"];
    }
    public function doWebFixfans() {
        global $_W, $_GPC;
        $fanslist = pdo_fetchall("SELECT * FROM " . tablename('fans') . " WHERE  weid=:weid and nickname='' and realname=''", array(
            ':weid' => $_W['weid']
        ));
        $access_token = $this->get_weixin_token();
        foreach ($fanslist as & $fans) {
            $oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $fans['from_user'] . "&lang=zh_CN";
            $content = ihttp_get($oauth2_url);
            $info = @json_decode($content['content'], true);
            if (!empty($info["nickname"])) {
                $row = array(
                    'nickname' => $info["nickname"],
                    'realname' => $info["nickname"],
                    'gender' => $info['sex'],
                    'avatar' => $info["headimgurl"]
                );
                pdo_update('fans', $row, array(
                    'id' => $fans['id']
                ));
            }
        }
        message('检修完成', referer() , 'success');
    }
    /**
     * 判断是否是东家
     * @param unknown $from_user
     * @param unknown $profile
     * @return Ambigous <number, unknown>
     */
    public function checkisAgent($from_user, $profile,$is_special=false) {
        global $_W, $_GPC;
        $flag = $profile['flag'];
        //如暂不是代理
        if (!empty($profile['id']) && $profile['flag'] == 0) {
        	if($is_special){
        		$where="status >=1";
        	}else{
        		$where="status =3";
        	}
			//订单完成
        	$total = pdo_fetchcolumn('SELECT count(id) FROM ' . tablename('bj_qmxk_order') . " WHERE  $where AND  weid = :weid  AND from_user = :from_user", array(
        			':weid' => $_W['weid'],
        			':from_user' => $from_user
        	));
            $totalmoney = pdo_fetchcolumn('SELECT sum(price) FROM ' . tablename('bj_qmxk_order') . " WHERE $where  AND  weid = :weid  AND from_user = :from_user", array(
                ':weid' => $_W['weid'],
                ':from_user' => $from_user
            ));
            //查询分销代理规则
            $commtime = pdo_fetch("select promotercount,promotermoney,promotertimes from
            		 " . tablename('bj_qmxk_rules') . "   where weid = " . $_W['weid']);

            $lastorder = pdo_fetch('SELECT createtime FROM ' . tablename('bj_qmxk_order') . "
            		 WHERE $where  AND  weid = :weid  AND from_user = :from_user
            		 order by createtime desc limit 1", array(
                        		 		':weid' => $_W['weid'],
                        		 		':from_user' => $from_user
                        		 ));
            // $commtime['promotertimes']    0购买一单 成为代理 1无条件成为代理 2达到单数 3达到金额
            //promotercount 成为代理需要成交单数
            //promotermoney  成为代理需要成交总金额
            $toagent = 0;
            if ($total >= 1 && $commtime['promotertimes'] == 0) {
                $toagent = 1;
            }
            if ($commtime['promotercount'] <= $total && $commtime['promotertimes'] == 2) {
                $toagent = 1;
            }
            if ($commtime['promotermoney'] <= $totalmoney && $commtime['promotertimes'] == 3) {
                $toagent = 1;
            }
            if ($commtime['promotertimes'] == 1) {
                $toagent = 1;
            }
            if ($toagent == 1) {
                $flagtime = $lastorder['createtime'];
                if (empty($flagtime)) {
                    $flagtime = TIMESTAMP;
                }
                pdo_update('bj_qmxk_member', array(
                    'flagtime' => $flagtime,
                    'flag' => 1
                ) , array(
                    'id' => $profile['id']
                ));
			//	$msg = "恭喜您（".$profile["realname"]."）已成为".$this->module['config']['agent_title']."！";
			//	$this->sendcustomMsg($profile[from_user],$msg);
				/**
				*新增回复
				 */
				$message="恭喜您成为了【".$_W["account"]["name"]."】正式店主会员并拥有了个人专属推广二维码，感谢你对【".$_W["account"]["name"]."】的支持！请加【".$_W["account"]["name"]."】会员交流学习微信号：QXCFKF02为好友,\n了解公司政策，\n共享推广经验，\n学习推广知识！\n";
				$this->sendcustomMsg($profile[from_user],$message);
				$flag = 1;
                /**
                 *判断是否为东家的同时 检验是否符合勋章要求（并添加）
                 */
                if(!empty($profile['shareid'])){
                	//往上推一级
                	$info1_shareid=$this->GiveMedal($profile['shareid'],1);
                }
                if(!empty($info1_shareid)){
                	//往上推二级
                	$info2_shareid=$this->GiveMedal($info1_shareid,2);
                }
                if(!empty($info2_shareid)){
                	//往上推三级
                	$this->GiveMedal($info2_shareid,3);
                }
            }
        }
     	//如果是代理，则判断是否可以给自己加勋(如：消费金额达到一定条件)
        if (!empty($profile['id']) && $profile['flag'] == 1){
        	$this->GiveMyselfMedal($from_user);
        }
        return $flag;
    }
    
    
    /**
     * 给自己加勋
     */
    public function  GiveMyselfMedal($from_user){
    	
    	
    	global $_W, $_GPC;
    	$cfg = $this->module['config'];
    	//勋章信息
    	$medal_info= pdo_fetchall('select * from ' . tablename('bj_qmxk_medal') . " where weid = " . $_W['weid']."
        		and medal_status=1 order by monetary desc");
    	//获取该用户的总销售金额
    	$ordermoney=pdo_fetchcolumn("SELECT sum(price)  as ordermoney FROM " . tablename('bj_qmxk_order') . "
    			WHERE weid =". $_W['weid']."   and from_user='".$from_user."' and status>=1");
    	
    	$member_info= pdo_fetch('select * FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid  AND from_user=:from_user ",array(
    			':weid' => $_W['weid'],
    			':from_user' => $from_user
    	));
    	//查询已拥有的勋章信息
    	$old_have_medal= pdo_fetch('select id,medal_id  FROM ' . tablename('bj_qmxk_relation_medal') . "
    				WHERE `weid` =:weid   and from_user=:from_user and status=:status",array(
    	    						':weid' => $_W['weid'],
    	    						':from_user'=>$from_user,
    	    						':status'=>1
    	    				));
    	$old_medal_info=pdo_fetch('select medal_name,monetary  FROM ' . tablename('bj_qmxk_medal') . "
    				WHERE `weid` =:weid  AND id=:medal_id  and medal_status=:medal_status",array(
    	    						':weid' => $_W['weid'],
    	    						':medal_id'=>$old_have_medal["medal_id"],
    	    						':medal_status'=>1
    	    				));
    	
    	 	for($i=0;$i<count($medal_info);$i++){
    	 		$monetary=$medal_info[$i]["monetary"];
    	 		$medal_id=$medal_info[$i]["id"];
    	 		$medal_name=$medal_info[$i]["medal_name"];
    	 		

    	 		if(strpos($medal_info[$i]["rules_type"], '3') !== false){
    	 			if($ordermoney>=$monetary){
    	 				//符合勋章条件：1. 没有勋章，新增勋章。如果串行，break  
    	 				//    2. 有勋章。已获得勋章和本次循环可获得勋章相等，如果串行，break； 
    	 				// 已获得勋章和本次循环可获得勋章不相等，并行，插入;  串行，如果勋章是升级，更新
    	 				if($old_have_medal["id"]){
    	 					//有勋章
    	 					if($medal_id ==$old_have_medal["medal_id"] ){
    	 						break;
    	 					}else{
    	 						if($cfg["multi"]==2){
    	 							if($monetary>$old_medal_info["monetary"]){
    	 								$data = array(
    	 										'weid' => $_W['weid'],
    	 										'from_user' => $from_user,
    	 										'status' => 1,
    	 										'medal_id' => $medal_id,
    	 										'get_time'=>date("Y-m-d H:i:s")
    	 								);
    	 								pdo_update('bj_qmxk_relation_medal', $data,array("id"=>$old_have_medal["id"]));
    	 								//升级提示
    	 								$this->sendcustomMsg($from_user, "恭喜您(".$member_info['realname'].")由(".$old_medal_info["medal_name"].")升级到（".$medal_name."）");
    	 								break;
    	 							}
    	 						}else{
    	 							$data = array(
    	 									'weid' => $_W['weid'],
    	 									'from_user' => $from_user,
    	 									'status' => 1,
    	 									'medal_id' => $medal_id,
    	 									'get_time'=>date("Y-m-d H:i:s")
    	 							);
    	 							if(pdo_insert('bj_qmxk_relation_medal', $data)){
    	 								$this->sendcustomMsg($from_user, "恭喜您(".$member_info['realname'].")获得（".$medal_name."）");
    	 							}
    	 						}
    	 					}
    	 				}else{
    	 					//之前没有勋章
    	 					$data = array(
    	 							'weid' => $_W['weid'],
    	 							'from_user' => $from_user,
    	 							'status' => 1,
    	 							'medal_id' => $medal_id,
    	 							'get_time'=>date("Y-m-d H:i:s")
    	 					);
    	 					if(pdo_insert('bj_qmxk_relation_medal', $data)){
    	 						$this->sendcustomMsg($from_user, "恭喜您(".$member_info['realname'].")获得（".$medal_name."）");
    	 					}
    	 					if($cfg["multi"]==2){
    	 						break;
    	 					}
    	 				}
    	 				
    	 			}
    	 			
    	 		}
    	 	}
    
    }
    /**
     * 给予勋章
     */
    public function   GiveMedal($shareid,$level){
    	global $_W;
    	$cfg = $this->module['config'];
    	//上级会员信息
    	$member_info= pdo_fetch('select * FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid  AND id=:shareid ",array(
    			':weid' => $_W['weid'],
    			':shareid' => $shareid
    	));
    	
    	//勋章信息
    	$medal_info= pdo_fetchall('select * from ' . tablename('bj_qmxk_medal') . " where weid = ". $_W['weid']."
        		and medal_status=1 order by one_num desc,one_num_sum desc");
    	
    	//获取该级的1级代理个数
    	$num_1_1=$this->CountAgent($member_info["from_user"], $shareid,1);
    	//获取该级的2级代理个数
    	$num_1_2=$this->CountAgent($member_info["from_user"], $shareid,2);
    	//获取该级的3级代理个数
    	$num_1_3=$this->CountAgent($member_info["from_user"], $shareid,3);

    	//查询已拥有的勋章信息
    	$old_have_medal= pdo_fetch('select id,medal_id  FROM ' . tablename('bj_qmxk_relation_medal') . "
    				WHERE `weid` =:weid   and from_user=:from_user and status=:status ",array(
    	    						':weid' => $_W['weid'],
    	    						':from_user'=> $member_info["from_user"],
    	    						':status'=>1
    	    				));
    	$old_medal_info=pdo_fetch('select medal_name,one_num,one_num_sum  FROM ' . tablename('bj_qmxk_medal') . "
    				WHERE `weid` =:weid  AND id=:medal_id  and medal_status=:medal_status",array(
    	    						':weid' => $_W['weid'],
    	    						':medal_id'=>$old_have_medal["medal_id"],
    	    						':medal_status'=>1
    	    				));
    	
    	for($i=0;$i<count($medal_info);$i++){
    		$one_num=$medal_info[$i]["one_num"];
    		$two_num=$medal_info[$i]["two_num"];
    		$three_num=$medal_info[$i]["three_num"];
    		$one_num_sum=$medal_info[$i]["one_num_sum"];
    		$one_two_num_sum=$medal_info[$i]["one_two_num_sum"];
    		$one_two_three_num_sum=$medal_info[$i]["one_two_three_num_sum"];
    		$medal_id=$medal_info[$i]["id"];
    		$medal_name=$medal_info[$i]["medal_name"];

    		//符合勋章条件：1. 没有勋章，新增勋章。如果串行，break
    		//    2. 有勋章。已获得勋章和本次循环可获得勋章相等，如果串行，break；
    		// 已获得勋章和本次循环可获得勋章不相等，并行，插入;  串行，如果勋章是升级，更新
    	if( strpos($medal_info[$i]["rules_type"], '2') !== false){
    			if(($num_1_1>=$one_num_sum) && (($num_1_1+$num_1_2)>=$one_two_num_sum) && (($num_1_1+$num_1_2+$num_1_3)>=$one_two_three_num_sum)){
					//判断有无勋章
    				if($old_have_medal["id"]){
    					//有勋章
    					if($medal_id ==$old_have_medal["medal_id"] ){
    						break;
    					}else{
    						if($cfg["multi"]==2){
    							if($one_num_sum>$old_medal_info["one_num_sum"]){
    							//单个存在
    							$data = array(
    									'weid' => $_W['weid'],
    									'from_user' => $member_info["from_user"],
    									'status' => 1,
    									'medal_id' => $medal_id,
    									'get_time'=>date("Y-m-d H:i:s")
    							);
    							pdo_update('bj_qmxk_relation_medal', $data,array("id"=>$old_have_medal["id"]));
    							//升级提示
    							$this->sendcustomMsg($member_info["from_user"], "恭喜您(".$member_info['realname'].")由(".$old_medal_info["medal_name"].")升级到（".$medal_name."）");
    							break;
    							}
    						}else{
    							//多个存在
    							$data = array(
    									'weid' => $_W['weid'],
    									'from_user' =>  $member_info["from_user"],
    									'status' => 1,
    									'medal_id' => $medal_id,
    									'get_time'=>date("Y-m-d H:i:s")
    							);
    							if(pdo_insert('bj_qmxk_relation_medal', $data)){
    								$this->sendcustomMsg($member_info["from_user"], "恭喜您(".$member_info['realname'].")获得（".$medal_name."）");
    							}
    						}
    					}
    				}else{
    					//无勋章
    					//之前没有勋章
    					$data = array(
    							'weid' => $_W['weid'],
    							'from_user' =>  $member_info["from_user"],
    							'status' => 1,
    							'medal_id' => $medal_id,
    							'get_time'=>date("Y-m-d H:i:s")
    					);
    					if(pdo_insert('bj_qmxk_relation_medal', $data)){
    						$this->sendcustomMsg($member_info["from_user"], "恭喜您(".$member_info['realname'].")获得（".$medal_name."）");
    					}
    					if($cfg["multi"]==2){
    						break;
    					}
    				}
    			}else{
    				continue;
    			}
    		}elseif(strpos($medal_info[$i]["rules_type"], '1') !== false){
    			if(($num_1_1>=$one_num) && ($num_1_2>=$two_num) && ($num_1_3>=$three_num)){
    				//判断有无勋章
    				if($old_have_medal["id"]){
    					//有勋章
    					if($medal_id ==$old_have_medal["medal_id"] ){
    						break;
    					}else{
    						if($cfg["multi"]==2){
    							if($one_num>$old_medal_info["one_num"]){
    							//单个存在
    							$data = array(
    									'weid' => $_W['weid'],
    									'from_user' => $member_info["from_user"],
    									'status' => 1,
    									'medal_id' => $medal_id,
    									'get_time'=>date("Y-m-d H:i:s")
    							);
    							pdo_update('bj_qmxk_relation_medal', $data,array("id"=>$old_have_medal["id"]));
    							//升级提示
    							$this->sendcustomMsg($member_info["from_user"], "恭喜您(".$member_info['realname'].")由(".$old_medal_info["medal_name"].")升级到（".$medal_name."）");
    							break;
    							}
    						}else{
    							//多个存在
    							$data = array(
    									'weid' => $_W['weid'],
    									'from_user' =>  $member_info["from_user"],
    									'status' => 1,
    									'medal_id' => $medal_id,
    									'get_time'=>date("Y-m-d H:i:s")
    							);
    							if(pdo_insert('bj_qmxk_relation_medal', $data)){
    								$this->sendcustomMsg($member_info["from_user"], "恭喜您(".$member_info['realname'].")获得（".$medal_name."）");
    							}
    						}
    					}
    				}elseif(strpos($medal_info[$i]["rules_type"], '3') !== false){
    					//无勋章
    					//之前没有勋章
    					$data = array(
    							'weid' => $_W['weid'],
    							'from_user' =>  $member_info["from_user"],
    							'status' => 1,
    							'medal_id' => $medal_id,
    							'get_time'=>date("Y-m-d H:i:s")
    					);
    					if(pdo_insert('bj_qmxk_relation_medal', $data)){
    						$this->sendcustomMsg($member_info["from_user"], "恭喜您(".$member_info['realname'].")获得（".$medal_name."）");
    					}
    					if($cfg["multi"]==2){
    						break;
    					}
    				}
    			}else{
    				continue;
    			}
    		}
    
    	}
    	return $member_info["shareid"];
    }
   
    /**
     * 取消勋章
     */
    public function   DeleteMedal($shareid,$level,$realname){
    	global $_W;
    	//会员信息
    	$member_info= pdo_fetch('select * FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid  AND id=:shareid ",array(
    			':weid' => $_W['weid'],
    			':shareid' => $shareid
    	));
    	 
    	//勋章信息
    	$medal_info= pdo_fetchall('select * from ' . tablename('bj_qmxk_medal') . " where weid = " . $_W['weid']."
        		and medal_status=1");
    	 
    	//获取该级的1级代理个数
    	$num_1_1=$this->CountAgent($member_info["from_user"], $shareid,1);
    	//获取该级的2级代理个数
    	$num_1_2=$this->CountAgent($member_info["from_user"], $shareid,2);
    	//获取该级的3级代理个数
    	$num_1_3=$this->CountAgent($member_info["from_user"], $shareid,3);
    	 
    	for($i=0;$i<count($medal_info);$i++){
    		$one_num=$medal_info[$i]["one_num"];
    		$two_num=$medal_info[$i]["two_num"];
    		$three_num=$medal_info[$i]["three_num"];
    		$one_num_sum=$medal_info[$i]["one_num_sum"];
    		$one_two_num_sum=$medal_info[$i]["one_two_num_sum"];
    		$one_two_three_num_sum=$medal_info[$i]["one_two_three_num_sum"];
    		$medal_id=$medal_info[$i]["id"];
    		$medal_name=$medal_info[$i]["medal_name"];
    		$exist= pdo_fetchcolumn('select count(*)  FROM ' . tablename('bj_qmxk_relation_medal') . "
    				WHERE `weid` =:weid  AND medal_id=:medal_id  and from_user=:from_user and status=:status",array(
        						':weid' => $_W['weid'],
        						':medal_id' => $medal_id,
        						':from_user'=>$member_info["from_user"],
        						':status'=>1
        				));
    		if($exist){
    			if($medal_info[$i]["rules_type"]==2){
    				if(($num_1_1>=$one_num_sum) && (($num_1_1+$num_1_2)>=$one_two_num_sum) && (($num_1_1+$num_1_2+$num_1_3)>=$one_two_three_num_sum)){
    					continue;
					}else{
						pdo_update('bj_qmxk_relation_medal',array("status"=>-1),array('weid' => $_W['weid'],'from_user' => $member_info["from_user"],'medal_id' => $medal_id));
						$this->sendcustomMsg($member_info["from_user"], "(".$member_info['realname'].")您好，由于您的".$level."级（".$realname."）被取消东家身份，系统将取消您的（".$medal_name."）");
						
					}
				}elseif($medal_info[$i]["rules_type"]==1){
	    			if(($num_1_1>=$one_num) && ($num_1_2>=$two_num) && ($num_1_3>=$three_num)){
	    				continue;
	    			}else{
	    				pdo_update('bj_qmxk_relation_medal',array("status"=>-1),array('weid' => $_W['weid'],'from_user' => $member_info["from_user"],'medal_id' => $medal_id));
	    				$this->sendcustomMsg($member_info["from_user"], "(".$member_info['realname'].")您好，由于您的".$level."级（".$realname."）被取消东家身份，系统将取消您的（".$medal_name."）");
					}
				}
    				
    		}else{
    			continue;
    		}
    	}
    	return $member_info["shareid"];
    }
    
    
    
    /**
     * 获取代理个数
     */
    public function CountAgent($from_user,$shareid,$level=1){
    	global $_W;
    	if($level==1){
    		$sql1_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=1 and mber1.shareid = " .$shareid;
    		$count1 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
    		return $count1;
    	}
    	if($level==2){
    		$sql1_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=1 and mber1.shareid = " .$shareid;
    	 $level2 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and  flag=1  and shareid = " . $shareid);
            $str = "";
            foreach ($level2 as & $citem) {
                $str = $str . $citem['id'] . ',';
            }
            $str = $str . '-1';
            $sql2_member = "select mber2.from_user from " . tablename('bj_qmxk_member') . " mber2 where  mber2.realname<>'' and mber2.id!=mber2.shareid  and mber2.flag=1 and mber2.shareid in (" . $str . ")  ";
            $count2 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
    		return $count2;
    	}
    	if($level==3){
    		$sql1_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=1 and mber1.shareid = " .$shareid;
    		$level2 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and  flag=1  and shareid = " .$shareid);
    		$rowindex = 0;
    		$str = "";
    		foreach ($level2 as & $citem) {
    			$str = $str . $citem['id'] . ',';
    		}
    		$str = $str . '-1';
    		$sql2_member = "select mber2.from_user from " . tablename('bj_qmxk_member') . " mber2 where  mber2.realname<>'' and mber2.id!=mber2.shareid  and mber2.flag=1 and mber2.shareid in (" . $str . ")  ";
    	  $level3 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and flag=1  and shareid in( " . $str . ")");
            $rowindex = 0;
            $str3 = "";
            foreach ($level3 as & $citem) {
                $str3 = $str3 . $citem['id'] . ',';
            }
            $str3 = $str3 . '-1';
            $sql3_member = "select mber3.from_user from " . tablename('bj_qmxk_member') . " mber3 where  mber3.realname<>'' and mber3.id!=mber3.shareid and mber3.flag=1 and mber3.shareid in (" . $str3 . ")  ";
            $count3 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
    		return $count3;
    	}
    }
    
    /**
     *  获取拥有的勋章
     */
    public function getMedal($from_user){
    	global $_W;
    	$medalList = pdo_fetchall("select * from " . tablename('bj_qmxk_relation_medal') ." as m_r
    			join  " . tablename('bj_qmxk_medal') ." as m on  m_r.medal_id = m.id
    			WHERE m_r.`weid` = ".$_W['weid']." and m.weid= ".$_W['weid']." and m_r.from_user='".$from_user."'
    			 and m_r.status=1 and m.medal_status=1"
    	);
    	return $medalList;
    }
    
    
	//成功消费累计达到16800元，有10个直1，可以成为经销商   2015/06/16
    public function getOrderNum($id){
	
        global $_W;
        $orderlist = pdo_fetch("select * from " . tablename('bj_qmxk_order') ."WHERE `weid` = :weid and id=:id",array(
            ':weid' => $_W['weid'],
            ':id' => $id
        ));
     
		$profile = pdo_fetch('select * FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND from_user=:from_user ",array(
			':weid' => $_W['weid'],
			':from_user' => $orderlist[from_user]
        ));
		$flag=$this->checkisAgent($orderlist[from_user],$profile);
// 		if($profile[issalesman]==1){
			
// 		}else{
// 			//成功消费累计达到16800元
// 			$date = pdo_fetchcolumn('select sum(price) FROM ' . tablename('bj_qmxk_order') . " WHERE `weid` = :weid AND from_user=:from_user  and status =3",array(
// 				':weid' => $_W['weid'],
// 				':from_user' => $orderlist[from_user]
// 			));
			
// 			$res = pdo_fetch('select * from ' . tablename('bj_qmxk_salesman') . " where weid = " . $_W['weid']);
			
// 			//判断此直1的分享者是否有10个直1
			
// 			$date2 = pdo_fetchcolumn('select count(*) FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND shareid=:shareid AND flag=1 ",array(
// 				':weid' => $_W['weid'],
// 				':shareid' => $profile[id]
// 			));
// 			if($date2>=$res[member]&&$date>=$res[money]){
// 				pdo_update('bj_qmxk_member',array(
// 					'issalesman' => 1,
// 					'salesmanbz' =>'满足条件',
// 					'salesmantime' => time()
// 				),array(
// 					'from_user' => $orderlist[from_user]
// 				));
	
// 				$msg = "您的直一消费圈达到了".$res[member]."个，消费累计达到".$res[money]."元，已成为总代";
// 				$this->sendcustomMsg($orderlist[from_user],$msg);
// 			}
//         }
    }
    public function getMid() {
        global $_W, $_GPC;
        if (empty($_COOKIE['mid_' . BJ_QMXK_VERSION . $_W['weid']])) {
            $profile = $this->getProfile();
            setcookie('mid_' . BJ_QMXK_VERSION . $_W['weid'], $profile['id'], time() + 3600);
            return $profile['id'];
        } else {
            return $_COOKIE['mid_' . BJ_QMXK_VERSION . $_W['weid']];
        }
    }
    public function getMember($mid) {
        global $_W, $_GPC;
        $profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND id = :id", array(
            ':weid' => $_W['weid'],
            ':id' => $mid
        ));
        return $profile;
    }
    /**
     * 获取所有上级
     * @param string $from_user
     * @return Ambigous <mixed, boolean>
     */
    public function  get_shareid($mid,&$shareid_arr=array()){
    	//$shareid_arr=array();
    	$member_info = "select mber1.shareid from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid  and mber1.id='".$mid."'";
    	$shareid=pdo_fetchcolumn($member_info);
    	if($shareid){
    		array_push($shareid_arr, $shareid);
    		//当有上级存在的时候
    		$this->get_shareid($shareid,$shareid_arr);
    	}
    	return $shareid_arr;
    }
    public function getProfile($from_user = '') {
        global $_W, $_GPC;
        if (empty($from_user)) {
            $from_user = $this->getFromUser();
        }
        $profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND from_user = :from_user", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        if (!empty($profile['id']) && empty($_COOKIE['mid_' . BJ_QMXK_VERSION . $_W['weid']])) {
            setcookie('mid_' . BJ_QMXK_VERSION . $_W['weid'], $profile['id'], time() + 3600);
        }
        if ($profile['flag'] == 1 && ($profile['flagtime'] == 0 || empty($profile['flagtime'])) && !empty($profile['id'])) {
            pdo_update('bj_qmxk_member', array(
                'flagtime' => TIMESTAMP
            ) , array(
                'id' => $profile['id']
            ));
        }
        return $profile;
    }
    public function getParentNickName($from_user) {
        global $_W;
        $myfansx = pdo_fetch('SELECT shareid FROM ' . tablename('bj_qmxk_member') . " WHERE weid = :weid and from_user =:from_user  limit 1", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        if (!empty($myfansx['shareid']) && $myfansx['shareid'] != 0) {
            $myfansx = pdo_fetch('SELECT realname FROM ' . tablename('bj_qmxk_member') . " WHERE weid = :weid and id =:shareid  limit 1", array(
                ':weid' => $_W['weid'],
                ':shareid' => $myfansx['shareid']
            ));
            return $myfansx['realname'];
        } else {
            return '-';
        }
    }
    public function getLevel($fanscount) {
        global $_W;
        $myfansx = pdo_fetch('SELECT medal_name FROM ' . tablename('bj_qmxk_phb_medal') . " WHERE weid = :weid and  fans_count<=:fans_count order by fans_count desc  limit 1", array(
            ':weid' => $_W['weid'],
            ':fans_count' => $fanscount
        ));
        if (!empty($myfansx['medal_name'])) {
            return $myfansx['medal_name'];
        } else {
            return $this->module['config']['noagent_title'];
        }
    }
    /**
     * 注册分销会员
     * @param unknown $fromaction
     * @param unknown $from_user_children   (针对添加子账号。手动生成的$from_user)
     * @return boolean
     */
    public function autoRegedit($fromaction,$from_user_children=null,$main_openid=null) {
        if (BAIJIA_DEVELOPMENT) {
            return true;
        }
        global $_W, $_GPC;
        
        //$openid 代表 微信openId
        $openid = $this->getFromUser();
        // from_user 代表用户账号标识,如果没有子账号，则该标识即为微信openid； 否则为子账号的自身标识
        $from_user = empty($from_user_children)?$openid:$from_user_children;
        
       
        $myfansx = pdo_fetch('SELECT id,from_user,nickname,follow,is_children FROM ' . tablename('fans') . " WHERE `weid` = :weid AND from_user=:from_user limit 1", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        $seid = 0;
        $shareids = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_share_history') . " WHERE  from_user=:from_user and weid=:weid limit 1", array(
            ':from_user' => $from_user,
            ':weid' => $_W['weid']
        ));
        if($myfansx["is_children"]){
        	$child_share_info=$this->getMember($shareids["sharemid"]);
        	$openid=$child_share_info["openid"];
        }
        $nickname = $myfansx['nickname'];
        if (empty($nickname)) {
            $access_token = $this->get_weixin_token();
            $oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
            $content = ihttp_get($oauth2_url);
            $info = @json_decode($content['content'], true);
            $nickname = $info['nickname'];
            if (!empty($nickname)) {
                pdo_update('fans', array(
                    'nickname' => $nickname
                ) , array(
                    'id' => $myfansx['id']
                ));
            }
            if (!empty($info["headimgurl"])) {
                pdo_update('fans', array(
                    'avatar' => $info["headimgurl"]
                ) , array(
                    'id' => $myfansx['id']
                ));
            }
        }
        $profile = pdo_fetch('SELECT from_user,id,realname FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND from_user=:from_user ", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));

        if (empty($profile['id'])) {
		//当会员信息不存在时
            if (!empty($shareids['sharemid'])) {
                $seid = $shareids['sharemid'];
  		$sharemember = pdo_fetch('SELECT from_user,id,flag FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND id=:id ", array(
                    ':weid' => $_W['weid'],
                    ':id' => $seid
                ));
                  //判断分享人是否为代理（只有代理才能建立上下级关系）
                  if($sharemember["flag"]==0){
                  	$seid = 0;
                  }
            } else {
                $seid = 0;
            }
            $theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
                ':weid' => $_W['weid']
            ));
		//关注即成为代理
            if ($theone['promotertimes'] == 1) {
                $data = array(
                    'weid' => $_W['weid'],
                    'from_user' => $from_user,
                    'realname' => $nickname,
                    'commission' => 0,
                    'createtime' => TIMESTAMP,
                    'flagtime' => TIMESTAMP,
                    'shareid' => $seid,
                    'status' => 1,
                    'flag' => 1,
                		'openid'=>$openid
                );
            } else {
                $data = array(
                    'weid' => $_W['weid'],
                    'from_user' => $from_user,
                    'realname' => $nickname,
                    'commission' => 0,
                    'createtime' => TIMESTAMP,
                    'flagtime' => TIMESTAMP,
                    'shareid' => $seid,
                    'status' => 1,
                    'flag' => 0,
                		'openid'=>$openid
                );
            }

            pdo_insert('bj_qmxk_member', $data);
			$shareinfo = pdo_fetch('select * FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = ".$_W['weid']." AND id=".$seid );
			
				if($shareinfo['issalesman']==1){

				}else{

					//判断此直1的分享者是否有10个直1，达到10个，可成为待审核经销商  2015/05/08
					$date = pdo_fetchcolumn('select count(*) FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND shareid=:shareid AND flag=1 ",array(
						':weid' => $_W['weid'],
						':shareid' => $seid
					));
					//成功消费累计达到16800元
						
					$date2 = pdo_fetchcolumn('select sum(price) FROM ' . tablename('bj_qmxk_order') . " WHERE `weid` = :weid AND from_user=:from_user  and status =3",array(
						':weid' => $_W['weid'],
						':from_user' => $shareinfo[from_user]
					));
						
					$res = pdo_fetch('select * from ' . tablename('bj_qmxk_salesman') . " where weid = " . $_W['weid']);
					if($date>=$res[member]&&$date2>=$res[money]){
						pdo_update('bj_qmxk_member',array(
							'issalesman' => 1,
							'salesmanbz' =>'满足条件',
							'salesmantime' => time()
						),array(
							id => $seid
						));
						$msg = "您直一消费圈达到了".$res[member]."个，成功消费累计达到16800元，恭喜您已成为总代";
						$this->sendcustomMsg($shareinfo[from_user],$msg);
					}
				}
            if (!empty($seid) && $seid != 0 && $theone['promotertimes'] == 1) {
               
                $joinfans = pdo_fetch('SELECT from_user,nickname FROM ' . tablename('fans') . " WHERE `weid` = :weid AND from_user=:from_user limit 1", array(
                    ':weid' => $_W['weid'],
                    ':from_user' => $from_user
                ));
                if (!empty($sharemember) && !empty($sharemember['id']) && !empty($joinfans['nickname'])) {
                    $this->sendtjrtzdl($joinfans['nickname'], $sharemember['from_user']);
                }
				
            }
        } else {
	//当会员信息存在时
            if (empty($profile['realname'])) {
                if (!empty($nickname)) {
                    $data = array(
                        'realname' => $nickname
                    );
                    pdo_update('bj_qmxk_member', $data, array(
                        'id' => $profile['id']
                    ));
                }
            } else {
                $nickname = $profile['realname'];
            }
        }
        $profile = $this->getProfile();
        $this->checkisAgent($from_user, $profile);
        if (empty($nickname)) {
            $cfg = $this->module['config'];
            $ydyy = $cfg['ydyy'];
            include $this->template('register');
            exit;
        }
    }
    /**
     * 新版生成推广二维码
     */
    public function memberQrcodeNew($from_user) {
    	global $_W, $_GPC;
    	$account = $_W['account'];
    	$express = pdo_fetch("select * from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 and isdel=0 limit 1", array(
    			':weid' => $_W['weid']
    	));
    	$profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE weid = :weid  AND from_user = :from_user AND flag = 1", array(
    			':weid' =>$_W['weid'],
    			':from_user' => $from_user
    	));
    	if (empty($profile['id'])) {
    		$regurl = $_W['siteroot'] . "mobile.php?act=module&name=bj_qmxk&do=fansIndex&weid=" . $_W['weid'];
    		message('亲！请您先注册成为会员!',$regurl);
    	//	return $this->respText("请先注册成代理！ <a href='" . $regurl . "'>马上注册成为分销员</a>");
    	}
    	if (!empty($express['channel'])) {
    		$homeurl = $_W['siteroot'] . "mobile.php?act=module&name=bj_qmxk&do=list&weid=" . $_W['weid'];
    		$follow = pdo_fetch("select * from " . tablename('bj_qmxk_follow') . " WHERE weid=:weid and follower=:from_user  limit 1", array(
    				':weid' => $_W['weid'],
    				':from_user' => $from_user
    		));
    		if (!empty($follow['follower'])) {
    			if ($follow['createtime'] > TIMESTAMP) {
    				exit;
    			} else {
    				pdo_update('bj_qmxk_follow', array(
    				'createtime' => TIMESTAMP + (3)
    				) , array(
    				'weid' => $_W['weid'],
    				'follower' => $from_user
    				));
    			}
    		} else {
    			$insert = array(
    					'weid' => $_W['weid'],
    					'follower' => $from_user,
    					'leader' => $from_user,
    					'channel' => '',
    					'credit' => 0,
    					'createtime' => TIMESTAMP + (3)
    			);
    			pdo_insert('bj_qmxk_follow', $insert);
    		}
    		$qmjf_qr = pdo_fetch("select * from " . tablename('bj_qmxk_qr') . " WHERE weid=:weid and from_user=:from_user and channel=:channel  limit 1", array(
    				':weid' => $_W['weid'],
    				':from_user' => $from_user,
    				':channel' => $express['channel']
    		));
    		if ((empty($qmjf_qr['id']) || empty($qmjf_qr['qr_url']) || empty($qmjf_qr['media_id']) || empty($qmjf_qr['id'])) || (!empty($qmjf_qr['expiretime']) && $qmjf_qr['expiretime'] < TIMESTAMP)) {
				//重新生成
    			pdo_update('bj_qmxk_follow', array(
    			'createtime' => TIMESTAMP + (6)
    			) , array(
    			'weid' => $_W['weid'],
    			'follower' => $from_user
    			));
    			if (!empty($express['notice'])) {
    				$this->sendcustomMsg( $from_user, $express['notice']);
    			}
    			if (empty($qmjf_qr['id'])) {
    				$insert = array(
    						'weid' => $_W['weid'],
    						'from_user' => $from_user,
    						'channel' => $express['channel'],
    						'qr_url' => '',
    						'media_id' => 0,
    						'expiretime' => TIMESTAMP + (60 * 24 * 6) ,
    						'createtime' => TIMESTAMP
    				);
    				pdo_insert('bj_qmxk_qr', $insert);
    			}
    			$tqmjf_qr = pdo_fetch("select * from " . tablename('bj_qmxk_qr') . " WHERE weid=:weid and from_user=:from_user and channel=:channel limit 1", array(
    					':weid' => $_W['weid'],
    					':from_user' => $from_user,
    					':channel' => $express['channel']
    			));
    			$newid = $tqmjf_qr['id'];
    			//选择是跳转到商城首页（引导素材）还是关注界面
    			if ($express['msgtype'] == 2) {
    				//关注界面
    				$qrfile = $this->getLimitQR($account, $newid, $from_user);
    			} else {
    				if (!empty($profile['id'])) {
    					$qrfile = $this->getURLQR($profile['id'], $from_user, $homeurl);
    				} else {
    					$qrfile = $this->getURLQR(0, $from_user, $homeurl);
    				}
    			}
    			for ($a = 0; $a < 3; $a++) {
    				$qrpic = $this->genImage($express['bg'], $qrfile, $_W['weid'], $from_user);
    				if (!empty($qrpic)) {
    					$a = 4;
    				}
    			}
    			$media_id = $this->uploadImage($account, IA_ROOT . $qrpic);
    			$content = @json_decode($media_id, true);
    			pdo_update('bj_qmxk_qr', array(
    			'expiretime' => TIMESTAMP + (86400 * 2) ,
    			'media_id' => $content['media_id'],
    			'qr_url' => $qrpic
    			) , array(
    			'id' => $newid
    			));
    			$showErWeiMaUrl=$this->createMobileUrl('QrCode',array('mid'=>$profile['id'],"media_id"=>$content['media_id']));
    			header("location:$showErWeiMaUrl");
    			//$this->sendcustomIMG($account, $from_user, $content['media_id']);
    			//exit();
    		} else {
    			$showErWeiMaUrl=$this->createMobileUrl('QrCode',array('mid'=>$profile['id'],"media_id"=>$qmjf_qr['media_id']));
    			header("location:$showErWeiMaUrl");
    			///return $this->respImage($qmjf_qr['media_id']);
    		}
    	} else {
    		message('商家未设置二维码生成');
    		//return $this->respText('商家未设置二维码生成');
    	}
    }
    
    /*----------------新版生成推广二维码附属函数开始-------------------*/
    public function getLimitQR($account, $scene_id, $from_user)
    {
    	global $_W;
    	$qr_url = null;
    	$data = array('action_name' => 'QR_LIMIT_SCENE', 'action_info' => array('scene' => array('scene_id' => $scene_id)));
    	$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$this->get_weixin_token($account)}";
    	$ret = $this->curlPost($url, json_encode($data));
    	$content = @json_decode($ret, true);
    	$qr_url = $this->getQRImage($content['ticket']);
    	$insert = array(
    			'weid' => $_W['weid'],
    			'qrcid' => $scene_id,
    			'name' => $from_user,
    			'keyword' => $scene_id,
    			'ticket' => $content['ticket'],
    			'model' => 2,
    			'expire' => 0,
    			'createtime' => TIMESTAMP,
    			'status' => '1',
    	);
    	pdo_insert('qrcode', $insert);
    	file_put_contents(IA_ROOT . BJ_QMXK_BASE . "/common/text.txt", var_export($content,TRUE),FILE_APPEND);
    	return $qr_url;
    }
    public function getQRImage($ticket)
    {
    	$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
    	return $url;
    }
    public function uploadImage($account, $img) {
    	return $this->uploadRes($this->get_weixin_token($account) , $img, 'image');
    }
    public function getURLQR($mid, $from_user, $homeurl) {
    	include IA_ROOT . BJ_QMXK_BASE . "/common/phpqrcode.php";
    	$value = $homeurl . '&mid=' . $mid;
    	$errorCorrectionLevel = "L";
    	$matrixPointSize = "4";
    	$rand_file = $from_user . '.png';
    	$att_target_file = 'qr-' . $rand_file;
    	$target_file = IA_ROOT . BJ_QMXK_BASE . '/tmppic/' . $att_target_file;
    	QRcode::png($value, $target_file, $errorCorrectionLevel, $matrixPointSize);
    	return $target_file;
    }
    public function sendcustomIMG($account, $from_user, $msg) {
    	$access_token = $this->get_weixin_token($account);
    	$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
    	$post = '{"touser":"' . $from_user . '","msgtype":"image","image":{"media_id":"' . $msg . '"}}';
    	$this->curlPost($url, $post);
    }
    public function uploadRes($access_token, $img, $type) {
    	$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
    	$post = array(
    			'media' => '@' . $img
    	);
    	$ret = $this->curlPost($url, $post);
    	return $ret;
    }
    private function genImage($bg, $qr_file, $weid, $from_user) {
    	$express = pdo_fetch("select * from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 limit 1", array(
    			':weid' => $weid
    	));
    	if (!empty($express['channel'])) {
    		$rand_file = $from_user . '.jpg';
    		$att_target_file = 'qr-image-' . $rand_file;
    		$att_head_cache_file = 'head-image-' . $rand_file;
    		$target_file = IA_ROOT . BJ_QMXK_BASE . '/tmppic/' . $att_target_file;
    		$head_cache_file = IA_ROOT . BJ_QMXK_BASE . '/tmppic/' . $att_head_cache_file;
    		$bg_file = IA_ROOT . "/resource/attachment/" . $bg;
    		$ch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_channel') . " WHERE channel=:channel", array(
    				":channel" => $express['channel']
    		));
    		$ch = $this->decode_channel_param($ch, $ch['bgparam']);
    		$this->mergeImage($bg_file, $qr_file, $target_file, array(
    				'left' => $ch['qrleft'],
    				'top' => $ch['qrtop'],
    				'width' => $ch['qrwidth'],
    				'height' => $ch['qrheight']
    		));
    		$enableHead = $ch['avatarenable'];
    		$enableName = $ch['nameenable'];
    		$myheadimg = pdo_fetch('SELECT avatar FROM ' . tablename('fans') . " WHERE  weid=:weid and from_user = :from_user LIMIT 1", array(
    				':weid' => $weid,
    				':from_user' => $from_user
    		));
    		$fans = fans_search($from_user, array(
    				'nickname',
    				'avatar'
    		));
    		if (!empty($fans)) {
    			if ($enableName) {
    				if (strlen($fans['nickname']) > 0) {
    					$this->writeText($target_file, $target_file, '我是' . $fans['nickname'], array(
    							'size' => $ch['namesize'],
    							'left' => $ch['nameleft'],
    							'top' => $ch['nametop']
    					));
    				}
    			}
    			if ($enableHead) {
    				if (strlen($fans['avatar']) > 10) {
    					$head_file = $myheadimg['avatar'];
    					$bild = $head_cache_file;
    					$url = $this->curl_file_get_contents($head_file);
    					$fp = fopen($bild, 'w');
    					$ws = fwrite($fp, $url);
    					fclose($fp);
    					if ($ws == false || $ws == 0 || empty($ws)) {
    						return '';
    					}
    					$this->mergeImage($target_file, $head_cache_file, $target_file, array(
    							'left' => $ch['avatarleft'],
    							'top' => $ch['avatartop'],
    							'width' => $ch['avatarwidth'],
    							'height' => $ch['avatarheight']
    					));
    				}
    			}
    		}
    		return BJ_QMXK_BASE . '/tmppic/' . $att_target_file;
    	}
    	return '';
    }
    /*==========新版生成推广二维码附属函数结束===========*/
    
    /**
     * 生成推广二维码
     * @param unknown $from_user
     */
    public function memberQrcode($from_user) {
        global $_W;
        $share = BAIJIA_COOKIE_QRCODE . $_W['weid'];
        $timex = pdo_fetchcolumn("select createtime from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 and isdel=0 limit 1", array(
            ':weid' => $_W['weid']
        ));
       // $id = $this->getMid();
       $id=pdo_fetchcolumn("select id from " . tablename('bj_qmxk_member') . " where from_user='".$from_user."' and weid= ".$_W['weid']);
        if (($_COOKIE[$share . $timex] != $_W['weid'] . "share" . $id) || !file_exists(IA_ROOT . BJ_QMXK_BASE . "/style/images/share/share" . $id . ".png")) {
            include IA_ROOT . BJ_QMXK_BASE . "/common/phpqrcode.php";
            $value = $_W['siteroot'] . $this->createMobileUrl('list', array(
                'mid' => $id,
                'weid' => $_W['weid'],
                'joinway' => 1
            ));
            $errorCorrectionLevel = "L";
            $matrixPointSize = "4";
            $imgname_qrx = "share_qrx$id.png";
           $imgurl_qrx = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname_qrx";
            QRcode::png($value, $imgurl_qrx, $errorCorrectionLevel, $matrixPointSize);
            $express = pdo_fetch("select * from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 and isdel=0 limit 1", array(
                ':weid' => $_W['weid']
            ));
            $imgname = "share$id.png";
            $imgurl = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname";
            if (!empty($express['channel'])) {
                $rand_file = $from_user . '.png';
                $att_target_file = 'qr-image-' . $rand_file;
                $att_head_cache_file = 'head-image-' . $rand_file;
                $target_file = $imgurl;
                $head_cache_file = IA_ROOT . BJ_QMXK_BASE . '/style/images/share/' . $att_head_cache_file;
                $bg_file = IA_ROOT . "/resource/attachment/" . $express['bg'];
                $ch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_channel') . " WHERE channel=:channel", array(
                    ":channel" => $express['channel']
                ));
                $ch = $this->decode_channel_param($ch, $ch['bgparam']);
                $this->mergeImage($bg_file, $imgurl_qrx, $target_file, array(
                    'left' => $ch['qrleft'],
                    'top' => $ch['qrtop'],
                    'width' => $ch['qrwidth'],
                    'height' => $ch['qrheight']
                ));
                $enableHead = $ch['avatarenable'];
                $enableName = $ch['nameenable'];
                $myheadimg = pdo_fetch('SELECT avatar FROM ' . tablename('fans') . " WHERE  weid=:weid and from_user = :from_user LIMIT 1", array(
                    ':weid' => $_W['weid'],
                    ':from_user' => $from_user
                ));
                $fans = fans_search($from_user, array(
                    'nickname',
                    'avatar'
                ));
                $needcache = true;
                if (!empty($fans)) {
                    if ($enableName) {
                        if (strlen($fans['nickname']) > 0) {
                            $this->writeText($target_file, $target_file, '我是' . $fans['nickname'], array(
                                'size' => $ch['namesize'],
                                'left' => $ch['nameleft'],
                                'top' => $ch['nametop']
                            ));
                        }
                    }
                    if ($enableHead) {
                        if (strlen($fans['avatar']) > 10) {
                            $head_file = $myheadimg['avatar'];
                            $bild = $head_cache_file;
                            $ws = false;
                            for ($a = 0; $a < 3; $a++) {
                                $url = $this->curl_file_get_contents($head_file);
                                $fp = fopen($bild, 'w');
                                $ws = fwrite($fp, $url);
                                fclose($fp);
                                if (!empty($ws) && $ws != false) {
                                    $a = 4;
                                }
                            }
                            $this->mergeImage($target_file, $bild, $target_file, array(
                                'left' => $ch['avatarleft'],
                                'top' => $ch['avatartop'],
                                'width' => $ch['avatarwidth'],
                                'height' => $ch['avatarheight']
                            ));
                        }
                    }
                }
            } else {
                $imgname = "share$id.png";
                $imgurl = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname";
                QRcode::png($value, $imgurl, $errorCorrectionLevel, $matrixPointSize);
            }
            setCookie($share . $timex, $_W['weid'] . "share" . $id, time() + 3600 * 24);
        }
    }
	/**
	 * 生成推广商品二维码
	 * @param string $from_user
	 * @param string $goodsid
	 */
	public function memberQrcode1($from_user, $goodsid) {
        global $_W;
        $share = BAIJIA_COOKIE_QRCODE . $_W['weid'] . $goodsid;
        $timex = pdo_fetchcolumn("select createtime from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 and isdel=0 limit 1", array(
            ':weid' => $_W['weid']
        ));
        //$id = $this->getMid();
        $id=pdo_fetchcolumn("select id from " . tablename('bj_qmxk_member') . " where from_user='".$from_user."' and weid= ".$_W['weid']);
        if (($_COOKIE[$share . $timex] != $_W['weid'] . "share" . $id . $goodsid) || !file_exists(IA_ROOT . BJ_QMXK_BASE . "/style/images/share/".$id."share_qrx".$goodsid.".png")) {
            include IA_ROOT . BJ_QMXK_BASE . "/common/phpqrcode.php";
            $value = $_W['siteroot'] . $this->createMobileUrl('detail', array(
                'mid' => $id,
                'id' => $goodsid,
                'weid' => $_W['weid'],
                'joinway' => 1
            ));
            $errorCorrectionLevel = "L";
            $matrixPointSize = "4";
            $imgname_qrx = $id."share_qrx$goodsid.png";
            $imgurl_qrx = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname_qrx";
            QRcode::png($value, $imgurl_qrx, $errorCorrectionLevel, $matrixPointSize);
            $express = pdo_fetch("select * from " . tablename('bj_qmxk_channel') . " WHERE weid=:weid and active=1 and isdel=0 limit 1", array(
                ':weid' => $_W['weid']
            ));
            $imgname = "share$id.png";
            $imgurl = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname";
//             if (!empty($express['channel'])) {
//                 $rand_file = $from_user . '.png';
//                 $att_target_file = 'qr-image-' . $rand_file;
//                 $att_head_cache_file = 'head-image-' . $rand_file;
//                 $target_file = $imgurl;
//                 $head_cache_file = IA_ROOT . BJ_QMXK_BASE . '/style/images/share/' . $att_head_cache_file;
//                 $bg_file = IA_ROOT . "/resource/attachment/" . $express['bg'];
//                 $ch = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_channel') . " WHERE channel=:channel", array(
//                     ":channel" => $express['channel']
//                 ));
//                 $ch = $this->decode_channel_param($ch, $ch['bgparam']);
//                 $this->mergeImage($bg_file, $imgurl_qrx, $target_file, array(
//                     'left' => $ch['qrleft'],
//                     'top' => $ch['qrtop'],
//                     'width' => $ch['qrwidth'],
//                     'height' => $ch['qrheight']
//                 ));
//                 $enableHead = $ch['avatarenable'];
//                 $enableName = $ch['nameenable'];
//                 $myheadimg = pdo_fetch('SELECT avatar FROM ' . tablename('fans') . " WHERE  weid=:weid and from_user = :from_user LIMIT 1", array(
//                     ':weid' => $_W['weid'],
//                     ':from_user' => $from_user
//                 ));
//                 $fans = fans_search($from_user, array(
//                     'nickname',
//                     'avatar'
//                 ));
//                 $needcache = true;
//                 if (!empty($fans)) {
//                     if ($enableName) {
//                         if (strlen($fans['nickname']) > 0) {
//                             $this->writeText($target_file, $target_file, '我是' . $fans['nickname'], array(
//                                 'size' => $ch['namesize'],
//                                 'left' => $ch['nameleft'],
//                                 'top' => $ch['nametop']
//                             ));
//                         }
//                     }
//                     if ($enableHead) {
//                         if (strlen($fans['avatar']) > 10) {
//                             $head_file = $myheadimg['avatar'];
//                             $bild = $head_cache_file;
//                             $ws = false;
//                             for ($a = 0; $a < 3; $a++) {
//                                 $url = $this->curl_file_get_contents($head_file);
//                                 $fp = fopen($bild, 'w');
//                                 $ws = fwrite($fp, $url);
//                                 fclose($fp);
//                                 if (!empty($ws) && $ws != false) {
//                                     $a = 4;
//                                 }
//                             }
//                             $this->mergeImage($target_file, $bild, $target_file, array(
//                                 'left' => $ch['avatarleft'],
//                                 'top' => $ch['avatartop'],
//                                 'width' => $ch['avatarwidth'],
//                                 'height' => $ch['avatarheight']
//                             ));
//                         }
//                     }
//                 }
//             } else {
//                 $imgname = "share$id.png";
//                 $imgurl = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname";
//                 QRcode::png($value, $imgurl, $errorCorrectionLevel, $matrixPointSize);
//             }
            setCookie($share . $timex, $_W['weid'] . "share" . $id . $goodsid, time() + 3600 * 24);
        }
    }
	
    public function encode_channel_param($gpc) {
        $params = array(
            'qrleft' => intval($gpc['qrleft']) ,
            'qrtop' => intval($gpc['qrtop']) ,
            'qrwidth' => intval($gpc['qrwidth']) ,
            'qrheight' => intval($gpc['qrheight']) ,
            'avatarleft' => intval($gpc['avatarleft']) ,
            'avatartop' => intval($gpc['avatartop']) ,
            'avatarwidth' => intval($gpc['avatarwidth']) ,
            'avatarheight' => intval($gpc['avatarheight']) ,
            'avatarenable' => intval($gpc['avatarenable']) ,
            'nameleft' => intval($gpc['nameleft']) ,
            'nametop' => intval($gpc['nametop']) ,
            'namesize' => intval($gpc['namesize']) ,
            'namecolor' => intval($gpc['namecolor']) ,
            'nameenable' => intval($gpc['nameenable'])
        );
        return serialize($params);
    }
    public function writeText($bg, $out, $text, $param = array()) {
        list($bgWidth, $bgHeight) = getimagesize($bg);
        extract($param);
        $im = imagecreatefromjpeg($bg);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = IA_ROOT . '/source/modules/bj_qmxk/font/msyhbd.ttf';
        $white = imagecolorallocate($im, 255, 255, 255);
        imagettftext($im, $size, 0, $left, $top + $size / 2, $white, $font, $text);
        ob_start();
        imagejpeg($im, NULL, 100);
        $contents = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);
        $fh = fopen($out, "w+");
        fwrite($fh, $contents);
        fclose($fh);
    }
    public function curl_file_get_contents($durl) {
        $r = null;
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $durl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $r = curl_exec($ch);
            curl_close($ch);
        }
        return $r;
    }
    public function decode_channel_param($item, $p) {
        $gpc = unserialize($p);
        $item['qrleft'] = intval($gpc['qrleft']) ? intval($gpc['qrleft']) : 145;
        $item['qrtop'] = intval($gpc['qrtop']) ? intval($gpc['qrtop']) : 475;
        $item['qrwidth'] = intval($gpc['qrwidth']) ? intval($gpc['qrwidth']) : 240;
        $item['qrheight'] = intval($gpc['qrheight']) ? intval($gpc['qrheight']) : 240;
        $item['avatarleft'] = intval($gpc['avatarleft']) ? intval($gpc['avatarleft']) : 111;
        $item['avatartop'] = intval($gpc['avatartop']) ? intval($gpc['avatartop']) : 10;
        $item['avatarwidth'] = intval($gpc['avatarwidth']) ? intval($gpc['avatarwidth']) : 86;
        $item['avatarheight'] = intval($gpc['avatarheight']) ? intval($gpc['avatarheight']) : 86;
        $item['avatarenable'] = intval($gpc['avatarenable']);
        $item['nameleft'] = intval($gpc['nameleft']) ? intval($gpc['nameleft']) : 210;
        $item['nametop'] = intval($gpc['nametop']) ? intval($gpc['nametop']) : 28;
        $item['namesize'] = intval($gpc['namesize']) ? intval($gpc['namesize']) : 30;
        $item['namecolor'] = $gpc['namecolor'];
        $item['nameenable'] = intval($gpc['nameenable']);
        return $item;
    }
    public function mergeImage($bg, $qr, $out, $param) {
        list($bgWidth, $bgHeight) = getimagesize($bg);
        list($qrWidth, $qrHeight) = getimagesize($qr);
        extract($param);
        $bgImg = $this->imagecreate($bg);
        $qrImg = $this->imagecreate($qr);
        imagecopyresized($bgImg, $qrImg, $left, $top, 0, 0, $width, $height, $qrWidth, $qrHeight);
        ob_start();
        imagejpeg($bgImg, NULL, 100);
        $contents = ob_get_contents();
        ob_end_clean();
        imagedestroy($bgImg);
        imagedestroy($qrImg);
        $fh = fopen($out, "w+");
        fwrite($fh, $contents);
        fclose($fh);
    }
    public function imagecreate($bg) {
        $bgImg = @imagecreatefromjpeg($bg);
        if (FALSE == $bgImg) {
            $bgImg = @imagecreatefrompng($bg);
        }
        if (FALSE == $bgImg) {
            $bgImg = @imagecreatefromgif($bg);
        }
        return $bgImg;
    }
    /**
     * 添加粉丝
     */
    public function doMobileXoauth() {
        global $_W, $_GPC;
        $weid = $_W['weid'];
        if ($_GPC['code'] == "authdeny") {
            exit();
        }
        if (isset($_GPC['code'])) {
            $appid = $_W['account']['key'];
            $secret = $_W['account']['secret'];
            if (empty($appid) || empty($secret)) {
                if (BAIJIA_AGENT_ALL == true) {
                    $appid = BAIJIA_APPID;
                    $secret = BAIJIA_SECRET;
                }
            }
            if (empty($appid) || empty($secret)) {
                message('微信公众号没有配置公众号AppId和公众号AppSecret!');
            }
            $state = $_GPC['state'];
            $code = $_GPC['code'];
            $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $secret . "&code=" . $code . "&grant_type=authorization_code";
            $content = ihttp_get($oauth2_code);
            $token = @json_decode($content['content'], true);
            if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
                echo '<h1>获取微信公众号授权' . $code . '失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'] . '<h1>';
                exit;
            }
            $from_user = $token['openid'];
            $access_token = $this->get_weixin_token();
            $oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $from_user . "&lang=zh_CN";
            $content = ihttp_get($oauth2_url);
            $info = @json_decode($content['content'], true);
            if ($info['subscribe'] == 1) {
                $follow = 1;
            } else {
                $follow = 0;
            }
            $fans = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE from_user=:from_user and weid=:weid", array(
                ':from_user' => $from_user,
                ':weid' => $_W['weid']
            ));
            $nickname = $info["nickname"];
            if (empty($fans) || empty($fans['id']) || empty($fans["nickname"])) {
                if ($follow == 0 && $state == 0) {
                    $this->getFromUser(1);
                    return;
                }
                if ($follow == 0 && $state == 1) {
                    $access_token = $token['access_token'];
                    $oauth2_url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $from_user . "&lang=zh_CN";
                    $content = ihttp_get($oauth2_url);
                    $info = @json_decode($content['content'], true);
                }
                if (empty($info) || !is_array($info) || empty($info['openid'])) {
                    echo '<h1>获取微信公众号授权失败[无法取得info], 请稍后重试！<h1>';
                    exit;
                }
                $sex = $info['sex'];
                $nickname = $info["nickname"];
            }
            if (empty($fans) || empty($fans['id'])) {
            	//添加
                $row = array(
                    'weid' => $_W['weid'],
                    'nickname' => $nickname,
                    'realname' => $nickname,
                    'follow' => $follow,
                    'gender' => $sex,
                    'salt' => random(8) ,
                    'from_user' => $from_user,
                    'createtime' => TIMESTAMP,
                );
               pdo_insert('fans', $row);
                if (!empty($info["headimgurl"])) {
                    pdo_update('fans', array(
                        'avatar' => $info["headimgurl"]
                    ) , array(
                        'from_user' => $from_user
                    ));
                }
            } else {
                if ($fans['follow'] == 0 && ($fans['follow'] != $follow)) {
                	//之前未关注
                    pdo_update('fans', array(
                        'follow' => $follow
                    ) , array(
                        'from_user' => $from_user,
                        'weid' => $_W['weid']
                    ));
                }
                if (empty($fans["nickname"]) && !empty($nickname)) {
                    $row = array(
                        'nickname' => $nickname,
                        'realname' => $nickname
                    );
                    pdo_update('fans', $row, array(
                        'from_user' => $from_user
                    ));
                }
                if (!empty($info["headimgurl"])) {
                    pdo_update('fans', array(
                        'avatar' => $info["headimgurl"]
                    ) , array(
                        'from_user' => $from_user
                    ));
                }
            }
            setcookie(BAIJIA_COOKIE_OPENID . $_W['weid'], $from_user, time() + 86400);
            setcookie(BAIJIA_COOKIE_CHECKOPENID . $_W['weid'], $from_user, time() + 600);
            setcookie("mid", '', time() - 1);
            $url = $_COOKIE[BAIJIA_COOKIE_XOAUHURL . $_W['weid']];
            header("location:$url");
            exit;
        } else {
            echo '<h1>网页授权域名设置出错!</h1>';
            exit;
        }
    }
    public function GrabImage($url, $filename = "") {
        if ($url == "") return false;
        if ($filename == "") {
            $ext = strrchr($url, ".");
            if ($ext != ".gif" && $ext != ".jpg" && $ext != ".png") return false;
            $filename = date("YmdHis") . $ext;
        }
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        $fp2 = @fopen($filename, "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return $filename;
    }
    public function get_weixin_token() {
        global $_W, $_GPC;
        $account = $_W['account'];
        if (is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) {
            return $account['access_token']['token'];
        } else {
            if (empty($account['weid'])) {
                message('参数错误.');
            }
            $appid = $account['key'];
            $secret = $account['secret'];
            if (empty($appid) || empty($secret)) {
                if (BAIJIA_AGENT_ALL == true) {
                    $appid = BAIJIA_APPID;
                    $secret = BAIJIA_SECRET;
                }
            }
            if (empty($appid) || empty($secret)) {
                message('请填写公众号的appid及appsecret, (需要你的号码为微信服务号)！', create_url('account/post', array(
                    'id' => $account['weid']
                )) , 'error');
            }
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
            $content = ihttp_get($url);
            if (empty($content)) {
                message('获取微信公众号授权失败, 请稍后重试！');
            }
            $token = @json_decode($content['content'], true);
            if (empty($token) || !is_array($token)) {
                message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
            }
            if (empty($token['access_token']) || empty($token['expires_in'])) {
                message('解析微信公众号授权失败, 请稍后重试！');
            }
            $record = array();
            $record['token'] = $token['access_token'];
            $record['expire'] = TIMESTAMP + $token['expires_in'];
            $row = array();
            $row['access_token'] = iserializer($record);
            pdo_update('wechats', $row, array(
                'weid' => $account['weid']
            ));
            return $record['token'];
        }
    }
    public function getSignPackage($urlaction = 'list', $datas = array() , $imgUrl = '', $title = '') {
        global $_W, $_GPC;
        if (BAIJIA_DEVELOPMENT) {
            return true;
        }
        $appid = $_W['account']['key'];
        if (empty($appid)) {
            if (BAIJIA_AGENT_ALL == true) {
                $appid = BAIJIA_APPID;
            }
        }
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      	$url=  str_replace(':8000','',$url);
        $jsapiTicket = $this->get_js_ticket();
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $mid = $this->getMid();
        if (empty($datas)) {
            $datas = array(
                'id' => $mid
            );
        }
        $signature = sha1($string);
        $cfg = $this->module['config'];
        if (empty($title)) {
            $title = $_W['account']['name'];
        }
        if (empty($imgUrl)) {
            $imgUrl = $_W['attachurl'] . $cfg['logo'];
        }
        $datas['mid'] = $mid;
        $signPackage = array(
            "appId" => $appid,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "title" => $title,
            "imgUrl" => $imgUrl,
            "link" => $_W['siteroot'] . $this->createMobileUrl($urlaction, $datas, true) ,
            "signature" => $signature,
            "description" => $cfg['description'],
            "rawString" => $string
        );
        return $signPackage;
    }
    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str.= substr($chars, mt_rand(0, strlen($chars) - 1) , 1);
        }
        return $str;
    }
    public function get_js_ticket() {
        $data = @json_decode(file_get_contents(IA_ROOT . BJ_QMXK_BASE . "/jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $accessToken = $this->get_weixin_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $content = ihttp_get($url);
            $res = @json_decode($content['content'], true);
            $ticket = $res['ticket'];
            if ($ticket) {
                $data = array();
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                $fp = fopen(IA_ROOT . BJ_QMXK_BASE . "/jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
    public function doMobileCheckopenid() {
        global $_W, $_GPC;
        $weid = $_W['weid'];
        if ($_GPC['code'] == "authdeny") {
            return;
        }
        $ncheck_openid = BAIJIA_COOKIE_OPENID . $_W['weid'];
        $now_from_user = $_COOKIE[$ncheck_openid];
        if (empty($now_from_user)) {
            return;
        }
        if (isset($_GPC['code'])) {
            $appid = $_W['account']['key'];
            $secret = $_W['account']['secret'];
            if (empty($appid) || empty($secret)) {
                if (BAIJIA_AGENT_ALL == true) {
                    $appid = BAIJIA_APPID;
                    $secret = BAIJIA_SECRET;
                }
            }
            if (empty($appid) || empty($secret)) {
                message('微信公众号没有配置公众号AppId和公众号AppSecret!');
            }
            $state = $_GPC['state'];
            $code = $_GPC['code'];
            $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $secret . "&code=" . $code . "&grant_type=authorization_code";
            $content = ihttp_get($oauth2_code);
            $token = @json_decode($content['content'], true);
            if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
                echo '<h1>获取微信公众号授权2' . $code . '失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'] . '<h1>';
                exit;
            }
            $from_user = $token['openid'];
            if ($from_user != $now_from_user) {
                setcookie(BAIJIA_COOKIE_OPENID . $_W['weid'], "", time() - 1);
                $this->getFromUser();
            } else {
                $url = $_COOKIE[BAIJIA_COOKIE_CHECKOPURL . $_W['weid']];
                setcookie(BAIJIA_COOKIE_CHECKOPENID . $_W['weid'], $from_user, time() + 600);
                header("location:$url");
                exit;
            }
        } else {
            echo '<h1>网页授权域名设置出错!</h1>';
            exit;
        }
    }
    public function validateopenid() {
        global $_W, $_GPC;
        $ncheck_openid = BAIJIA_COOKIE_OPENID . $_W['weid'];
        $now_from_user = $_COOKIE[$ncheck_openid];
        if (empty($now_from_user)) {
            return;
        }
        $appid = $_W['account']['key'];
        $secret = $_W['account']['secret'];
        if (empty($appid) || empty($secret)) {
            if (BAIJIA_AGENT_ALL == true) {
                $appid = BAIJIA_APPID;
                $secret = BAIJIA_SECRET;
            }
        }
        if (empty($appid) || empty($secret)) {
            message('微信公众号没有配置公众号AppId和公众号AppSecret!');
        }
        $oauth_openid = BAIJIA_COOKIE_CHECKOPENID . $_W['weid'];
        if (empty($_COOKIE[$oauth_openid])) {
            $url = $_W['siteroot'] . $this->createMobileUrl('checkopenid');
            setcookie(BAIJIA_COOKIE_CHECKOPURL . $_W['weid'], "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", time() + 600);
            $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
            header("location:$oauth2_code");
        }
    }
    /**
     * 获取当前openid
     * @param number $state
     * @return string
     */
    public function getFromUser($state = 0) {
    	//:TODO切换账号只要更改此处就行
        global $_W, $_GPC;
	//	return 'from_user';
        if (BAIJIA_DEVELOPMENT == true) {
            return $_W['fans']['from_user'];
        }
        $oauth_openid = BAIJIA_COOKIE_OPENID . $_W['weid'];
        	$appid = $_W['account']['key'];
        	$secret = $_W['account']['secret'];
        	if (empty($appid) || empty($secret)) {
        		if (BAIJIA_AGENT_ALL == true) {
        			$appid = BAIJIA_APPID;
        			$secret = BAIJIA_SECRET;
        		}
        	}
        	if (empty($appid) || empty($secret)) {
        		message('微信公众号没有配置公众号AppId和公众号AppSecret!');
        	}
        	if (empty($_COOKIE[$oauth_openid])) {
        		$url = $_W['siteroot'] . $this->createMobileUrl('xoauth');
        		if ($state == 1) {
        			$scope = "snsapi_userinfo";
        		} else {
        			$scope = "snsapi_base";
        			setcookie(BAIJIA_COOKIE_XOAUHURL . $_W['weid'], "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", time() + 600);
        		}
        		$oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=" . $scope . "&state=" . $state . "#wechat_redirect";
        		header("location:$oauth2_code");
        		exit;
        	} else {
        		return $_COOKIE[$oauth_openid];
        	}
	//return "oo_jRt23TguhyL_xmKye48M8Xcoc-helei1";
    }
    public function sendtempmsg($template_id, $url, $data, $topcolor) {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        //根据$from_user获取openid  2015-12-10(贺磊)
        $member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
        		':from_user' => $from_user,
        		':weid' => $_W['weid']
        ));
        $tokens = $this->get_weixin_token();
        if (empty($tokens)) {
            return;
        }
        $postarr = '{"touser":"' . $member_info["openid"] . '","template_id":"' . $template_id . '","url":"' . $url . '","topcolor":"' . $topcolor . '","data":' . $data . '}';
        $res = ihttp_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $tokens, $postarr);
        return true;
    }
    public function sendcustomMsg($from_user, $msg) {
    	global $_W;
        $access_token = $this->get_weixin_token();
        //根据$from_user获取openid  2015-12-10(贺磊)
        $member_info= pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
        		':from_user' => $from_user,
        		':weid' => $_W['weid']
        ));
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $post = '{"touser":"' . $member_info["openid"] . '","msgtype":"text","text":{"content":"' . $msg . '"}}';
        $this->curlPost($url, $post);
    }
    public function curlPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        curl_close($ch);
        return $info;
    }
    /**
     * 购买增加余额处理: 根据订单id，查询订单总金额，查询可返利的分销人员、金额，再调用子模块加余额
     * @param $orderId  订单id
     * 1. 获得分销佣金，给上级、上上级等加余额
     * 2. 购买者自身得返利
     * 3. 同级关注者得返利 
     * 4. 自动全球分红
     */
    public function buyDealCredit($orderId){
    	global $_W;
    	$cfg = $this->module['config'];
    	$orderInfo=pdo_fetch("select * from  " . tablename('bj_qmxk_order') . " where  weid=".$_W['weid']." and id=".$orderId);
    	$orderGoodsInfo=pdo_fetchall("select commission,commission2,commission3,total  from  " . tablename('bj_qmxk_order_goods') . " where  weid=".$_W['weid']." and orderid=".$orderId);
    	/*-------------分销佣金处理------------------*/
    	$shareId = $this->getShareId($orderInfo["from_user"]); //当前订单用户的上一级
    	$shareId2 = $this->getShareId($orderInfo["from_user"], 2);
    	$shareId3 = $this->getShareId($orderInfo["from_user"], 3);
    	if ($shareId == $shareId2) {
    		$shareId2 = 0;
    	}
    	if ($shareId == $shareId3) {
    		$shareId3 = 0;
    	}
    	if ($shareId2 == $shareId3) {
    		$shareId3 = 0;
    	}
    	for($i=0;$i<count($orderGoodsInfo);$i++){
    		$data=array(
    				$shareId=>$orderGoodsInfo[$i]["commission"]*$orderGoodsInfo[$i]["total"],
    				$shareId2=>$orderGoodsInfo[$i]["commission2"]*$orderGoodsInfo[$i]["total"],
    				$shareId3=>$orderGoodsInfo[$i]["commission3"]*$orderGoodsInfo[$i]["total"]
    		);
    		$this->addExpand($data);
    	}
    	/*=======分销佣金处理结束=========*/
    	
    	/*-------------自动分红处理------------------*/
    	if($cfg["dividend_type"]==1){   //自动分红
    		$this->dividendDealCreditAuto($orderInfo["price"],$orderInfo["id"]);
    	}
    	/*=======自动分红处理结束=========*/
    	
    	/*-------------同级关注者得返利 处理------------------*/
    	if($cfg["subscribeDividend"]==1){
    		$this->addSiblingRebate($orderInfo["price"],$orderInfo["id"]);
    	}
    	/*=======同级关注者得返利 处理结束=========*/
    	/*-----------自身消费所获得的返利-------*/
    	if($cfg["consumerRebates"]==1){
    		 $this->yueFenpei($orderInfo["from_user"], $orderInfo["rebates_money"],0,"消费返点");
    	}
    	/*=====自身消费所获得的返利结束=====*/
    	
    	
    }
    
    /**
     * 手工分红增加余额处理: 获取到系统勋章列表，查询每一种勋章的用户，给用户增加余额
     * @param arr   [   {勋章id=>{钱:100, 人数:10} },    {} ]
     */
    public function dividendDealCredit($arr){
    	global $_W;
    	$info=$arr;
    	foreach($info as $k=>$v){
    		$medal_id=$k;
    		$money=$info[$k]["money"];
    	//	$personels=$info[$k]["personel"];
    		$from_users=pdo_fetchall('select group_concat(m.id)  as ids from
					 ' . tablename('bj_qmxk_relation_medal') . " rm
					left join ". tablename('bj_qmxk_member') . " m on rm.from_user=m.from_user
	    					where rm. weid = " . $_W['weid']."  and rm.status=1
    				and rm.medal_id=$medal_id");
    			
    		$member_ids=$from_users[0]["ids"];
    		
    		if($money  &&  $member_ids){
    			$from_user_arr=pdo_fetchall("select from_user from ". tablename('bj_qmxk_member') . " where  id in($member_ids)");
    			for($i=0;$i<count($from_user_arr);$i++){
    				$from_user=$from_user_arr[$i]["from_user"];
    				$data=array(
    						'from_user'=>$from_user,
    						'money'=>$money,
    						'add_time'=>date("Y-m-d H:i:s"),
    						'medal_id'=>$medal_id,
    						'weid'=>$_W["weid"],
    						'person_num'=>count($from_user_arr)
    				);
    				pdo_insert('bj_qmxk_relation_dividend',$data );
    				//执行修改余额操作	
				$this->yueFenpei($from_user, $money,1,"等级分红");
    			}
    		}
    	}
    }
    /**
     * 自动分红增加余额处理: ......
     * @param $money   分红总金额
     */
    public function dividendDealCreditAuto($money,$orderid){
    	global $_W;
    	//勋章信息
    	$medal_info= pdo_fetchall('select * from ' . tablename('bj_qmxk_medal') . " where weid = " . $_W['weid']."
        		and medal_status=1");
    	$pay_price=$money;
    	
    	//获取获得该勋章的人数
    	for($i=0;$i<count($medal_info);$i++){
    		$medal_id=$medal_info[$i]["id"];  
    		$globalDividend=$medal_info[$i]["globalDividend"];
    		$now_get_medal_info= pdo_fetchall('select *  from ' . tablename('bj_qmxk_relation_medal') . "
    					where weid = " . $_W['weid']." and status=1 and medal_id=$medal_id");
    		$now_get_medal_num=count($now_get_medal_info);
    		for($j=0;$j<$now_get_medal_num;$j++){
    			$fenpei_money=(($pay_price*$globalDividend)/$now_get_medal_num)/100;  //分配勋章金额
    			$medal_from_user=$now_get_medal_info[$j]["from_user"];
    			$exist_dividend=pdo_fetchcolumn("select count(*) from ". tablename('bj_qmxk_relation_dividend') . "   where order_id=".$orderid."  and weid=" . $_W['weid']."
    						and from_user='".$medal_from_user."' and medal_id=".$medal_id."
    						");
    			if(empty($exist_dividend)){
    				$relation_medal_data=array(  'from_user' =>$medal_from_user,'money'=>$fenpei_money,
    						'add_time'=>date("Y-m-d H:i:s"),'medal_id'=>$medal_id,'d_status'=>0,
    						'weid'=>$_W['weid'],'order_money'=>$pay_price,'order_id'=>$orderid,'person_num'=>$now_get_medal_num);
    				pdo_insert('bj_qmxk_relation_dividend', $relation_medal_data);
    				//执行修改余额操作
    				$this->yueFenpei($medal_from_user, $fenpei_money,1,"等级分红");
    	
    			}
    		}
    	}
    }
  
    /**
     * 添加返佣余额
     */
    public function addExpand($data){
    	global $_W;
    	$cfg = $this->module['config'];
    	if(count($data)){
    		foreach($data as $k=>$v){
    			if(!empty($k)){
    				//:TODO设置各种余额比例
    				$from_user=$this->getShareFromUser($k);
    				$this->yueFenpei($from_user, $v,1,"推广返佣");
    			}
    		}
    	}
    }
    
    /**
     * 添加同级返利
     * @param $pay_price   返利总金额
     * @param $orderid   订单id
     */
    public  function addSiblingRebate($pay_price,$orderid){
    	global $_W;
    	$cfg = $this->module['config'];
    		//关注分红（分钱）
    		$from_user=$this->getFromUser();
    		//判断有无上级
    		$share_id=pdo_fetchcolumn("select shareid  from ". tablename('bj_qmxk_member') . "  where weid= ".$_W['weid']." and   from_user='".$from_user."'");
    		if(!empty($share_id)){
    			//有上级
    			//获取兄弟
    			$brother_from_user=pdo_fetchall("select from_user from  ". tablename('bj_qmxk_member') . "  where weid= ".$_W['weid']." and   shareid=$share_id ");
    			for($i=0;$i<count($brother_from_user);$i++){
    				$money=($pay_price*($cfg["subscribeDividendRate"]/100))/count($brother_from_user);
    				$data=array(
    						'from_user'=>$brother_from_user[$i]["from_user"],
    						'money'=>$money,
    						'add_time'=>date("Y-m-d H:i:s"),
    						'weid'=>$_W["weid"],
    						'person_num'=>count($brother_from_user),
    						'order_money'=>$pay_price,
    						'order_id'=>$orderid
    				);
    				pdo_insert('bj_qmxk_subscribe_dividend', $data);
    				//执行修改余额操作
    				$this->yueFenpei($brother_from_user[$i]["from_user"], $money,0,"关注分钱");
    				
    				
    			}
    		}
    }

    
    public  function getShareFromUser($mid){
    	global $_W;
    	return pdo_fetchcolumn("select openid  from  " . tablename('bj_qmxk_member') . " where  id=".$mid." and  weid=".$_W["weid"]);
    }
    
    /**
     * 余额分配
     * @param string $from_user
     * @param double $money
     * $isNeedFlag  余额分配时是否必须是代理身份 1是
     */
    public function yueFenpei($from_user,$money,$isNeedFlag=1,$remark){
    	global $_W;
    	$cfg = $this->module['config'];
    	
    	 if($isNeedFlag==1){
    	 	//判断身份
    	 	$flag=pdo_fetchcolumn("select flag from " . tablename('bj_qmxk_member') . "
    	 		where from_user='".$from_user."' and weid=".$_W["weid"]);
    	 	if($flag==1){
    	 		$this->doYueFenpei($from_user,$money,$remark);
    	 	}
    	 }else {
    	 	$this->doYueFenpei($from_user,$money,$remark);
    	 }
    	 
    	 
    }
    
    public  function  doYueFenpei($from_user,$money,$remark){
    	global $_W;
    	$cfg = $this->module['config'];
    	//执行修改余额操作
    	//购物余额
    	$credit2=($cfg["shoppingBalance"]*$money)/100;
    	//自由余额（可提现、可购物）
    	$credit3=($cfg["withdrawBalance"]*$money)/100;
    	//不动产余额
    	$credit4=($cfg["immobileBalance"]*$money)/100;
    	pdo_query("update " . tablename('fans') . " SET credit2=credit2+" . $credit2 . ",credit3=credit3+" . $credit3 . ",credit4=credit4+" . $credit4 . "  WHERE from_user='" . $from_user . "' AND  weid=" . $_W['weid'] . "  ");
    	if(!empty($credit2)){
    		$paylog = array(
    				'type' => 'charge',
    				'weid' => $_W['weid'],
    				'openid' => $from_user,
    				'tid' => date('Y-m-d H:i:s') ,
    				'fee' => $credit2,
    				'module' => 'bj_qmxk',
    				'tag' => '前台产生交易，购物余额增加' . $credit2 . '元'
    		);
    		pdo_insert('paylog', $paylog);
	}
    	if(!empty($credit3)){
    		$this->sendcustomMsg($from_user, "恭喜您，自由积分+".$credit3."(".$remark.")");
    	}
    	if(!empty($credit2)){
    		$this->sendcustomMsg($from_user, "恭喜您，购物余额+".$credit2."(".$remark.")");
    	}
    	if(!empty($credit4)){
    		$this->sendcustomMsg($from_user, "恭喜您，发展基金+".$credit4."(".$remark.")");
    	}
    }
    /**
     * (退款)支付金额原路返回
     * @param int $orderid
     * @return boolean
     */
    public function returnMoney($orderid){
    	global $_W;
    	$item = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_order') . ' WHERE id = :id', array(':id' => $orderid));
    	$used_credit2=$item["used_credit2"]; //购物余额
    	$used_credit3=$item["used_credit3"]; //自由余额
    	$non_payment=$item["non_payment"]; //微信支付
    	$from_user=$item['from_user'];
    	$fans = pdo_fetch('SELECT credit2,credit3 FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
    			':weid' => $_W["weid"],
    			':from_user' =>$from_user
    	));
    	$msg=" 您的订单:（" . $item['ordersn']."）退款申请已通过审核；";
    	if(!empty($fans)){
    		$refund=array();
    		if($used_credit2>0 || $used_credit3>0){
    			pdo_update('fans', array(
    			"credit2" => $used_credit2+$fans["credit2"],
    			"credit3" => $used_credit3+$fans["credit3"]
    			) , array(
    			'from_user' => $from_user,
    			'weid' => $_W["weid"]
    			));
    			if($used_credit2>0){
    				$refund["credit2"]=$used_credit2;
    				$msg .="购物余额+".$used_credit2."元；";
    			}
    			if($used_credit3>0){
    				$refund["credit3"]=$used_credit3;
    				$msg .="自由积分+".$used_credit3."元；";
    			}
    		}
    		if($non_payment>0){
    			$result=$this->WxPayment($non_payment,$from_user,"TK","商城退款");
    			if($result["return_code"]=="SUCCESS" && $result["result_code"]=="SUCCESS"){ //发放成功
    				$refund["wechat_pay"]=$non_payment;
    				$msg =  ' 后台微信退款，微信零钱+' . $non_payment . '元';
    			}
    		}
    		$refund["createtime"]=date("Y-m-d H:i:s");
    		$refund["ordernum"]=$item["ordersn"];
    		pdo_insert("bj_qmxk_order_refund",$refund);
    		if(pdo_insertid()){
    			$this->sendcustomMsg($from_user,$msg);
    			return true;
    		}
    	}
    }
    /**
     生成订单号
     */
    public function create_order_num($type){
    	//生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码
	 @date_default_timezone_set("PRC");
	 
	  //订购日期
	 
	  $order_date = date('Y-m-d');
	 
	  //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
	 
	  $order_id_main = date('YmdHis') . rand(10000000,99999999);
	 
	  //订单号码主体长度
	 
	  $order_id_len = strlen($order_id_main);
	  $order_id_sum = 0;
	  for($i=0; $i<$order_id_len; $i++){
	  	$order_id_sum += (int)(substr($order_id_main,$i,1));
	  }
	  //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
	  $order_id = $type.$order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $order_id;
    
    }
    
    public function WxPayment($withdrawal_amount,$from_user,$type=false,$remark="薪资提现"){
    	global $_W;
    	$cfg = $this->module['config'];
    	
    	//读取微信支付配置
    	$payConfig = $_W['account']['payment']['wechat'];
    	$partner_trade_no=$this->create_order_num($type);
    	require IA_ROOT . '/payment/Weixinnewpay/WxPayPubHelper.class.php';
    	//  import('@.ORG.Weixinnewpay.WxPayPubHelper');
    	//=========步骤2：使用企业付款接口============
    	//使用企业支付接口
    	$unifiedOrder = new Transfer_pub($payConfig['appid'],$payConfig['mchid'],
    			$payConfig['signkey'],$payConfig['secret']);
    	$unifiedOrder->setParameter("openid",$from_user);//用户openid
    	$unifiedOrder->setParameter("partner_trade_no",$partner_trade_no);//商户订单号
    	$unifiedOrder->setParameter("amount",$withdrawal_amount*100);//总金额
    	$unifiedOrder->setParameter("check_name","NO_CHECK");//校验用户姓名选项
    	$unifiedOrder->setParameter("desc",$remark);//企业付款操作说明信息
    	$token=$_W['account']["token"];
    	//echo $token;
    	//die;
    	//=========步骤3：调起支付============
    	/**--------------windows-----------------**/
//     	$url1='\\payment\\Weixinnewpay\\'.$token.'\\apiclient_cert.pem';
//     	$url2='\\payment\\Weixinnewpay\\'.$token.'\\apiclient_key.pem';
    	/**==============Linux===================**/
    	    $url1='/payment/Weixinnewpay/'.$token.'/apiclient_cert.pem';
    	    $url2='/payment/Weixinnewpay/'.$token.'/apiclient_key.pem';
    	$result = $unifiedOrder->getResult($url1,$url2);
    	return $result;
    }
    /**
     * 读取excel文件
     * @param unknown $filename
     * @param unknown $file_type
     * @param string $encode
     * @return Ambigous <multitype:, string>
     */
    public function read_excel($filename,$file_type,$encode='utf-8'){
    		include_once('./source/library/phpexcel/PHPExcel.php');
    		if($file_type == 'xls'){
    			$objReader = PHPExcel_IOFactory::createReader('Excel5');
    		}else{
    			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
    		}
    		
    		/**  Identify the type of $inputFileName  **/
    		$inputFileType = PHPExcel_IOFactory::identify($filename);
    		/**  Create a new Reader of the type that has been identified  **/
    		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
    		/**  Load $inputFileName to a PHPExcel Object  **/
    		$objPHPExcel = $objReader->load($filename);
    		
    		$objSheet = $objPHPExcel->getActiveSheet();
    		$highestRow=$objSheet->getHighestRow();
    		$highestColumn = $objSheet->getHighestColumn();
    		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    		$excelData = array();
    		for ($row = 1; $row <= $highestRow; $row++) {
    			for ($col = 0; $col < $highestColumnIndex; $col++) {
    				$excelData[$row][] =(string)$objSheet->getCellByColumnAndRow($col, $row)->getValue();
    			}
    		}
    		return $excelData;
    	}
}


function pagination1($tcount, $pindex, $psize = 15, $url = '', $context = array(
    'before' => 5,
    'after' => 4,
    'ajaxcallback' => ''
)) {
    global $_W;
    $pdata = array(
        'tcount' => 0,
        'tpage' => 0,
        'cindex' => 0,
        'findex' => 0,
        'pindex' => 0,
        'nindex' => 0,
        'lindex' => 0,
        'options' => ''
    );
    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }
    $pdata['tcount'] = $tcount;
    $pdata['tpage'] = ceil($tcount / $psize);
    if ($pdata['tpage'] <= 1) {
        return '';
    }
    $cindex = $pindex;
    $cindex = min($cindex, $pdata['tpage']);
    $cindex = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];
    if ($context['isajax']) {
        if (!$url) {
            $url = $_W['script_name'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
    } else {
        if ($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
        }
    }
    $html = '<div class="pagination pagination-centered"><ul>';
    if ($pdata['cindex'] > 1) {
        $html.= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
        $html.= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
    }
    if (!$context['before'] && $context['before'] != 0) {
        $context['before'] = 5;
    }
    if (!$context['after'] && $context['after'] != 0) {
        $context['after'] = 4;
    }
    if ($context['after'] != 0 && $context['before'] != 0) {
        $range = array();
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($context['isajax']) {
                $aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
            } else {
                if ($url) {
                    $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                } else {
                    $_GET['page'] = $i;
                    $aa = 'href="?' . http_build_query($_GET) . '"';
                }
            }
        }
    }
    if ($pdata['cindex'] < $pdata['tpage']) {
        $html.= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
        $html.= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
    }
    $html.= '</ul></div>';
    return $html;
}
function img_url($img = '') {
    global $_W;
    if (empty($img)) {
        return "";
    }
    if (substr($img, 0, 6) == 'avatar') {
        return $_W['siteroot'] . "resource/image/avatar/" . $img;
    }
    if (substr($img, 0, 8) == './themes') {
        return $_W['siteroot'] . $img;
    }
    if (substr($img, 0, 1) == '.') {
        return $_W['siteroot'] . substr($img, 2);
    }
    if (substr($img, 0, 5) == 'http:') {
        return $img;
    }
    return $_W['attachurl'] . $img;
}
function tpl_form_field_date2($name, $value = array() , $ishour = false) {
    $s = '';
    if (!defined('INCLUDE_DATE')) {
        $s = '
		<link type="text/css" rel="stylesheet" href="./resource/style/datetimepicker.css" />
		<script type="text/javascript" src="./resource/script/datetimepicker.js"></script>';
        define('INCLUDE_DATE', true);
    }
    if (strexists($name, '[')) {
        $id = str_replace(array(
            '[',
            ']'
        ) , '_', $name);
    } else {
        $id = $name;
    }
    $value = empty($value) ? date('Y-m-d', TIMESTAMP) : $value;
    $ishour = empty($ishour) ? 2 : 0;
    $s.= '
	<input type="text" class="datepicker" id="datepicker_' . $id . '" name="' . $name . '" value="' . $value . '" readonly="readonly" />
	<script type="text/javascript">
		$("#datepicker_' . $id . '").datetimepicker({
			format: "yyyy-mm-dd",
			minView: "' . $ishour . '",
			//pickerPosition: "top-right",
			autoclose: true
		});
	</script>';
    return $s;
}

?>