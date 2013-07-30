var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.content) { gboard.content = {}; }
if(!gboard.content.main) { gboard.content.main = {}; }


//--------------------------------------------------
// 다국어 페이지 에서 사용하는 언어 데이터
//--------------------------------------------------
if(!gboard.content.main.lang){gboard.content.main.lang={

// 한글
ko: {
	text_code: '코드',
	text_list: '다국어 텍스트 설정',				// 다국어로 설정되어 있는 텍스트 리스트
	text_list_help1: '현재는 list view, column edit 기능만 지원한다. 추가를 하려면 SQL 로 직접 해야 한다.'
},
en : {
	text_code: '코드',
	text_list: '다국어 텍스트 설정',				// 다국어로 설정되어 있는 텍스트 리스트
	text_list_help1: '현재는 list view, column edit 기능만 지원한다. 추가를 하려면 SQL 로 직접 해야 한다.'
}

};}

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
	gboard.content.main.data.conf.text = gboard.content.main.lang[clang];
	
	gboard.content.main.data.body.txt_text_title(gboard.content.main.data.conf.text.text_list);
	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.text_list_help1);
	
	// table header 값을 준다
	var _table_header = {code:gboard.content.main.data.conf.text.text_code};
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
        'sAjaxSource': 'http://gboard.org/lang/text_list',
		'sServerMethod': 'POST',
        'aoColumns': aoColumns,
        'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
        	var $tds = $('td', nRow);
        	for(var i=1 ; i<$tds.length ; i++) {
        		var $td = $($tds.get(i));
        		$td.editable('http://gboard.org/lang/text_change', {
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
								'row_id': this.parentNode.getAttribute('id'),
								'lang': support_language[idx-1].code
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