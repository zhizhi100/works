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

$id = $profile['id'];
$op = $_GPC['op'];

$cfg = $_W['account']['modules']['bj_qmxk']['config'];
$rebacktime = 8;
if (!empty($cfg['rebacktime'])) {
    $rebacktime = intval($cfg['rebacktime']);
}

if ($op == 'returngood') {
    $orderid = intval($_GPC['orderid']);
    $item = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_order') . ' WHERE id = :id AND from_user = :from_user', array(':id' => $orderid, ':from_user' => $from_user));
    $dispatch = pdo_fetch('select * from ' . tablename('bj_qmxk_dispatch') . ' where id=:id limit 1', array(':id' => $item['dispatch']));
    if (empty($item)) {
        message('抱歉，您的订单不存在或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
    }
    if($item['special']){
    	message('抱歉，特殊订单不能退货或退款！', $this->createMobileUrl('myorder'), 'error');
    }
    if ($item['status'] < 2) {
        message('订单非完成状态不能申请退货');
    }
    
	//判断订单是否分佣
	$order_goods = pdo_fetchall("select b.* from ".tablename('bj_qmxk_order')." a 
								left join ".tablename('bj_qmxk_order_goods')." b on a.id=b.orderid
								where a.id=".$orderid." AND a.from_user='".$from_user."'"
								);
	$flag=0;
	foreach($order_goods as $og){
		if($og['status']>0){
			$flag = 1;
		}
		if($og['status2']>0){
			$flag = 1;
		}
		if($og['status3']>0){
			$flag = 1;
		}
	}
	if($flag){
		message('该订单已经申请佣金提现，无法退货！');
	}
    if (!empty($item['createtime'])) {
        if ($item['createtime'] < time() - $rebacktime * 24 * 60 * 60) {
            message('退货申请时间已过无法退货。');
        }
    } else {
        message('该订单无法退货');
    }
    $opname = '退货';
    if (checksubmit('submit')) {
		pdo_update('bj_qmxk_order', array('status' => -4,'rsreson' => $_GPC['rsreson']), array('id' => $orderid, 'from_user' => $from_user));
		message('申请退货成功，请等待审核！', $this->createMobileUrl('myorder'), 'success');
    }
    include $this->template('order_detail_return');
    exit;
}
if ($op == 'returnpay') {
    $orderid = intval($_GPC['orderid']);
    $item = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_order') . ' WHERE id = :id AND from_user = :from_user', array(':id' => $orderid, ':from_user' => $from_user));
    $dispatch = pdo_fetch('select * from ' . tablename('bj_qmxk_dispatch') . ' where id=:id limit 1', array(':id' => $item['dispatch']));
    if (empty($item['id'])) {
        message('抱歉，您的订单不存在或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
    }
    if($item['special']){
    	message('抱歉，特殊订单不能退货或退款！', $this->createMobileUrl('myorder'), 'error');
    }
    $opname = '退款';
    if (checksubmit('submit')) {
        if ($item['paytype'] == 3) {
            message('货到付款订单不能进行退款操作!', referer(), 'error');
        }
        if ($item['status'] != 1) {
            message('订单非已付款状态不能申请退款');
        }
        pdo_update('bj_qmxk_order', array('status' => -2, 'rsreson' => $_GPC['rsreson']), array('id' => $orderid, 'from_user' => $from_user));
        message('申请退款成功，请等待审核！', $this->createMobileUrl('myorder'), 'success');
    }
	
    include $this->template('order_detail_return');
    exit;
}
if ($op == 'confirm') {
    $orderid = intval($_GPC['orderid']);
	$discount = $this->setDiscountGivenByOrder($orderid, $_W['fans']['from_user']);
    $order = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE id = :id AND from_user = :from_user", array(
        ':id' => $orderid,
        ':from_user' => $from_user
    ));
    if (empty($order)) {
        message('抱歉，您的订单不存在或是已经被取消！', $this->createMobileUrl('myorder') , 'error');
    }
    if($order["status"] !=3){
    	$this->buyDealCredit($orderid);  //添加余额（订单完成时）避免重复提交
    }
    if (!empty($orderid)) {
        $this->setOrderCredit($orderid, $_W['weid']);
    }
    pdo_update('bj_qmxk_order', array(
        'status' => 3
    ) , array(
        'id' => $orderid,
        'from_user' => $from_user
    ));
	$this->getOrderNum($orderid);
    $this->checkisAgent($from_user, $profile);
    $tagent = $this->getMember($this->getShareId());
 
    $this->sendxjdlshtz($order['ordersn'], $order['price'], $profile['realname'], $tagent['from_user']);
    message('确认收货完成！', $this->createMobileUrl('myorder') , 'success');
} else if ($op == 'detail') {
    $orderid = intval($_GPC['orderid']);
    $item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' AND from_user = '" . $from_user . "' and id='{$orderid}' limit 1");
    if (empty($item)) {
        message('抱歉，您的订单不存或是已经被取消！', $this->createMobileUrl('myorder') , 'error');
    }
    $goodsid = pdo_fetchall("SELECT goodsid,total FROM " . tablename('bj_qmxk_order_goods') . " WHERE orderid = '{$orderid}'", array() , 'goodsid');
    $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$orderid}'");
    foreach ($goods as & $g) {
        $option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(
            ":id" => $g['optionid']
        ));
        if ($option) {
            $g['title'] = "[" . $option['title'] . "]" . $g['title'];
            $g['marketprice'] = $option['marketprice'];
        }
    }
    unset($g);
    $dispatch = pdo_fetch("select id,dispatchname,dispatchtype from " . tablename('bj_qmxk_dispatch') . " where id=:id limit 1", array(
        ":id" => $item['dispatch']
    ));
    include $this->template('order_detail');
}else if ($op == 'cancel') {
	$orderid = intval($_GPC['orderid']);
	$item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' AND from_user = '" . $from_user . "' and id='{$orderid}' limit 1");
	if (empty($item)) {
		message('抱歉，您的订单不存或是已经被取消！', $this->createMobileUrl('myorder') , 'error');
	}
	if($_GPC['submit']){
		
	
		if ($item['status']==0) {
			pdo_update('bj_qmxk_order', array(
				'status' => - 1,
				'remark' => $_GPC['remark']
			) , array(
				'id' => $orderid
			));
			message('您的订已经被取消成功！', $this->createMobileUrl('myorder') , 'success');
		}
	}else{
		include $this->template('order_cancel');
	}
} else {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $status = intval($_GPC['status']);
  
    $where = " weid = '{$_W['weid']}' AND from_user = '" . $from_user . "'";;
    if ($status == 2) {
        $where.= " and  (status=2  or   status=1) ";
    }elseif( $status==4){
    	$where.= " ";
    } else if ($status == -1) {
        $where.= " and ( status=-2 or status=-4 or status=-5 or status=-6 )";
    }else{
        $where.= " and status=$status";
    }

    $list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE $where ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array() , 'id');
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' AND from_user = '" . $from_user . "'");
    $pager = pagination($total, $pindex, $psize);
    if (!empty($list)) {
        foreach ($list as & $row) {
            $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('bj_qmxk_order_goods') . " o left join " . tablename('bj_qmxk_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$row['id']}'");
            foreach ($goods as & $item) {
                $option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("bj_qmxk_goods_option") . " where id=:id limit 1", array(
                    ":id" => $item['optionid']
                ));
                if ($option) {
                    $item['title'] = "[" . $option['title'] . "]" . $item['title'];
                    $item['marketprice'] = $option['marketprice'];
                }
            }
            unset($item);
            $row['goods'] = $goods;
            $row['total'] = $goodsid;
            $row['dispatch'] = pdo_fetch("select id,dispatchname from " . tablename('bj_qmxk_dispatch') . " where id=:id limit 1", array(
                ":id" => $row['dispatch']
            ));
        }
    }
    $carttotal = $this->getCartTotal();
    $fans = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid and from_user=:from_user", array(
        ':weid' => $_W['weid'],
        ':from_user' => $from_user
    ));
    include $this->template('order');
} ?>