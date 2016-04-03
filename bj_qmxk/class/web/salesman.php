<?php
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$cfg = $this->module['config'];
if ($op == 'display') {
    
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
     $list = pdo_fetchall("select qmxk.*,fans.credit1 from " . tablename('bj_qmxk_member') . " qmxk 
			left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.weid=fans.weid 
			where qmxk.flag = 1 and qmxk.issalesman = 1 and qmxk.weid = " . $_W['weid'] . " and qmxk.realname<>'' 
			group by fans.from_user 
			ORDER BY qmxk.id DESC 
			limit " . ($pindex - 1) * $psize . ',' . $psize);
     foreach ($list as $k=>$v){
         $follower = pdo_fetchcolumn("select count(*) from ".tablename('bj_qmxk_member')." 
		              where `weid` =".$weid." and shareid=".$v['id'] ." AND flag=1" );
         $list[$k]['follower']=$follower;
     }
     
     $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') . "where flag = 1 and issalesman = 1 and realname<>'' and weid =" . $_W['weid']);;
    
     
     $pager = pagination1($total, $pindex, $psize);
   
    include $this->template('salesman');
}

/*if($op == 'check'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_fetchall("select qmxk.*,fans.credit1 from " . tablename('bj_qmxk_member') . " qmxk left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.weid=fans.weid where qmxk.flag = 1 and qmxk.issalesman = 0 and qmxk.ischeck = 1 and qmxk.weid = " . $_W['weid'] . " and qmxk.realname<>'' group by fans.from_user ORDER BY qmxk.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') . "where flag = 1 and issalesman = 1 and realname<>'' and weid =" . $_W['weid']);;
    $pager = pagination1($total, $pindex, $psize);
    
    include $this->template('salesman_check');
}

if($op == 'docheck'){
    $id = $_GPC['id'];
    if($_GPC['confirm']==1){
        pdo_update('bj_qmxk_member',array(
            'issalesman'=>'1',
            'salesmantime'=>time()
        ),array(
            'id'=>$id
        ));
		$user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $id);
        //发送微信消息给用户
		$msg = "恭喜您已成为总代！";
		$this->sendcustomMsg($user[from_user],$msg);
		
        message('设置总代成功！', $this->createWebUrl('salesman') , 'success');
    }else{
        $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $id);
        include $this->template('salesman_docheck');
    }
    exit;
}
*/
if($op == 'delete'){
    $id = $_GPC['id'];
    
    pdo_update('bj_qmxk_member',array(
        'issalesman'=>'0'
    ),array(
        'id'=>$id
    ));
    //发送微信消息给用户
    message('取消总代成功！', $this->createWebUrl('salesman') , 'success');
   
}

if($op == 'allfans'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $list = pdo_fetchall("select * from " . tablename('fans') . "  where weid = " . $_W['weid'] . " ORDER BY id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn("select count(id) from" . tablename('fans') . "where weid = " . $_W['weid']) ;
    $pager = pagination1($total, $pindex, $psize);
   
    include $this->template('salesman_allfans');
     
}

if($op == 'add'){
    $id = $_GPC['id'];
    $list = pdo_fetch("select * from " . tablename('fans') . "  where weid = " . $_W['weid'] . " and id = " . $id);
    $re = pdo_fetch("select * from " . tablename('bj_qmxk_member') . "  where weid = " . $_W['weid'] . " and from_user = '" . $list[from_user]. "'") ;
    
    if(!empty($re)){
        if($re[issalesman]==1){
            message('此用户已经是总代了！', $this->createWebUrl('salesman') , 'success');
        }
        pdo_update('bj_qmxk_member',array(
            'issalesman'=>'1',
			'salesmanbz' =>'后台设置',
            'salesmantime'=>time()
        ),array(
            'id'=>$re[id]
        ));
        //发送微信消息给用户
		$msg = "恭喜您已成为总代！";
		$this->sendcustomMsg($list[from_user],$msg);
		
        message('设置总代成功！', $this->createWebUrl('salesman') , 'success');
    }else{
        $data = array(
            'weid' => $_W['weid'],
            'from_user' => $list[from_user],
            'realname' => $list[nickname],
            'commission' => 0,
            'createtime' => TIMESTAMP,
            'flagtime' => TIMESTAMP,
            'shareid' => 0,
            'status' => 1,
            'flag' => 1,
            'issalesman'=>'1',
            'salesmantime'=>TIMESTAMP
        );
        pdo_insert('bj_qmxk_member',$data);
        //发送微信消息给用户
		$msg = "恭喜您已成为总代！";
		$this->sendcustomMsg($list[from_user],$msg);
		
        message('设置总代成功！', $this->createWebUrl('salesman') , 'success');
    } 
}

if($op == 'sort'){
	$sort = array();
	$sort['realname'] = $_GPC['realname'];
	$sort['mobile'] = $_GPC['mobile'];
    if($_GPC['type']=="salesman"){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
		$where = "where qmxk.flag = 1 and qmxk.issalesman = 1 and qmxk.weid = " . $_W['weid'];
		$where1 = "where flag = 1 and issalesman = 1 and weid = " . $_W['weid'];
		if($_GPC['realname']){
			$where .= " and qmxk.realname='".$_GPC['realname']."'";
			$where1 .= " and realname='".$_GPC['realname']."'";
		}
		if($_GPC['mobile']){
			$where .= " and qmxk.mobile='".$_GPC['mobile']."'";
			$where1 .= " and mobile='".$_GPC['mobile']."'";
		}

        $list = pdo_fetchall("select qmxk.*,fans.credit1 from " . tablename('bj_qmxk_member') . " qmxk left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.weid=fans.weid ".$where." group by fans.from_user ORDER BY qmxk.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') .$where1);
        $pager = pagination1($total, $pindex, $psize);
         
        include $this->template('salesman');
    }
    if($_GPC['type']=="ischeck"){
    	$pindex = max(1, intval($_GPC['page']));
        $psize = 20;
		$where = "where qmxk.flag = 1 and qmxk.issalesman  = 0 and qmxk.ischeck = 1 and qmxk.weid = " . $_W['weid'];
		$where1 = "where flag = 1 and issalesman  = 0 and ischeck = 1 and weid = " . $_W['weid'];
		if($_GPC['realname']){
			$where .= " and qmxk.realname='".$_GPC['realname']."'";
			$where1 .= " and realname='".$_GPC['realname']."'";
		}
		if($_GPC['mobile']){
			$where .= " and qmxk.mobile='".$_GPC['mobile']."'";
			$where1 .= " and mobile='".$_GPC['mobile']."'";
		}
        $list = pdo_fetchall("select qmxk.*,fans.credit1 from " . tablename('bj_qmxk_member') . " qmxk left join " . tablename('fans') . " fans on qmxk.from_user=fans.from_user and qmxk.weid=fans.weid ".$where." group by fans.from_user ORDER BY qmxk.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') .$where1);
        $pager = pagination1($total, $pindex, $psize);
         
        include $this->template('salesman_check');
    }
    if($_GPC['type']=="fans"){
    	$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$where = "where weid = " . $_W['weid'];
		if($_GPC['realname']){
			$where .= " and realname='".$_GPC['realname']."'";
		}
		if($_GPC['mobile']){
			$where .= " and mobile='".$_GPC['mobile']."'";
		}
		$list = pdo_fetchall("select * from " . tablename('fans') . $where . " ORDER BY id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
		$total = pdo_fetchcolumn("select count(id) from" . tablename('fans') . $where) ;
		$pager = pagination1($total, $pindex, $psize);
	   
		include $this->template('salesman_allfans');
    }
}

if($op == 'config'){
	if($_GPC['submit'] == '修改'){
		$list = pdo_fetch('select * from ' . tablename('bj_qmxk_salesman') . " where weid = ".$_W['weid']);
		if(empty($list)){
			pdo_insert('bj_qmxk_salesman',array(
				'member' => $_GPC['member'],
				'money' => $_GPC['money'],
				'weid' => $_W['weid']
			));
		}else{
			pdo_update('bj_qmxk_salesman',array(
				'member' => $_GPC['member'],
				'money' => $_GPC['money']
			),array(
				'weid' => $_W['weid']
			));
		}
	}
	$sort = pdo_fetch('select * from ' . tablename('bj_qmxk_salesman') . " where weid = ".$_W['weid']);
	include $this->template('salesman_conf');
}


?>