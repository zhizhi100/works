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

$category = pdo_fetchall("SELECT * FROM " . tablename('bj_qmxk_category') . " WHERE weid = '{$_W['weid']}' and enabled=1 ORDER BY parentid ASC, displayorder DESC", array() , 'id');
foreach ($category as $index => $row) {
    if (!empty($row['parentid'])) {
        $children[$row['parentid']][$row['id']] = $row;
        unset($category[$index]);
    }
}
$carttotal = $this->getCartTotal();
$cfg = $this->module['config'];
$ydyy = $cfg['ydyy'];
$id = $profile['id'];
include $this->template('list_category_new'); 
?>
