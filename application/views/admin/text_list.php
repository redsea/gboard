<!-- 다국어 텍스트 리스트를 보여준다 -->
<html>
<head>
	<title>Language</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="/resource/css/jquery/smoothness/jquery-ui-1.10.2.custom.min.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/datatable/jquery.dataTables.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/datatable/jquery.dataTables_themeroller.css" type="text/css" />
	<link rel="stylesheet" href="/resource/css/application/common/content-common.css" type="text/css" />

	<script type="text/javascript" src="/resource/js/lib/jquery/core/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="/resource/js/lib/jquery/ui/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="/resource/js/lib/knockout/knockout-2.2.1.js"></script>
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.cookie.js"></script>
	<script type="text/javascript" src="/resource/js/lib/jquery/plugin/jquery.jeditable.js"></script>
	<script type="text/javascript" src="/resource/js/lib/datatable/jquery.dataTables.js"></script>
	
	<script type="text/javascript" src="/resource/js/application/model/admin/data/text.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/data/url.js"></script>
	<script type="text/javascript" src="/resource/js/application/view/admin/text_list.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/text_list.js"></script>
	
	<script type="text/html" id="tpl-table-header">
		<th width="20%"><div data-bind="text:code"></div></th>
		<th width="40%"><div data-bind="text:ko"></div></th>
		<th width="40%"><div data-bind="text:en"></div></th>
	</script>
</head>

<body>

<div id="window-buffer" class="window-buffer">
	<div id="window-hidden-language" class="window-hidden"><?=json_encode($language)?></div>
</div>

<div class="window-container">
	<div class="view-port-page">
		<h1 class="content-title1" data-bind="text:txt_text_title"></h1>
		<div class="content-helper">
			<p class="content-helper-text" data-bind="text:txt_text_list_help1"></p>
		</div>
		<div style="height:10px;"></div>
		<table class="datatable-highlight content-datatable" id="multi-language" cellpadding="0" cellspacing="0" border="0">
			<thead><tr data-bind="template:{name:'tpl-table-header', data:table_header}"></tr></thead>
			<tbody></tbody>
			<tfoot><tr data-bind="template:{name:'tpl-table-header', data:table_header}"></tr></tfoot>
		</table>
		<div style="height:10px;"></div>
	</div>
</div>

</body>

</html>