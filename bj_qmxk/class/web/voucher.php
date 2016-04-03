<?php
$cfg = $this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'post') {
	$id = intval($_GPC['id']);
    if (!empty($id)){
	    $item = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_voucher') . " WHERE id = :id", array(
            ':id' => $id
        ));
        if (empty($item)) {
            message('抱歉，代金券不存在或是已经删除！', '', 'error');
        }
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['voucher_name'])) {
			message('请输入代金券名称！');
		}
		$data = array(
			'voucher_name' => $_GPC['voucher_name'],
            'money' => $_GPC['money'],
            'start_validity' => $_GPC['start_validity'],
            'end_validity' => $_GPC['end_validity'],
		    'add_time' => date('Y-m-d H:i:s'),
		    'operator' => $_W['username']
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
			pdo_update('bj_qmxk_voucher', $data, array('id' => $id));
		} else {
			pdo_insert('bj_qmxk_voucher', $data);
		}
		message('代金券更新成功！', $this->createWebUrl('voucher', array('op' => 'post','id' => $id)) , 'success');
	}
} elseif ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = '';
    if (!empty($_GPC['voucher_name'])) {
        $condition.= " AND voucher_name LIKE '%{$_GPC['voucher_name']}%'";
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_voucher') . " WHERE 1=1 $condition ORDER BY add_time DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('bj_qmxk_voucher') . " WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM " . tablename('bj_qmxk_voucher') . " WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，代金券不存在或是已经被删除！');
    }
    pdo_delete('bj_qmxk_voucher', array('id' => $id));
    message('删除成功！', referer() , 'success');
}
include $this->template('voucher'); 
?>