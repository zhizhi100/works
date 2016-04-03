<?php

if ($_GPC['op'] == 'addChildren') {
	$now_from_user = $this->getFromUser();
	//根据当前from_user获取主账号的openid=>查询粉丝基本信息
	$main_openid = pdo_fetch("SELECT flag FROM " . tablename('bj_qmxk_member') . " WHERE
			 from_user=:from_user and weid=:weid", array(
			':from_user' => $now_from_user,
			':weid' => $_W['weid']
	));
	if($main_openid["flag"] =="1"){
		//界面显示
		include $this->template('addChildren');
	}else{
		message("对比起您暂时还不是东家!无法添加子账号");
	}

	exit();
}
if($_GPC['op'] == 'applyed'){
	//添加子账号逻辑
	//获取当前账号的from_user
	$now_from_user = $this->getFromUser();
	//根据当前from_user获取主账号的openid=>查询粉丝基本信息
	$main_openid = pdo_fetch("SELECT openid,flag FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
			':from_user' => $now_from_user,
			':weid' => $_W['weid']
	));
	if($main_openid["flag"]!=1){
		//不是东家
		echo -1;
		exit();
	}else{
		//主账号的openid(from_user)
		$main_from_user=$main_openid["openid"];
		
		//主账号的信息
		$main_member_info = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user=:from_user and weid=:weid", array(
				':from_user' => $main_from_user,
				':weid' => $_W['weid']
		));
		
		
		$fans = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE from_user=:from_user and weid=:weid", array(
				':from_user' => $main_from_user,
				':weid' => $_W['weid']
		));
		
		//生成子账号的from_user
		$guest_num = pdo_fetch("SELECT seq('guest_seq') as guest_num");
		$from_user=$main_from_user."-".$guest_num["guest_num"];
		//粉丝表(添加粉丝)
		$row = array(
				'weid' => $_W['weid'],
				'nickname' => $_GPC['realname'] ,
				'realname' => $_GPC['realname'],
				'follow' => $fans["follow"],
				'gender' => $fans["sex"],
				'salt' => random(8) ,
				'from_user' => $from_user,
				'createtime' => TIMESTAMP,
				'avatar' => $fans["avatar"],
				'is_children' => 1
		);
		pdo_insert('fans', $row);
		//分享记录
		$data = array(
				'weid' => $_W['weid'],
				'from_user' => $from_user,  //当前from_user
				'sharemid' => $main_member_info["id"], //分享者的id
				'joinway' => 0
		);
		
		pdo_insert('bj_qmxk_share_history', $data);
		//分享点击
		pdo_update('bj_qmxk_member', array(
		'clickcount' => $main_member_info['clickcount'] + 1
		) , array(
		'id' => $main_member_info["id"]
		));
		//分销用户表
		$this->autoRegedit("addChildren",$from_user,$main_from_user);
		
		$profile = $this->getProfile($from_user);
		$this->checkisAgent($from_user, $profile);
		echo 1;
		exit();
	}

}
if($_GPC['op'] == 'changeAccount'){
	$openid=$_GPC['openid'];
    setcookie(BAIJIA_COOKIE_OPENID . $_W['weid'], $openid, time() + 86400);
    setcookie(BAIJIA_COOKIE_CHECKOPENID . $_W['weid'], $openid, time() + 60000);
    setcookie("mid", '', time() - 1);
	message('切换成功', $this->createMobileUrl('fansindex') , 'success');
	exit();
}

//获取当前账号的from_user
$now_from_user = $this->getFromUser();
//根据当前from_user获取主账号的基本信息
$main_member_info = pdo_fetch("SELECT *  FROM " . tablename('bj_qmxk_member') . " 
		WHERE from_user=:from_user and weid=:weid", array(
		':from_user' => $now_from_user,
		':weid' => $_W['weid']
));
setcookie('mid_' . BJ_QMXK_VERSION . $_W['weid'], $main_member_info['id'], time() + 3600);

//主账号的openid(from_user)
$main_from_user=$main_member_info["openid"];
	
	

//获取子账号
$children= pdo_fetchall("select * from " . tablename('bj_qmxk_member') . "   where 
weid={$_W['weid']}  and openid='{$main_from_user}'");
$children_num=count($children);

include $this->template('children');
?>