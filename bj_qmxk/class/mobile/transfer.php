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
$op = $_GPC['op'];
$from_user = $this->getFromUser();
if($op=="ajaxquery"){
	$member_num=trim(strtolower($_GPC["member_number"]));
	if(empty($member_num)){
		$return["code"]="error";
		$return["data"]="请输入转账对方的会员编号";
	}else{
		$mid=intval(trim(str_replace('qxcf','',$member_num)));
		$to_from_user=pdo_fetchcolumn("select from_user from ".tablename('bj_qmxk_member')." where id=".$mid." and weid=".$_W['weid']." and from_user<>'".$from_user."'");
		if($to_from_user){
			$fans_info=pdo_fetch("select avatar,follow,realname,nickname,createtime from  ".tablename('fans')." where from_user='".$to_from_user."' and weid=".$_W["weid"]);
			if($fans_info["realname"] || $fans_info["nickname"]){
				$result["realname"]=$fans_info["realname"]?$fans_info["realname"]:$fans_info["nickname"];
				$result["avatar"]=$fans_info["avatar"]?$fans_info["avatar"]:BJ_QMXK_ROOT."/style/images/yh.png";
				$result["createtime"]=$fans_info["follow"]?date('Y-m-d',$fans_info["createtime"]):"还未关注";
				$return["code"]="success";
				$return["data"]=$result;
			}else{
				$return["code"]="error";
				$return["data"]="查询信息有误!";
			}
		}else{
			$return["code"]="error";
			$return["data"]="暂时没有相关会员信息!";
		}
	}
	echo json_encode($return);
exit();
}elseif($op=="ajaxtransfer"){
	$member_num=trim(strtolower($_GPC["member_number"]));
	if(empty($member_num)){
		$return["code"]="error";
		$return["data"]="请输入转账对方的会员编号";
		echo json_encode($return);
		exit();
	}
	$transfer_money=$_GPC["transfer_money"];
	if(empty($transfer_money)){
		$return["code"]="error";
		$return["data"]="请输入转账转账金额";
		echo json_encode($return);
		exit();
	}
	$mid=intval(trim(str_replace('qxcf','',$member_num)));
	$from_fans_info=pdo_fetch("select avatar,follow,realname,nickname,createtime,credit3,id from  ".tablename('fans')." where from_user='".$from_user."' and weid=".$_W["weid"]);
	$to_from_user=pdo_fetchcolumn("select from_user from ".tablename('bj_qmxk_member')." where id=".$mid." and weid=".$_W['weid']." and  from_user<>'".$from_user."'");
	$fans_info=pdo_fetch("select avatar,follow,realname,nickname,createtime,credit3,id from  ".tablename('fans')." where from_user='".$to_from_user."' and weid=".$_W["weid"]);
	if($to_from_user && $fans_info){
		if($from_fans_info["realname"]){
			$realname=$from_fans_info["nickname"];
		}else{
			$realname=$from_fans_info["realname"];
		}
		if($fans_info["realname"]){
			$to_realname=$fans_info["nickname"];
		}else{
			$to_realname=$fans_info["realname"];
		}
		$data=array(
				"from_user"=>$from_user,
				"to_mid"=>$mid,
				"money"=>$transfer_money,
				"createtime"=>date("Y-m-d H:i"),
				"type"=>1,
				"weid"=>$_W["weid"],
				"from_realname"=>$realname,
				"to_realname"=>$to_realname
		);
		pdo_insert("bj_qmxk_transfer",$data);
		if(pdo_insertid()){
		
			pdo_update('fans', array(
			'credit3' => ($from_fans_info['credit3']-$transfer_money)
			) , array(
			'id' => $from_fans_info['id'],
			'weid' => $_W['weid']
			));
			$this->sendcustomMsg($from_user,"您好，成功转账".$transfer_money."元(".$to_realname."),现自由积分". ($from_fans_info['credit3']-$transfer_money)."元");
			
			
		    pdo_update('fans', array(
        	'credit3' => ($fans_info['credit3']+$transfer_money)
        	) , array(
        	'id' => $fans_info['id'],
        	'weid' => $_W['weid']
        	));
		  
        	$this->sendcustomMsg($to_from_user,"您好,(".$realname.")给您转账".$transfer_money."元,现自由积分".($fans_info['credit3'] +$transfer_money)."元");
        
		}
		$return["code"]="success";
		$return["data"]="恭喜您!转账成功";
	}else{
			$return["code"]="error";
			$return["data"]="暂时没有相关会员信息!";
	}
	echo json_encode($return);
}else{
	$mid=$this->getMid();
	//转账记录
	$transfer_list=pdo_fetchall("select * from  ".tablename('bj_qmxk_transfer')." where weid=".$_W["weid"]."   and from_user='".$from_user."'   ORDER BY createtime");
	//接收记录
	$receivelist=pdo_fetchall("select * from  ".tablename('bj_qmxk_transfer')." where weid=".$_W["weid"]."   and to_mid=$mid    ORDER BY createtime");
	$credit3=pdo_fetchcolumn("select credit3  from  ".tablename('fans')." where from_user='".$from_user."' and weid=".$_W["weid"]);
	include $this->template('transfer'); 
}
?>
