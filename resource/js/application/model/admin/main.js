var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.admin) { gboard.admin = {}; }
if(!gboard.admin.main) { gboard.admin.main = {}; }


//--------------------------------------------------
// admin login 에서 사용하는 언어 데이터
//--------------------------------------------------
if(!gboard.admin.main.lang){gboard.admin.main.lang={

// 한글
ko: {
	keep_connect: '접속유지시간: ',		// 하단 상태바 접속 유지 시간
	keep_connect_end: '만료됨',		// 하단 상태바 접속 유지시간 기간 표시. 접속 유지 완료(세션 종료됨)
	check_keep_connect: '계산중',		// 속도가 느릴때를 대비해서 넣어 둔 값
	go_home: '홈',					// quick menu home 이름.
	my_profile: '프로필',				// 나의 프로필
},
en : {
	keep_connect: '접속유지시간: ',		// 하단 상태바 접속 유지 시간
	keep_connect_end: '만료됨',		// 하단 상태바 접속 유지시간 기간 표시. 접속 유지 완료(세션 종료됨)
	check_keep_connect: '계산중',		// 속도가 느릴때를 대비해서 넣어 둔 값
	go_home: '홈',					// quick menu home 이름
	my_profile: '프로필'				// 나의 프로필
}
	
};}


//--------------------------------------------------
// Login 에서의 actions
//--------------------------------------------------
if(!gboard.admin.main.action){gboard.admin.main.action={

keepTimeLoopId: null,					// session 유지 시간 표시 timer id

updateKeepTime: function() {
	if(gboard.admin.main.model.expire_second <= 0) {
		if(gboard.admin.main.action.keepTimeLoopId) {
			clearTimeout(gboard.admin.main.action.keepTimeLoopId);
		}
		gboard.admin.main.model.data.keep_time(
			gboard.admin.main.lang[gboard.admin.main.model.language].keep_connect_end);
		return;
	}

	var hour = Math.floor(gboard.admin.main.model.expire_second/3600)+'';
	var min  = Math.floor((gboard.admin.main.model.expire_second%3600)/60)+'';
	var sec  = Math.floor((gboard.admin.main.model.expire_second%3600)%60)+'';
	
	if(hour.length == 1) { hour = '0'+hour; }
	if(min.length == 1)  { min  = '0'+min; }
	if(sec.length == 1)  { sec  = '0'+sec; }
	
	gboard.admin.main.model.data.keep_time(hour+':'+min+':'+sec);
	gboard.admin.main.action.triggerKeepTime();
},

triggerKeepTime: function() {
	if(gboard.admin.main.action.keepTimeLoopId) {
		clearTimeout(gboard.admin.main.action.keepTimeLoopId);
	}

	gboard.admin.main.model.expire_second--;
	gboard.admin.main.action.keepTimeLoopId = setTimeout(gboard.admin.main.action.updateKeepTime, 1000);
}

};}


