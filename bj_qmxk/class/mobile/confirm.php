<?php
$id = $profile['id'];
$weid = $_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$totalprice = 0;
$allgoods = array();
$id = intval($_GPC['id']);
$optionid = intval($_GPC['optionid']);
$total = intval($_GPC['total']);
$cfg=$this->module['config'];
//优惠等级
if (!empty($profile['memberlevel'])){ 
  $memdiscount = pdo_fetchcolumn('SELECT discount FROM '.tablename('bj_qmxk_memberlevel')." WHERE  weid = :weid and id={$profile['memberlevel']}" , array(':weid' => $_W['weid']));  
}
if (empty($total)) {
    $total = 1;
}
$direct = false;
$returnurl = "";
$issendfree = 0;

if (!empty($id)) {
    $item = pdo_fetch("select id,thumb,ccate,title,weight,marketprice,total,type,totalcnf,sales,unit,istime,timeend,issendfree,profit from " . tablename("bj_qmxk_goods") . " where id=:id limit 1", array(
        ":id" => $id
    ));
    if ($item['issendfree'] == 1) {
        $issendfree = 1;
    }
    if ($item['istime'] == 1) {
        if (time() > $item['timeend']) {
            message('抱歉，商品限购时间已到，无法购买了！', referer() , "error");
        }
    }
    if (!empty($optionid)) {
        $option = pdo_fetch("select title,marketprice,weight,stock,profit from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(
            ":id" => $optionid
        ));
        if ($option) {
            $item['optionid'] = $optionid;
            $item['title'] = $item['title'];
            $item['optionname'] = $option['title'];
            $item['marketprice'] = $option['marketprice'];
            $item['weight'] = $option['weight'];
			$item['profit'] = $option['profit'];
        }
    }
    $item['stock'] = $item['total'];
    $item['total'] = $total;
    $item['totalprice'] = $total * $item['marketprice'];
	$item['profit'] =  $item['profit'];
    $allgoods[] = $item;
    $totalprice+= $item['totalprice'];
    if ($item['type'] == 1) {
        $needdispatch = true;
    }
    $direct = true;
    $returnurl = $this->createMobileUrl("confirm", array(
        "id" => $id,
        "optionid" => $optionid,
        "total" => $total
    ));
}
if (!$direct) {
    $list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_cart') . " WHERE  weid = '{$_W['weid']}' AND from_user = '" . $from_user . "'");
    if (!empty($list)) {
        foreach ($list as & $g) {
            $item = pdo_fetch("select id,thumb,ccate,title,weight,marketprice,total,type,totalcnf,sales,unit,issendfree,profit from " . tablename("bj_qmxk_goods") . " where id=:id limit 1", array(
                ":id" => $g['goodsid']
            ));
            $option = pdo_fetch("select title,marketprice,weight,stock,profit from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(
                ":id" => $g['optionid']
            ));
            if ($option) {
                if ($item['issendfree'] == 1) {
                    $issendfree = 1;
                }
                $item['optionid'] = $g['optionid'];
                $item['title'] = $item['title'];
                $item['optionname'] = $option['title'];
                $item['marketprice'] = $option['marketprice'];
				$item['profit'] = $option['profit'];
                $item['weight'] = $option['weight'];
            }
            $item['stock'] = $item['total'];
            $item['total'] = $g['total'];
            $item['totalprice'] = $g['total'] * $item['marketprice'];
			$item['profit'] = $item['profit'];
            $allgoods[] = $item;
            $totalprice+= $item['totalprice'];
            if ($item['type'] == 1) {
                $needdispatch = true;
            }
        }
        unset($g);
    }
    $returnurl = $this->createMobileUrl("confirm");
}
if (count($allgoods) <= 0) {
    header("location: " . $this->createMobileUrl('myorder'));
    exit();
}
//配送方式
$dispatch = pdo_fetchall("select id,dispatchname,dispatchtype,firstprice,firstweight,secondprice,secondweight,pinkage_money from " . tablename("bj_qmxk_dispatch") . " WHERE weid = {$_W['weid']} order by displayorder");

