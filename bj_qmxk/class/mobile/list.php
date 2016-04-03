<?php
$cfg = $this->module['config'];
$title = $cfg['shopname'];
if (empty($title)) {
    $title = '商城首页';
}
$from_user = $this->getFromUser();
$fans = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE from_user=:from_user and weid=:weid", array(
    ':from_user' => $from_user,
    ':weid' => $_W['weid']
));
if (!empty($fans) && !empty($fans['id']) && $fans['follow'] != 1 && empty($_COOKIE[BAIJIA_COOKIE_OPENID . '_checkfollow' . $_W['weid']])) {
    setcookie(BAIJIA_COOKIE_OPENID . $_W['weid'], "", time() - 1);
    setcookie(BAIJIA_COOKIE_OPENID . '_checkfollow' . $_W['weid'], "1", time() + 300);
}
$this->validateopenid();
$day_cookies = 15;
$shareid = BAIJIA_COOKIE_SID . $_W['weid'];
//if ((($_GPC['mid'] != $_COOKIE[$shareid]) && !empty($_GPC['mid']))) {
	$this->shareClick($_GPC['mid'], $_GPC['joinway']);
 //   setcookie($shareid, $_GPC['mid'] , time() + 3600 * 24 * $day_cookies);
//}
$this->autoRegedit('list');
$profile = $this->getProfile();

$signPackage = $this->getSignPackage();
if ($fans['follow'] != 1) {
	header("location:".$cfg["ydyy"]);
	exit();
    $shownotice = true;
}
//banner图
$advs = pdo_fetchall("select * FROM " . tablename('bj_qmxk_adv') . " WHERE enabled = 1 AND weid= '{$_W['weid']}' ORDER BY displayorder ASC");

foreach ($advs as & $adv) {
    if (substr($adv['link'], 0, 5) != 'http:') {
        $adv['link'] = $adv['link'];
    }
}
unset($adv);
//推广文章
$ars = pdo_fetchall("select * FROM " . tablename('bj_qmxk_article') . " WHERE recommend = 1 ORDER BY add_time DESC LIMIT 0,5");
//文章栏目
$column = pdo_fetchall("select * FROM " . tablename('bj_qmxk_article_column') . " WHERE isopen = 1 ORDER BY displayorder ASC,id DESC");

//分类（首页推荐）
$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}'
and isrecommand=1 AND  enabled=1 and parentid=0 order by displayorder asc ");

//$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}' and enabled=1 ORDER BY  displayorder asc", array() , 'id');
foreach ($category as $index => $row) {
		$goods[$row['id']]  = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_goods') . "
				WHERE weid = '{$_W['weid']}' and isrecommandcategory=1 and pcate=".$row['id']." and deleted=0 AND status = 1");
}



//商品
$condition = '  and  g.isrecommand=1';
$cfg = $this->module['config'];
$islist = pdo_fetchall("SELECT g. * FROM " . tablename('bj_qmxk_goods') . " g 
		WHERE g.weid = '{$_W['weid']}' 
 and  g.deleted=0 AND  g.status = '1'   $condition  ORDER BY  g.displayorder asc,g.sales DESC, g.id   DESC ");

$id = $profile['id'];
$theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
		':weid' => $_W['weid']
));


include $this->template('list_new'); 
?>
