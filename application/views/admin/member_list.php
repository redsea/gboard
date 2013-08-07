<!-- member 리스트를 보여준다 -->
<html>
<head>
	<title>Member List</title>
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
	<script type="text/javascript" src="/resource/js/application/view/admin/member_list.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/member_list.js"></script>
	
	<script type="text/html" id="tpl-table-header">
		<th><div data-bind="text:user_id"></div></th>
		<th><div data-bind="text:email_address"></div></th>
		<th><div data-bind="text:user_name"></div></th>
		<th><div data-bind="text:nick_name"></div></th>
		<th><div data-bind="text:block"></div></th>
		<th><div data-bind="text:cdate"></div></th>
	</script>
	
	<script type="text/html" id="tpl-profiles">
		<tr>
			<td data-bind="text:size"></td>
			<td><img data-bind="attr:{src:preview}" /></td>
		</tr>
	</script>
</head>

<body>

<div id="window-buffer" class="window-buffer"></div>

<div class="window-container">
	<div class="view-port-page">
		<h1 class="content-title1" data-bind="text:txt_text_title"></h1>
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
	
	<div id="view-port-popup" class="view-port-popup">
		<div class="view-block"></div>
		<div id="view-popup" class="view-popup">
			<div class="view-popup-content">
				<div style="height:1px;"></div>
				<h1 class="popup-sub-title" data-bind="text:txt_title1_detail"></h1>
				<table class="view-detail-table" border="1">
					<tbody>
						<tr>
							<td data-bind="text:txt_row1_column1" class="table-td-title"></td>
							<td data-bind="text:val_row1_column1"></td>
							<td data-bind="text:txt_row1_column2" class="table-td-title"></td>
							<td data-bind="text:val_row1_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_row2_column1" class="table-td-title"></td>
							<td data-bind="text:val_row2_column1"></td>
							<td data-bind="text:txt_row2_column2" class="table-td-title"></td>
							<td data-bind="text:val_row2_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_row3_column1" class="table-td-title"></td>
							<td data-bind="text:val_row3_column1"></td>
							<td data-bind="text:txt_row3_column2" class="table-td-title"></td>
							<td data-bind="text:val_row3_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_row4_column1" class="table-td-title"></td>
							<td data-bind="text:val_row4_column1"></td>
							<td data-bind="text:txt_row4_column2" class="table-td-title"></td>
							<td data-bind="text:val_row4_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_row5_column1" class="table-td-title"></td>
							<td data-bind="text:val_row5_column1"></td>
							<td data-bind="text:txt_row5_column2" class="table-td-title"></td>
							<td data-bind="text:val_row5_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_row6_column1" class="table-td-title"></td>
							<td data-bind="text:val_row6_column1"></td>
							<td data-bind="text:txt_row6_column2" class="table-td-title"></td>
							<td data-bind="text:val_row6_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_row7_column1" class="table-td-title"></td>
							<td data-bind="text:val_row7_column1"></td>
							<td data-bind="text:txt_row7_column2" class="table-td-title"></td>
							<td data-bind="text:val_row7_column2"></td>
						</tr>
					</tbody>
				</table>
				<div style="height:10px;"></div>
				<h1 class="popup-sub-title" data-bind="text:txt_title2_detail"></h1>
				<table class="view-detail-table" border="1">
					<tbody>
						<tr>
							<td data-bind="text:txt2_row1_column1" class="table-td-title"></td>
							<td colspan="3" data-bind="text:val2_row1_column1"></td>
						</tr>
						<tr>
							<td data-bind="text:txt2_row1_column2" class="table-td-title"></td>
							<td colspan="3" data-bind="text:val2_row1_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt2_row2_column1" class="table-td-title"></td>
							<td data-bind="text:val2_row2_column1"></td>
							<td data-bind="text:txt2_row2_column2" class="table-td-title"></td>
							<td data-bind="text:val2_row2_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt2_row3_column1" class="table-td-title"></td>
							<td data-bind="text:val2_row3_column1"></td>
							<td data-bind="text:txt2_row3_column2" class="table-td-title"></td>
							<td data-bind="text:val2_row3_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt2_row4_column1" class="table-td-title"></td>
							<td data-bind="text:val2_row4_column1"></td>
							<td data-bind="text:txt2_row4_column2" class="table-td-title"></td>
							<td data-bind="text:val2_row4_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt2_row5_column1" class="table-td-title"></td>
							<td data-bind="text:val2_row5_column1"></td>
							<td data-bind="text:txt2_row5_column2" class="table-td-title"></td>
							<td data-bind="text:val2_row5_column2"></td>
						</tr>
						<tr>
							<td data-bind="text:txt2_row6_column1" class="table-td-title"></td>
							<td data-bind="text:val2_row6_column1"></td>
							<td data-bind="text:txt2_row6_column2" class="table-td-title"></td>
							<td data-bind="text:val2_row6_column2"></td>
						</tr>
					</tbody>
				</table>
				<div style="height:10px;"></div>
				<h1 class="popup-sub-title" data-bind="text:txt_title3_detail"></h1>
				<table class="view-detail-table" border="1">
					<thead>
						<td data-bind="text:txt_head_size" class="table-td-title"></td>
						<td data-bind="text:txt_head_preview" class="table-td-title"></td>
					</thead>
					<tbody data-bind="template:{name:'tpl-profiles', foreach:user_profiles}">
					</tbody>
				</table>
				<div style="height:10px;"></div>
				<button id="button-close-popup" data-bind="m_button:txt_close"></button>
				<div style="height:20px;"></div>
			</div>
		</div>
	</div>
	
	<div id="view-popup-dialog" class="view-popup-dialog" data-bind="attr:{title:txt_dialog_title}"></div>
</div>

</body>

</html>