//查询余额(可购买余额，可提现可购买余额)
$yue = pdo_fetch('SELECT credit2,credit3 FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
		':weid' => $_W['weid'],
		':from_user' => $from_user
));


foreach ($dispatch as & $d) {
    $weight = 0;
     foreach ($allgoods as $g) {
        $weight+= $g['weight'] * $g['total'];  
        $total_marketprice+= $g['marketprice'] * $g['total'];
        
        if ($g['issendfree'] == 1) {
            $issendfree = 1;
        }
    }
    //当商品价格>=满（）元包邮价格
    if($d["pinkage_money"]!=0.00){
    	if($d["pinkage_money"]<=$total_marketprice){
    		$issendfree = 1;
    	}
    }
   
    $price = 0;
    if ($issendfree != 1) {
        if ($weight <= $d['firstweight']) {
            $price = $d['firstprice'];
        } else {
            $price = $d['firstprice'];
            $secondweight = $weight - $d['firstweight'];
            if ($secondweight % $d['secondweight'] == 0) {
                $price+= (int)($secondweight / $d['secondweight']) * $d['secondprice'];
            } else {
                $price+= (int)($secondweight / $d['secondweight'] + 1) * $d['secondprice'];
            }
        }
    }
    $d['price'] = $price;
}
unset($d);

