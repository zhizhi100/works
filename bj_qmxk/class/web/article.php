<?php
$cfg = $this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$column = pdo_fetchall("SELECT id,column_name FROM ".tablename('bj_qmxk_article_column')." WHERE isopen =1 ORDER BY displayorder asc");
if ($operation == 'post') {	
	$id = intval($_GPC['id']);
	if (!empty($id)){
	    $item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_article') . " WHERE id = :id", array(
            ':id' => $id
        ));
        if (empty($item)) {
            message('抱歉，文章不存在或是已经删除！', '', 'error');
        }
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('请输入文章标题！');
		}
		$data = array(
            'column_id' => intval($_GPC['column_id']),
			'title' => $_GPC['title'],
            'content' => htmlspecialchars_decode($_GPC['content']),
            'describe' => $_GPC['describe'],
            'share_num' => intval($_GPC['share_num']),
            'read_num' => intval($_GPC['read_num']),
            'recommend' => intval($_GPC['recommend']),
			'add_time' => date('Y-m-d H:i:s'),
			'operator' => $_W['username'],
			'weid'=>$_W["weid"]
		);
		if (!empty($id)) {
			pdo_update('bj_qmxk_article', $data, array('id' => $id));
		} else {
			pdo_insert('bj_qmxk_article', $data);
		}
		message('文章更新成功！', $this->createWebUrl('article', array('op' => 'post','id' => $id)) , 'success');
	}
}elseif($operation == 'batch'){
	if($_GPC['alldelete'] && is_array($_GPC['chk'])){
		foreach ($_GPC['chk'] as $key => $v) {
			pdo_delete('bj_qmxk_article', array('id' => $v));
		}
		message('删除成功！', referer() , 'success');
	}
} elseif ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if (!empty($_GPC['title'])) {
        $condition.= " AND title LIKE '%{$_GPC['title']}%'";
    }
    if (isset($_GPC['column_id']) && $_GPC['column_id'] != '') {
        $condition.= " AND column_id = '" . intval($_GPC['column_id']) . "'";
    }
    if (isset($_GPC['recommend']) && $_GPC['recommend'] != '') {
        $condition.= " AND recommend = '" . intval($_GPC['recommend']) . "'";
    }
    $list = pdo_fetchall("SELECT a.*,b.column_name FROM " . tablename('bj_qmxk_article') . " a LEFT JOIN " . tablename('bj_qmxk_article_column') . " b 
        ON a.column_id = b.id WHERE 1=1 $condition ORDER BY add_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_article') . " a LEFT JOIN 
        " . tablename('bj_qmxk_article_column') . " b ON a.column_id = b.id WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM " . tablename('bj_qmxk_article') . " WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，文章不存在或是已经被删除！');
    }
    pdo_delete('bj_qmxk_article', array('id' => $id));
    message('删除成功！', referer() , 'success');
}
include $this->template('article'); 
?>