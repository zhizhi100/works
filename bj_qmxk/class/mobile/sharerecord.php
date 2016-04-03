<?php
$cfg = $this->module['config'];
//查询规则
$integral_rules = pdo_fetch("select sharecredit,clickcredit,logincredit  from
            		 " . tablename('bj_qmxk_rules') . "   where weid = " . $_W['weid']);

$sharecredit_integral_rules_arr=unserialize($integral_rules["sharecredit"]);  //把规则转数组（分享)
$logincredit_integral_rules_arr=unserialize($integral_rules["logincredit"]);  //把规则转数组（登录签到)

$logincredit_data_limit=$logincredit_integral_rules_arr["num"];  //登录签到
$logincredit_integral_limit=$logincredit_integral_rules_arr["credit"];

$sharecredit_data_limit=$sharecredit_integral_rules_arr["num"];  //各种分享
$sharecredit_integral_limit=$sharecredit_integral_rules_arr["credit"];





if($_GPC['op'] == 'applyed'){
	//添加分享记录
	$data["share_type"]=$_GPC['share_type'];
	$data["model"]=$_GPC['model'];
	$data["record_id"]=$_GPC['record_id'];
	$data["share_id"]=$_GPC['share_id'];
	$data["weid"]=$_W["weid"];
	$data["sharetime"]=date("Y-m-d H:i:s");
	pdo_insert("share_record",$data);
	$result=pdo_insertid();
	if($result){
		//执行分享后相关数据
		pdo_query("update " . tablename('bj_qmxk_'.$data["model"]) . " set share_num=share_num+1 where 
			id=".$data["record_id"]." and 	weid=".$data["weid"]);
		

		$share_info=pdo_fetch("select * from  " . tablename('bj_qmxk_member') . "   where weid = " . $_W['weid']." 
				and  id=".$data["share_id"]);
		
	//	if($share_info["flag"]==1){
			//是代理   那么 加积分
		
			//每天三次  一次5分  （时间+分享者id）
			//查询分享记录
			  $share_num=pdo_fetchcolumn("select count(*) from  ".tablename('bj_qmxk_points_record')."
    		 where from_user='".$share_info['from_user']."'  and (TO_DAYS(NOW())-TO_DAYS(`addtime`)=0)  and  type=1");  //分享	
			if($share_num<$sharecredit_data_limit){
				$integral["points"]=$sharecredit_integral_limit;
				$integral["type"]=1;
				$integral["from_user"]=$share_info['from_user'];
				$integral["balance"]=1;
				$integral["addtime"]=date("Y-m-d H:i:s");
				if($data["model"]=="article"){
					$integral["remark"]=$msg="分享文章，积分+".$sharecredit_integral_limit;
				}elseif($data["model"]=="goods"){
					$integral["remark"]=$msg="分享商品，积分+".$sharecredit_integral_limit;
				}
				
				pdo_insert("bj_qmxk_points_record",$integral);
				//修改积分
				if(pdo_insertid()){
					//修改积分
					$fans = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid and from_user=:from_user", array(
							':weid' => $_W['weid'],
							':from_user' => $share_info['from_user']
					));
					pdo_update('fans', array(
					'credit1' => $fans['credit1'] +$sharecredit_integral_limit
					) , array(
					'id' => $fans['id'],
					'weid' => $_W['weid']
					));
					
					$this->sendcustomMsg($share_info['from_user'],$msg);
				}
			}	
					
		//}			
		echo "success";
	}
}
if($_GPC['op'] == 'sign'){
    $from_user = $this->getFromUser();
    //查询积分记录
    $member_info=pdo_fetch("select * from  " . tablename('bj_qmxk_member') . "   where weid = " . $_W['weid']."
				and from_user='".$from_user."' ");
    
  //  if($member_info["flag"]==1){
	    $sign_num=pdo_fetchcolumn("select count(*) from  ".tablename('bj_qmxk_points_record')."
	    		 where from_user='".$from_user."'  and (TO_DAYS(NOW())-TO_DAYS(`addtime`)=0)  and  type=3");  //今日签到
	    if($sign_num<$logincredit_data_limit){
	    	 $data["remark"]=$msg="每日签到，积分+".$logincredit_integral_limit;
	        $data["points"]=$logincredit_integral_limit;
	        $data["type"]=3;
	        $data["from_user"]=$from_user;
	        $data["balance"]=1;
	        $data["addtime"]=date("Y-m-d H:i:s");
	        pdo_insert("bj_qmxk_points_record",$data);
	        //修改积分
	        if(pdo_insertid()){
	        	$fans = pdo_fetch('SELECT * FROM ' . tablename('fans') . " WHERE  weid = :weid and from_user=:from_user", array(
	        			':weid' => $_W['weid'],
	        			':from_user' => $from_user
	        	));
	        	pdo_update('fans', array(
	        	'credit1' => $fans['credit1'] +$logincredit_integral_limit
	        	) , array(
	        	'id' => $fans['id'],
	        	'weid' => $_W['weid']
	        	));
	        
	        	$this->sendcustomMsg($from_user,$msg);
	        	$return["code"]="success";
	        	$return["data"]=$logincredit_integral_limit;
	        	echo json_encode($return);
	        }
        }else{
      		$return["code"]="error";
        	$return["data"]="您今日已签到!";
        	echo json_encode($return);
  		}
    
//     }else{
//     	$return["code"]="error";
//     	$return["data"]="对不起，你还不是".$cfg["agent_title"];
//     	echo json_encode($return);
//     }
}
?>