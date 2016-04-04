1、从原始文件复制的状态
已知问题：
A、安装、卸载脚本已完成表结构创建，数据库函数SEQ创建脚本有bug。
B、前端部分依赖的内容不在插件目录，we7默认不带这部分资源，主要是前端组件。
修复方法：需要的资源都放在插件目录，不对整体we7环境依赖，需要修改引用位置。
C、后端业务逻辑部分，因为we7版本不一致，表结构不同，需要添加表字段或添加同义词。
D、扩展的菜单已通过修改插件XML文件完成，具体结果需详细测试。
E、商品管理的几个标签出不来。

小问题：
0x01,插件版权信息修改。
0x02,seq函数创建错误。
0x03,在bj_qmxk\template\mobile\share_detail.html文件中引用modules/juyousha/中的内容，实际juyousha不存在。
0x04,在bj_qmxk\template\mobile\cart.html中，有{BJ_QMXK_ROOT}/images/jquery.gcjs.js的引用，需要处理{BJ_QMXK_ROOT}的值。
0x05,原厂商拼了recouse,应该是recourse或resource吧？
0x06,手机端themes显示的问题
0x07,bj_qmxk\template\mobile\rules.html中获取new_ueditor.all.min.js，根本没有此文件
0x08,犯浑，替换了部分文件中“分钱”“datetimepicker”等内容。


2、处理前端依赖组件问题 2016.4.3
0x01,订单处理，显示jquery.gcjs.js连接404错误。
bj_qmxk\template\common.html中：
  ./source/modules/bj_qmxk/images/jquery.gcjs.js修改为../addons/bj_qmxk/images/jquery.gcjs.js
  同样处理的还有jquery.form.js,tooltipbox.js
  
0x02,订单处理，显示datetimepicker.css连接404错误。
  copy文件到bj_qmxk\recouse\css\目录：daterangepicker.css,datetimepicker.css
  copy文件到bj_qmxk\recouse\js\目录：daterangepicker.js,datetimepicker.js
  修改bj_qmxk\class\core.php文件：
  ./resource/style/datetimepicker.css--->../addons/bj_qmxk/recouse/css/datetimepicker.css
  ./resource/script/datetimepicker.js--->../addons/bj_qmxk/recouse/js/datetimepicker.js
  
0x03,订单处理，显示jquery-ui-timepicker-addon.js连接404错误。
  引用如下：{BJ_QMXK_ROOT}/recouse/js/jquery-ui-timepicker-addon.js
  修改bj_qmxk\class\dev.php中BJ_QMXK_ROOT的定义：
  '/source/modules/bj_qmxk'----->'/addons/bj_qmxk'
  define ( 'BJ_QMXK_ROOT', '.' . BJ_QMXK_BASE );---->define ( 'BJ_QMXK_ROOT', BJ_QMXK_BASE );

0x04,大量资源引用问题：/resource/script/类引用
  bj_qmxk\template\applyed.html:src="./resource/script/daterangepicker.js"
  template\applyed_old.html ……
     详见附件1
  	全部替换：./resource/script/---->../addons/bj_qmxk/recouse/js/
  	缺少的内容需要copy到bj_qmxk\recouse\js\目录
  	./resource/script和../addons/bj_qmxk/recouse/js/都有common.js，内容不同，copy为common.web.js
  	
0x05,样式不一致的问题 ？？？？？？
   	原系统有yfnmall\data\tpl\web\default\modules\bj_qmxk目录
   	这个目录的文件如何处理？
	暂时不处理模板，先考虑样式统一
	在order.html中增加common.css的加载，在order.html中测试，可考虑修改common.html模板。
	在\addons\bj_qmxk\style\css\目录中添加common.css和bootstrap2.css两个文件，实际不能工作，多余。
	估计修改css文件不可行，开发商使用bootstrap version 2.3.2，微擎使用 bootstrap3，直接切换版本会影响微擎本身内容显示不正常。 
	
0x06,require模块调用的问题
	原厂商使用require模块调用，必须按规范把相应模块copy到对应的目录。
	微擎默认生成的require语句，但resource目录中没有相应的js与css文件，手工copy。
	copy不成功，估计需要下载完整版。
	从wz项目中复制resource\components目录。
  
  

