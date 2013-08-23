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

init: function(clang, board_id, category_info) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.board.text[clang];

	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.help_board);

	gboard.content.main.data.body.table_header = ko.observable({
			doc_num: gboard.content.main.data.conf.text.number,
			doc_title: gboard.content.main.data.conf.text.title,
			doc_author: gboard.content.main.data.conf.text.author,
			doc_cdate: gboard.content.main.data.conf.text.cdate,
			doc_read: gboard.content.main.data.conf.text.read_count,
			doc_like: gboard.content.main.data.conf.text.like});

	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));

	var $oTable = $('#data-tables1').dataTable({
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': gboard.board.url.document_list1+'/'+board_id+'/'+category_info[0].category_srl,
        'sServerMethod': 'POST',
        'aaSorting': [[4, 'desc']],
        "bAutoWidth": false,
        'aoColumns': [
        	{
        		'aTargets': [0],
        		//'mData':'document_title', 
        		'mData': function(source, type, val) {
        			if(source.comment_count > 0) {
	        			return source.document_title+'&nbsp;&nbsp;&nbsp;['+source.comment_count+']';
	        		}
	        		return source.document_title;
        		},
        		'bSortable': false
	        	//'mRender':function(data, type, full){
	        	//	console.log('--->title');
	        	//	console.log(data);
	        	//	return data;
	        	//}
        	},
        	{'mData':'nick_name', 'bSortable': false, 'sClass':'center', 'sWidth': '100px'},
        	{'mData':'read_count', 'sClass':'center', 'sWidth':'70px'},
        	{'mData':'like_count', 'sClass':'center', 'sWidth':'70px'},
        	{
        		'mData':'c_date', 'sClass':'center', 'sWidth': '12%',
        		'mRender':function(data, type, full){
        			return gboard.content.main.action.get_date_format(data);
        		},
        		
        	}
        ]
	});
}

};}
