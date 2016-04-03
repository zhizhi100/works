<?php
// tpl_form_field_image();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $medal= pdo_fetchall("SELECT * FROM ".tablename('bj_qmxk_medal')."
    		 WHERE weid = '{$_W['weid']}'  and medal_status =1 ORDER BY id asc");
	include $this->template('medal');
	exit();
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (!empty($id)){
          $medal= pdo_fetch("SELECT * FROM ".tablename('bj_qmxk_medal')." 
          		WHERE weid = '{$_W['weid']}' and id=$id ORDER BY id asc");
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['medal_name'])) {
			message('抱歉，请输入勋章名称！');
		}
		$globalDividend=intval($_GPC['globalDividend']);
		if (empty($globalDividend)) {
			message('分红比例请输入数字！');
		}
		$rules_type_arr=$_GPC['rules_type'];
		$rules_type_string=implode(",", $rules_type_arr);
		$data = array(
			'weid' => $_W['weid'],
			'medal_name' => $_GPC['medal_name'],
			'one_num' => $_GPC['one_num'],
			'two_num' => $_GPC['two_num'],
			'three_num' => $_GPC['three_num'],
			'one_num_sum' => $_GPC['one_num_sum'],
			'one_two_num_sum' => $_GPC['one_two_num_sum'],
			'one_two_three_num_sum' => $_GPC['one_two_three_num_sum'],
			'monetary'=>$_GPC['monetary'],
			'rules_type' => $rules_type_string,
			'avatar' => $_GPC['avatar'],
			'globalDividend' =>$globalDividend
		);
		if (!empty($id)) {
			pdo_update('bj_qmxk_medal', $data, array('id' => $id));
		} else {
			pdo_insert('bj_qmxk_medal', $data);
		}
		message('更新勋章成功！', $this->createWebUrl('medal', array('op' => 'display')), 'success');
	}
	include $this->template('medal');
} elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$memberlevel = pdo_fetch("SELECT id FROM ".tablename('bj_qmxk_medal')." WHERE id = '$id'");
	if (empty($memberlevel)) {
		message('抱歉，勋章不存在或是已经被删除！', $this->createWebUrl('medal', array('op' => 'display')), 'error');
	}
	pdo_update('bj_qmxk_medal', array("medal_status"=>-1),array('id' => $id));
	message('勋章删除成功！', $this->createWebUrl('medal', array('op' => 'display')), 'success');
}elseif($operation=="dividend"){
	if(	$_GPC["opp"]=="dodividend"){
		$info=$_GPC["config"];
	//	print_r($info);
		$exits=pdo_fetchcolumn("select * from  ". tablename('bj_qmxk_relation_dividend')."  where  
				 to_days(add_time)=to_days(now())");
		if(!$exits){
			if(count($info)){
				//添加手动分红
				$cfg = $this->module['config'];
				if($cfg["dividend_type"]==2){
					$this->dividendDealCredit($info);
				}
			}
		}else{
			message("今日已经操作过分红!");
		}

	}
		//昨日总销售量已付款的订单金额
		$todaydividend=pdo_fetchcolumn("select sum(price) as money from
	         		 " . tablename('bj_qmxk_order') ."  where weid=".$_W["weid"]." and  status =3 and    (TO_DAYS(NOW())-TO_DAYS(FROM_UNIXTIME(`createtime`,'%Y-%m-%d'))=1)");

		//当前系统勋章列表
		$medal_info= pdo_fetchall('select * from ' . tablename('bj_qmxk_medal') . " where weid = " . $_W['weid']."
	        		and medal_status=1");
		//获得每个勋章的人数
		for($i=0;$i<count($medal_info);$i++){
			$medal_id=$medal_info[$i]["id"];
			$globalDividend=$medal_info[$i]["globalDividend"];
			$now_get_medal_info= pdo_fetchall('select *  from ' . tablename('bj_qmxk_relation_medal') . "
	    					where weid = " . $_W['weid']."  and   
					TO_DAYS( get_time) < TO_DAYS(now()) and status=1 and medal_id=$medal_id");
			$medal_info[$i]["now_get_medal_num"]=count($now_get_medal_info);
			$medal_info[$i]["config_money"]=(($todaydividend*$medal_info[$i]["globalDividend"])/100)/$medal_info[$i]["now_get_medal_num"];
// 			$from_users=pdo_fetchall('select group_concat(m.id)  as ids from
// 					 ' . tablename('bj_qmxk_relation_medal') . " rm 
// 					left join ". tablename('bj_qmxk_member') . " m on rm.from_user=m.from_user
// 	    					where rm. weid = " . $_W['weid']."  and rm.status=1
// 					 and rm.medal_id=$medal_id");
			
// 			$medal_info[$i]["personel"]=$from_users[0]["ids"];
			$medal_info[$i]["total_num"]=pdo_fetchcolumn('select count(*) from ' . tablename('bj_qmxk_relation_medal') . "
	    					where weid = " . $_W['weid']."  and status=1 and medal_id=$medal_id");
		}
		//print_r($medal_info);
	include $this->template('medal');
}elseif($operation=="dividendrecord"){
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = 'and  1=1 ';
	$keyword=$_GPC["keyword"];
	if (!empty($_GPC['keyword'])) {
		$condition.= " AND realname LIKE '%{$_GPC['keyword']}%'";
	}
	$add_time=$_GPC['add_time'];
	if (!empty($_GPC['add_time'])) {
		$condition.= " AND TO_DAYS( rm.add_time)=TO_DAYS('".$_GPC['add_time']."')";
		
	}
		$record= pdo_fetchall('select *  from ' . tablename('bj_qmxk_relation_dividend') . " rm
				left join " . tablename('bj_qmxk_medal') ." m on  m.id=rm.medal_id
				left join ".tablename('bj_qmxk_member')."  me on me.from_user=rm.from_user
    					where rm.weid = " . $_W['weid']."  $condition ORDER BY add_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		$total= pdo_fetchcolumn('select  COUNT(*)  from ' . tablename('bj_qmxk_relation_dividend') . " rm
				left join " . tablename('bj_qmxk_medal') ." m on  m.id=rm.medal_id
				left join ".tablename('bj_qmxk_member')."  me on me.from_user=rm.from_user
    					where rm.weid = " . $_W['weid']."  $condition ORDER BY rm.add_time DESC  ");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('medal');
}

?>