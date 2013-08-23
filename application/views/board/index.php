<!-- document list 를 보여준다 -->
<html>
<head>
	<title>Document List</title>
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
	
	<script type="text/javascript" src="/resource/js/application/model/board/data/text.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/board/data/url.js"></script>
	<script type="text/javascript" src="/resource/js/application/view/board/board.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/board/board.js"></script>
	
	<script type="text/html" id="tpl-table-header">
		<th><div data-bind="text:doc_title"></div></th>
		<th><div data-bind="text:doc_author"></div></th>
		<th><div data-bind="text:doc_read"></div></th>
		<th><div data-bind="text:doc_like"></div></th>
		<th><div data-bind="text:doc_cdate"></div></th>
	</script>
	
	<script type="text/html" id="tpl-profiles">
		<tr>
			<td data-bind="text:size"></td>
			<td><img data-bind="attr:{src:preview}" /></td>
		</tr>
	</script>
</head>

<body>

<div id="window-buffer" class="window-buffer">
	<?php if($board_srl): ?>
	<div id="window-hidden-board-id" class="window-hidden">
		<?=$board_srl?>
	</div>
	<?php endif; ?>
	<?php if($categories): ?>
	<div id="window-hidden-category-info" class="window-hidden">
		<?=json_encode($categories)?>
	</div>
    <?php endif; ?>
</div>

<div class="window-container">
	<div class="view-port-page">
		<h1 class="content-title1"><?=$board_name?></h1>
		<div class="content-helper">
			<p class="content-helper-text" data-bind="text:txt_text_list_help1"></p>
		</div>
		<div style="height:10px;"></div>

		<table class="datatable-highlight content-datatable-non-fixed" id="data-tables1" cellpadding="0" cellspacing="0" border="0">
			<thead><tr data-bind="template:{name:'tpl-table-header', data:table_header}"></tr></thead>
			<tbody></tbody>
			<tfoot><tr data-bind="template:{name:'tpl-table-header', data:table_header}"></tr></tfoot>
		</table>
		<div style="height:10px;"></div>
	</div>
</div>

</body>

</html>