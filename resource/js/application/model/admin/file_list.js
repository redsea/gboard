var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.content) { gboard.content = {}; }
if(!gboard.content.main) { gboard.content.main = {}; }


if(!gboard.content.main.data){gboard.content.main.data={

conf: {
	language: 'ko',
	text: null
},

body: {
	//table_header: ko.observable(),	// 이건 init 에서 값을 준다
	txt_text_title: ko.observable(),
	txt_text_list_help1: ko.observable(),
	txt_title_detail_file: ko.observable(),
	
	txt_file_srl: ko.observable(),
	txt_member_srl: ko.observable(),
	txt_download_count: ko.observable(),
	txt_file_type: ko.observable(),
	txt_file_size: ko.observable(),
	txt_c_date: ko.observable(),
	txt_u_date: ko.observable(),
	txt_comment: ko.observable(),
	txt_is_s3: ko.observable(),
	txt_file_name: ko.observable(),
	txt_orig_url: ko.observable(),
	txt_thumbnail_url: ko.observable(),
	txt_orig_width: ko.observable(),
	txt_orig_height: ko.observable(),
	txt_thumbnail_width: ko.observable(),
	txt_thumbnail_height: ko.observable(),
	txt_close: ko.observable(),
	txt_dialog_title: ko.observable(),
	
	val_file_srl: ko.observable(),
	val_owner: ko.observable(),
	val_download_count: ko.observable(),
	val_file_type: ko.observable(),
	val_storage: ko.observable(),
	val_file_size: ko.observable(),
	val_ipaddress: ko.observable(),
	val_orig_name: ko.observable(),
	val_orig_url: ko.observable(),
	val_orig_width: ko.observable(),
	val_orig_height: ko.observable(),
	val_thumbnail_url: ko.observable(),
	val_thumbnail_width: ko.observable(),
	val_thumbnail_height: ko.observable(),
	val_comment: ko.observable(),
	val_c_date: ko.observable(),
	val_u_date: ko.observable()
}

};}


