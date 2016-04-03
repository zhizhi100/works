<?php
/**
推广订单
 */
$cfg = $this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if (!empty($_GPC['shareid'])) {
        $shareid = $_GPC['shareid'];

        if ($cfg['globalCommissionLevel'] < 2) {
        	$level2enable = ' and 1!=1 ';
        }
        if ($cfg['globalCommissionLevel'] < 3) {
        	$level3enable = ' and 1!=1 ';
        }
        
        
        $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $shareid . " and weid = " . $_W['weid']);
        $condition = " and orders.status >= 0 ";
        $condition1 = $condition . " AND (orders.shareid = '" . $shareid . "') AND orders.createtime>=" . $user['flagtime'] . " AND orders.from_user<>'" . $user['from_user'] . "'";
        $condition2 = $condition . " AND (orders.shareid2 = '" .$shareid . "') AND orders.createtime>=" . $user['flagtime'] . " $level2enable AND orders.from_user<>'" . $user['from_user'] . "'";
        $condition3 = $condition . " AND (orders.shareid3 = '" . $shareid . "') AND orders.createtime>=" . $user['flagtime'] . " $level3enable AND orders.from_user<>'" . $user['from_user'] . "'";
        
        $conditionMember = "select m.realname from " . tablename('bj_qmxk_member') . " m where m.from_user=orders.from_user and m.weid=" . $_W['weid'];
        $list = pdo_fetchall("SELECT 1 as level,orders.status,orders.createtime,orders.ordersn,bjog.status as status1,orders.id,orders.price,orders.dispatch,orders.addressid,orders.paytype,
        		bjog.commission*bjog.total as commission,( $conditionMember) realname 
        		FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition1 union all (SELECT 2 as level,orders.status,orders.createtime,orders.ordersn,bjog.status2 as status1,orders.id,orders.price,orders.dispatch,orders.addressid,orders.paytype,bjog.commission2*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id  WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition2) union all(SELECT 3 as level,orders.status,orders.createtime,orders.ordersn,bjog.status3 as status1,orders.id,orders.price,orders.dispatch,orders.addressid,orders.paytype,bjog.commission3*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id   WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition3) ORDER BY  createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

        
        
        $listx = pdo_fetchall("SELECT 1 as level,orders.status,orders.createtime,orders.ordersn,bjog.status as status1,bjog.commission*bjog.total as commission,( $conditionMember) realname FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition1 union all (SELECT 2 as level,orders.status,orders.createtime,orders.ordersn,bjog.status2 as status1,bjog.commission2*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id  WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition2) union all(SELECT 3 as level,orders.status,orders.createtime,orders.ordersn,bjog.status3 as status1,bjog.commission3*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id   WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition3) ");
        $total=sizeof($listx);
        $pager = pagination($total, $pindex, $psize);
        if (!empty($list)) {
        	foreach ($list as $key => $l) {
        		$commission = pdo_fetch("select total,sum(commission) as commission, sum(commission2) as commission2, sum(commission3) as commission3 from " . tablename('bj_qmxk_order_goods') . " where orderid = " . $l['id']);
        		$list[$key]['commission'] = $commission['commission'] * $commission['total'];
        		if ($cfg['globalCommissionLevel'] >= 2) {
        			$list[$key]['commission2'] = $commission['commission2'] * $commission['total'];
        		} else {
        			$list[$key]['commission2'] = 0;
        		}
        		if ($cfg['globalCommissionLevel'] >= 3) {
        			$list[$key]['commission3'] = $commission['commission3'] * $commission['total'];
        		} else {
        			$list[$key]['commission3'] = 0;
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
    }
}
include $this->template('expandorder'); 
?>