<?php
$cfg = $this->module['config'];
$modules = 'receive';
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if (!empty($_GPC['realname'])) {
        $condition.= " AND b.realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['voucher_name'])) {
        $condition.= " AND c.voucher_name LIKE '%{$_GPC['voucher_name']}%'";
    }
    if (isset($_GPC['state']) && $_GPC['state'] != '') {
        $condition.= " AND a.state = '" . intval($_GPC['state']) . "'";
    }
    $list = pdo_fetchall("SELECT a.*,b.realname,c.voucher_name FROM " . tablename('bj_qmxk_voucher_receive') . " a LEFT JOIN 
        " . tablename('bj_qmxk_member') . " b ON a.member_id = b.from_user LEFT JOIN " . tablename('bj_qmxk_voucher') . " c ON 
        a.voucher_id = c.id WHERE 1=1 $condition ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('bj_qmxk_voucher_receive') . " a LEFT JOIN " . tablename('bj_qmxk_member') . " b 
        ON a.member_id = b.from_user LEFT JOIN " . tablename('bj_qmxk_voucher') . " c ON a.voucher_id = c.id WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);
}
include $this->template('voucher_receive'); 
?>