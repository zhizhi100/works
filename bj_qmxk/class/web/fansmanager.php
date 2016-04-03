<?php
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$cfg = $this->module['config'];
$medal=pdo_fetchall("select * from ".tablename('bj_qmxk_medal')." where medal_status=1 and  weid=".$weid);
if ($op == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_fetchall("select qmxk.*,fans.credit1 from " . tablename('bj_qmxk_member') . " qmxk
    		 left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.weid=fans.weid 
    		where qmxk.flag = 1 and qmxk.weid = " . $_W['weid'] . " and 
    		qmxk.realname<>'' group by fans.from_user
    		 ORDER BY qmxk.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
	foreach ($list as $k=>$v){
         $follower = pdo_fetchcolumn("select count(*) from ".tablename('bj_qmxk_member')." 
		              where `weid` =".$weid." and shareid=".$v['id'] ." AND flag=1" );
         $list[$k]['follower']=$follower;
     }
    $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') . "where flag = 1 and realname<>'' and weid =" . $_W['weid']);;
    $pager = pagination1($total, $pindex, $psize);
    $commissions = pdo_fetchall("select mid, sum(commission) as commission from " . tablename('bj_qmxk_commission') . " where weid = " . $_W['weid'] . " and flag = 0 group by mid");
    $commission = array();
    foreach ($commissions as $c) {
        $commission[$c['mid']] = $c['commission'];
    }
   
}
if ($op == 'nocheck') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_fetchall("select qmxk.*,fans.credit1 from " . tablename('bj_qmxk_member') . " qmxk left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.weid=fans.weid where qmxk.flag = 0 and qmxk.realname<>'' and qmxk.weid = " . $_W['weid'] . " group by fans.from_user  ORDER BY qmxk.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') . "where flag = 0 and realname<>'' and weid =" . $_W['weid']);;
    $pager = pagination1($total, $pindex, $psize);
    include $this->template('fansmanagered');
    exit;
}
if ($op == 'sort') {
	if(!empty($_GPC['medal_id'])){
		$medal_id=$_GPC['medal_id'];
		$string=" and medal.medal_id='$medal_id' and medal.status=1";
	}
	if(!empty(intval($_GPC['memberId']))){
		$memberId=$_GPC['memberId'];
		$string=" and qmxk.id=".$memberId ;
	}
    $sort = array(
        'realname' => $_GPC['realname'],
        'mobile' => $_GPC['mobile']
    );
    if ($_GPC['opp'] == 'nocheck') {
        $status = 0;
    } else {
        $status = 1;
    }
    $list = pdo_fetchall("select qmxk.*,fans.credit1,medal.get_time from" . tablename('bj_qmxk_member') . " qmxk
    		 left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.realname<>'' and 
    		qmxk.weid=fans.weid 
    		left join  " . tablename('bj_qmxk_relation_medal') . " medal on    qmxk.from_user=medal.from_user
    		where qmxk.flag = " . $status . " and qmxk.weid =" . $_W['weid'] . "   and
    		 qmxk.realname like '%" . $sort['realname'] . "%' and qmxk.mobile like '%" . $sort['mobile'] . "%'  $string
    		group by fans.from_user ORDER BY qmxk.id DESC");
    $commissions = pdo_fetchall("select mid, sum(commission) as commission from " . tablename('bj_qmxk_commission') . " where weid = " . $_W['weid'] . " and flag = 0  group by mid");
    $commission = array();
    foreach ($commissions as $c) {
        $commission[$c['mid']] = $c['commission'];
    }
    if ($_GPC['opp'] == 'nocheck') {
        include $this->template('fansmanagered');
        exit;
    }
}


if ($op == 'user') {
    $from_user = $_GPC['from_user'];
    $fans = pdo_fetch('SELECT nickname,createtime,credit1 FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
        ':weid' => $_W['weid'],
        ':from_user' => $from_user
    ));
    $myheadimg = pdo_fetchcolumn('SELECT avatar FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
        ':weid' => $_W['weid'],
        ':from_user' => $from_user
    ));
    $fans['avatar'] = $myheadimg;
    $profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND from_user = :from_user", array(
        ':weid' => $_W['weid'],
        ':from_user' => $from_user
    ));
    if (!empty($profile['id'])) {
        $mylist = pdo_fetchall("SELECT b.createtime createtime, nickname, avatar FROM " . tablename('bj_qmxk_share_history') . " a LEFT JOIN " . tablename('fans') . " b ON a.from_user = b.from_user  and a.weid=b.weid WHERE a.sharemid = (select id from " . tablename('bj_qmxk_member') . " c where c.from_user=:leader and c.weid=:weid  limit 1) and a.from_user!=:leader AND a.weid=:weid  ", array(
            ':leader' => $from_user,
            ':weid' => $_W['weid']
        ));
        $count = 0;
        if (true) {
            $sql1_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=1 and mber1.shareid = " . $profile['id'];
            $count1 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
            $mylist1 = pdo_fetchall("	select *,1 as level from " . tablename('fans') . " fans where  from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ") ) and fans.weid={$_W['weid']}");
        }
        if (true && $cfg['globalCommissionLevel'] >= 2) {
            $level2 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and  flag=1  and shareid = " . $profile['id']);
            $rowindex = 0;
            $str = "";
            foreach ($level2 as & $citem) {
                $str = $str . $citem['id'] . ',';
            }
            $str = $str . '-1';
            $sql2_member = "select mber2.from_user from " . tablename('bj_qmxk_member') . " mber2 where  mber2.realname<>'' and mber2.id!=mber2.shareid  and mber2.flag=1 and mber2.shareid in (" . $str . ")  ";
            $count2 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
            $mylist2 = pdo_fetchall("	select * ,2 as level from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
        }
        if (true && $cfg['globalCommissionLevel'] >= 3) {
            $level3 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and flag=1  and shareid in( " . $str . ")");
            $rowindex = 0;
            $str3 = "";
            foreach ($level3 as & $citem) {
                $str3 = $str3 . $citem['id'] . ',';
            }
            $str3 = $str3 . '-1';
            $sql3_member = "select mber3.from_user from " . tablename('bj_qmxk_member') . " mber3 where  mber3.realname<>'' and mber3.id!=mber3.shareid and mber3.flag=1 and mber3.shareid in (" . $str3 . ")  ";
            $count3 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
            $mylist3 = pdo_fetchall("	select * ,3 as level from " . tablename('fans') . " fans where from_user!='{$from_user}' and ( fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
        }
        $count = $count1 + $count2 + $count3;
    } else {
        $count = 0;
    }
    include $this->template('clicklog');
    exit;
}