if(!gboard.content.main.action) { gboard.content.main.action={

get_date_format: function(date) {
	if(!date || date.length < 14) { return ''; }
	
	return date.substring(0, 4)+'-'+	// year
		date.substring(4, 6)+'-'+		// month
		date.substring(6, 8)+' '+		// day
		date.substring(8, 10)+':'+		// hour
		date.substring(10, 12)+':'+		// min
		date.substring(12, 14);			// sec
},

/**
 * 파일 상세 보기 ajax 결과 처리
 */
show_file_detail: function(result, jqXHR, data, textStatus, excep) {
	$('.window-container').addClass('view-popup-stop-scrolling');
	$('#view-port-popup').fadeIn();
	
	// 실패 했다면 dialog 를 띄운다
	if(!result || !data || !data.error) {
		var $dialog = $('#view-popup-dialog')
			.text(gboard.content.main.data.conf.text.error_message)
			.dialog({
				appendTo: '#view-port-popup',
				close: function(evt, ui) {
					evt.stopPropagation();
					evt.preventDefault();
					
					$('.window-container').removeClass('view-popup-stop-scrolling');
					$('#view-port-popup').fadeOut(function(){$('#view-popup').hide();});
				}
			});
		return false;
	}
	
	if(data.error != 'S000001') {
		var $dialog = $('#view-popup-dialog')
			.text(data.message)
			.dialog({
				appendTo: '#view-port-popup',
				close: function(evt, ui) {
					evt.stopPropagation();
					evt.preventDefault();
					
					$('.window-container').removeClass('view-popup-stop-scrolling');
					$('#view-port-popup').fadeOut(function(){$('#view-popup').hide();});
				}
			});
		return false;
	}
	
	// 성공 했으면 detail 을 보여 준다.
	gboard.content.main.data.body.val_file_srl(data.file_srl);
	gboard.content.main.data.body.val_owner(data.user_id);
	gboard.content.main.data.body.val_download_count(data.download_count);
	gboard.content.main.data.body.val_file_type(data.file_type);
	gboard.content.main.data.body.val_storage(data.storage);
	gboard.content.main.data.body.val_file_size(data.file_size);
	gboard.content.main.data.body.val_ipaddress(data.ipaddress);
	gboard.content.main.data.body.val_orig_name(data.orig_name);
	gboard.content.main.data.body.val_orig_url(data.url);
	gboard.content.main.data.body.val_orig_width(data.width);
	gboard.content.main.data.body.val_orig_height(data.height);
	gboard.content.main.data.body.val_thumbnail_url(data.thumbnail_url);
	gboard.content.main.data.body.val_thumbnail_width(data.thumbnail_width);
	gboard.content.main.data.body.val_thumbnail_height(data.thumbnail_height);
	gboard.content.main.data.body.val_comment(data.file_comment);
	gboard.content.main.data.body.val_c_date( gboard.content.main.action.get_date_format(data.c_date) );
	gboard.content.main.data.body.val_u_date( gboard.content.main.action.get_date_format(data.u_date) );
	
	gboard.content.main.action.get_date_format(data.c_date);
	
	$('#view-popup').show();
	$('#view-port-popup').fadeIn();
	$('.view-popup-content').scrollTop(0);
},

/**
 * 파일 상세 보기 ajax
 */
file_detail: function(file_id) {
	file_id = file_id.replace(/file_/, '');

	$.ajax({
			type: 'POST',
			url: gboard.admin.url.file_detail,
			data: {file_id:file_id},
			success: function(data, textStatus, jqXHR) {
				try {
					if(typeof data == 'string') { data = eval('('+data+')'); }
				} catch(exception) {
					(cb)(false, jqXHR, null, 'error', exception, userdata);
					return;
				}
				
				gboard.content.main.action.adapter_file_detail(data);
				gboard.content.main.action.show_file_detail(true, jqXHR, data, textStatus, null);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				gboard.content.main.action.show_file_detail(false, jqXHR, null, textStatus, errorThrown);
			}
		});
},

/**
 * file detail ajax 결과 adapter
 */
adapter_file_detail: function(input) {
	return input;
},

init: function(clang) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.admin.text[clang];

	gboard.content.main.data.body.txt_text_title(gboard.content.main.data.conf.text.file_list);
	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.help_file_list);
	
	gboard.content.main.data.body.txt_title_detail_file(gboard.content.main.data.conf.text.title_detail_file);
	gboard.content.main.data.body.txt_file_srl(gboard.content.main.data.conf.text.file_srl);
	gboard.content.main.data.body.txt_member_srl(gboard.content.main.data.conf.text.owner);
	gboard.content.main.data.body.txt_download_count(gboard.content.main.data.conf.text.download_count);
	gboard.content.main.data.body.txt_file_type(gboard.content.main.data.conf.text.file_type);
	gboard.content.main.data.body.txt_file_size(gboard.content.main.data.conf.text.size);
	gboard.content.main.data.body.txt_c_date(gboard.content.main.data.conf.text.cdate);
	gboard.content.main.data.body.txt_u_date(gboard.content.main.data.conf.text.udate);
	gboard.content.main.data.body.txt_comment(gboard.content.main.data.conf.text.comment);
	gboard.content.main.data.body.txt_is_s3(gboard.content.main.data.conf.text.is_s3);
	gboard.content.main.data.body.txt_file_name(gboard.content.main.data.conf.text.file_name);
	gboard.content.main.data.body.txt_orig_url(gboard.content.main.data.conf.text.orig_url);
	gboard.content.main.data.body.txt_thumbnail_url(gboard.content.main.data.conf.text.thumbnail_url);
	gboard.content.main.data.body.txt_orig_width(gboard.content.main.data.conf.text.orig_width);
	gboard.content.main.data.body.txt_orig_height(gboard.content.main.data.conf.text.orig_height);
	gboard.content.main.data.body.txt_thumbnail_width(gboard.content.main.data.conf.text.thumbnail_width);
	gboard.content.main.data.body.txt_thumbnail_height(gboard.content.main.data.conf.text.thumbnail_height);
	gboard.content.main.data.body.txt_close(gboard.content.main.data.conf.text.close);
	gboard.content.main.data.body.txt_dialog_title(gboard.content.main.data.conf.text.title_failed);
	
	gboard.content.main.data.body.table_header = ko.observable({
			owner: gboard.content.main.data.conf.text.owner,
			file_name: gboard.content.main.data.conf.text.file_name,
			preview: gboard.content.main.data.conf.text.preview,
			size: gboard.content.main.data.conf.text.size,
			cdate: gboard.content.main.data.conf.text.cdate});
	
	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));
	
	var $oTable = $('#data-tables1').dataTable({
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': gboard.admin.url.file_list,
        'sServerMethod': 'POST',
        'aoColumns': [
        	{'mData':'user_id', 'sClass':'center'},
        	{'mData':'orig_name'},
        	{'mData':'preview_tag', 'bSortable': false, 'sClass':'center'},
        	{'mData':'file_size', 'sClass':'center'},
        	{'mData':'c_date', 'sClass':'center'},
        ],
        'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
        	$(nRow).click(function(evt){
        			evt.stopPropagation();
					evt.preventDefault();
					
        			var $this = $(this);
        			gboard.content.main.action.file_detail($this.attr('id'));
        		});
        }
	});
}

};}

ko.bindingHandlers.m_button = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		var $element = $(element);
		
		$element.text(valueUnwrapped).button().addClass('close-popup').click(function(evt){
			evt.stopPropagation();
			evt.preventDefault();
			
			$('.window-container').removeClass('view-popup-stop-scrolling');
			$('#view-port-popup').fadeOut(function(){
					$('#view-popup').hide();
				});
		});
	}
};