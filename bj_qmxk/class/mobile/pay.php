<?php

global $_W, $_GPC;
$from_user =$this->getFromUser();
$_W['fans'] = pdo_fetch('SELECT follow,nickname,from_user,avatar,credit1,credit2 FROM '.tablename('fans')." 
		WHERE  weid = :weid  AND from_user = :from_user LIMIT 1" , array(':weid' => $_W['weid'],
				':from_user' => $from_user));

$orderid = intval($_GPC['orderid']);
$order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id", array(
    ':id' => $orderid
));
$goodsstr = "";
$bodygoods = "";
if ($order['status'] != '0') {
    message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('myorder') , 'error');
}
$ordergoods = pdo_fetchall("SELECT goodsid, total,optionid FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}'", array() , 'goodsid');
if (!empty($ordergoods)) {
    $goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total,credit FROM " . tablename('bj_qmxk_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
}
if (!empty($goods)) {
    foreach ($goods as $row) {
        $goodsstr.= "{$row['title']}({$ordergoods[$row['id']]['total']})<br/>";
        $bodygoods.= "名称：{$row['title']} ，数量：{$ordergoods[$row['id']]['total']} <br />";
    }
}
if (checksubmit('codsubmit')) {
    if (!empty($this->module['config']['noticeemail'])) {
        $address = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id = :id", array(
            ':id' => $order['addressid']
        ));
        $body = "<h3>购买商品清单</h3> <br />";
        if (!empty($goods)) {
            $body.= $bodygoods;
        }
        $body.= "<br />总金额：{$order['price']}元 （货到付款）<br />";
        $body.= "<h3>购买用户详情</h3> <br />";
        $body.= "真实姓名：$address[realname] <br />";
        $body.= "地区：$address[province] - $address[city] - $address[area]<br />";
        $body.= "详细地址：$address[address] <br />";
        $body.= "手机：$address[mobile] <br />";
        ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
    }
    $tagent = $this->getMember($this->getShareId());
    $this->sendgmsptz($order['ordersn'], $order['price'], $profile['realname'], $tagent['from_user']);
	$discount = $this->getDiscountByOrder($order['id'], $order['from_user']);
    pdo_update('bj_qmxk_order', array(
        'status' => '1',
        'paytype' => '3', 
		'discount'=> $discount
    ) , array(
        'id' => $orderid
    ));
    $this->sendMobilePayMsg($order, $goods, "付款完成", $ordergoods);
    message('订单提交成功，请您收到货时付款！', $this->createMobileUrl('myorder') , 'success');
}



if (checksubmit()) {
    if ($order['paytype'] == 1 && ($_W['fans']['credit2']+$_W['fans']['credit3']) < $order['price']) {
        message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array(
            'name' => 'member',
            'weid' => $_W['weid']
        )) , 'error');
    }
    if ($order['price'] == '0') {
        $this->payResult(array(
            'tid' => $orderid,
            'from' => 'return',
            'type' => 'credit2'
        ));
        exit;
    }
}
//折扣优惠
$discount = $this->getDiscountByOrder($order['id'], $order['from_user']);
$price=$order['price']-$discount;

// $from_user = $this->getFromUser();
$profile = pdo_fetch('SELECT * FROM '.tablename('bj_qmxk_member')." WHERE  weid = :weid  AND from_user = :from_user" , array(':weid' => $_W['weid'],':from_user' => $order['from_user']));

 if (!empty($profile['memberlevel'])){ 
    $memdiscount = pdo_fetchcolumn('SELECT discount FROM '.tablename('bj_qmxk_memberlevel')." WHERE  weid = :weid and id={$profile['memberlevel']}" , array(':weid' => $_W['weid']));  
    $price=($order['goodsprice']-$discount)*$memdiscount/100+$order['dispatchprice'];
    $price=sprintf("%.2f", $price);
    $params['memdiscount']=$memdiscount;
  }

$params['tid'] = $orderid;
$params['user'] = $from_user;
$params['fee'] = $price;
$params['title'] = $_W['account']['name'];
$params['origin_fee'] = $order['price'];
$params['discount'] = $discount;
$params['ordersn'] = $order['ordersn'];
$params['used_credit2'] = $order["used_credit2"];
$params['used_credit3'] = $order["used_credit3"];
$params['non_payment'] = $order["non_payment"];
$params['vochuer_money'] = $order["vochuer_money"];
$params['voucher_record_ids'] = $order["voucher_record_ids"];
$params['virtual'] = $order['goodstype'] == 2 ? true : false;
if (empty($_GPC['topay'])) {
    $this->bjpay($params, $order['sendtype']);
} else {
    $this->bjpay($params, $order['sendtype']);
} 
?>
