<?php

$weid = $_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$id = $profile['id'];
if ($op == 'display') {
    $opp = $_GPC['opp'];
    $rule = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE `weid` = :weid ", array(
        ':weid' => $_W['weid']
    ));
    $fans = pdo_fetch('SELECT realname FROM ' . tablename('fans') . " WHERE  weid = :weid  AND from_user = :from_user LIMIT 1", array(
        ':weid' => $_W['weid'],
        ':from_user' => $from_user
    ));
    if (empty($profile['realname'])) {
        $profile['realname'] = $fans['realname'];
    }
    $cfg = $this->module['config'];
    $ydyy = $cfg['ydyy'];
    include $this->template('register');
    exit;
}
$myfansx = pdo_fetch('SELECT nickname FROM ' . tablename('fans') . " WHERE `weid` = :weid AND from_user=:from_user limit 1", array(
    ':weid' => $_W['weid'],
    ':from_user' => $from_user
));
if (empty($myfansx['nickname']) && !empty($_GPC['realname'])) {
    pdo_update('fans', array(
        'nickname' => $_GPC['realname']
    ) , array(
        'id' => $myfansx['id']
    ));
}
if (!empty($profile['id'])) {
    $data = array(
        'realname' => $_GPC['realname'],
        'mobile' => $_GPC['mobile'],
        'pwd' => $_GPC['password'],
		'bankprovince' => $_GPC['province'],
		'bankcity' => $_GPC['city'],
		'bankcounty' => $_GPC['county'],
		'bankperson' => $_GPC['bankperson'],
		'bankbelong' => $_GPC['bankbelong'],
        'bankcard' => $_GPC['bankcard'],
        'banktype' => $_GPC['banktype'],
        'bankcard' => $_GPC['bankcard'],
        'banktype' => $_GPC['banktype'],
        'alipay' => $_GPC['alipay'],
        'wxhao' => $_GPC['wxhao'],
    );
    pdo_update('bj_qmxk_member', $data, array(
        'id' => $profile['id']
    ));
    echo 2;
    exit;
}
if ($op == 'add') {

    $shareids = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_share_history') . " WHERE  from_user=:from_user and weid=:weid limit 1", array(
        ':from_user' => $from_user,
        ':weid' => $_W['weid']
    ));
    if (!empty($shareids['sharemid'])) {
        $seid = $shareids['sharemid'];
    } else {
        $seid = 0;
    }
    $theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
        ':weid' => $_W['weid']
    ));
    if ($theone['promotertimes'] == 1) {
        $data = array(
            'weid' => $_W['weid'],
            'from_user' => $from_user,
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile'],
            'pwd' => $_GPC['password'],
            'alipay' => $_GPC['alipay'],
            'wxhao' => $_GPC['wxhao'],
            'commission' => 0,
            'createtime' => TIMESTAMP,
            'flagtime' => TIMESTAMP,
            'shareid' => $seid,
            'status' => 1,
            'flag' => 1
        );
    } else {
        $data = array(
            'weid' => $_W['weid'],
            'from_user' => $from_user,
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile'],
            'pwd' => $_GPC['password'],
            'alipay' => $_GPC['alipay'],
            'wxhao' => $_GPC['wxhao'],
            'commission' => 0,
            'createtime' => TIMESTAMP,
            'flagtime' => TIMESTAMP,
            'shareid' => $seid,
            'status' => 1,
            'flag' => 0
        );
    }
    if ($data['from_user'] == $profile['from_user']) {
        pdo_update('bj_qmxk_member', $data, array(
            'id' => $profile['id']
        ));
    } else {
        pdo_insert('bj_qmxk_member', $data);
    }
    if ($seid > 0) {
		 //判断此直1的分享者是否有10个直1，达到10个，可成为待审核经销商  2015/05/08
        $date = pdo_fetchcolumn('select count(*) FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND shareid=:shareid ",array(
            ':weid' => $_W['weid'],
            ':shareid' => $seid
        ));
        $res = pdo_fetch('select * from ' . tablename('bj_qmxk_salesman') . " where weid = " . $_W['weid']);
        if($date>=$res[member]){
            pdo_update('bj_qmxk_member',array(
                'ischeck' => 1
            ),array(
                id => $seid
            ));
        }
        $sharemember = pdo_fetch('SELECT from_user,id FROM ' . tablename('bj_qmxk_member') . " WHERE `weid` = :weid AND id=:id ", array(
            ':weid' => $_W['weid'],
            ':id' => $seid
        ));
        $joinfans = pdo_fetch('SELECT from_user,nickname FROM ' . tablename('fans') . " WHERE `weid` = :weid AND from_user=:from_user limit 1", array(
            ':weid' => $_W['weid'],
            ':from_user' => $from_user
        ));
        if (!empty($sharemember) && !empty($sharemember['id']) && !empty($joinfans['nickname']) && $theone['promotertimes'] == 1) {
            $this->sendtjrtzdl($joinfans['nickname'], $sharemember['from_user']);
        }
    }
    $theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
        ':weid' => $_W['weid']
    ));
    echo 1;
    exit;
} 
?>
