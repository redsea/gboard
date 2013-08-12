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

init: function(clang) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.admin.text[clang];

	gboard.content.main.data.body.txt_text_title(gboard.content.main.data.conf.text.service_list);
	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.help_service_list);
	
	gboard.content.main.data.body.table_header = ko.observable({
			service_id: gboard.content.main.data.conf.text.service_id,
			service_name: gboard.content.main.data.conf.text.service_name,
			controller: gboard.content.main.data.conf.text.controller,
			action: gboard.content.main.data.conf.text.action,
			active: gboard.content.main.data.conf.text.active,
			cdate: gboard.content.main.data.conf.text.cdate});

	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));

	var $oTable = $('#data-tables1').dataTable({
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': gboard.admin.url.service_list2,
        'sServerMethod': 'POST',
        'aoColumns': [
        	{'mData':'service_id', 'bSortable': false},
        	{'mData':'service_name'},
        	{'mData':'controller'},
        	{'mData':'controller_action'},
        	{'mData':'is_active', 'bSortable': false, 'sClass':'center'},
        	{
        		'mData':'c_date', 'sClass':'center', 
        		'mRender':function(data, type, full){
        			return gboard.content.main.action.get_date_format(data);
        		}
        	}
        ]
	});
}

};}
