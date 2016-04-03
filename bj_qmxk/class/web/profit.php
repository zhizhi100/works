<?php
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$cfg = $this->module['config'];
if ($op == 'display') {
    if($_GPC['months']){
        $months = $_GPC['months'];
        $t1 = strtotime($months.'-1');
        $y=date("Y",$t1);
        $m=date("m",$t1);
        if($y==date("Y",time())){
            if($m>=date("m",time())){
                message('对不起，您查询的该月利润数据还未产生！', $this->createWebUrl('profit') , 'success');
            }else{
				if((date("m",time())-$m)==1){
					if(date("d",time())<10) message('对不起，上一个月的利润数据要到10号才产生！', $this->createWebUrl('profit') , 'success');
				}
			}
        }
        $t0=date('t',$t1);
        $t2=mktime(23,59,59,$m,$t0,$y);
    
    }else{  //默认显示上个月的利润
        $y=date("Y",time());
		if(date("d",time())>=10){
			//10号以后统计上个月的
        	$m=date("m",time())-1;   //上一个月
		}else{
			//10号以前统计上上个月的
			$m=date("m",time())-2;   //上一个月
		}
        if($m<10)$m='0'.$m;
        $time = strtotime($y.'-'.$m.'-1');
        $t0=date('t',$time);           // 上一个月一共有几天
        $t1=mktime(0,0,0,$m,1,$y);        // 创建上一个月开始时间
        $t2=mktime(23,59,59,$m,$t0,$y);       // 创建上一个月结束时间
        $months = $y.'-'.$m;
    }
    $profitlist = pdo_fetch("select * FROM " . tablename('bj_qmxk_profit') . "
        WHERE `weid` = ".$_W['weid']." AND months = '$months'"
    );
    
    if(!empty($profitlist)){
        $allprofit = $profitlist['allprofit'];
        $modify = $profitlist['modify'];
    }else{ //第一次查询该月的数据，先写入数据表
        $allprofit = pdo_fetchcolumn("select sum(profit) FROM " . tablename('bj_qmxk_order') . " 
            WHERE `weid` = ".$_W['weid']." and `status`=3 AND (createtime>".$t1." and createtime<".$t2.")");
		$allprofit = $allprofit * 0.1;
        if($allprofit){
            $data = array(
                'weid' => $_W['weid'],
                'allprofit' => $allprofit,
                'months' => $months,
                'modify' => 0
            );
        }else{
            $data = array(
                'weid' => $_W['weid'],
                'allprofit' => 0,
                'months' => $months,
                'modify' => 0
            );
        }
        $modify = 0;
        pdo_insert('bj_qmxk_profit',$data);
    
        $members = pdo_fetchall("select id,from_user from" . tablename('bj_qmxk_member') . " 
            where weid = ".$_W['weid']." and issalesman = 1 and salesmantime  < ".$t2);
		//echo "select from_user from" . tablename('bj_qmxk_member') . "where weid = ".$_W['weid']." and issalesman = 1 and salesmantime  < ".$t2
        //print_r($members);exit;
		foreach ($members as $v){
		    
		    $follower = pdo_fetchcolumn("select count(*) from ".tablename('bj_qmxk_member')." 
		              where `weid` =".$weid." and shareid=".$v['id'] ." AND flag=1" );
		    if($follower>=10){
		    
                $m = array(
                    'weid' => $_W['weid'],
                    'months' => $months,
                    'ispay' => 0,
                    'from_user' => $v['from_user']
                );
        
                pdo_insert('bj_qmxk_profit_member',$m);
		    }
        }
         
    }
	
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$sql = "select a.*,b.ispay from " . tablename('bj_qmxk_member') . " a 
		        LEFT JOIN " . tablename('bj_qmxk_profit_member') . " b ON a.from_user = b.from_user 
		        where a.weid = ".$_W['weid']." and a.salesmantime < ".$t2." and b.months='".$months."' 
		        limit " . ($pindex - 1) * $psize . ',' . $psize;
		
		$list = pdo_fetchall($sql);
		
		foreach ($list as $k=>$v){
			 $follower = pdo_fetchcolumn("select count(*) from ".tablename('bj_qmxk_member')." 
						  where `weid` =".$weid." and shareid=".$v['id'] ." AND flag=1" );
			 $list[$k]['follower']=$follower;
		}
		
		$total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_profit_member') . " 
		    where weid = ".$_W['weid']." and months='$months'");	
		$avaprofit = floor($allprofit/$total);
		$pager = pagination1($total, $pindex, $psize);
		
		//生成工资表文件
			$myfile = fopen(BJ_QMXK_ROOT."/recouse/file/gz_".$months.".csv", "w");
			$txt = "会员姓名,手机号码,应发工资,收款人姓名,收款银行地址,收款银行所属支行,银行账户,支付宝,是否已发放\n";
			$sql = "select a.*,b.ispay from " . tablename('bj_qmxk_member') . " a LEFT JOIN " . tablename('bj_qmxk_profit_member') . " b ON a.from_user = b.from_user where a.weid = ".$_W['weid']." and a.salesmantime < ".$t2." and b.months='".$months."'";
			$list2 = pdo_fetchall($sql);
			foreach($list2 as $v){
				$txt .= $v['realname'].",".$v['mobile'].",".$avaprofit.",".$v['bankperson'].','.$v['bankprovince'].$v['bankcity'].$v['bankcounty'].','.$v['bankbelong'].','.$v['bankcard']."：".$v['banktype'].",".$v['alipay'];
				if($ispay){
					$txt .= ",已发放"."\n";
				}else{
					$txt .= ",未发放"."\n";
				}
			}
			fwrite($myfile, $txt);
			fclose($myfile);
	
		
		include $this->template('profit');

}

/*if ($op == 'list') {
    $where = " where weid = ".$_W['weid']." and issalesman = 1";
    if($_GPC['realname']){
        $realname = $_GPC['realname'];
        $where .=" and realname like '%" . $realname . "%'";
    }
    if($_GPC['mobile']){
        $mobile = $_GPC['mobile'];
        $where .=" and mobile =" . $mobile ;
    }
    
    //查找状态为未分红的总代
    $noprofit = pdo_fetchall("select * from " . tablename("bj_qmxk_member") . $where . " and isprofit = 0");
    foreach ($noprofit as $v){
        // 1、有十个直1消费圈
        $date1 = pdo_fetchcolumn('select count(*) FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND shareid=:shareid ",array(
            ':weid' => $_W['weid'],
            ':shareid' => $noprofit['id']
        ));
        if($date1>=10){
            pdo_update('bj_qmxk_member',array(
                'isprofit' => 1
            ),array(
                id => $noprofit['id']
            ));
        }else{
            // 2、推荐一名总代
            $date2 = pdo_fetchcolumn('select count(*) FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND shareid=:shareid AND issalesman=1",array(
                ':weid' => $_W['weid'],
                ':shareid' => $noprofit['id']
            ));
            if($date2>0){
                pdo_update('bj_qmxk_member',array(
                    'isprofit' => 1
                ),array(
                    id => $noprofit['id']
                ));
            }
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $total = pdo_fetchcolumn("select count(id) from" . tablename('bj_qmxk_member') . $where . " and isprofit > 0");  
    $pager = pagination1($total, $pindex, $psize);
    $list = pdo_fetchall("select * from " . tablename("bj_qmxk_member") .  $where . " and isprofit > 0 limit " . ($pindex - 1) * $psize . ',' . $psize);
    include $this->template('profit_list');
    
}
if($op == 'check'){
    $id = $_GPC['id'];
    pdo_update('bj_qmxk_member',array(
        'isprofit'=>'2',
    ),array(
        'id'=>$id
    ));
    $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $id);
    //发送微信消息给用户
    $msg = "恭喜您可以参加利润分红！";
    $this->sendcustomMsg($user[from_user],$msg);
    
    message('设置'.$user['realname'].'成为可以分红的总代成功！', $this->createWebUrl('profit',array('op'=>'list')) , 'success');
}*/
if($op == 'send'){
   	$gz = intval($_GPC['gz']);
	if($_GPC['opp']=='sent'){
	    //echo "ok";exit;
		
		if($_GPC['ispay']){
			pdo_update('bj_qmxk_profit_member',array(
				'ispay' => 1,
				'profit' => $gz
			),array(
				'weid' => $_W['weid'],
				'from_user' => $_GPC['from_user'],
				'months' => $_GPC['months']
			));
			
			$paylog = array(
                    'type' => 'zhifu',
                    'weid' => $_W['weid'],
                    'openid' => $_GPC['from_user'],
                    'tid' => date('Y-m-d H:i:s') ,
                    'fee' => $gz,
                    'module' => 'bj_qmxk',
                    'tag' => ' 后台工资打款' . $gz . '元'
                );
            pdo_insert('paylog', $paylog);
			//发送微信消息给用户
			$msg = "您".$_GPC['months']."的工资已成功发放，工资为：".$gz."，请及时查询你绑定的支付宝或银行账户余额！";
			$this->sendcustomMsg($_GPC['from_user'],$msg);
		
			message('工资发放成功！', $this->createWebUrl('profit',array('months'=>$_GPC['months'])) , 'success');
		}else{	
			
			message('暂未处理！', $this->createWebUrl('profit',array('months'=>$_GPC['months'])) , 'success');
		}
		
	}else{
		$user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and  from_user = '" . $_GPC['from_user']."'");
		
		
		include $this->template('profit_send');
	   	exit;
   }
   	

}

if($op == 'modify'){
    pdo_update('bj_qmxk_profit',array(
        'allprofit'=>$_GPC['allprofit'],
        'modify'=>1
    ),array(
        'months'=>$_GPC['months']
    ));
    message('利润修改成功！', $this->createWebUrl('profit',array('months'=>$_GPC['months'])) , 'success');
}

if($op == 'tj'){
    
    $total = pdo_fetchcolumn("select sum(allprofit) from ".tablename("bj_qmxk_profit")." where weid = ".$_W['weid']);
    include $this->template('profit_tj');
}

?>