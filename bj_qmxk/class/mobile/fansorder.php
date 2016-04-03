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
$id = $profile['id'];
if (intval($profile['id']) && $profile['status'] == 0) {
    include $this->template('forbidden');
    exit;
}
if (empty($profile)) {
    message('请先注册', $this->createMobileUrl('register') , 'error');
    exit;
}
// $status = 0;
$condition = '';
// $condition.= " AND status >= '" . intval($status) . "'";
$condition.= " AND status>=1";
$user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $profile['id'] . " and weid = " . $_W['weid']);
$condition.= " AND (shareid = '" . $profile['id'] . "'  or   shareid2 = '" . $profile['id'] . "'  or  shareid3 = '" . $profile['id'] . "')  AND createtime>=" . $profile['flagtime'] . " AND from_user<>'" . $from_user . "'";
$list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' $condition ORDER BY status ASC, createtime DESC limit 20");
$allcount = pdo_fetchcolumn("SELECT count(id) FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' $condition  ORDER BY status ASC, createtime DESC");
$countYestay = pdo_fetchcolumn("SELECT count(id) FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' $condition and createtime>=" . strtotime(date('Y-m-d 00:00:00', strtotime('-1 day'))) . " and createtime<=" . strtotime(date('Y-m-d 23:59:59', strtotime('-1 day'))) . "  ORDER BY status ASC, createtime DESC");
$countToday = pdo_fetchcolumn("SELECT count(id) FROM " . tablename('bj_qmxk_order') . " WHERE weid = '{$_W['weid']}' $condition and createtime>=" . strtotime(date('Y-m-d 00:00:00', strtotime('0 day'))) . " and createtime<=" . strtotime(date('Y-m-d 23:59:59', strtotime('0 day'))) . "  ORDER BY status ASC, createtime DESC");
if (!empty($list)) {
    foreach ($list as $key => $l) {
    	$list[$key]["order_realname"]=pdo_fetchcolumn("select realname from " . tablename('bj_qmxk_member') . " where from_user='".$list[$key]['from_user']."'  and weid = " . $_W['weid']);
        $commission = pdo_fetchall("select goodsid,total,commission, commission2, commission3 from " . tablename('bj_qmxk_order_goods') . " where orderid = " . $l['id']);
        for($i=0;$i<count($commission);$i++){
        	$goods_id=$commission[$i]['goodsid'];
        	$goods = pdo_fetch("select title,thumb from " . tablename('bj_qmxk_goods') . " where id = " . $goods_id);

        	$list[$key]["goods"][$i]['thumb'] = $goods['thumb'];
        	$list[$key]["goods"][$i]['title'] = $goods['title'];
        	$list[$key]["goods"][$i]['goodsid'] = $commission['goodsid'];
        	$list[$key]["goods"][$i]['commission'] = $commission[$i]['commission'] * $commission[$i]['total'];
        	if ($cfg['globalCommissionLevel'] >= 2) {
        		$list[$key]["goods"][$i]['commission2'] = $commission[$i]['commission2'] * $commission[$i]['total'];
        	} else {
        	$list[$key]["goods"][$i]['commission2'] = 0;
        	}
        	if ($cfg['globalCommissionLevel'] >= 3) {
        		$list[$key]["goods"][$i]['commission3'] = $commission[$i]['commission3'] * $commission[$i]['total'];
        	} else {
        		$list[$key]["goods"][$i]['commission3'] = 0;
        	}
        }
      
 
    }
}
if (!empty($list)) {
    foreach ($list as & $row) {
        !empty($row['addressid']) && $addressids[$row['addressid']] = $row['addressid'];
        $row['dispatch'] = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_dispatch') . " WHERE id = :id", array(
            ':id' => $row['dispatch']
        ));
    }
    unset($row);
}
if (!empty($addressids)) {
    $address = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_address') . " WHERE id IN ('" . implode("','", $addressids) . "')", array() , 'id');
}
include $this->template('fansorder'); 
?>