// if($op=="performance"){
// 	$from_user = $_GPC['from_user'];
// 	$fans = pdo_fetch('SELECT nickname,createtime,credit1,avatar FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
// 			':weid' => $_W['weid'],
// 			':from_user' => $from_user
// 	));
// 	$profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND from_user = :from_user", array(
// 			':weid' => $_W['weid'],
// 			':from_user' => $from_user
// 	));
// 	if (!empty($profile['id'])) {
// 		//粉丝（关注后至少一次进入商城首页的）
// 		$sql0_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=0 and mber1.shareid = " . $profile['id'];
// 		$count0 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql0_member . ")) and fans.weid={$_W['weid']}");
// 		$mylist0 = pdo_fetchall("	select * from " . tablename('fans') . " fans where  from_user!='{$from_user}' and (fans.from_user in (" . $sql0_member . ") ) and fans.weid={$_W['weid']}");
// 		$count = 0;
// 		if (true) {
// 			//一级代理
// 			$sql1_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=1 and mber1.shareid = " . $profile['id'];
// 			$count1 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
// 			$mylist1 = pdo_fetchall("	select *,1 as level from " . tablename('fans') . " fans where  from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ") ) and fans.weid={$_W['weid']}");
// 		}
// 		if (true && $cfg['globalCommissionLevel'] >= 2) {
// 			$level2 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and  flag=1  and shareid = " . $profile['id']);
// 			$rowindex = 0;
// 			$str = "";
// 			foreach ($level2 as & $citem) {
// 				$str = $str . $citem['id'] . ',';
// 			}
// 			$str = $str . '-1';
// 			$sql2_member = "select mber2.from_user from " . tablename('bj_qmxk_member') . " mber2 where  mber2.realname<>'' and mber2.id!=mber2.shareid  and mber2.flag=1 and mber2.shareid in (" . $str . ")  ";
// 			$count2 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
// 			$mylist2 = pdo_fetchall("	select * ,2 as level from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
// 		}
// 		if (true && $cfg['globalCommissionLevel'] >= 3) {
// 			$level3 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and flag=1  and shareid in( " . $str . ")");
// 			$rowindex = 0;
// 			$str3 = "";
// 			foreach ($level3 as & $citem) {
// 				$str3 = $str3 . $citem['id'] . ',';
// 			}
// 			$str3 = $str3 . '-1';
// 			$sql3_member = "select mber3.from_user from " . tablename('bj_qmxk_member') . " mber3 where  mber3.realname<>'' and mber3.id!=mber3.shareid and mber3.flag=1 and mber3.shareid in (" . $str3 . ")  ";
// 			$count3 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
// 			$mylist3 = pdo_fetchall("	select * ,3 as level from " . tablename('fans') . " fans where from_user!='{$from_user}' and ( fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
// 		}
// 		$count = $count1 + $count2 + $count3;
// 	} else {
// 		$count = 0;
// 	}
// 	include $this->template('allclicklog');
// 	exit;
// }
if ($op == 'performance') {
	if(!empty(intval($_GPC['memberId']))){
		$memberId=$_GPC['memberId'];
		$string1=" and mber1.id=".$memberId ;
		$string2=" and mber2.id=".$memberId ;
		$string3=" and mber3.id=".$memberId ;
	}
	$identity=intval($_GPC["identity"]);
	$from_user = $_GPC['from_user'];
	$fans = pdo_fetch('SELECT nickname,createtime,credit1,avatar FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
			':weid' => $_W['weid'],
			':from_user' => $from_user
	));
	$profile = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_member') . " WHERE  weid = :weid  AND from_user = :from_user", array(
			':weid' => $_W['weid'],
			':from_user' => $from_user
	));
	if (!empty($profile['id'])) {
		//粉丝（关注后至少一次进入商城首页的）
		if(empty($identity) || $identity==2){
			$sql0_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=0   ".$string1."  and mber1.shareid = " . $profile['id'] ;
			$count0 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql0_member . ")) and fans.weid={$_W['weid']}");
			$mylist0 = pdo_fetchall("	select * from " . tablename('fans') . " fans where  from_user!='{$from_user}' and (fans.from_user in (" . $sql0_member . ") ) and fans.weid={$_W['weid']}");
			
		}
		$count = 0;
		if(empty($identity) || $identity==1){
			if (true) {
				//一级代理
				$sql1_member = "select mber1.from_user from " . tablename('bj_qmxk_member') . " mber1 where    mber1.realname<>'' and mber1.id!=mber1.shareid and mber1.flag=1  ".$string1."   and mber1.shareid = " . $profile['id'];
				$count1 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
				$mylist1 = pdo_fetchall("	select *,1 as level from " . tablename('fans') . " fans where  from_user!='{$from_user}' and (fans.from_user in (" . $sql1_member . ") ) and fans.weid={$_W['weid']}");
			}
			if (true && $cfg['globalCommissionLevel'] >= 2) {
				$level2 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and  flag=1  and shareid = " . $profile['id']);
				$rowindex = 0;
				$str = "";
				foreach ($level2 as & $citem) {
					$str = $str . $citem['id'] . ',';
				}
				$str = $str . '-1';
				$sql2_member = "select mber2.from_user from " . tablename('bj_qmxk_member') . " mber2 where  mber2.realname<>'' and mber2.id!=mber2.shareid  and mber2.flag=1 ".$string2."  and mber2.shareid in (" . $str . ")  ";
				$count2 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
				$mylist2 = pdo_fetchall("	select * ,2 as level from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql2_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and fans.weid={$_W['weid']}");
			}
			if (true && $cfg['globalCommissionLevel'] >= 3) {
				$level3 = pdo_fetchall("select id from " . tablename('bj_qmxk_member') . " where id!=shareid and flag=1  and shareid in( " . $str . ")");
				$rowindex = 0;
				$str3 = "";
				foreach ($level3 as & $citem) {
					$str3 = $str3 . $citem['id'] . ',';
				}
				$str3 = $str3 . '-1';
				$sql3_member = "select mber3.from_user from " . tablename('bj_qmxk_member') . " mber3 where  mber3.realname<>'' and mber3.id!=mber3.shareid and mber3.flag=1 ".$string3."  and mber3.shareid in (" . $str3 . ")  ";
				$count3 = pdo_fetchcolumn("	select count(*) from " . tablename('fans') . " fans where from_user!='{$from_user}' and (fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
				$mylist3 = pdo_fetchall("	select * ,3 as level from " . tablename('fans') . " fans where from_user!='{$from_user}' and ( fans.from_user in (" . $sql3_member . ")) and (fans.from_user not in (" . $sql1_member . ")) and (fans.from_user not in  (" . $sql2_member . ")) and fans.weid={$_W['weid']}");
			}
		}
		$count = $count1 + $count2 + $count3;
	} else {
		$count = 0;
	}
	include $this->template('allclicklog');
	EXIT();
}

