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
/**
*商品评论
 */

$operation = $_GPC['op'];
if ($operation == 'publish') {
$from_user=$_GPC["from_user"];
$goods_id=$_GPC["goods_id"];
$content=$_GPC["content"];
if($from_user && $goods_id && $content){
	//当前用户有没有购买该商品并且订单是已经完成的
	$verify=pdo_fetchcolumn("select count(*)  from  ".tablename('bj_qmxk_order_goods')."  og  
			left join  ".tablename('bj_qmxk_order')."  o on  o.id =og.orderid  
			where  og.weid=".$_W['weid']." and o.from_user='".$from_user."' and o.status=3 and og.goodsid=$goods_id
			");
	if($verify){
		$data=array(
				"from_user"=>$from_user,
				"goods_id"=>$goods_id,
				"weid"=>$_W["weid"],
				"createtime"=>date("Y-m-d H:i:s"),
				"content"=>$content
		);
		pdo_insert("bj_qmxk_goods_comment",$data);
		if(pdo_insertid()){
			$return["code"]="success";
			$return["data"]="评论成功!请耐心等待审核";
		}else{
			$return["code"]="error";
			$return["data"]="评论失败!";
		}
	}else{
		$return["code"]="error";
		$return["data"]="对不起您还未购买该商品!";
	}
	
}else{
	$return["code"]="error";
	$return["data"]="参数错误";
}

echo json_encode($return);
} 
?>