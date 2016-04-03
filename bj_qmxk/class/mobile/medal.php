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
$from_user=$this->getFromUser();
if (intval($profile['id']) && $profile['status'] == 0) {
    include $this->template('forbidden');
    exit;
}
if ($op == 'display') {
	$medal_info=$this->getMedal($from_user);
	for($i=0;$i<count($medal_info);$i++){
		$medal_id=$medal_info[$i]["medal_id"];
		$medal_info[$i]["money"]=pdo_fetchcolumn("select sum(d.money) as money from 
				" . tablename('bj_qmxk_relation_dividend') ."  d
				left join " . tablename('bj_qmxk_order') ." o  on o.id=d.order_id
				where d.medal_id=".$medal_id." and o.status>=3
				and d.d_status<>-1 and d.from_user='".$from_user."' and d.weid=$weid
				");
	} 
	$total_money=pdo_fetchcolumn("select sum(d.money) as money from " . tablename('bj_qmxk_relation_dividend') ." d
left join " . tablename('bj_qmxk_order') ." o  on o.id=d.order_id
			where 
			 d.d_status<>-1 and d.from_user='".$from_user."' and d.weid=$weid and o.status>=3
				");
	$already_money=pdo_fetchcolumn("select sum(d.money) as money from " . tablename('bj_qmxk_relation_dividend') ."  d 
			left join " . tablename('bj_qmxk_order') ." o  on o.id=d.order_id
			where
			d.d_status=2 and d.from_user='".$from_user."' and d.weid=$weid and o.status>=3
			");
	include $this->template('page_medal');
	exit();
}


if ($op == 'dividendDetail') {
$medal_id=$GPC["medal_id"];
if($medal_id){
	$str ="and d.medal_id=".$medal_id;
}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	  $list = pdo_fetchall("select o.ordersn,m.medal_name,d.order_money,d.money,d.add_time,d.d_status from  " . tablename('bj_qmxk_relation_dividend') ."  d 
	  		left join  " . tablename('bj_qmxk_order') ." o  on o.id=d.order_id
	  		left join  " . tablename('bj_qmxk_medal') ."  m on  m.id=d.medal_id where m.medal_status=1  and 
	  		d.from_user='".$from_user."' and m.weid=$weid and o.status>=3  $str 
	  		LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$total = sizeof($list);
	//$pager = pagination2($total, $pindex, $psize);
	$pager = pagination($total, $pindex, $psize);
	$list2 = pdo_fetchall("select * from ". tablename('bj_qmxk_fh_commission')."  where
			 weid=$weid and from_user='".$from_user."' ");

	include $this->template('page_dividendDetail');
	exit;
}
?>