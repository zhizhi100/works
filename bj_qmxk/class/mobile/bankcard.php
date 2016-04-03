<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Your Name <you@example.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id:$

$weid = $_W['weid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$rule = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rule') . " WHERE `weid` = :weid ", array(
    ':weid' => $_W['weid']
));
if (empty($from_user)) {
    message('你想知道怎么加入么?', $rule['gzurl'], 'sucessr');
    exit;
}
$id = $profile['id'];
if (intval($profile['id']) && $profile['status'] == 0) {
    include $this->template('forbidden');
    exit;
}
if (empty($profile)) {
    message('请先注册', $this->createMobileUrl('register') , 'error');
    exit;
}
if ($op == 'edit') {
    $data = array(
        'bankcard' => $_GPC['bankcard'],
        'banktype' => $_GPC['banktype'],
        'alipay' => $_GPC['alipay'],
        'wxhao' => $_GPC['wxhao']
    );
    if (!empty($data['bankcard']) && !empty($data['banktype'])) {
        pdo_update('bj_qmxk_member', $data, array(
            'from_user' => $from_user
        ));
        if ($_GPC['opp'] == 'complated') {
            echo 3;
            exit;
        }
        echo 1;
    } else {
        echo 0;
    }
    exit;
}
include $this->template('bankcard'); 
?>