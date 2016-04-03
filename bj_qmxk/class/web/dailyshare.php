<?php
$cfg = $this->module['config'];
$pindex = max(1, intval($_GPC['page']));
$psize = 20;

$list=pdo_fetchall(" select s.*,m.realname,m.id as  mid  from  " . tablename('share_record') . " s left join
 " . tablename('bj_qmxk_member') . " m on m.id=s.share_id
		where s.weid=".$_W["weid"]." order by  sharetime desc  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

	$total = pdo_fetchcolumn(" select count(*)  from  " . tablename('share_record') . " s left join
 " . tablename('bj_qmxk_member') . " m on m.id=s.share_id
		where s.weid=".$_W["weid"]." order by  sharetime desc");

for($i=0;$i<count($list);$i++){
	$model=$list[$i]["model"];
	$record_id=$list[$i]["record_id"];
	$list[$i]["title"]=pdo_fetchcolumn(" select title  from  " . tablename('bj_qmxk_'.$model) . " where id=$record_id and  weid=".$_W["weid"]);
}
$pager = pagination($total, $pindex, $psize);
include $this->template('dailyshare');
?>