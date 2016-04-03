<?php
$this->validateopenid();
$weid = $_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$cfg = $this->module['config'];

$this->autoRegedit('fansindex');

$profile = $this->getProfile();

$id = $profile['id'];
if (intval($profile['id']) && $profile['status'] == 0) {
	include $this->template('forbidden');
	die;
}


$ushareid = $this->getShareId();
if (!empty($ushareid) && $ushareid != 0) {
    $shareprofile = $this->getMember($ushareid);
}
if (!empty($profile['id'])) {
    $count = 0;
    if (true) {
        $sql1_member = 'select mber1.from_user from ' . tablename('bj_qmxk_member') . ' mber1 where mber1.realname<>\'\' and mber1.id!=mber1.shareid and mber1.shareid = ' . $profile['id'];
        //1级粉丝(非代理)人数
        $count1 = pdo_fetchcolumn('	select count(*) from ' . tablename('bj_qmxk_member') . " fans where from_user!='{$from_user}' and ( fans.from_user in (" . $sql1_member . " and mber1.flag=0)) and fans.weid={$_W['weid']}");
        //1级代理人数
        $count1_1 = pdo_fetchcolumn('	select count(*) from ' . tablename('bj_qmxk_member') . " fans where from_user!='{$from_user}' and ( fans.from_user in (" . $sql1_member . " and mber1.flag=1)) and fans.weid={$_W['weid']}");
        //1级贡献的销售额
        $price1_1 = pdo_fetchcolumn('SELECT sum((o.price)) FROM ' . tablename('bj_qmxk_order') . ' as o left join ' . tablename('bj_qmxk_order_goods') . ' as g on o.id = g.orderid and o.weid = g.weid WHERE o.from_user in (' . $sql1_member . ') and o.weid = ' . $_W['weid'] . ' and o.status >=1 and o.from_user != \'' . $from_user . '\' and  g.createtime>=' . $profile['flagtime']);
    }
    if (true && $cfg['globalCommissionLevel'] >= 2) {
        $level2 = 'select level2m.id from ' . tablename('bj_qmxk_member') . ' level2m where level2m.id!=level2m.shareid and  level2m.shareid = ' . $profile['id'];
        $sql2_member = 'select mber2.from_user from ' . tablename('bj_qmxk_member') . ' mber2 where mber2.realname<>\'\' and mber2.id!=mber2.shareid and mber2.shareid in (' . $level2 . ')  ';
        //2级粉丝(非代理)人数
        $count2 = pdo_fetchcolumn('	select count(*) from ' . tablename('bj_qmxk_member') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . '  and mber2.flag=0)) and ( fans.from_user not in (' . $sql1_member . "   and mber1.flag=0) ) and fans.weid={$_W['weid']}");
        //2级代理人数
        $count2_1 = pdo_fetchcolumn('	select count(*) from ' . tablename('bj_qmxk_member') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . '  and mber2.flag=1)) and ( fans.from_user not in (' . $sql1_member . "   and mber1.flag=1) ) and fans.weid={$_W['weid']}");
        $price2_1 = pdo_fetchcolumn('SELECT sum((o.price)) FROM ' . tablename('bj_qmxk_order') . ' as o left join ' . tablename('bj_qmxk_order_goods') . ' as g on o.id = g.orderid and o.weid = g.weid WHERE o.from_user in (' . $sql2_member . ') and o.shareid not in (' . $sql1_member . ') and o.weid = ' . $_W['weid'] . ' and o.status >=1  and o.from_user != \'' . $from_user . '\' and  g.createtime>=' . $profile['flagtime']);
    } else {
        $str = 0;
    }
    if (true && $cfg['globalCommissionLevel'] >= 3) {
        $level3 = 'select level3m.id from ' . tablename('bj_qmxk_member') . ' level3m where level3m.id!=level3m.shareid and level3m.shareid in( ' . $level2 . ')';
        $sql3_member = 'select mber3.from_user from ' . tablename('bj_qmxk_member') . ' mber3 where mber3.realname<>\'\' and mber3.id!=mber3.shareid and mber3.shareid in (' . $level3 . ')  ';
        $count3 = pdo_fetchcolumn('	select count(*) from ' . tablename('bj_qmxk_member') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql3_member . '  and mber3.flag=0)) and (fans.from_user not in (' . $sql1_member . '   and mber1.flag=0)) and (fans.from_user not in  (' . $sql2_member . "   and mber2.flag=0)) and fans.weid={$_W['weid']}");
        $count3_1 = pdo_fetchcolumn('	select count(*) from ' . tablename('bj_qmxk_member') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql3_member . '  and mber3.flag=1)) and (fans.from_user not in (' . $sql1_member . '   and mber1.flag=1)) and (fans.from_user not in  (' . $sql2_member . "   and mber2.flag=1)) and fans.weid={$_W['weid']}");
        $price3_1 = pdo_fetchcolumn('SELECT sum((o.price)) FROM ' . tablename('bj_qmxk_order') . ' as o left join ' . tablename('bj_qmxk_order_goods') . ' as g on o.id = g.orderid and o.weid = g.weid WHERE o.from_user in (' . $sql3_member . ') and o.from_user not in (' . $sql2_member . ') and o.from_user not in (' . $sql1_member . ') and o.weid = ' . $_W['weid'] . ' and o.status >=1 and o.from_user != \'' . $from_user . '\' and  g.createtime>=' . $profile['flagtime']);
    } else {
        $str3 = 0;
    }
    $count = $count1 + $count2 + $count3;
    $count = $count + $count1_1 + $count2_1 + $count3_1;
    $priceTotal = $price1_1 + $price2_1 + $price3_1;

	
    $clickcount = $profile['clickcount'];
    $sql1_member = 'select mber1.from_user from ' . tablename('bj_qmxk_member') . ' mber1 where mber1.id!=mber1.shareid and mber1.shareid = ' . $profile['id'];
    $followcount = pdo_fetchcolumn('	select count(fans.id) from ' . tablename('fans') . " fans where fans.follow=1 and from_user!='{$from_user}' and ( fans.from_user in (" . $sql1_member . ") ) and fans.weid={$_W['weid']}");


//余额
 $myCredit= pdo_fetch('	select *  from ' . tablename('fans') . " where from_user='".$from_user."' and weid=". $_W["weid"]);
//收入


 if ($cfg['globalCommissionLevel'] < 2) {
 	$level2enable = ' and 1!=1 ';
 }
 if ($cfg['globalCommissionLevel'] < 3) {
 	$level3enable = ' and 1!=1 ';
 }
	 //我得到的分销佣金(订单已完成)
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
    
    //我得到的分销佣金(订单已付款但未完成的)
    //已付款
    $commission5_1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =1)) or ((o.paytype=1 or o.paytype=2 ) and o.status =1)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $commission5_1x2 = pdo_fetchcolumn("SELECT sum((g.commission2*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid2 = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =1)) or ((o.paytype=1 or o.paytype=2 ) and o.status =1)) $level2enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $commission5_1x3 = pdo_fetchcolumn("SELECT sum((g.commission3*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid3 = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =1)) or ((o.paytype=1 or o.paytype=2 ) and o.status =1)) $level3enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission5_1)) {
    	$commission5_1 = 0;
    }
    if (empty($commission5_1x2)) {
    	$commission5_1x2 = 0;
    }
    if (empty($commission5_1x3)) {
    	$commission5_1x3 = 0;
    }
    $commission5_1 = $commission5_1 + $commission5_1x2 + $commission5_1x3;
    if ($commission5_1 == 0) {
    	$commission5_1 = "0.00";
    }
    
    //已收货
    $commission6_1 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =2)) or ((o.paytype=1 or o.paytype=2 ) and o.status =2)) and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $commission6_1x2 = pdo_fetchcolumn("SELECT sum((g.commission2*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid2 = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =2)) or ((o.paytype=1 or o.paytype=2 ) and o.status =2)) $level2enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $commission6_1x3 = pdo_fetchcolumn("SELECT sum((g.commission3*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE (o.shareid3 = " . $profile['id'] . ") and o.weid = " . $_W['weid'] . " and ( (o.paytype=3 and (o.status =2)) or ((o.paytype=1 or o.paytype=2 ) and o.status =2)) $level3enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission6_1)) {
    	$commission6_1 = 0;
    }
    if (empty($commission6_1x2)) {
    	$commission6_1x2 = 0;
    }
    if (empty($commission6_1x3)) {
    	$commission6_1x3 = 0;
    }
    $commission6_1 = $commission6_1 + $commission6_1x2 + $commission6_1x3;
    if ($commission6_1 == 0) {
    	$commission6_1 = "0.00";
    }
    //已退货
    $commission3 = pdo_fetchcolumn("SELECT sum((g.commission*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE ((o.shareid = " . $profile['id'] . " and g.status = -1) ) and o.weid = " . $_W['weid'] . "  and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $commission3x2 = pdo_fetchcolumn("SELECT sum((g.commission2*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE ((o.shareid2 = " . $profile['id'] . " and g.status2 = -1) ) and o.weid = " . $_W['weid'] . "   $level2enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    $commission3x3 = pdo_fetchcolumn("SELECT sum((g.commission3*g.total)) FROM " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid WHERE ((o.shareid3 = " . $profile['id'] . " and g.status3 = -1) ) and o.weid = " . $_W['weid'] . "   $level3enable and o.from_user != '" . $from_user . "' and  g.createtime>=" . $profile['flagtime']);
    if (empty($commission3)) {
    	$commission3 = 0;
    }
    if (empty($commission3x2)) {
    	$commission3x2 = 0;
    }
    if (empty($commission3x3)) {
    	$commission3x3 = 0;
    }
    $commission3 = $commission3 + $commission3x2 + $commission3x3;
    if ($commission3 == 0) {
    	$commission3 = "0.00";
    }
    
    //订单已付款但未完成的返点金额
    $totalUnfinishedRebate=pdo_fetchcolumn('select sum(rebates_money)  from  ' . tablename('bj_qmxk_order') . " where  from_user='".$from_user."' and weid=". $_W["weid"]." and  (status =1 or  status=2)");
    
    //订单已完成的返点金额
    $totalCompletedRebate=pdo_fetchcolumn('select sum(rebates_money)  from  ' . tablename('bj_qmxk_order') . " where  from_user='".$from_user."' and weid=". $_W["weid"]." and  status =3");
    
//分红佣金
 $dividend= pdo_fetchcolumn('	select  sum(money)  from ' . tablename('bj_qmxk_relation_dividend') . " where  from_user='".$from_user."' and weid=". $_W["weid"]);
if(!$dividend){
	$dividend=0.00;
}
//累计收入（已完成佣金+分红+消费返点）
 $income=$completedCommission+$dividend+$totalCompletedRebate;
 //待确定积分（未完成佣金+消费返点）
 $unidentified =$commission5_1+$commission6_1+$commission3+$totalUnfinishedRebate;
 
 if(empty($unidentified)){
 	$unidentified=0.00;
 }
 
 //团队销售额=个人的消费总额+下级消费总额(已付款的订单)
 //获取总消费金额
 //总消费金额
 $totalxiaofeiMoney=pdo_fetchcolumn("select sum(price) from " . tablename('bj_qmxk_order') ."  where from_user='".$from_user."' and weid= ".$_W['weid']." and  status=3");
 if(empty($totalxiaofeiMoney)){ 
 	$totalxiaofeiMoney = 0.00;
 }else{
 	$totalxiaofeiMoney=sprintf("%.2f", $totalxiaofeiMoney);
 }
 
 $teamMoney=$priceTotal+$totalxiaofeiMoney;
 
 
 
} else {
    $count = 0;
    $followcount = 0;
}

$myheadimg = pdo_fetch('SELECT * FROM ' . tablename('fans') . ' WHERE  weid = :weid  AND from_user = :from_user LIMIT 1', array(':weid' => $_W['weid'], ':from_user' => $from_user));

//$this->memberQrcode($from_user);


//优惠等级
if (!empty($profile)){
  $userx = pdo_fetch("select a.*,b.levelname,b.discount from ".tablename('bj_qmxk_member')." a left join  ".tablename('bj_qmxk_memberlevel').
  " b on a.memberlevel=b.id where   a.weid = :weid  AND a.from_user = :from_user", array(':weid' => $_W['weid'],':from_user' => $from_user));	
}


$userx = pdo_fetch("select a.*,b.levelname,b.discount from ".tablename('bj_qmxk_member')." a left join  ".tablename('bj_qmxk_memberlevel').
" b on a.memberlevel=b.id where   a.weid = :weid  AND a.from_user = :from_user", array(':weid' => $_W['weid'],':from_user' => $from_user));
$commtime = pdo_fetch('select promotercount,promotermoney,promotertimes from ' . tablename('bj_qmxk_rules') . ' where weid = ' . $_W['weid']);
$total = pdo_fetchcolumn('SELECT count(id) FROM ' . tablename('bj_qmxk_order') . ' WHERE status= \'3\' AND  weid = :weid  AND from_user = :from_user', array(':weid' => $_W['weid'], ':from_user' => $from_user));
$totalmoney = pdo_fetchcolumn('SELECT sum(price) FROM ' . tablename('bj_qmxk_order') . ' WHERE status= \'3\' AND  weid = :weid  AND from_user = :from_user', array(':weid' => $_W['weid'], ':from_user' => $from_user));
$tmsg = '购买完成一单才能成为'.$this->module['config']['agent_title'];
if ($commtime['promotercount'] > $total && $commtime['promotertimes'] == 2) {
    $tmsg = '购买完成' . ($commtime['promotercount'] - $total) . '个订单才能成为'.$this->module['config']['agent_title'];
}
if ($commtime['promotermoney'] > $totalmoney && $commtime['promotertimes'] == 3) {
    $tmsg = '购买完成' . ($commtime['promotermoney'] - $totalmoney) . '金额的订单才能成为'.$this->module['config']['agent_title'];
}

//已发工资
$allprofit = pdo_fetchcolumn("select sum(profit) from " . tablename('bj_qmxk_profit_member') ." where weid = " . $_W['weid'] . " and from_user ='" . $from_user . "' and ispay=1");
if(!$allprofit) $allprofit = 0.00;

//获取勋章
$medalList=$this->getMedal($from_user);

if (!empty($profile['id']) && $profile['flag'] == 1) {
	$medal_name = $this->module['config']['agent_title']; //代理等级称呼
} else {
	$medal_name = $this->module['config']['noagent_title']; //非代理等级称呼
}
if(count($medalList)>0){//如果获得了勋章, 等级称呼为最高勋章的称呼
	$medal_name = $medalList[0]['medal_name'];
}

include $this->template('newhome');
?>