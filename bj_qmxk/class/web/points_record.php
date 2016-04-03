<?php
$cfg = $this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if (isset($_GPC['type']) && $_GPC['type'] != '') {
        $condition.= " AND a.type = '" . intval($_GPC['type']) . "'";
    }
    if (!empty($_GPC['realname'])) {
        $condition.= " AND b.realname LIKE '%{$_GPC['realname']}%'";
    }
    if (isset($_GPC['balance']) && $_GPC['balance'] != '') {
        $condition.= " AND a.balance = '" . intval($_GPC['balance']) . "'";
    }
    $list = pdo_fetchall("SELECT a.*,b.realname,b.id as mid FROM " . tablename('bj_qmxk_points_record') . " a LEFT JOIN  " . tablename('bj_qmxk_member') . " b 
        ON a.from_user = b.from_user WHERE 1=1 $condition ORDER BY a.addtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('bj_qmxk_points_record') . " a LEFT JOIN  " . tablename('bj_qmxk_member') . " b 
        ON a.from_user = b.from_user WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);
}
include $this->template('points_record'); 
?>