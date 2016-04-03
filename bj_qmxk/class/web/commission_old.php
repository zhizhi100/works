<?php

$cfg = $this->module['config'];
$weid = $_W['weid'];
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$members = pdo_fetchall("select id, realname, mobile from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and status = 1");
$member = array();
foreach ($members as $m) {
    $member['realname'][$m['id']] = $m['realname'];
    $member['mobile'][$m['id']] = $m['mobile'];
}
if ($op == 'display') {
    if ($_GPC['opp'] == 'check') {
        $zhifucommission = $cfg['zhifuCommission'];
        if (!$zhifucommission) {
            message('请先在参数设置，设置佣金打款限额！', $this->createWebUrl('Commission') , 'success');
        }
        $shareid = $_GPC['shareid'];
        $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
        $bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $user['from_user'] . "'");
        if (!empty($user['shareid'])) {
            $user2 = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
        }
        if (!empty($user2['shareid'])) {
            $user3 = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user2['shareid']);
        }
        $info = pdo_fetch("select og.id, og.total, og.price, og.status, og.commission, og.commission2,og.commission3, og.applytime, og.content, g.title from " . tablename('bj_qmxk_order_goods') . " as og left join " . tablename('bj_qmxk_goods') . " as g on og.goodsid = g.id and og.weid = g.weid where og.id = " . $_GPC['id']);
        include $this->template('applying_detail');
        exit;
    }
    if ($_GPC['opp'] == 'checked') {
        $checked = array(
            'status' => $_GPC['status'],
            'checktime' => time() ,
            'content' => trim($_GPC['content'])
        );
        $temp = pdo_update('bj_qmxk_order_goods', $checked, array(
            'id' => $_GPC['id']
        ));
        if ($_GPC['status'] == 2) {
            $shareid = $_GPC['shareid'];
            $ogid = $_GPC['id'];
            $commission = array(
                'weid' => $_W['weid'],
                'mid' => $shareid,
                'ogid' => $ogid,
                'commission' => $_GPC['commission'],
                'content' => trim($_GPC['content']) ,
                'isshare' => 0,
                'createtime' => time()
            );
            if ($_GPC['commission'] > 0) {
                $temp = pdo_insert('bj_qmxk_commission', $commission);
                $commission = pdo_fetch("select from_user,commission from " . tablename('bj_qmxk_member') . " where id = " . $shareid);
                pdo_update('bj_qmxk_member', array(
                    'commission' => $commission['commission'] + $_GPC['commission']
                ) , array(
                    'id' => $shareid
                ));
                pdo_query("update " . tablename('bj_qmxk_member') . " SET zhifu=zhifu+'" . $_GPC['commission'] . "' WHERE id='" . $shareid . "' AND  weid=" . $_W['weid'] . "  ");
                $paylog = array(
                    'type' => 'zhifu',
                    'weid' => $_W['weid'],
                    'openid' => $commission['from_user'],
                    'tid' => date('Y-m-d H:i:s') ,
                    'fee' => $_GPC['commission'],
                    'module' => 'bj_qmxk',
                    'tag' => ' 后台打款' . $_GPC['commission'] . '元【1级会员佣金】'
                );
                pdo_insert('paylog', $paylog);
                $this->sendsjytktz($_GPC['commission'], '1', $commission['from_user']);
            }
            $user = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
            if (!empty($user['shareid']) && $cfg['globalCommissionLevel'] >= 2) {
                $user2 = pdo_fetch("select id from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
                if (!empty($user2)) {
                    if (!empty($_GPC['commission2']) && $cfg['globalCommissionLevel'] >= 2) {
                        $commission2 = array(
                            'weid' => $_W['weid'],
                            'mid' => $user['shareid'],
                            'ogid' => $ogid,
                            'commission' => $_GPC['commission2'],
                            'content' => trim($_GPC['content']) ,
                            'isshare' => 1,
                            'createtime' => time()
                        );
                        if ($_GPC['commission2'] > 0) {
                            pdo_insert('bj_qmxk_commission', $commission2);
                            $commission = pdo_fetch("select from_user,commission from " . tablename('bj_qmxk_member') . " where id = " . $user2['id']);
                            pdo_update('bj_qmxk_member', array(
                                'commission' => $commission['commission'] + $_GPC['commission2']
                            ) , array(
                                'id' => $user2['id']
                            ));
                            pdo_query("update " . tablename('bj_qmxk_member') . " SET zhifu=zhifu+'" . $_GPC['commission2'] . "' WHERE id='" . $user2['id'] . "' AND  weid=" . $_W['weid'] . "  ");
                            $paylog = array(
                                'type' => 'zhifu',
                                'weid' => $_W['weid'],
                                'openid' => $commission['from_user'],
                                'tid' => date('Y-m-d H:i:s') ,
                                'fee' => $_GPC['commission2'],
                                'module' => 'bj_qmxk',
                                'tag' => ' 后台打款' . $_GPC['commission2'] . '元【2级会员佣金】'
                            );
                            pdo_insert('paylog', $paylog);
                            $this->sendsjytktz($_GPC['commission2'], '2', $commission['from_user']);
                        }
                    }
                }
            }
            if (!empty($user2['id'])) {
                $nuser2 = pdo_fetch("select shareid from " . tablename('bj_qmxk_member') . " where id = " . $user2['id']);
            }
            if (!empty($nuser2['shareid']) && $cfg['globalCommissionLevel'] >= 3) {
                $nuser3 = pdo_fetch("select id from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $nuser2['shareid']);
                if (!empty($nuser3)) {
                    if (!empty($_GPC['commission3']) && $cfg['globalCommissionLevel'] >= 3) {
                        $commission3 = array(
                            'weid' => $_W['weid'],
                            'mid' => $nuser2['shareid'],
                            'ogid' => $ogid,
                            'commission' => $_GPC['commission3'],
                            'content' => trim($_GPC['content']) ,
                            'isshare' => 1,
                            'createtime' => time()
                        );
                        if ($_GPC['commission3'] > 0) {
                            pdo_insert('bj_qmxk_commission', $commission3);
                            $commission = pdo_fetch("select from_user,commission from " . tablename('bj_qmxk_member') . " where id = " . $nuser3['id']);
                            pdo_update('bj_qmxk_member', array(
                                'commission' => $commission['commission'] + $_GPC['commission3']
                            ) , array(
                                'id' => $nuser3['id']
                            ));
                            pdo_query("update " . tablename('bj_qmxk_member') . " SET zhifu=zhifu+'" . $_GPC['commission3'] . "' WHERE id='" . $nuser3['id'] . "' AND  weid=" . $_W['weid'] . "  ");
                            $paylog = array(
                                'type' => 'zhifu',
                                'weid' => $_W['weid'],
                                'openid' => $commission['from_user'],
                                'tid' => date('Y-m-d H:i:s') ,
                                'fee' => $_GPC['commission3'],
                                'module' => 'bj_qmxk',
                                'tag' => ' 后台打款' . $_GPC['commission3'] . '元【3级会员佣金】'
                            );
                            pdo_insert('paylog', $paylog);
                            $this->sendsjytktz($_GPC['commission3'], '3', $commission['from_user']);
                        }
                    }
                }
            }
            message('打款完成！', $this->createWebUrl('commission') , 'success');
        }
        if (empty($temp)) {
            message('审核失败，请重新审核！', $this->createWebUrl('commission', array(
                'opp' => 'check',
                'shareid' => $_GPC['shareid'],
                'id' => $_GPC['id']
            )) , 'error');
        } else {
            message('审核成功！', $this->createWebUrl('commission') , 'success');
        }
    }
    if ($_GPC['opp'] == 'sort') {
        $sort = array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile']
        );
        $shareid = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
        $list = pdo_fetchall("select o.shareid, o.status, g.id, g.applytime,g.commission,g.commission2,g.commission3  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = 1 and o.shareid in (" . $shareid . ") ORDER BY o.id desc");
        $total = sizeof($list);
    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select o.shareid, o.status, g.id, g.applytime,g.commission,g.commission2,g.commission3  from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = 1 ORDER BY o.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = 1");
        $pager = pagination1($total, $pindex, $psize);
    }
    if (!empty($list)) {
        foreach ($list as $key => $l) {
            $user = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $l['shareid']);
            if (empty($user['id'])) {
                $list[$key]['commission'] = 0;
                $list[$key]['commission2'] = 0;
                $list[$key]['commission3'] = 0;
            } else {
                $user2 = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
                if (empty($user2['id'])) {
                    $list[$key]['commission2'] = 0;
                    $list[$key]['commission3'] = 0;
                } else {
                    $user3 = pdo_fetch("select id from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user2['shareid']);
                    if (empty($user3['id'])) {
                        $list[$key]['commission3'] = 0;
                    }
                }
            }
        }
    }
    include $this->template('applying');
    exit;
}
if ($op == 'applyed') {
    if ($_GPC['opp'] == 'jieyong') {
        $shareid = $_GPC['shareid'];
        $user = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
        $bankcard = pdo_fetch("select id, bankcard, banktype,alipay from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and from_user = '" . $user['from_user'] . "'");
        if (!empty($user['shareid'])) {
            $user2 = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
        }
        if (!empty($user2['shareid'])) {
            $user3 = pdo_fetch("select * from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user2['shareid']);
        }
        $info = pdo_fetch("select og.id, og.total, og.price, og.status, og.commission, og.commission2,og.commission3, og.applytime, og.content, g.title from " . tablename('bj_qmxk_order_goods') . " as og left join " . tablename('bj_qmxk_goods') . " as g on og.goodsid = g.id and og.weid = g.weid where og.id = " . $_GPC['id']);
        include $this->template('applyed_detail');
        exit;
    }
    if ($_GPC['opp'] == 'sort') {
        $sort = array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile']
        );
        $shareid = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
        $list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime,g.commission,g.commission2,g.commission3 from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = 2 and o.shareid in (" . $shareid . ") ORDER BY o.checktime desc");
        $total = sizeof($list);
    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime,g.commission,g.commission2,g.commission3 from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = 2 ORDER BY g.checktime DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = 2");
        $pager = pagination1($total, $pindex, $psize);
    }
    if (!empty($list)) {
        foreach ($list as $key => $l) {
            $user = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $l['shareid']);
            if (empty($user['id'])) {
                $list[$key]['commission'] = 0;
                $list[$key]['commission2'] = 0;
                $list[$key]['commission3'] = 0;
            } else {
                $user2 = pdo_fetch("select id,shareid from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user['shareid']);
                if (empty($user2['id'])) {
                    $list[$key]['commission2'] = 0;
                    $list[$key]['commission3'] = 0;
                } else {
                    $user3 = pdo_fetch("select id from " . tablename('bj_qmxk_member') . " where flag=1 and id = " . $user2['shareid']);
                    if (empty($user3['id'])) {
                        $list[$key]['commission3'] = 0;
                    }
                }
            }
        }
    }
    include $this->template('applyed');
    exit;
}
if ($op == 'invalid') {
    if ($_GPC['opp'] == 'delete') {
        $delete = array(
            'status' => - 2
        );
        $temp = pdo_update('bj_qmxk_order_goods', $delete, array(
            'id' => $_GPC['id']
        ));
        if (empty($temp)) {
            message('删除失败，请重新删除！', $this->createWebUrl('commission', array(
                'op' => 'invalid'
            )) , 'error');
        } else {
            message('删除成功！', $this->createWebUrl('commission', array(
                'op' => 'invalid'
            )) , 'success');
        }
    }
    if ($_GPC['opp'] == 'detail') {
        $shareid = $_GPC['shareid'];
        $user = pdo_fetch("select realname, mobile from " . tablename('bj_qmxk_member') . " where id = " . $_GPC['shareid']);
        $info = pdo_fetch("select og.id, og.total, og.price, og.status, og.checktime, og.content, g.title from " . tablename('bj_qmxk_order_goods') . " as og left join " . tablename('bj_qmxk_goods') . " as g on og.goodsid = g.id and og.weid = g.weid where og.id = " . $_GPC['id']);
        include $this->template('invalid_detail');
        exit;
    }
    if ($_GPC['opp'] == 'invalided') {
        $invalided = array(
            'status' => $_GPC['status'],
            'content' => trim($_GPC['content'])
        );
        $temp = pdo_update('bj_qmxk_order_goods', $invalided, array(
            'id' => $_GPC['id']
        ));
        if (empty($temp)) {
            message('提交失败，请重新提交！', $this->createWebUrl('commission', array(
                'op' => 'invalid',
                'opp' => 'detail',
                'shareid' => $_GPC['shareid'],
                'id' => $_GPC['id']
            )) , 'error');
        } else {
            message('提交成功！', $this->createWebUrl('commission', array(
                'op' => 'invalid'
            )) , 'success');
        }
    }
    if ($_GPC['opp'] == 'sort') {
        $sort = array(
            'realname' => $_GPC['realname'],
            'mobile' => $_GPC['mobile']
        );
        $shareid = "select id from " . tablename('bj_qmxk_member') . " where weid = " . $_W['weid'] . " and realname like '%" . $sort['realname'] . "%' and mobile like '%" . $sort['mobile'] . "%'";
        $list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = -1 and o.shareid in (" . $shareid . ") ORDER BY o.id desc");
        $total = sizeof($list);
    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select o.shareid, o.status, g.id, g.checktime from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = -1 ORDER BY o.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize);
        $pager = pagination1($total, $pindex, $psize);
        $total = pdo_fetchcolumn("select count(o.id) from " . tablename('bj_qmxk_order') . " as o left join " . tablename('bj_qmxk_order_goods') . " as g on o.id = g.orderid and o.weid = g.weid where o.weid = " . $_W['weid'] . " and g.status = -1");
    }
    include $this->template('invalid');
    exit;
} 
?>
