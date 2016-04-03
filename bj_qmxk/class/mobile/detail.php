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

$from_user = $this->getFromUser();
$fans = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE from_user=:from_user and weid=:weid", array(
    ':from_user' => $from_user,
    ':weid' => $_W['weid']
));
if (!empty($fans) && !empty($fans['id']) && $fans['follow'] != 1 && empty($_COOKIE[BAIJIA_COOKIE_OPENID . '_checkfollow' . $_W['weid']])) {
    setcookie(BAIJIA_COOKIE_OPENID . $_W['weid'], "", time() - 1);
    setcookie(BAIJIA_COOKIE_OPENID . '_checkfollow' . $_W['weid'], "1", time() + 300);
    $from_user = $this->getFromUser();
}
$this->validateopenid();
$cfg = $this->module['config'];
$day_cookies = 15;
$shareid = BAIJIA_COOKIE_SID . $_W['weid'];
if ((($_GPC['mid'] != $_COOKIE[$shareid]) && !empty($_GPC['mid']))) {
    $this->shareClick($_GPC['mid'], $_GPC['joinway']);
    setcookie($shareid, $this->getShareId() , time() + 3600 * 24 * $day_cookies);
}

$fx_user=$_GPC['fx_user'];
$beggerinfo=array();
if (isset($_GPC['fx_user'])){
       $beggerinfo = pdo_fetch("SELECT nickname,avatar FROM " . tablename('fans') . " WHERE from_user = :id", array(':id' =>$fx_user));
    
}
$this->autoRegedit('list');
$profile = $this->getProfile();
$this->checkisAgent($from_user, $profile);
$goodsid = intval($_GPC['id']);
$goods = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE id = :id", array(
    ':id' => $goodsid
));
$arr = $this->time_tran($goods['timeend']);
$goods['timelaststr'] = $arr[0];
$goods['timelast'] = $arr[1];
$ccate = intval($goods['ccate']);
$commission = pdo_fetchcolumn(" SELECT commission FROM " . tablename('bj_qmxk_goods') . " WHERE id=" . $goodsid . " ");
$member = pdo_fetch(" SELECT * FROM " . tablename('bj_qmxk_member') . " WHERE from_user='" . $from_user . "' AND weid=" . $_W['weid'] . " ");
if ($commission == false || $commission == null || $commission < 0) {
    $commission = $this->module['config']['globalCommission'];
}
if (empty($goods)) {
    message('抱歉，商品不存在或是已经被删除！');
}
if ($goods['totalcnf'] != 2 && empty($goods['total'])) {
    message('抱歉，商品库存不足！');;
}
if ($goods['istime'] == 1) {
    if (time() < $goods['timestart']) {
        message('抱歉，还未到购买时间, 暂时无法购物哦~', referer() , "error");
    }
    if (time() > $goods['timeend']) {
        message('抱歉，商品限购时间已到，不能购买了哦~', referer() , "error");
    }
}
$imgname_qrx = $profile['id']."share_qrx";
$imgurl_qrx = IA_ROOT . BJ_QMXK_BASE . "/style/images/share/$imgname_qrx";
$fname = $imgurl_qrx;
$a = glob("$fname*.png");
foreach($a as $k=>$v){
	unlink($v);
}
$this->memberQrcode1($from_user, $goodsid);
pdo_query("update " . tablename('bj_qmxk_goods') . " set viewcount=viewcount+1 where id=:id and weid='{$_W['weid']}' ", array(
    ":id" => $goodsid
));
$piclist = array(
    array(
        "attachment" => $goods['thumb']
    )
);
if ($goods['thumb_url'] != 'N;') {
    $urls = unserialize($goods['thumb_url']);
    if (is_array($urls)) {
        $piclist = array_merge($piclist, $urls);
    }
}
$signPackage = $this->getSignPackage('detail', array(
    'id' => $goods['id']
) , $_W['attachurl'] . $goods['thumb'], $goods['title']);
$marketprice = $goods['marketprice'];
$productprice = $goods['productprice'];
$stock = $goods['total'];
$allspecs = pdo_fetchall("select * from " . tablename('bj_qmxk_spec') . " where goodsid=:id order by displayorder asc", array(
    ':id' => $goodsid
));
foreach ($allspecs as & $s) {
    $s['items'] = pdo_fetchall("select * from " . tablename('bj_qmxk_spec_item') . " where  `show`=1 and specid=:specid order by displayorder asc", array(
        ":specid" => $s['id']
    ));
}
unset($s);
$options = pdo_fetchall("select id,title,thumb,marketprice,productprice,profit,costprice, stock,weight,specs from " . tablename('bj_qmxk_goods_option') . " where goodsid=:id order by id asc", array(
    ':id' => $goodsid
));
$specs = array();
if (count($options) > 0) {
    $specitemids = explode("_", $options[0]['specs']);
    foreach ($specitemids as $itemid) {
        foreach ($allspecs as $ss) {
            $items = $ss['items'];
            foreach ($items as $it) {
                if ($it['id'] == $itemid) {
                    $specs[] = $ss;
                    break;
                }
            }
        }
    }
}
$params = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods_param') . " WHERE goodsid=:goodsid order by displayorder asc", array(
    ":goodsid" => $goods['id']
));
$carttotal = $this->getCartTotal();
$rmlist = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . " WHERE weid = '{$_W['weid']}'  and deleted=0 AND status = '1' and ishot='1' ORDER BY displayorder DESC, sales DESC limit 4 ");
$ydyy = $cfg['ydyy'];
$description = $cfg['description'];
$id = $profile['id'];
$theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
    ':weid' => $_W['weid']
));
$fans = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE from_user=:from_user and weid=:weid", array(
    ':from_user' => $from_user,
    ':weid' => $_W['weid']
));
if ($fans['follow'] != 1) {
    $shownotice = true;
}
  // 获取聚友杀信息
    $openid = $_W['fans']['from_user'];
