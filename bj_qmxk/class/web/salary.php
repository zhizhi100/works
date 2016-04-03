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
$cfg = $this->module['config'];
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$members = pdo_fetchall("select id, realname, mobile from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and status = 1");
$member = array();
foreach ($members as $m) {
    $member['realname'][$m['id']] = $m['realname'];
    $member['mobile'][$m['id']] = $m['mobile'];
}
if ($op == 'display') {
	//print_r($this->module);
	if ($_GPC['opp'] == 'check') {
		$zhifucommission = $cfg['zhifuCommission'];
		if (!$zhifucommission) {
			message('请先在参数设置，设置佣金打款限额！', $this->createWebUrl('Salary') , 'success');
		}
		$mid = $_GPC['mid'];
		$id = $_GPC['id'];
		$user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['mid']);
		$bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $user['from_user'] . "'");
	
		$info= pdo_fetch("select *  from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
			and id=".$id." and mid=".$mid
		);
		include $this->template('salary_detail');
		exit;
	}
if ($_GPC['opp'] == 'checked') {
		$id= $_GPC['id'];
		$mid = $_GPC['mid'];
		$status=$_GPC['status'];
		$remark=$_GPC['content'];
		if($status==2){
				$paymenttime=time();
		}else{
			$checktime=time();
		}
		if(empty($paymenttime)){
			$paymenttime=0;
		}
		if(empty($checktime)){
			$checktime=0;
		}
		
		if ($_GPC['status'] == 2) {
			 //打款
			 $from_user=$this->getShareFromUser($mid);
			if ($_GPC['commission'] > 0) {
				$result=$this->WxPayment($_GPC['commission'],$from_user);
				if($result["return_code"]=="SUCCESS" && $result["result_code"]=="SUCCESS"){ //发放成功
					
					$temp =pdo_update('bj_qmxk_salary', array(
							'status'=>$status,
							'content'=>$remark,
							'paymenttime'=>$paymenttime
					) , array(
							'id' => $id,
							'mid'=>$mid
					));
					
					
// 					pdo_query("update " . tablename('fans') . " SET credit3=credit3-" . $_GPC['commission']  . "
// 					  WHERE
// 						 from_user='" . $from_user . "' AND  weid=" . $_W['weid'] . "  ");
					
					$paylog = array(
							'type' => 'zhifu',
							'weid' => $_W['weid'],
							'openid' => $from_user,
							'tid' => date('Y-m-d H:i:s') ,
							'fee' => $_GPC['commission'],
							'module' => 'bj_qmxk',
							'tag' => ' 后台打款' . $_GPC['commission'] . '元【会员薪资】'
					);
					pdo_insert('paylog', $paylog);
					$msg =  ' 后台打款' . $_GPC['commission'] . '元【会员薪资】';
					$this->sendcustomMsg($from_user,$msg);
				}else{
					message("操作失败：".$result["return_msg"], $this->createWebUrl('salary', array(
					'opp' => 'check',
					'mid' => $_GPC['mid'],
					'id' => $_GPC['id'],
					)) , 'error');
				}
			}
			message('打款完成！', $this->createWebUrl('salary') , 'success');
		}else{
			//审核操作
			$temp=pdo_query("update " . tablename('bj_qmxk_salary') . " SET status=".$status . ",content='".$remark."'
					,checktime=$checktime
					  WHERE
						 id=".$id." AND  mid=" .$mid. "  and  weid= ".$_W["weid"]);
			if (empty($temp)) {
				message('审核失败，请重新审核！', $this->createWebUrl('salary', array(
				'opp' => 'check',
				'mid' => $_GPC['mid'],
				'id' => $_GPC['id'],
				)) , 'error');
			} else {
				message('审核成功！', $this->createWebUrl('salary') , 'success');
			}
		}
		exit();
}
	if($_GPC["opp"]=="sort"){
		$realname=$_GPC["realname"];
		$membercode=$_GPC["membercode"];
		$status=$_GPC["status"];
		$time_type=$_GPC["time_type"];
		$start_date=$_GPC["start_date"];
		$end_date=$_GPC["end_date"];
	
		if($start_date){
			$start_time=strtotime($start_date);
		}

		if($end_date){
			$end_time=strtotime($end_date);
		}
		$end_time=strtotime($_GPC["end_time"]);
		if($realname){
			$where =" and m. realname like '%".$realname."%' ";
		}
		if($membercode){
			$where =" and s.mid =".$membercode;
		}
		if($status !="all"){
			$where =" and s.status =".$status;
		}
		if($time_type){
			if((empty($start_time)) && (!empty($end_time))){
				$where =" and s.".$time_type." <".$end_time ;
			}elseif((!empty($start_time)) && (empty($end_time))){
				$where =" and s.".$time_type." >".$start_time ;
			}elseif((!empty($start_time)) && (!empty($end_time))){
				$where =" and s.".$time_type." between ".$start_time." and ".$end_time ;
			}
		}
		
	}
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$commission_info= pdo_fetchall("select s.*  from " . tablename('bj_qmxk_salary') . " s 
left join  " . tablename('bj_qmxk_member') . " m on m.id=s.mid 
			where s.weid = " . $_W['weid']."  $where
			order by s.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize
			);
	$listx= pdo_fetchall("select s.* from " . tablename('bj_qmxk_salary') . " s 
	left join  " . tablename('bj_qmxk_member') . " m on m.id=s.mid 
			where s.weid = " . $_W['weid']."  $where
			order by s.createtime desc"
	);
	$total = sizeof($listx);
	$pager = pagination1($total, $pindex, $psize);
	include $this->template('salary_display');
}elseif($op=="unaudited"){
	//未审核
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$commission_info= pdo_fetchall("select *  from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
	and status=0		order by createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize
	);
	$total= pdo_fetchall("select  * from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
		and status=0	order by createtime desc"
	);
	$total = sizeof($total);
	$pager = pagination1($total, $pindex, $psize);
	include $this->template('salary_display');
}elseif($op=="audited"){
	//已审核
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$commission_info= pdo_fetchall("select *  from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
	and status=1		order by createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize
	);
	$total= pdo_fetchall("select count(*)  from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
		and status=1	order by createtime desc"
	);
	$total = sizeof($total);
	$pager = pagination1($total, $pindex, $psize);
	include $this->template('salary_display');
}elseif($op=="invalid"){
	//审核无效
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$commission_info= pdo_fetchall("select *  from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
	and status=-1		order by createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize
	);
	$total= pdo_fetchall("select * from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
		and status=-1	order by createtime desc"
	);
	$total = sizeof($total);
	$pager = pagination1($total, $pindex, $psize);
	include $this->template('salary_display');
}elseif($op=="paid"){
	//已打款
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$commission_info= pdo_fetchall("select *  from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
	and status=2		order by createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize
	);
	$total= pdo_fetchall("select * from " . tablename('bj_qmxk_salary') . " where weid = " . $_W['weid']."
		and status=2	order by createtime desc"
	);
	$total = sizeof($total);
	$pager = pagination1($total, $pindex, $psize);
	include $this->template('salary_display');
}
?>
