<?php
$cfg = $this->module['config'];
$modules = 'column';
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'post') {	
	$id = intval($_GPC['id']);
	if (!empty($id)){
	    $item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_article_column') . " WHERE id = :id", array(':id' => $id));
        if (empty($item)) {
            message('抱歉，文章栏目不存在或是已经删除！', '', 'error');
        }
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['column_name'])) {
			message('请输入栏目名称！');
		}
		$data = array(
			'column_name' => $_GPC['column_name'],
			'displayorder' => $_GPC['displayorder'],
            'isopen' => intval($_GPC['isopen']),
		);
		if (!empty($_FILES['pic']['tmp_name'])) {
		    file_delete($_GPC['pic_old']);
		    $upload = file_upload($_FILES['pic']);
		    if (is_error($upload)) {
		        message($upload['message'], '', 'error');
		    }
		    $data['pic'] = $upload['path'];
		}
		if (!empty($id)) {
			pdo_update('bj_qmxk_article_column', $data, array('id' => $id));
		} else {
			pdo_insert('bj_qmxk_article_column', $data);
		}
		message('文章栏目更新成功！', $this->createWebUrl('article_column', array('op' => 'post','id' => $id)) , 'success');
	}
} elseif ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = '';
    if (!empty($_GPC['column_name'])) {
        $condition.= " AND column_name LIKE '%{$_GPC['column_name']}%'";
    }
    if (isset($_GPC['isopen']) && $_GPC['isopen'] != '') {
        $condition.= " AND isopen = '" . intval($_GPC['isopen']) . "'";
    }
    $list = pdo_fetchall("SELECT a.*,count(b.id) as num FROM " . tablename('bj_qmxk_article_column') . " a LEFT JOIN " . tablename('bj_qmxk_article') . " b 
        on a.id = b.column_id WHERE 1=1 $condition GROUP BY a.id ORDER BY displayorder LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_article_column') . " WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM " . tablename('bj_qmxk_article_column') . " WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，栏目不存在或是已经被删除！');
    }
    pdo_delete('bj_qmxk_article_column', array('id' => $id));
    message('删除成功！', referer() , 'success');
}
include $this->template('article_column'); 
?>