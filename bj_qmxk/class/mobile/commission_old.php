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

$weid = $_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$cfg = $this->module['config'];
$id = $profile['id'];
if (intval($profile['id']) && $profile['status'] == 0) {
    include $this->template('forbidden');
    exit;
}
if ($op == 'display') {
    $commissioningpe = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( g.status = 0 ) and o.status >= 3 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commissioningpe)) {
        $commissioningpe = "0.00";
    }
    $commission1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and (g.status = 0) and (o.status =1 or o.status =2) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission1)) {
        $commission1 = "0.00";
    }
    $commission2 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and (g.status = 1) and o.status >=3 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission2)) {
        $commission2 = "0.00";
    }
    $commission3 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and (g.status = -1) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission3)) {
        $commission3 = "0.00";
    }
    $commission4 = pdo_fetchcolumn("SELECT sum((o.price)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =0 or o.status =1)) or ((o.paytype=1 or o.paytype=2 ) and o.status =0)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission4)) {
        $commission4 = "0.00";
    }
    $commission4_1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =0 or o.status =1)) or ((o.paytype=1 or o.paytype=2 ) and o.status =0)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission4_1)) {
        $commission4_1 = "0.00";
    }
    $commission5 = pdo_fetchcolumn("SELECT sum((o.price)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status >1)) or ((o.paytype=1 or o.paytype=2 ) and o.status >0)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission5)) {
        $commission5 = "0.00";
    }
    $commission5_1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =1)) or ((o.paytype=1 or o.paytype=2 ) and o.status =1)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission5_1)) {
        $commission5_1 = "0.00";
    }
    $commission6 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =1 or o.status =0)) or ((o.paytype=1 or o.paytype=2 ) and o.status =1)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission6)) {
        $commission6 = "0.00";
    }
    $commission6_1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =2)) or ((o.paytype=1 or o.paytype=2 ) and o.status =2)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission6_1)) {
        $commission6_1 = "0.00";
    }
    $commission7 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and o.status =3 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission7)) {
        $commission7 = "0.00";
    }
    $commission8 = pdo_fetchcolumn("SELECT sum((o.price)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =1 or o.status =0)) or ((o.paytype=1 or o.paytype=2 ) and o.status =1)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission8)) {
        $commission8 = "0.00";
    }
    $commission9 = pdo_fetchcolumn("SELECT sum((o.price)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and o.status =3 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission9)) {
        $commission9 = "0.00";
    }
    $commission9_1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and o.status =3 and g.status=1 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission9_1)) {
        $commission9_1 = "0.00";
    }
    $commissioned = $profile['commission'];
    $total = pdo_fetchcolumn("select count(id) from " . tablename('bj_qmxk_commission') . " where mid =" . $profile['id'] . " and flag = 0");
    if ($_GPC['opp'] == 'more') {
        $opp = 'more';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $list = pdo_fetchall("select co.isshare,co.commission, co.createtime, og.orderid, og.goodsid, og.total,oo.ordersn from " . tablename('bj_qmxk_commission') . " as co left join " . tablename('bj_qmxk_order_goods') . " as og on co.ogid = og.id and co.weid = og.weid left join " . tablename('bj_qmxk_order') . " as oo on oo.id = og.orderid and co.weid = og.weid where co.mid =" . $profile['id'] . " and co.flag = 0 ORDER BY co.createtime DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
        $pager = pagination1($total, $pindex, $psize);
    } else {
        $list = pdo_fetchall("select co.isshare,co.commission, co.createtime, og.orderid, og.goodsid, og.total,oo.ordersn from " . tablename('bj_qmxk_commission') . " as co left join " . tablename('bj_qmxk_order_goods') . " as og on co.ogid = og.id and co.weid = og.weid left join " . tablename('bj_qmxk_order') . " as oo on oo.id = og.orderid and co.weid = og.weid where co.mid =" . $profile['id'] . " and co.flag = 0 ORDER BY co.createtime DESC limit 10");
    }
    $addresss = pdo_fetchall("select id, realname from " . tablename('bj_qmxk_address') . " where weid = " . $_W['weid']);
    $address = array();
    foreach ($addresss as $adr) {
        $address[$adr['id']] = $adr['realname'];
    }
    $goods = pdo_fetchall("select id, title from " . tablename('bj_qmxk_goods') . " where weid = " . $_W['weid']);
    $good = array();
    foreach ($goods as $g) {
        $good[$g['id']] = $g['title'];
    }
}
if ($op == 'commissionDetail') {
    $condition.= " AND orders.shareid = '" . $profile['id'] . "' AND orders.createtime>=" . $profile['flagtime'] . " AND orders.from_user<>'" . $from_user . "'";
    $list = pdo_fetchall("SELECT orders.*,bjog.status as status1,bjog.commission*bjog.total as commission ,bjog.commission2*bjog.total as commission2 ,bjog.commission3*bjog.total as commission3 FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition ORDER BY orders.status ASC, orders.createtime DESC");
    $list2 = pdo_fetchall("SELECT * FROM " . tablename('paylog') . " WHERE openid='" . $from_user . "' AND type='zhifu' AND weid=" . $_W['weid'] . " ORDER BY plid DESC ");
    include $this->template('page_commissionDetail');
    exit;
}
if ($op == 'commapply') {
    $bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $from_user . "'");
    if (empty($bankcard['bankcard']) || empty($bankcard['banktype'])) {
        message('请先完善银行卡信息！', $this->createMobileUrl('bankcard', array(
            'id' => $bankcard['id'],
            'opp' => 'complated'
        )) , 'error');
    }
    $commissioningpe = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( g.status = 0 ) and o.status >= 3 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commissioningpe)) {
        $commissioningpe = "0.00";
    }
    $zhifucommission = $cfg['zhifuCommission'];
    if ($commissioningpe < $zhifucommission) {
        message('您还未满足打款金额！', referer() , 'error');
    }
    include $this->template('page_commapply');
    exit;
}
if ($op == 'applyed') {
    if ($profile['flag'] == 0) {
        message('申请佣金失败！');
    }
    $isbank = pdo_fetch("select id, bankcard, banktype from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $from_user . "'");
    if (empty($isbank['bankcard']) || empty($isbank['banktype'])) {
        message('请先完善银行卡信息！', $this->createMobileUrl('bankcard', array(
            'id' => $isbank['id'],
            'opp' => 'complated'
        )) , 'error');
    }
    $orders = pdo_fetchall("SELECT g.id,g.commission,g.total,g.createtime FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE o.shareid = " . $profile['id'] . " and o.weid = " . $_W['weid'] . " and ( g.status = 0 ) and o.status >= 3 and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $update = array(
        'status' => 1,
        'applytime' => time()
    );
    $almoney = 0;
    foreach ($orders as $order) {
        if (!empty($order['commission']) && $order['commission'] > 0 && $order['createtime'] >= $profile['flagtime']) {
            pdo_update('bj_qmxk_order_goods', $update, array(
                'id' => $order['id']
            ));
            $almoney = $almoney + ($orders['commission'] * $orders['total']);
        }
    }
    $tagent = $this->getMember($this->getShareId());
    $this->sendyjsqtz($almoney, $profile['realname'], $tagent['from_user']);
    message('申请成功！', $this->createMobileUrl('commission') , 'success');
}
include $this->template('page_commission'); 
?> 