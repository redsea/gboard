<html>
<head>
	<title>Language</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="/resource/css/jquery/smoothness/jquery-ui-1.10.2.custom.min.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/common/content-common.css" type="text/css" />

	<link rel="stylesheet" href="/resource/css/application/admin/mlanguage.css" type="text/css" />
<!-- 	<link rel="stylesheet" href="/resource/css/application/component/quick-bar.css" type="text/css" /> -->
<!-- 	<link rel="stylesheet" href="/resource/css/application/component/tree.css" type="text/css" /> -->
<!-- 	<link rel="stylesheet" href="/resource/css/application/component/notification.css" type="text/css" /> -->
<!-- 	<link rel="stylesheet" href="/resource/css/application/component/simple-icon-list.css" type="text/css" /> -->

	<script type="text/javascript" src="/resource/js/lib/jquery/core/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="/resource/js/lib/jquery/ui/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="/resource/js/lib/knockout/knockout-2.2.1.js"></script>
<!-- 	<script type="text/javascript" src="/resource/js/application/model/network/gboard.js"></script> -->
	
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.cookie.js"></script>
	
<!-- 	<script type="text/javascript" src="/resource/js/application/component/quick-bar.js"></script> -->
<!-- 	<script type="text/javascript" src="/resource/js/application/component/tree.js"></script> -->
<!-- 	<script type="text/javascript" src="/resource/js/application/component/notification.js"></script> -->
<!-- 	<script type="text/javascript" src="/resource/js/application/component/simple-icon-list.js"></script> -->
	
	<script type="text/javascript" src="/resource/js/application/view/admin/mlanguage.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/mlanguage.js"></script>
	
	<!-- simple icon list template -->
	<script type="text/html" id="tpl-simple-icon-list-element">
		<li class="list-element-simple-icon" data-bind="click:clickThis">
			<div class="list-element-simple-icon-image" data-bind="style:{'backgroundImage':bg}"></div>
			<div class="list-element-simple-icon-text" data-bind="text:name1"></div>
		</li>
	</script>
</head>
<body>

<div id="window-buffer" class="window-buffer">
	<div id="window-hidden-language" class="window-hidden"><?=json_encode($language)?></div>
</div>

<div class="window-container">
	<ul data-bind="template:{name:'tpl-simple-icon-list-element', foreach:lang_list}"></ul>
</div>
</body>
</html>