<?php
$cfg = $this->module['config'];
$modules = 'share';
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if (!empty($_GPC['title'])) {
        $condition.= " AND title LIKE '%{$_GPC['title']}%'";
    }
    if (!empty($_GPC['reader'])) {
        $condition.= " AND c.realname LIKE '%{$_GPC['reader']}%'";
    }
    if (!empty($_GPC['sharer'])) {
        $condition.= " AND d.realname LIKE '%{$_GPC['sharer']}%'";
    }
    $list = pdo_fetchall("SELECT a.*,b.title,c.realname as reader,d.realname as sharer FROM " . tablename('bj_qmxk_article_share') . " a 
        LEFT JOIN " . tablename('bj_qmxk_article') . " b ON a.article_id = b.id LEFT JOIN " . tablename('bj_qmxk_member') . " c ON 
        a.reader_id = c.from_user LEFT JOIN " . tablename('bj_qmxk_member') . " d ON a.sharer_id = d.from_user WHERE 1=1 $condition ORDER BY 
        reading_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('bj_qmxk_article_share') . " a LEFT JOIN " . tablename('bj_qmxk_article') . " b 
        ON a.article_id = b.id LEFT JOIN " . tablename('bj_qmxk_member') . " c ON a.reader_id = c.from_user LEFT JOIN 
        " . tablename('bj_qmxk_member') . " d ON a.sharer_id = d.from_user WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);
}
include $this->template('article_share');
?>