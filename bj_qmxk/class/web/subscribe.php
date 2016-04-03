<?php
// tpl_form_field_image();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = 'and  1=1 ';
	if (!empty($_GPC['keyword'])) {
		$keyword=$_GPC["keyword"];
		$condition.= " AND realname LIKE '%{$_GPC['keyword']}%'";
	}
	$param_ordersn = $_GPC['ordersn'];
	if (!empty($_GPC['ordersn'])) {
		$ordersn=$_GPC['ordersn'];
		$condition.= " AND ordersn LIKE '%{$_GPC['ordersn']}%'";
	}
	if (!empty($_GPC['add_time'])) {
		$condition.= " AND TO_DAYS(add_time)=TO_DAYS('".$_GPC['add_time']."')";
		$add_time=$_GPC['add_time'];
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_subscribe_dividend')." sd 
    		left join ".tablename('bj_qmxk_member')." m on m.from_user=sd.from_user
    		left join ".tablename('bj_qmxk_order')." o on o.id = sd.order_id
    		 WHERE  sd. weid = '{$_W['weid']}'  $condition ORDER BY add_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM  ".tablename('bj_qmxk_subscribe_dividend')." sd 
    		left join ".tablename('bj_qmxk_member')." m on m.from_user=sd.from_user
    		left join ".tablename('bj_qmxk_order')." o on o.id = sd.order_id
    		 WHERE  sd. weid = '{$_W['weid']}'  $condition ORDER BY add_time DESC");
	$pager = pagination($total, $pindex, $psize);
	
	include $this->template('subscribe');
	exit();
} 

?>