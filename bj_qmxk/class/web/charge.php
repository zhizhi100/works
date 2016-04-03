<?php
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$weid = $_W['weid'];
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    if ($_GPC['submit'] == '搜索') {
        $gpmobile = $_GPC['mobile'];
        $gprealname = $_GPC['realname'];
        $list = pdo_fetchall("SELECT * FROM " . tablename('fans') . "  WHERE weid=" . $_W['weid'] . "   AND (mobile like '%" . $gpmobile . "%' and realname like '%" . $gprealname . "%')  and realname<>'' ORDER BY id DESC");
        $total = pdo_fetchcolumn("SELECT  COUNT(*) FROM " . tablename('fans') . "  WHERE weid=" . $_W['weid'] . "   AND (mobile like '%" . $gpmobile . "%' and realname like '%" . $gprealname . "%')   and realname<>''    ORDER BY id DESC");
        include $this->template('charge');
        exit();
    }
    if (intval($_GPC['so']) == 1) {
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('fans') . " WHERE weid = :weid    and realname<>''  ", array(
            ':weid' => $_W['weid']
        ));
        $pager = pagination($total, $pindex, $psize);
        $list = pdo_fetchall("SELECT * FROM " . tablename('fans') . "  WHERE weid=" . $_W['weid'] . "   and realname<>''   ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
    } else {
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('fans') . " WHERE `weid` = :weid   and realname<>''  ", array(
            ':weid' => $_W['weid']
        ));
        $pager = pagination($total, $pindex, $psize);
        $list = pdo_fetchall("SELECT * FROM " . tablename('fans') . " WHERE weid=" . $_W['weid'] . "   and realname<>''   ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
    }
    include $this->template('charge');
}
if ($op == 'post') {
    if (empty($_GPC['from_user'])) {
        message('请选择会员！', create_url('site/module', array(
            'do' => 'charge',
            'op' => 'list',
            'name' => 'bj_qmxk',
            'weid' => $_W['weid']
        )) , 'success');
    }
    if (checksubmit()) {
        if ($_GPC['chargeType'] == 'charge') {
            $chargenum = round($_GPC['chargenum'], 2);
            if ($chargenum) {
                pdo_query("update " . tablename('fans') . " SET credit3=credit3+'" . $chargenum . "' WHERE from_user='" . $_GPC['from_user'] . "' AND  weid=" . $_W['weid'] . "  ");
                $paylog = array(
                    'type' => 'charge',
                    'weid' => $weid,
                    'openid' => $_GPC['from_user'],
                    'tid' => date('Y-m-d H:i:s') ,
                    'fee' => $chargenum,
                    'module' => 'bj_qmxk',
                    'tag' => ' 后台充值' . $chargenum . '元（自由余额）'
                );
                pdo_insert('paylog', $paylog);
                $this->sendcustomMsg($_GPC['from_user'], "恭喜您后台充值成功，自由余额+".$chargenum."元");
                message("打款成功！", referer() , 'success');
            }
        }
        if ($_GPC['chargeType'] == 'credit2') {
        	$chargenum = round($_GPC['chargenum'], 2);
        	if ($chargenum) {
        		pdo_query("update " . tablename('fans') . " SET credit2=credit2+'" . $chargenum . "' WHERE from_user='" . $_GPC['from_user'] . "' AND  weid=" . $_W['weid'] . "  ");
        		$paylog = array(
        				'type' => 'charge',
        				'weid' => $weid,
        				'openid' => $_GPC['from_user'],
        				'tid' => date('Y-m-d H:i:s') ,
        				'fee' => $chargenum,
        				'module' => 'bj_qmxk',
        				'tag' => ' 后台充值' . $chargenum . '元（购物余额）'
        		);
        		pdo_insert('paylog', $paylog);
        		$this->sendcustomMsg($_GPC['from_user'], "恭喜您后台充值成功，购物余额+".$chargenum."元");
        		message("打款成功！", referer() , 'success');
        	}
        }
        if ($_GPC['chargeType'] == 'credit1') {
            if (is_int($_GPC['credit1num'])) {
                message("充值积分必须是整数！", referer() , 'error');
            }
            if (intval($_GPC['credit1num']) <= 0) {
                message("充值积分不能为负数或者0", referer() , 'error');
            }
            $credit1num = intval($_GPC['credit1num']);
            if ($credit1num) {
                pdo_query("update " . tablename('fans') . " SET credit1=credit1+'" . $credit1num . "' WHERE from_user='" . $_GPC['from_user'] . "' AND  weid=" . $_W['weid'] . "  ");
                $paylog = array(
                    'type' => 'credit1',
                    'weid' => $weid,
                    'openid' => $_GPC['from_user'],
                    'tid' => date('Y-m-d H:i:s') ,
                    'fee' => $credit1num,
                    'module' => 'bj_qmxk',
                    'tag' => ' 充值' . $credit1num . '积分'
                );
                pdo_insert('paylog', $paylog);
                message("充值成功！", referer() , 'success');
            }
        }
    }
    $profile = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
        ':weid' => $_W['weid'],
        ':from_user' => $_GPC['from_user']
    ));
    if (!$profile) {
        message('请选择会员！', create_url('site/module', array(
            'do' => 'charge',
            'op' => 'list',
            'name' => 'bj_qmxk',
            'weid' => $_W['weid']
        )) , 'success');
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('paylog') . " WHERE  openid='" . $_GPC['from_user'] . "' AND type='" . $_GPC['chargeType'] . "' AND `weid` = " . $_W['weid']);
    $pager = pagination($total, $pindex, $psize);
    $list = pdo_fetchall("SELECT * FROM " . tablename('paylog') . " WHERE openid='" . $_GPC['from_user'] . "' AND type='" . $_GPC['chargeType'] . "' AND weid=" . $_W['weid'] . " ORDER BY plid DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
    $mlist = pdo_fetchall("SELECT `name`,`title` FROM " . tablename('modules'));
    $mtype = array();
    foreach ($mlist as $k => $v) {
        $mtype[$v['name']] = $v['title'];
    }
    if ($_GPC['chargeType'] == 'charge' or $_GPC['chargeType'] == 'charge_credit2') {
        include $this->template('charge_post');
    }
    if ($_GPC['chargeType'] == 'credit1') {
        include $this->template('charge_post_credit1');
    }
}
if($op=="transfer"){
	if($_GPC["opp"]=="sort"){
		$to_realname=$_GPC["to_realname"];
		$from_realname=$_GPC["from_realname"];
		$start_date=$_GPC["start_date"];
		$end_date=$_GPC["end_date"];
	
		if($to_realname){
			$where =" and to_realname like '%".$to_realname."%' ";
		}
		if($from_realname){
			$where =" and from_realname like '%".$from_realname."%' ";
		}
		
		if((empty($start_date)) && (!empty($end_date))){
			$where =" and  createtime <='".$end_date."' ";
		}elseif((!empty($start_date)) && (empty($end_date))){
			$where =" and  createtime >='".$start_date."' " ;
		}elseif((!empty($start_date)) && (!empty($start_date))){
			$where =" and createtime  between '".$start_date."' and '".$end_date."'" ;
		}
	
	}
	
	$transfer_list=pdo_fetchall("select * from  ".tablename('bj_qmxk_transfer')." where weid=".$_W["weid"]."   $where   ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize);
	for($i=0;$i<count($transfer_list);$i++){
		$transfer_list[$i]["from_mid"]=pdo_fetchcolumn("select id from  ".tablename('bj_qmxk_member')." where weid=".$_W['weid']." and from_user='".$transfer_list[$i]['from_user']."'");
	}
	$total=pdo_fetchcolumn("select count(*) from ".tablename('bj_qmxk_transfer')." where weid=".$_W["weid"]."   $where  ORDER BY createtime DESC");
	$pager = pagination(count($transfer_list), $pindex, $psize);
	include $this->template('transfer');
} 
?>