if (!empty($openid)) {
      $juinfo = $this->getClickInfo($goodsid, $openid);
}
if (!empty($profile['memberlevel'])){ 
    $memdiscount = pdo_fetchcolumn('SELECT discount FROM '.tablename('bj_qmxk_memberlevel')." WHERE  weid = :weid and id={$profile['memberlevel']}" , array(':weid' => $_W['weid']));  
}
//当前用户有没有购买该商品并且订单是已经完成的
$verify=pdo_fetchcolumn("select count(*)  from  ".tablename('bj_qmxk_order_goods')."  og
			left join  ".tablename('bj_qmxk_order')."  o on  o.id =og.orderid
			where  og.weid=".$_W['weid']." and o.from_user='".$from_user."' and o.status=3 and og.goodsid=$goodsid
		");
//评论
$pindex = max(1, intval($_GPC['page']));
$psize =20;
$comment= pdo_fetchall("SELECT g.title,m.realname,m.avatar,gc.remark,gc.createtime,gc.content ,gc.status,gc.audittime,gc.id  FROM " . tablename('bj_qmxk_goods_comment') . "   gc left join  ".tablename('fans')." m
			on gc.from_user=m.from_user
			left join " . tablename('bj_qmxk_goods') . " g on g.id=gc.goods_id
			WHERE gc.weid = '{$_W['weid']}'   and gc.status=2 and g.id=$goodsid  ORDER BY gc.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$comment_total= pdo_fetchcolumn("SELECT count(*)  FROM " . tablename('bj_qmxk_goods_comment') . "   gc left join  ".tablename('fans')." m
			on gc.from_user=m.from_user
			left join " . tablename('bj_qmxk_goods') . " g on g.id=gc.goods_id
		WHERE gc.weid = '{$_W['weid']}'   and gc.status=2  and g.id=$goodsid   ORDER BY gc.createtime DESC");
$pager = pagination($comment_total, $pindex, $psize);
include $this->template('detail'); 
?>
