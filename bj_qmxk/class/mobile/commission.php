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
//:TODO  如果该用户正有  正在审核的  提现申请    那么我的 提现金额  怎么变化
$weid = $_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$cfg = $this->module['config'];
$id = $profile['id'];
if (intval($profile['id']) && $profile['status'] == 0) {
    include $this->template('forbidden');
    exit;
}
//查询可提现金额
//自由余额
$freeSalary=pdo_fetchcolumn("select  credit3  from  " . tablename('fans') . "  where  weid = ".$_W['weid']." and from_user = '".$from_user."' ");
if(empty($freeSalary)){
	$freeSalary="0.00";
}



if ($cfg['globalCommissionLevel'] < 2) {
    $level2enable = ' and 1!=1 ';
}
if ($cfg['globalCommissionLevel'] < 3) {
    $level3enable = ' and 1!=1 ';
}

if ($op == 'display') {
	//不可支配薪资
	$nogovernSalary=pdo_fetchcolumn("select  credit4  from  " . tablename('fans') . "  where  weid = ".$_W['weid']." and from_user = '".$from_user."' ");
	if(empty($nogovernSalary)){
		$nogovernSalary="0.00";
	}
	//代理薪资
	$agencySalary=pdo_fetchcolumn("select  sum(money)   from  " . tablename('bj_qmxk_relation_dividend') . "  where  weid = ".$_W['weid']." and from_user = '".$from_user."' ");
	if(empty($agencySalary)){
		$agencySalary="0.00";
	}
	//推广薪资
 	$completedCommission = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and o.status =3  and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $completedCommissionx2 = pdo_fetchcolumn("SELECT sum((g.commission2*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid2 = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and o.status =3 $level2enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $completedCommissionx3 = pdo_fetchcolumn("SELECT sum((g.commission3*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid3 = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and o.status =3 $level3enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($completedCommission)) {
        $completedCommission = 0;
    }
    if (empty($completedCommissionx2)) {
        $completedCommissionx2 = 0;
    }
    if (empty($completedCommissionx3)) {
        $completedCommissionx3 = 0;
    }
    $completedCommission = $completedCommission + $completedCommissionx2 + $completedCommissionx3;
    if ($completedCommission == 0) {
        $completedCommission = "0.00";
    }
    
    $expandSalary = $completedCommission + $completedCommissionx2 + $completedCommissionx3;
    if ($expandSalary == 0) {
        $expandSalary = "0.00";
    }
    
	//关注薪资
	$subscribeSalary=pdo_fetchcolumn("select  sum(money)   from  " . tablename('bj_qmxk_subscribe_dividend') . "  where  weid = ".$_W['weid']." and from_user = '".$from_user."' ");
	if(empty($subscribeSalary)){
		$subscribeSalary="0.00";
	}
  	//订单已完成的返点金额
    $totalCompletedRebate=pdo_fetchcolumn('select sum(rebates_money)  from  ' . tablename('bj_qmxk_order') . " where  from_user='".$from_user."' and weid=". $_W["weid"]." and  status =3");
    if(empty($totalCompletedRebate)){
    	$totalCompletedRebate="0.00";
    }
	
	//累计收入
	$totalSalary=$agencySalary+$expandSalary+$subscribeSalary+$totalCompletedRebate;
	
	//未审核薪资
	$unauditedSalary=pdo_fetchcolumn("select  sum(commission)   from  " . tablename('bj_qmxk_salary') . "  where  weid = ".$_W['weid']." and
			 from_user = '".$from_user."'  and  status=0");
	if(empty($unauditedSalary)){
		$unauditedSalary="0.00";
	}
	//已审核薪资
	$auditedSalary=pdo_fetchcolumn("select  sum(commission)   from  " . tablename('bj_qmxk_salary') . "  where  weid = ".$_W['weid']." and
			 from_user = '".$from_user."'  and  status=1");
	if(empty($auditedSalary)){
		$auditedSalary="0.00";
	}
	
	//无效薪资
	$invalidSalary=pdo_fetchcolumn("select  sum(commission)   from  " . tablename('bj_qmxk_salary') . "  where  weid = ".$_W['weid']." and
			 from_user = '".$from_user."'  and  status=-1");
	if(empty($invalidSalary)){
		$invalidSalary="0.00";
	}
	
	//已打款薪资
	$paidSalary=pdo_fetchcolumn("select  sum(commission)   from  " . tablename('bj_qmxk_salary') . "  where  weid = ".$_W['weid']." and
			 from_user = '".$from_user."'  and  status=2");
	if(empty($paidSalary)){
		$paidSalary="0.00";
	}
	
	include $this->template('page_commission');
	//exit();
}




if ($op == 'commissionDetail') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 30;
    $action=$_GPC["action"];
   if($action=="agency"){
   		//代理薪资
   		$title="代理薪资";
   		$list=pdo_fetchall("select  *   from  " . tablename('bj_qmxk_relation_dividend') . "  d  left join  " . tablename('bj_qmxk_medal') . " m  on d.medal_id = m.id where d.weid = ".$_W['weid']." and d.from_user = '".$from_user."' ORDER BY  d.add_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize );
   		$listx=pdo_fetchall("select  *   from  " . tablename('bj_qmxk_relation_dividend') . "  d  left join  " . tablename('bj_qmxk_medal') . " m   on d.medal_id = m.id where d.weid = ".$_W['weid']." and d.from_user = '".$from_user."' ORDER BY  d.add_time DESC ");
   }elseif($action=="expand"){
   		//推广薪资
   	$title="推广薪资";
    $condition = " and orders.status =3 ";
    $condition1 = $condition . " AND (orders.shareid = '" . $profile['id'] . "') AND orders.createtime>=" . $profile['flagtime'] . " AND orders.from_user<>'" . $from_user . "'";
    $condition2 = $condition . " AND (orders.shareid2 = '" . $profile['id'] . "') AND orders.createtime>=" . $profile['flagtime'] . " $level2enable AND orders.from_user<>'" . $from_user . "'";
    $condition3 = $condition . " AND (orders.shareid3 = '" . $profile['id'] . "') AND orders.createtime>=" . $profile['flagtime'] . " $level3enable AND orders.from_user<>'" . $from_user . "'";
	
    $conditionMember = "select m.realname from " . tablename('bj_qmxk_member') . " m where m.from_user=orders.from_user and m.weid=" . $_W['weid'];
    $list = pdo_fetchall("SELECT 1 as level,orders.status,orders.createtime,orders.ordersn,bjog.status as status1,bjog.commission*bjog.total as commission,( $conditionMember) realname FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition1 union all (SELECT 2 as level,orders.status,orders.createtime,orders.ordersn,bjog.status2 as status1,bjog.commission2*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id  WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition2) union all(SELECT 3 as level,orders.status,orders.createtime,orders.ordersn,bjog.status3 as status1,bjog.commission3*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id   WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition3) ORDER BY  createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $listx = pdo_fetchall("SELECT 1 as level,orders.status,orders.createtime,orders.ordersn,bjog.status as status1,bjog.commission*bjog.total as commission,( $conditionMember) realname FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition1 union all (SELECT 2 as level,orders.status,orders.createtime,orders.ordersn,bjog.status2 as status1,bjog.commission2*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id  WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition2) union all(SELECT 3 as level,orders.status,orders.createtime,orders.ordersn,bjog.status3 as status1,bjog.commission3*bjog.total as commission,( $conditionMember) realname  FROM " . tablename('bj_qmxk_order') . " orders left join  " . tablename('bj_qmxk_order_goods') . " bjog on bjog.orderid=orders.id   WHERE  orders.weid = '{$_W['weid']}' and bjog.commission!=0 $condition3) ");
    }elseif($action=="subscribe"){
   		//关注薪资
   	$title="关注薪资";
   	$list=pdo_fetchall("select  *   from  " . tablename('bj_qmxk_subscribe_dividend') . " d left join  " . tablename('bj_qmxk_order') . "  o  on  o.id=d.order_id  where d. weid = ".$_W['weid']." and d.from_user = '".$from_user."' ORDER BY  d.add_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize );
   	$listx=pdo_fetchall("select  *   from  " . tablename('bj_qmxk_subscribe_dividend') . " d left join  " . tablename('bj_qmxk_order') . "  o  on  o.id=d.order_id  where d. weid = ".$_W['weid']." and d.from_user = '".$from_user."' ORDER BY  d.add_time DESC ");
   }elseif($action=="withdraw"){
   	//提现记录
   	$title="提现记录";
   	$list=pdo_fetchall("select * from  " . tablename('bj_qmxk_salary') . " where weid=".$_W['weid']." and from_user='".$from_user."'  ORDER BY  createtime  DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
   	$listx=pdo_fetchall("select * from  " . tablename('bj_qmxk_salary') . " where weid=".$_W['weid']." and from_user='".$from_user."'  ORDER BY  createtime  DESC");
   }elseif($action=="consumerRebates"){
   	//消费返点
   	$title="消费返点";
   	$list=pdo_fetchall("select * from  " . tablename('bj_qmxk_order') . " where weid=".$_W['weid']."  and from_user='".$from_user."'  and  status=3  ORDER BY  createtime  DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
   	$listx=pdo_fetchall("select * from  " . tablename('bj_qmxk_order') . " where weid=".$_W['weid']."  and from_user='".$from_user."'  and  status=3  ORDER BY  createtime  DESC");
   }
   
   $total = sizeof($listx);
   
   $pager = pagination($total, $pindex, $psize);
   
    //$list2 = pdo_fetchall("SELECT * FROM " . tablename('paylog') . " WHERE openid='" . $from_user . "' AND type='zhifu' AND weid=" . $_W['weid'] . " ORDER BY plid DESC ");
    
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

    $zhifucommission = $cfg['zhifuCommission'];
    if ($freeSalary < $zhifucommission) {
        message('您还未满足打款金额！',$this->createMobileUrl("fansindex") , 'error');
    }
   $is_apply= pdo_fetchcolumn("select  id  from   " . tablename('bj_qmxk_salary') . "  where weekofyear(FROM_UNIXTIME(`createtime`,'%Y-%m-%d')) = weekofyear(curdate())");
  	if($is_apply){
  		message('对不起您本周已经申请提现！（一周之内只能申请一次提现，再次申请必须等到下一周的任意一天，一周为周一至周日。）',$this->createMobileUrl("fansindex") , 'error');
  	}
    include $this->template('page_commapply');
    exit;
}
if ($op == 'applyed') {
	$zhifucommission = $cfg['zhifuCommission'];
    if ($profile['flag'] == 0) {
        message('对不起您还不是'.$cfg['agent_title'].'！暂时还不能提现');
    }
    $is_apply= pdo_fetchcolumn("select  id  from   " . tablename('bj_qmxk_salary') . "  where weekofyear(FROM_UNIXTIME(`createtime`,'%Y-%m-%d')) = weekofyear(curdate())");
    if($is_apply){
    	message('对不起您本周已经申请提现！（一周之内只能申请一次提现，再次申请必须等到下一周的任意一天，一周为周一至周日。）');
    }
    
    //输入的提现金额不能大于可提现金额
  	$withdrawal_amount= $_GPC["withdrawal_amount"];
  	if($withdrawal_amount>$freeSalary){
  		message('输入的提现金额不能大于提现余额！');
  	}
  	if ($withdrawal_amount%$zhifucommission!=0) {
  		message('输入的提现金额必须为'.$zhifucommission.'的倍数！',$this->createMobileUrl("fansindex") , 'error');
  	}
    $isbank = pdo_fetch("select id, bankcard, banktype from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $from_user . "'");
    if (empty($isbank['bankcard']) || empty($isbank['banktype'])) {
        message('请先完善银行卡信息！', $this->createMobileUrl('bankcard', array(
            'id' => $isbank['id'],
            'opp' => 'complated'
        )) , 'error');
    }
    $data=array(
    		'weid'=>$_W["weid"],
    		'mid'=>$id,
    		'commission'=>$withdrawal_amount,
    		'content'=>"现在在测试阶段",
    		'isout'=>0,
    		'createtime'=>time(),
    		'status'=>0,
    		'from_user'=>$from_user
    );
    pdo_insert("bj_qmxk_salary",$data);
    if(pdo_insertid()){
    	//减余额
    	pdo_query("update " . tablename('fans') . " SET credit3=credit3-" . $withdrawal_amount  . "
					  WHERE
						 from_user='" . $from_user . "' AND  weid=" . $_W['weid'] . "  ");
    }
//     if($result["return_code"]=="SUCCESS" && $result["result_code"]=="SUCCESS"){ //发放成功
		
//     }
   message('申请成功！', $this->createMobileUrl('commission') , 'success');
}

 ?> 