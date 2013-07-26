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
	add_text: '텍스트 추가'				// 다국어 텍스트 추가
},
en : {
	add_text: '텍스트 추가'				// 다국어 텍스트 추가
}

};}

if(!gboard.content.main.data){gboard.content.main.data={

conf: {
	language: 'ko',
	text: null
},

body: {
	lang_list: ko.observableArray(),
}

};}


if(!gboard.content.main.action) { gboard.content.main.action={

clickLanguageItem: function(obj, evt) {
	console.log(arguments);
	console.log('> click this1');
},

init: function(clang, support_language) {
	gboard.content.main.data.conf.language = clang;
	gboard.content.main.data.conf.text = gboard.content.main.lang[clang];

	ko.applyBindings(gboard.content.main.data.body, $('#window-content').get(0));
	
	for(var i=0 ; i<support_language.length ; i++) {
		var icon = (support_language[i].images && support_language[i].images['180x130']) ?
				'url('+support_language[i].images['180x130']+')' : '';
	
		gboard.content.main.data.body.lang_list.push({
				type: 'show-list',
				bg: icon,
				name1: support_language[i].name,
				clickThis: gboard.content.main.action.clickLanguageItem
			});
	}
	
	gboard.content.main.data.body.lang_list.push({
				type: 'add',
				bg: '',
				name1: gboard.content.main.data.conf.text.add_text,
				clickThis: gboard.content.main.action.clickLanguageItem
			});
	

	
}


};}