//订单提交
if (checksubmit('submit')) {
    $address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(
        ':id' => intval($_GPC['address'])
    ));
    if (empty($address)) {
        message('抱歉，请您填写收货地址！');
    }
    
    //判断代金券是否过期
    $voucher_record_ids_arr=$_GPC["voucher_record_ids"];
    if(count($voucher_record_ids_arr)){
    	$voucher_record_ids=implode(",", $voucher_record_ids_arr);
    	//:TODO获取未过期的 代金券
    	$all_voucher_receive=pdo_fetchall(" select * from " . tablename('bj_qmxk_voucher_receive') . " where weid= ".$_W["weid"]." and id in($voucher_record_ids) and state=1");
    	if(count($all_voucher_receive) == count($voucher_record_ids_arr)){
    		for($i=0;$i<count($all_voucher_receive);$i++){
    			$setmodel=$all_voucher_receive[$i]["setmodel"];
    			$now_date=date("Y-m-d H:i:s");
    			if($setmodel==1){
    				//结束日期
    				$end_date=$all_voucher_receive[$i]["end_validity"];
    			}else{
    				$days=$all_voucher_receive[$i]["days"];
    				$end_date=date("Y-m-d H:i:s", strtotime("+".$days." day", strtotime($all_voucher_receive[$i]["receive_time"])));
    			}
    			if($now_date>$end_date){
    				//unset($all_voucher_receive[$i]);
    				message('抱歉，代金券已过期，请核对后再提交！');
    				continue;
    			}
    		}
    	}else{
    		message('抱歉，代金券信息有变更，请核对后再提交！');
    		exit();
    	}
    
    }
    $goodsprice = 0;
	$profit = 0;
    foreach ($allgoods as $row) {
        if ($item['stock'] != - 1 && $row['total'] > $item['stock']) {
            message('抱歉，“' . $row['title'] . '”此商品库存不足！', $this->createMobileUrl('confirm') , 'error');
        }
        $goodsprice+= $row['totalprice'];
		$profit += $row['profit'];
        if ($row['issendfree'] == 1) {
            $issendfree = 1;
        }
    }
    $dispatchid = intval($_GPC['dispatch']);
    $dispatchitem = pdo_fetch("select dispatchtype from " . tablename('bj_qmxk_dispatch') . " where id=:id limit 1", array(
        ":id" => $dispatchid
    ));
    $dispatchprice = 0;
    if ($issendfree != 1) {
        foreach ($dispatch as $d) {
            if ($d['id'] == $dispatchid) {
                $dispatchprice = $d['price'];
            }
        }
    }
    //如果之前没有用户信息
    if (empty($profile) && empty($profile['id'])) {
        $shareids = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_share_history') . " WHERE  from_user=:from_user and weid=:weid limit 1", array(
            ':from_user' => $from_user,
            ':weid' => $_W['weid']
        ));
        if (!empty($shareids['sharemid'])) {
            $seid = $shareids['sharemid'];
        } else {
            $seid = 0;
        }
        $data = array(
            'weid' => $_W['weid'],
            'from_user' => $from_user,
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile'],
            'commission' => 0,
            'createtime' => TIMESTAMP,
            'flagtime' => TIMESTAMP,
            'shareid' => $seid,
            'status' => 1,
            'flag' => 0
        );
        pdo_insert('bj_qmxk_member', $data);
    }

    $shareId = $this->getShareId();
	$shareId2 = $this->getShareId($from_user, 2);
    $shareId3 = $this->getShareId($from_user, 3);
	
    if ($shareId == $shareId2) {
        $shareId2 = 0;
    }
    if ($shareId == $shareId3) {
        $shareId3 = 0;
    }
    if ($shareId2 == $shareId3) {
        $shareId3 = 0;
    }
    if(empty($_GPC["non_payment"]) || $_GPC["non_payment"]==0.00){
    	//余额支付
    	$sendtype=3;
    }else{
    	$sendtype=$_GPC["sendtype"];   //1微信支付，2支付宝，3余额
    }

	//添加订单
    //返点金额
    if(empty($cfg["rebatesScale"])){
    	$rebatesScale=0;
    }else{
    	$rebatesScale=$cfg["rebatesScale"];
    }
    $rebates_money=($goodsprice)*$rebatesScale/100;
    $data = array(
        'weid' => $_W['weid'],
        'from_user' => $from_user,
        'ordersn' => date('md') . random(4, 1) ,
        'price' => $goodsprice + $dispatchprice,  //总金额
        'dispatchprice' => $dispatchprice,  //配送费用
        'goodsprice' => $goodsprice,      //商品金额
        'status' => 0,
        'sendtype' =>$sendtype ,   //支付方式
        'dispatch' => $dispatchid,    //配送方式
        'paytype' => '2',   //支付类型
        'goodstype' => intval($cart['type']) ,
        'remark' => $_GPC['remark'],
		'profit'=>$profit,
        'addressid' => $address['id'],
        'createtime' => TIMESTAMP,
        'shareid' => $shareId,
        'shareid2' => $shareId2,
        'shareid3' => $shareId3,
    	//新增三个字段（已用可购买余额、提现余额、在线支付金额，代金券金额）
    	'used_credit2'=>$_GPC["shoppingBalance"],
    	'used_credit3'=>$_GPC["freeBalance"],
    	'non_payment'=>$_GPC["non_payment"],
    	'vochuer_money'=>$_GPC["voucher_money"],
    	'voucher_record_ids'=>$voucher_record_ids,
   		 'rebates_money'=>$rebates_money
    );
    pdo_insert('bj_qmxk_order', $data);
    $orderid = pdo_insertid();
    
    /**
     * 添加分佣
     */
    if(empty($_GPC["voucher_money"])){
    	$voucher_money=0;
    }else{
    	$voucher_money=$_GPC["voucher_money"];
    }
    foreach ($allgoods as $row) {
        if (empty($row)) {
            continue;
        }
        $d = array(
            'weid' => $_W['weid'],
            'goodsid' => $row['id'],
            'orderid' => $orderid,
            'total' => $row['total'],
            'price' => $row['marketprice'],
            'createtime' => TIMESTAMP,
            'optionid' => $row['optionid']
        );
        $o = pdo_fetch("select title from " . tablename('bj_qmxk_goods_option') . " where id=:id limit 1", array(
            ":id" => $row['optionid']
        ));
        if (!empty($o)) {
            $d['optionname'] = $o['title'];
        }
        $ccate = $row['ccate'];
        $commission = pdo_fetchcolumn(" SELECT commission FROM " . tablename('bj_qmxk_goods') . "  WHERE id=" . $row['id']);
        $commission2 = pdo_fetchcolumn(" SELECT commission2 FROM " . tablename('bj_qmxk_goods') . "  WHERE id=" . $row['id']);
        $commission3 = pdo_fetchcolumn(" SELECT commission3 FROM " . tablename('bj_qmxk_goods') . "  WHERE id=" . $row['id']);
        if ($commission == false || $commission == null || $commission < 0) {
        	if($commission<0){
        		$commission=0;
        	}else{
        		$commission = $this->module['config']['globalCommission'];
        	}
        }
        if ($commission2 == false || $commission2 == null || $commission2 < 0) {
        	if($commission2<0){
        		$commission2=0;
        	}else{
        		$commission2 = $this->module['config']['globalCommission2'];
        	}
        }
        if ($commission3 == false || $commission3 == null || $commission3 < 0) {
        	if($commission3 < 0){
        		$commission3=0;
        	}else{
        		$commission3 = $this->module['config']['globalCommission3'];
        	}
        }
		//按积分
        $commissionTotal = ($row['profit']-$voucher_money) * $commission / 100;
        $d['commission'] = $commissionTotal;
        $commissionTotal2 =  ($row['profit']-$voucher_money) * $commission2 / 100;
        $d['commission2'] = $commissionTotal2;
        $commissionTotal3 =  ($row['profit']-$voucher_money) * $commission3 / 100;
        $d['commission3'] = $commissionTotal3;
	
//         if ($cfg['globalCommissionLevel'] >= 2) {
//             $d['commission2'] = 0;
//         }
//         if ($cfg['globalCommissionLevel'] >= 3) {
//             $d['commission3'] = 0;
//         }
        pdo_insert('bj_qmxk_order_goods', $d);
    }
    
    
    
    //删除购物车
    if (!$direct) {
        pdo_delete("bj_qmxk_cart", array(
            "weid" => $_W['weid'],
            "from_user" => $from_user
        ));
    }
    $this->setOrderStock($orderid);
    
    die("<script>var a=confirm('提交订单成功,现在跳转到付款页面..');if(a==true){
 location.href='" . $this->createMobileUrl('pay', array(
        'orderid' => $orderid,
        'topay' => '1'
    )) . "';
 }else{location.reload();}</script>");
}




