<?php
$cfg = $this->module['config'];
$title = $cfg['shopname'];
if (empty($title)) {
	$title = '文章栏目';
}
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
$day_cookies = 15;
$shareid = BAIJIA_COOKIE_SID . $_W['weid'];
if ((($_GPC['mid'] != $_COOKIE[$shareid]) && !empty($_GPC['mid']))) {
	$this->shareClick($_GPC['mid'], $_GPC['joinway']);
	setcookie($shareid, $this->getShareId() , time() + 3600 * 24 * $day_cookies);
}
$this->autoRegedit('list');
$profile = $this->getProfile();
$this->checkisAgent($from_user, $profile);
$signPackage = $this->getSignPackage();
if ($fans['follow'] != 1) {
	$shownotice = true;
}
$id = intval($_GPC['id']);
//文章栏目
$column = pdo_fetchall("select * FROM " . tablename('bj_qmxk_article_column') . " WHERE isopen = 1 ORDER BY displayorder ASC,id DESC");
//栏目下所属文章
foreach ($column as & $c) {
    $c['list'] = pdo_fetchall("select * FROM " . tablename('bj_qmxk_article') . " WHERE column_id = '{$c['id']}' ORDER BY add_time DESC");
}
include $this->template('article_column'); 
?>
