<?php
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
    ':weid' => $_W['weid']
));
$logincredit_arr=unserialize($theone["logincredit"]);
$sharecredit_arr=unserialize($theone["sharecredit"]);
$id = $theone['id'];
$msgtemplate = array();
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'gmsptz'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['gmsptz'] = $tmsgtemplate['template'];
    $msgtemplate['gmsptzenable'] = $tmsgtemplate['tenable'];
}
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'tjrtz'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['tjrtz'] = $tmsgtemplate['template'];
    $msgtemplate['tjrtzenable'] = $tmsgtemplate['tenable'];
}
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'tjrtzewm'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['tjrtzewm'] = $tmsgtemplate['template'];
    $msgtemplate['tjrtzewmenable'] = $tmsgtemplate['tenable'];
}
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'tjrtzdl'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['tjrtzdl'] = $tmsgtemplate['template'];
    $msgtemplate['tjrtzdlenable'] = $tmsgtemplate['tenable'];
}
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'xjdlshtz'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['xjdlshtz'] = $tmsgtemplate['template'];
    $msgtemplate['xjdlshtzenable'] = $tmsgtemplate['tenable'];
}
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'yjsqtz'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['yjsqtz'] = $tmsgtemplate['template'];
    $msgtemplate['yjsqtzenable'] = $tmsgtemplate['tenable'];
}
$tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
    ':weid' => $_W['weid'],
    ':key' => 'sjytktz'
));
if (!empty($tmsgtemplate['id'])) {
    $msgtemplate['sjytktz'] = $tmsgtemplate['template'];
    $msgtemplate['sjytktzenable'] = $tmsgtemplate['tenable'];
}
if (checksubmit('submit') || checksubmit('submit2')) {
    $clickcredit = $_GPC['clickcredit'];
    $logincredit =serialize($_GPC['logincredit']);
    $sharecredit =serialize($_GPC['sharecredit']);
    if (!is_numeric($clickcredit)) {
        message('点击或扫描积分请输入合法数字！');
    }
    if ($_GPC['promotertimes'] == '2') {
        if (!is_numeric($_GPC['promotercount'])) {
            message('达到单数请输入合法数字！');
        }
    }
    if ($_GPC['promotertimes'] == '3') {
        if (!is_numeric($_GPC['promotermoney'])) {
            message('达到金额请输入合法数字！');
        }
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'gmsptz'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'gmsptz',
            'template' => $_GPC['gmsptz'],
            'tenable' => intval($_GPC['gmsptzenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['gmsptz'],
            'tenable' => intval($_GPC['gmsptzenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'tjrtz'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'tjrtz',
            'template' => $_GPC['tjrtz'],
            'tenable' => intval($_GPC['tjrtzenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['tjrtz'],
            'tenable' => intval($_GPC['tjrtzenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'tjrtzewm'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'tjrtzewm',
            'template' => $_GPC['tjrtzewm'],
            'tenable' => intval($_GPC['tjrtzewmenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['tjrtzewm'],
            'tenable' => intval($_GPC['tjrtzewmenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'tjrtzdl'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'tjrtzdl',
            'template' => $_GPC['tjrtzdl'],
            'tenable' => intval($_GPC['tjrtzdlenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['tjrtzdl'],
            'tenable' => intval($_GPC['tjrtzdlenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'xjdlshtz'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'xjdlshtz',
            'template' => $_GPC['xjdlshtz'],
            'tenable' => intval($_GPC['xjdlshtzenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['xjdlshtz'],
            'tenable' => intval($_GPC['xjdlshtzenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'yjsqtz'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'yjsqtz',
            'template' => $_GPC['yjsqtz'],
            'tenable' => intval($_GPC['yjsqtzenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['yjsqtz'],
            'tenable' => intval($_GPC['yjsqtzenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $tmsgtemplate = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_msg_template') . " WHERE  weid = :weid and tkey = :key", array(
        ':weid' => $_W['weid'],
        ':key' => 'sjytktz'
    ));
    if (empty($tmsgtemplate['id'])) {
        $datas = array(
            'weid' => $_W['weid'],
            'tkey' => 'sjytktz',
            'template' => $_GPC['sjytktz'],
            'tenable' => intval($_GPC['sjytktzenable'])
        );
        pdo_insert('bj_qmxk_msg_template', $datas);
    } else {
        $datas = array(
            'template' => $_GPC['sjytktz'],
            'tenable' => intval($_GPC['sjytktzenable'])
        );
        pdo_update('bj_qmxk_msg_template', $datas, array(
            'id' => $tmsgtemplate['id']
        ));
    }
    $insert = array(
        'weid' => $_W['weid'],
        'clickcredit' => $clickcredit,
    	'sharecredit'=>$sharecredit,
    	'logincredit'=>$logincredit,
        'rule' => htmlspecialchars_decode($_GPC['rule']) ,
        'terms' => htmlspecialchars_decode($_GPC['terms']) ,
        'commtime' => 0,
        'promotertimes' => $_GPC['promotertimes'],
        'promotermoney' => $_GPC['promotermoney'],
        'promotercount' => $_GPC['promotercount'],
        'ischeck' => $_GPC['ischeck'],
        'createtime' => TIMESTAMP
    );
    if (empty($id)) {
        pdo_insert('bj_qmxk_rules', $insert);
        !pdo_insertid() ? message('保存失败, 请稍后重试.', 'error') : '';
    } else {
        if (pdo_update('bj_qmxk_rules', $insert, array(
            'id' => $id
        )) === false) {
            message('更新失败, 请稍后重试.', 'error');
        }
    }
    message('更新成功！', $this->createWebUrl('rules') , 'success');
}
include $this->template('rules'); 
?>