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

init: function(clang, support_language) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.admin.text[clang];
	
	gboard.content.main.data.body.txt_text_title(gboard.content.main.data.conf.text.text_list);
	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.help_text_list);
	
	// table header 값을 준다
	var _table_header = {code:gboard.content.main.data.conf.text.code};
	for(var i=0 ; i<support_language.length ; i++) {
		_table_header[support_language[i].code] = support_language[i].name;
	}
	gboard.content.main.data.body.table_header = ko.observable(_table_header);
	
	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));
	
	var aoColumns = new Array();
	aoColumns.push({'mData':'name', 'bSortable': false, 'sClass':'center'});
	
	for(var prop in _table_header) {
		if(prop != 'code') {
			aoColumns.push({'mData':'text.'+prop, 'bSortable': false});
		}
	}
	
	// datatable ui 를 초기화 한다.
	var $oTable = $('#multi-language').dataTable({
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': gboard.admin.url.text_list,
		'sServerMethod': 'POST',
        'aoColumns': aoColumns,
        'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
        	var $tds = $('td', nRow);
        	for(var i=1 ; i<$tds.length ; i++) {
        		var $td = $($tds.get(i));
        		$td.editable(gboard.admin.url.text_change, {
        			// text update 결과 처리
					'callback': function(sValue, y) {
						var aPos = $oTable.fnGetPosition(this);
						var sdata = null;
						
						if(!sValue) {
							$oTable.fnUpdate('update failed', aPos[0], aPos[1]);
							return;
						}
						
						try {
							sdata = eval('('+sValue+')');
						} catch(exception) {
							$oTable.fnUpdate('update failed', aPos[0], aPos[1]);
							return;
						}
						$oTable.fnUpdate(sdata.text, aPos[0], aPos[1]);
					},
					// text update 파라미터를 설정하여 호출
					'submitdata': function(value, settings) {
						var idx = $oTable.fnGetPosition(this)[2];
						return {
								'text_name': this.parentNode.getAttribute('id'),
								'lang_code': support_language[idx-1].code
							};
					},
					'height': '100%',
					'width': '100%'
				});
        	}
        }
	});
}


};}