if ($op == 'delete') {
	$member = pdo_fetch("select * from ".tablename('bj_qmxk_member')." where id =".$_GPC['id']);
    $temp = pdo_delete('bj_qmxk_member', array(
        'id' => $_GPC['id']
    ));
    if (empty($temp)) {
        if ($_GPC['opp'] == 'nocheck') {
            message('删除失败，请重新删除！', $this->createWebUrl('fansmanager', array(
                'op' => 'nocheck'
            )) , 'error');
        } else {
            message('删除失败，请重新删除！', $this->createWebUrl('fansmanager') , 'error');
        }
    } else {
    	//:TODO删除代理后可能会影响上级所获得的勋章
    	if(!empty($member['shareid'])){
    		//往上推一级
    		$info1_shareid=$this->DeleteMedal($member['shareid'],1,$member["realname"]);
    	}
  	  	if(!empty($info1_shareid)){
			//往上推二级
			$info2_shareid=$this->DeleteMedal($info1_shareid,2,$member["realname"]);
		}
		if(!empty($info2_shareid)){
			//往上推三级
			$this->DeleteMedal($info2_shareid,3,$member["realname"]);
		}
		
        if ($_GPC['opp'] == 'nocheck') {
            message('删除成功！', $this->createWebUrl('fansmanager', array(
                'op' => 'nocheck'
            )) , 'success');
        } else {
            message('删除成功！', $this->createWebUrl('fansmanager') , 'success');
        }
    }
}