附件1：
  E:\PHPPro\works\bj_qmxk\template\applyed.html (1 hit)
	Line 100: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\applyed_old.html (1 hit)
	Line 108: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\applying.html (1 hit)
	Line 102: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\applying_old.html (1 hit)
	Line 109: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\article.html (4 hits)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
	Line 83: <link type="text/css" rel="stylesheet" href="./resource/script/kindeditor/themes/default/default.css" />
	Line 84: <script type="text/javascript" src="./resource/script/kindeditor/kindeditor-min.js"></script>
	Line 85: <script type="text/javascript" src="./resource/script/kindeditor/lang/zh_CN.js"></script>
  E:\PHPPro\works\bj_qmxk\template\article_column.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\article_share.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\category.html (2 hits)
	Line 161: <script type="text/javascript" src="./resource/script/colorpicker/spectrum.js"></script>
	Line 162: <link type="text/css" rel="stylesheet" href="./resource/script/colorpicker/spectrum.css" />
  E:\PHPPro\works\bj_qmxk\template\comment.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\dailyshare.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\fansmanager.html (1 hit)
	Line 147: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\fansmanagered.html (1 hit)
	Line 117: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\fhapplyed.html (1 hit)
	Line 100: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\goods.html (4 hits)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
	Line 77: <link type="text/css" rel="stylesheet" href="./resource/script/kindeditor/themes/default/default.css" />
	Line 78: <script type="text/javascript" src="./resource/script/kindeditor/kindeditor-min.js"></script>
	Line 79: <script type="text/javascript" src="./resource/script/kindeditor/lang/zh_CN.js"></script>
  E:\PHPPro\works\bj_qmxk\template\invalid.html (1 hit)
	Line 98: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\mobile\cart.html (4 hits)
	Line 17: 	<script type="text/javascript" src="./resource/script/jquery-1.7.2.min.js"></script>
	Line 18: 	<script type="text/javascript" src="./resource/script/common.js?x={BJ_QMXK_VERSION}"></script>
	Line 23: {if $initNG}<script type="text/javascript" src="./resource/script/angular.min.js?x={BJ_QMXK_VERSION}"></script>{/if}
	Line 26: 	<script type="text/javascript" src="./resource/script/cascade.js?x={BJ_QMXK_VERSION}"></script>
  E:\PHPPro\works\bj_qmxk\template\mobile\confirm.html (1 hit)
	Line 12: 	<script type="text/javascript" src="./resource/script/jquery-1.7.2.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\mobile\detail.html (1 hit)
	Line 21: 	<script type="text/javascript" src="./resource/script/jquery-1.7.2.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\mobile\header.html (5 hits)
	Line 17: 	<script type="text/javascript" src="./resource/script/jquery-1.7.2.min.js"></script>
	Line 18: 	<script type="text/javascript" src="./resource/script/common.js?x={BJ_QMXK_VERSION}"></script>
	Line 24: 	<script type="text/javascript" src="./resource/script/bootstrap.js?x={BJ_QMXK_VERSION}"></script>
	Line 26: {if $initNG}<script type="text/javascript" src="./resource/script/angular.min.js?x={BJ_QMXK_VERSION}"></script>{/if}
	Line 29: 	<script type="text/javascript" src="./resource/script/cascade.js?x={BJ_QMXK_VERSION}"></script>
  E:\PHPPro\works\bj_qmxk\template\mobile\order.html (4 hits)
	Line 17: 	<script type="text/javascript" src="./resource/script/jquery-1.7.2.min.js"></script>
	Line 18: 	<script type="text/javascript" src="./resource/script/common.js?x={BJ_QMXK_VERSION}"></script>
	Line 23: {if $initNG}<script type="text/javascript" src="./resource/script/angular.min.js?x={BJ_QMXK_VERSION}"></script>{/if}
	Line 26: 	<script type="text/javascript" src="./resource/script/cascade.js?x={BJ_QMXK_VERSION}"></script>
  E:\PHPPro\works\bj_qmxk\template\mobile\rules.html (2 hits)
	Line 86: 		$.getScript('./resource/script/ueditor/ueditor.config.js', function(){
	Line 88: 				$.getScript('./resource/script/ueditor/lang/zh-cn/zh-cn.js',function(){
  E:\PHPPro\works\bj_qmxk\template\notice.html (1 hit)
	Line 102: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\phbmedal.html (2 hits)
	Line 40: <script type="text/javascript" src="./resource/script/colorpicker/spectrum.js"></script>
	Line 41: <link type="text/css" rel="stylesheet" href="./resource/script/colorpicker/spectrum.css" />
  E:\PHPPro\works\bj_qmxk\template\points_record.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\profit.html (1 hit)
	Line 141: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\profit_list.html (1 hit)
	Line 104: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\profit_tj.html (1 hit)
	Line 29: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\rules.html (3 hits)
	Line 3: <link rel="stylesheet" href="./resource/script/kindeditor/themes/default/default.css" />
	Line 4: 		<script charset="utf-8" src="./resource/script/kindeditor/kindeditor-min.js"></script>
	Line 5: 		<script charset="utf-8" src="./resource/script/kindeditor/lang/zh_CN.js"></script>
  E:\PHPPro\works\bj_qmxk\template\salary_display.html (1 hit)
	Line 152: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\salesman.html (1 hit)
	Line 120: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\salesman_allfans.html (1 hit)
	Line 90: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\salesman_check.html (1 hit)
	Line 118: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\salesman_conf.html (1 hit)
	Line 45: <script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
  E:\PHPPro\works\bj_qmxk\template\setting.html (3 hits)
	Line 366: <link type="text/css" rel="stylesheet" href="./resource/script/kindeditor/themes/default/default.css" />
	Line 367: <script type="text/javascript" src="./resource/script/kindeditor/kindeditor-min.js"></script>
	Line 368: <script type="text/javascript" src="./resource/script/kindeditor/lang/zh_CN.js"></script>
  E:\PHPPro\works\bj_qmxk\template\voucher.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
  E:\PHPPro\works\bj_qmxk\template\voucher_receive.html (1 hit)
	Line 3: <script type="text/javascript" src="./resource/script/jquery-ui-1.10.3.min.js"></script>
