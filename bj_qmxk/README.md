1����ԭʼ�ļ����Ƶ�״̬
��֪���⣺
A����װ��ж�ؽű�����ɱ�ṹ���������ݿ⺯��SEQ�����ű���bug��
B��ǰ�˲������������ݲ��ڲ��Ŀ¼��we7Ĭ�ϲ����ⲿ����Դ����Ҫ��ǰ�������
�޸���������Ҫ����Դ�����ڲ��Ŀ¼����������we7������������Ҫ�޸�����λ�á�
C�����ҵ���߼����֣���Ϊwe7�汾��һ�£���ṹ��ͬ����Ҫ��ӱ��ֶλ����ͬ��ʡ�
D����չ�Ĳ˵���ͨ���޸Ĳ��XML�ļ���ɣ�����������ϸ���ԡ�

С���⣺
0x01,�����Ȩ��Ϣ�޸ġ�
0x02,seq������������
0x03,��bj_qmxk\template\mobile\share_detail.html�ļ�������modules/juyousha/�е����ݣ�ʵ��juyousha�����ڡ�
0x04,��bj_qmxk\template\mobile\cart.html�У���{BJ_QMXK_ROOT}/images/jquery.gcjs.js�����ã���Ҫ����{BJ_QMXK_ROOT}��ֵ��
0x05,ԭ����ƴ��recouse,Ӧ����recourse��resource�ɣ�
0x06,�ֻ���themes��ʾ������
0x07,bj_qmxk\template\mobile\rules.html�л�ȡnew_ueditor.all.min.js������û�д��ļ�


2������ǰ������������� 2016.4.3
0x01,����������ʾjquery.gcjs.js����404����
bj_qmxk\template\common.html�У�
  ./source/modules/bj_qmxk/images/jquery.gcjs.js�޸�Ϊ../addons/bj_qmxk/images/jquery.gcjs.js
  ͬ������Ļ���jquery.form.js,tooltipbox.js
  
0x02,����������ʾdatetimepicker.css����404����
  copy�ļ���bj_qmxk\recouse\css\Ŀ¼��daterangepicker.css,datetimepicker.css
  copy�ļ���bj_qmxk\recouse\js\Ŀ¼��daterangepicker.js,datetimepicker.js
  �޸�bj_qmxk\class\core.php�ļ���
  ./resource/style/datetimepicker.css--->../addons/bj_qmxk/recouse/css/datetimepicker.css
  ./resource/script/datetimepicker.js--->../addons/bj_qmxk/recouse/js/datetimepicker.js
  
0x03,����������ʾjquery-ui-timepicker-addon.js����404����
  �������£�{BJ_QMXK_ROOT}/recouse/js/jquery-ui-timepicker-addon.js
  �޸�bj_qmxk\class\dev.php��BJ_QMXK_ROOT�Ķ��壺
  '/source/modules/bj_qmxk'----->'/addons/bj_qmxk'
  define ( 'BJ_QMXK_ROOT', '.' . BJ_QMXK_BASE );---->define ( 'BJ_QMXK_ROOT', BJ_QMXK_BASE );

0x04,������Դ�������⣺/resource/script/������
  bj_qmxk\template\applyed.html:src="./resource/script/daterangepicker.js"
  template\applyed_old.html ����
     �������1
  	ȫ���滻��./resource/script/---->../addons/bj_qmxk/recouse/js/
  	ȱ�ٵ�������Ҫcopy��bj_qmxk\recouse\js\Ŀ¼
  	./resource/script��../addons/bj_qmxk/recouse/js/����common.js�����ݲ�ͬ��copyΪcommon.web.js
  
  
  

����1��
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