//--------------------------------------------------
// admin login 의 model
//--------------------------------------------------
if(!gboard.admin.main.model){gboard.admin.main.model={

language: 'ko',				// 페이지의 언어
expire_second: 3600,		// 세션 만료 시간
home_url: '',				// home url

data : {
	// text 변경
	keep_connect: ko.observable(gboard.admin.main.lang.keep_connect),	// 접속 유지 시간
	keep_time: ko.observable('Wait...'),								// 접쇽 유지 시간의 실제 시간 표시
	go_home: ko.observable('Home'),										// quick menu home 의 텍스트
	my_profile: ko.observable('Profile'),								// quick menu profile 의 텍스트
	content_depth: ko.observableArray(),
	
	// 형태 변경
	quick_cursor_pos: ko.observable('quick-menu-0'),		// 상단 quick-bar 커서 위치(quick menu item 의 id 로 표시함)
	quickItemClick: function(obj, evt) {					// 상단 quick-bar 아이템 click 했을 때의 반응
		evt.stopPropagation();
	
		var $this = $(evt.target);
		var index = null;
		
		if($this.attr('id')){ index = $this.attr('id'); }
		else				{ index = $this.parent().attr('id'); }
		//index = index.replace('quick-menu-', '');
		
		// cursor 를 이동 시킨다.
		gboard.admin.main.model.data.quick_cursor_pos(index);
	}
	
},

data_normal : {
	depth_indicator_text: []	// depth indicator 에서 보여줄 depth text
},

init: function(clang, expire_second, home_url) {
	gboard.admin.main.model.expire_second = expire_second;
	gboard.admin.main.model.home_url = home_url;
	
	// quick 메뉴의 2개는 기본으로 depth indicator text 로 설정 됨.
	// quick 메뉴에 추가 되면 depth indicator 를 위해서 
	// gboard.admin.main.model.data_normal.depth_indicator_text 에도 전체 depth 가
	// 추가 되어야 한다.
	gboard.admin.main.model.data_normal.depth_indicator_text.push(
		[gboard.admin.main.lang[clang].go_home]);
	gboard.admin.main.model.data_normal.depth_indicator_text.push(
		[gboard.admin.main.lang[clang].my_profile]);

	// XXX 테스트 용도로 넣은 값. 실제는 삭제 되어야 함.
	gboard.admin.main.model.data_normal.depth_indicator_text.push(
		['home', 'profile', 'hello', 'world']);

	ko.applyBindings(this.data);
	
	var _lang = gboard.admin.main.lang;
	gboard.admin.main.model.language = clang;
	
	this.data.keep_connect(_lang[clang].keep_connect);
	this.data.keep_time(_lang[clang].check_keep_connect);
	this.data.go_home(_lang[clang].go_home);
	this.data.my_profile(_lang[clang].my_profile);
	
	//this.data.quick_cursor_pos(1); // cursor 를 두번째 퀵메뉴로 이동
	
	gboard.admin.main.action.updateKeepTime();
}

};}


//--------------------------------------------------
// Main Page Custom binding
//--------------------------------------------------
// 상단 quick bar cursor 의 binding handler
ko.bindingHandlers.m_quick_cursor = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		var index = valueUnwrapped.replace('quick-menu-', '');
		var $item = $('#'+valueUnwrapped);
		var pos = $item.position();
		var $this = $(element);
		
		// content depth indicator 변경
		gboard.admin.main.model.data.content_depth.removeAll();
		if(gboard.admin.main.model.data_normal.depth_indicator_text.length > index) {
			for(var i=0 ; 
					i<gboard.admin.main.model.data_normal.depth_indicator_text[index].length ; 
					i++) {
				gboard.admin.main.model.data.content_depth.push(
					gboard.admin.main.model.data_normal.depth_indicator_text[index][i]);		
			}
		}
		
		// cursor 의 위치 변경
		if(index < 2)	{ $('#quick-bar-part-left').append($this); }
		else			{ $('#quick-bar-part-center').append($this); }
		$this.css('left', pos.left-4);
		
		// tooltip 을 변경
		var $child = $item.children().first();
		var title = $child.attr('title');
		$this.attr('title', $child.attr('title'));
	}
};

// content 영역 상단의 depth indicator 의 biding handler
ko.bindingHandlers.m_depth_ind = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		var $buffer = $('#window-buffer').show();
		var $indicator = $('#view-port-depth-indicator');
		var $depth_element = null, left=0, wid=0, hei=0, $wrapper=null, $line=null;
		
		$indicator.empty();
		for(var i=0 ; i<valueUnwrapped.length ; i++) {
			if($.trim(valueUnwrapped[i])) {
				$depth_element = $('<span>')
					.addClass('depth-indicator-element-text')
					.text($.trim(valueUnwrapped[i]));
				$buffer.append($depth_element);
				
				wid = $depth_element.width();
				hei = $depth_element.height();
				
				$wrapper = $('<div>').addClass('depth-indicator-element')
					.css({width:wid+18, left:left});
				$line = $('<div>').addClass('depth-indicator-element-line').css('left', left+wid+19);
				$depth_element.css({marginTop:(23-hei)/2, marginLeft:9, display:'block'});
				$wrapper.append($depth_element);
				
				$wrapper.hide();
				$line.hide();
				
				$indicator.append($wrapper);
				$indicator.append($line);
				
				left += wid +19;
				
				$wrapper.fadeIn('fast');
				$line.fadeIn('fast');
			}
		}
		
		$buffer.hide();
	}
};