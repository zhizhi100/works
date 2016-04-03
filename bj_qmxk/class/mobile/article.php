<?php
$cfg = $this->module['config'];

//$profile['id']   获取当前自己的mid
$now_mid=$profile['id'];
//$_GPC["mid"] //获取谁分享的
$mid=$_GPC["mid"] ;
$article_id=$_GPC["article_id"] ;
$article = pdo_fetch("SELECT * FROM " . tablename('bj_qmxk_article') . " WHERE id = :article_id", array(
		':article_id' => $article_id
));

$signPackage = $this->getSignPackage("article",array("mid"=>$now_mid,"article_id"=>$article_id));
$shareid = BAIJIA_COOKIE_SID . $_W['weid'];

if ((($_GPC['mid'] != $_COOKIE[$shareid]) && !empty($_GPC['mid']))) {
	$this->shareClick($_GPC['mid'], $_GPC['joinway']);
	setcookie($shareid, $this->getShareId() , time() + 3600 * 24 * $day_cookies);
}
$from_user = $this->getFromUser();
$this->autoRegedit('list');
$this->checkisAgent($from_user, $profile);
$fans = pdo_fetch("SELECT * FROM " . tablename('fans') . " WHERE from_user=:from_user and weid=:weid", array(
		':from_user' => $from_user,
		':weid' => $_W['weid']
));
if ($fans['follow'] != 1) {
	$shownotice = true;
}
$theone = pdo_fetch('SELECT * FROM ' . tablename('bj_qmxk_rules') . " WHERE  weid = :weid", array(
		':weid' => $_W['weid']
));
if($article){
    pdo_query("update " . tablename('bj_qmxk_article') . " set read_num=read_num+1 where id=:article_id", array(
        ":article_id" => $article_id
    ));
}
include $this->template('article'); 
?>
