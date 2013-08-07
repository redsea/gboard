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

init: function(clang) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.admin.text[clang];

	gboard.content.main.data.body.txt_text_title(gboard.content.main.data.conf.text.application_list);
	gboard.content.main.data.body.txt_text_list_help1(gboard.content.main.data.conf.text.help_application_list);
	
	gboard.content.main.data.body.table_header = ko.observable({
			company_name: gboard.content.main.data.conf.text.company_name,
			cdate: gboard.content.main.data.conf.text.cdate});
	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));
	
	var $oTable = $('#data-tables1').dataTable({
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': gboard.admin.url.application_list,
        'sServerMethod': 'POST',
        'aoColumns': [
        	{'mData':'company_name', 'sClass':'center'},
        	{'mData':'api_key', 'bSortable': false, 'sClass':'center'},
        	{'mData':'api_secret', 'bSortable': false, 'sClass':'center'},
        	{'mData':'api_version', 'bSortable': false, 'sClass':'center'},
        	{'mData':'c_date', 'sClass':'center'},
        ],
        'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
        	$(nRow).click(function(evt){
        			var $this = $(this);
        			
        			// TODO click 했을 때 detail 을 보여 줘야 한다.
        			//console.log($this);
        			alert('show detail['+$this.attr('id')+']');
        			
        			//console.log(this);
	        		//console.log('click item');
        		});
        	
        }
	});
	
}


};}