if ($op == 'detail') {
    $id = $_GPC['id'];
    $from_user=$this->getShareFromUser($id);
    $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $id);
	$shareuser = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $user['shareid']);
	$fans_info=pdo_fetch("select * from " . tablename('fans') . " where from_user='".$from_user."' and  weid=".$_W["weid"]);
    if ($_GPC['opp'] == 'nocheck') {
        include $this->template('fansmanagered_detail');
    } else {
        include $this->template('fansmanager_detail');
    }
    exit;
}

if ($op == 'member') {
	$id = $_GPC['id'];
	$user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $id);
	$shareuser = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $user['shareid']);
	$get_medal = $this->getMedal($user["from_user"]);
	$credit3= pdo_fetchcolumn("select credit3  from " . tablename('fans') . " where from_user = '".$user["from_user"]."'");
//	echo $credit3;
	if ($_GPC['opp'] == 'nocheck') {
		include $this->template('membered_detail');
	} else {
		include $this->template('member_detail');
	}
	exit;
}


if ($op == 'changeshareid'){
	$id = $_GPC['id'];
	$shareid = $_GPC['shareid'];
	if(!$shareid){
		$shareid = 0 ;
	}
	$user = pdo_fetchcolumn("select realname from " . tablename('bj_qmxk_member') . " where id = " . $shareid . " and weid = ".$weid);
	if(!$user && $shareid){
		exit("代理id有误，请重新输入！");
	}
	$re = pdo_update('bj_qmxk_member', array(
		'shareid' => $shareid
	), array(
        'id' => $_GPC['id']
    ));
	if($re){
		if(!$shareid){
			exit("--");
		}
		exit($user);
	}else{
		exit("0");	
	}
	
}
if ($op == 'status') {
    $status = array(
        'status' => $_GPC['status'],
        'flag' => $_GPC['flag'],
        'content' => trim($_GPC['content'])
    );
    if ($_GPC['opp'] == 'nocheck' && $_GPC['flag'] == 1) {
        $status['flagtime'] = TIMESTAMP;
    }
	
	$member = pdo_fetch("select * from ".tablename('bj_qmxk_member')." where id =".$_GPC['id']);
	
    $temp = pdo_update('bj_qmxk_member', $status, array(
        'id' => $_GPC['id']
    ));
	if((!$member['flag'])&&($_GPC['flag']==1)){
		$msg = "恭喜您已成为".$this->module['config']['agent_title'];
		$this->sendcustomMsg($member[from_user],$msg);
		//:TODO判断上三级是否符合拥有勋章的条件
		
		if(!empty($member['shareid'])){
			//往上推一级
			$info1_shareid=$this->GiveMedal($member['shareid'],1);
		}
		if(!empty($info1_shareid)){
			//往上推二级
			$info2_shareid=$this->GiveMedal($info1_shareid,2);
		}
		if(!empty($info2_shareid)){
			//往上推三级GiveMedalMyself
			$this->GiveMedal($info2_shareid,3);
		}
		
	}
    if (empty($temp)) {
        if ($_GPC['opp'] == 'nocheck') {
            message('设置用户权限失败，请重新设置！', $this->createWebUrl('fansmanager', array(
                'op' => 'detail',
                'opp' => 'nocheck',
                'id' => $_GPC['id']
            )) , 'error');
        } else {
            message('设置用户权限失败，请重新设置！', $this->createWebUrl('fansmanager', array(
                'op' => 'detail',
                'id' => $_GPC['id']
            )) , 'error');
        }
    } else {
        if ($_GPC['opp'] == 'nocheck') {
            message('设置用户权限成功！', $this->createWebUrl('fansmanager', array(
                'op' => 'nocheck'
            )) , 'success');
        } else {
            message('设置用户权限成功！', $this->createWebUrl('fansmanager') , 'success');
        }
    }
}
if ($op == 'recharge') {
    $id = $_GPC['id'];
    if ($_GPC['opp'] == 'recharged') {
        if (!is_numeric($_GPC['commission'])) {
            message('佣金请输入合法数字！', '', 'error');
        }
        $recharged = array(
            'weid' => $_W['weid'],
            'mid' => $id,
            'flag' => 1,
            'content' => trim($_GPC['content']) ,
            'commission' => $_GPC['commission'],
            'createtime' => time()
        );
        $temp = pdo_insert('bj_qmxk_commission', $recharged);
        $commission = pdo_fetchcolumn("select commission from " . tablename('bj_qmxk_member') . " where id = " . $id);
        if (empty($temp)) {
            message('充值失败，请重新充值！', $this->createWebUrl('fansmanager', array(
                'op' => 'recharge',
                'id' => $_GPC['id']
            )) , 'error');
        } else {
            pdo_update('bj_qmxk_member', array(
                'commission' => $commission + $_GPC['commission']
            ) , array(
                'id' => $id
            ));
            message('充值成功！', $this->createWebUrl('fansmanager', array(
                'op' => 'recharge',
                'id' => $_GPC['id']
            )) , 'success');
        }
    }
    $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $id);
    $commission = pdo_fetchcolumn("select sum(commission) from " . tablename('bj_qmxk_commission') . " where mid = " . $id . " and flag = 0 and weid = " . $_W['weid']);
    $commission = empty($commission) ? 0 : $commission;
    $commission = $commission - $user['commission'];
    $commissions = pdo_fetchall("select * from " . tablename('bj_qmxk_commission') . " where mid = " . $id . " and weid = " . $_W['weid'] . " and flag = 1");
    include $this->template('fansmanager_recharge');
    exit;
}
include $this->template('fansmanager'); 
?>