$carttotal = $this->getCartTotal();
$profile = pdo_fetch('SELECT resideprovince,residecity,residedist,address,realname,mobile FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
    ':weid' => $_W['weid'],
    ':from_user' => $from_user
));
$row = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE isdefault = 1 and openid = :openid limit 1", array(
    ':openid' => $from_user
));
//折扣优惠
$allgoods_id = array();
    foreach($allgoods as $g) {
      $allgoods_id[] = $g['id'];
    }

$totaldiscount = $this->getDiscountByGoodsIds($allgoods_id, $_W['fans']['from_user']);

//:TODO获取未过期的 代金券
$all_voucher_receive=pdo_fetchall(" select * from " . tablename('bj_qmxk_voucher_receive') . " where weid= ".$_W["weid"]." and from_user ='".$from_user."' and state=1");
for($i=0;$i<count($all_voucher_receive);$i++){
	$setmodel=$all_voucher_receive[$i]["setmodel"];
	$now_date=date("Y-m-d H:i:s");
	if($setmodel==1){
		//结束日期
		$end_date=$all_voucher_receive[$i]["end_validity"];
	}else{
		$days=$all_voucher_receive[$i]["days"];
		$end_date=date("Y-m-d H:i:s", strtotime("+".$days." day", strtotime($all_voucher_receive[$i]["receive_time"])));
	}
	if($now_date>$end_date){
		unset($all_voucher_receive[$i]);
	}
}
include $this->template('confirm'); 
?>
