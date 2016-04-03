<?php
$cfg = $this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'comment';
if($operation=="comment"){
	$pindex = max(1, intval($_GPC['page']));
	$psize =20;
	$condition = '';
	if (!empty($_GPC['keyword'])) {
		$condition.= " AND (g.title LIKE '%{$_GPC['keyword']}%' or gc.content LIKE '%{$_GPC['keyword']}%')";
	}
	
	if (!empty($_GPC['status'])) {
		$condition.= " AND gc.status = '" . intval($_GPC['status']) . "'";
	}
	if(!empty($_GPC["goods_id"])){
		$condition.= " AND g.id = '" . intval($_GPC['goods_id']) . "'";
	}
	if(!empty($_GPC["realname"])){
		$condition.= " AND  m.realname LIKE '%{$_GPC['realname']}%'";
	}
	$list = pdo_fetchall("SELECT g.title,m.realname,m.id as mid,gc.remark,gc.createtime,gc.content ,gc.status,gc.audittime,gc.id  FROM " . tablename('bj_qmxk_goods_comment') . "   gc left join  ".tablename('bj_qmxk_member')." m
			on gc.from_user=m.from_user
			left join " . tablename('bj_qmxk_goods') . " g on g.id=gc.goods_id
			WHERE gc.weid = '{$_W['weid']}'  $condition ORDER BY gc.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('bj_qmxk_goods_comment') . "   gc left join  ".tablename('bj_qmxk_member')." m
			on gc.from_user=m.from_user
			left join " . tablename('bj_qmxk_goods') . " g on g.id=gc.goods_id
			WHERE gc.weid = '{$_W['weid']}'  $condition ORDER BY gc.createtime DESC");
	
	$pager = pagination($total, $pindex, $psize);
	if (checksubmit('doveify')) {
		$id_arr=$_GPC["childrencheck"];
		$id_string=implode(",", $id_arr);
		$c_status=$_GPC["c_status"];
		$remark=$_GPC["remark"];
		$sql="update  " . tablename('bj_qmxk_goods_comment') . " set audittime='".date("Y-m-d H:i:s")."',operator='".$_W['username']."', status=$c_status,remark='".$remark."' where  id in($id_string)";
		pdo_fetch($sql);
		message('操作成功！', referer() , 'success');
	}
}

include $this->template('comment'); 
?>