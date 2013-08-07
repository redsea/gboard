<!-- 등록한 file 리스트를 보여준다 -->
<html>
<head>
	<title>File List</title>
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
	<script type="text/javascript" src="/resource/js/application/view/admin/file_list.js"></script>
	<script type="text/javascript" src="/resource/js/application/model/admin/file_list.js"></script>
	
	<script type="text/html" id="tpl-table-header">
		<th><div data-bind="text:owner"></div></th>
		<th><div data-bind="text:file_name"></div></th>
		<th><div data-bind="text:preview"></div></th>
		<th><div data-bind="text:size"></div></th>
		<th><div data-bind="text:cdate"></div></th>
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
				<h1 class="popup-sub-title" data-bind="text:txt_title_detail_file"></h1>
				<table class="view-detail-table" border="1">
					<tbody>
						<tr>
							<td data-bind="text:txt_file_srl" class="table-td-title"></td>
							<td data-bind="text:val_file_srl"></td>
							<td data-bind="text:txt_member_srl" class="table-td-title"></td>
							<td data-bind="text:val_owner"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_download_count" class="table-td-title"></td>
							<td data-bind="text:val_download_count"></td>
							<td data-bind="text:txt_file_type" class="table-td-title"></td>
							<td data-bind="text:val_file_type"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_is_s3" class="table-td-title"></td>
							<td data-bind="text:val_storage"></td>
							<td data-bind="text:txt_file_size" class="table-td-title"></td>
							<td data-bind="text:val_file_size"></td>
						</tr>
						<tr>
							<td  class="table-td-title">IP Address</td>
							<td data-bind="text:val_ipaddress"></td>
							<td data-bind="text:txt_file_name" class="table-td-title"></td>
							<td data-bind="text:val_orig_name"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_c_date" class="table-td-title"></td>
							<td data-bind="text:val_c_date"></td>
							<td data-bind="text:txt_u_date" class="table-td-title"></td>
							<td data-bind="text:val_u_date"></td>
						</tr>
						<tr>
							<td class="table-td-title" data-bind="text:txt_orig_url"></td>
							<td colspan="3"><a data-bind="text:val_orig_url, attr:{href:val_orig_url}" target="_blank"></a></td>
						</tr>
						<tr>
							<td data-bind="text:txt_orig_width" class="table-td-title"></td>
							<td data-bind="text:val_orig_width"></td>
							<td data-bind="text:txt_orig_height" class="table-td-title"></td>
							<td data-bind="text:val_orig_height"></td>
						</tr>
						<tr>
							<td class="table-td-title" data-bind="text:txt_thumbnail_url"></td>
							<td colspan="3"><a data-bind="text:val_thumbnail_url, attr:{href:val_thumbnail_url}" target="_blank"></a></td>
						</tr>
						<tr>
							<td data-bind="text:txt_thumbnail_width" class="table-td-title"></td>
							<td data-bind="text:val_thumbnail_width"></td>
							<td data-bind="text:txt_thumbnail_height" class="table-td-title"></td>
							<td data-bind="text:val_thumbnail_height"></td>
						</tr>
						<tr>
							<td data-bind="text:txt_comment" class="table-td-title"></td>
							<td colspan="3" data-bind="text:val_comment"></td>
						</tr>
					</tbody>
				</table>
				<div style="height:10px;"></div>
				<h1 class="popup-sub-title" data-bind="text:txt_orig_url"></h1>
				<img data-bind="attr:{src:val_orig_url}" />
				<div style="height:10px;"></div>
				<h1 class="popup-sub-title" data-bind="text:txt_thumbnail_url"></h1>
				<img data-bind="attr:{src:val_thumbnail_url}" />
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