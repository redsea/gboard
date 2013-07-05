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
	my_profile: '나',				// 나의 프로필
},
en : {
	keep_connect: '접속유지시간: ',		// 하단 상태바 접속 유지 시간
	keep_connect_end: '만료됨',		// 하단 상태바 접속 유지시간 기간 표시. 접속 유지 완료(세션 종료됨)
	check_keep_connect: '계산중',		// 속도가 느릴때를 대비해서 넣어 둔 값
	go_home: '홈',					// quick menu home 이름
	my_profile: 'Me'				// 나의 프로필
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
		gboard.admin.main.model.data_bottom.keep_time(
			gboard.admin.main.lang[gboard.admin.main.model.language].keep_connect_end);
		return;
	}

	var hour = Math.floor(gboard.admin.main.model.expire_second/3600)+'';
	var min  = Math.floor((gboard.admin.main.model.expire_second%3600)/60)+'';
	var sec  = Math.floor((gboard.admin.main.model.expire_second%3600)%60)+'';
	
	if(hour.length == 1) { hour = '0'+hour; }
	if(min.length == 1)  { min  = '0'+min; }
	if(sec.length == 1)  { sec  = '0'+sec; }
	
	gboard.admin.main.model.data_bottom.keep_time(hour+':'+min+':'+sec);
	gboard.admin.main.action.triggerKeepTime();
},

/**
 * 인증만료시간 을 표시 하기 위한 timeout trigger
 */
triggerKeepTime: function() {
	if(gboard.admin.main.action.keepTimeLoopId) {
		clearTimeout(gboard.admin.main.action.keepTimeLoopId);
	}

	gboard.admin.main.model.expire_second--;
	gboard.admin.main.action.keepTimeLoopId = setTimeout(gboard.admin.main.action.updateKeepTime, 1000);
},

/**
 * 상단 quick menu 의 커서 이동 완료 후 호출 될 callback function
 * 
 * @param depth {array} content 영역 indicator bar 에 표시 될 depth label
 */
cb_quick_cursor_move_end: function(depth) {
	// content depth indicator 변경
	gboard.admin.main.model.data_body.indicator.removeAll();
	var new_indicator = [];
	if(depth.length > 0) {
		for(var i=0 ; i<depth.length ; i++) {
			new_indicator.push(depth[i]);
		}
	}
	
	gboard.admin.main.model.data_body.indicator(new_indicator);
},

};}


//--------------------------------------------------
// admin login 의 model
//--------------------------------------------------
if(!gboard.admin.main.model){gboard.admin.main.model={

language: 'ko',				// 페이지의 언어
expire_second: 3600,		// 세션 만료 시간
initialized: false,			// init 함수가 호출 되었는지 여부

// 중간 content 의 data model
data_body : {
	// 컨텐츠 영역의 depth indicator 관련
	indicator: ko.observableArray()		// content 영역 indicator bar 에서 보여줄 view 의 depth 처리
},

// 하단 status bar 의 data model
data_bottom: {
	// text 변경
	keep_connect: ko.observable(gboard.admin.main.lang.en.keep_connect),	// 접속 유지 시간
	keep_time: ko.observable('Wait...'),									// 접쇽 유지 시간의 실제 시간 표시
},

/**
 * main 페이지를 초기화 한다.
 *
 * @param clang {string} 사용자 언어 설정값
 * @param expire_second {number} 페이지 인증 만료 시간 카운트용
 * @param home_url {string} home page 의 URL 
 * @param profile_image {array} 유저의 프로필 이미지
 */
init: function(clang, expire_second, home_url, profile_image) {
	if(gboard.admin.main.model.initialized) { return; }



/* 	$('#quick-bar-part-center').perfectScrollbar(); */

	// TODO 후...스크롤바는 좀 더 테스트 해 보자.
	//      내가 원하는 스크롤바 형태는 안 보일듯....그냥 만드는게 속 편하겠다.
	//      가로 스크롤, 윈도우가 커지면 스크롤도 자동으로 사라짐.
	//      포함된 것들도 조금씩 움직여야 한다.
	//      퀵바 센터에 넣는 것은 함수로 빼도록 하자. 하나씩 넣으면서 사이즈 계산을 해야 한다.
	//      스크롤바 알고리즘도 생각 해 보자. 구글링 하면 나오려나???


	gboard.admin.main.model.expire_second = expire_second;
	gboard.admin.main.model.language = clang;
	
	// quick bar 사용을 시작 한다.
	gboard.component.quickbar.init(gboard.admin.main.action.cb_quick_cursor_move_end);
	
	// home item 을 quick bar 에 넣는다.(고정)
	gboard.component.quickbar.pushItem({
			title: gboard.admin.main.lang[clang].go_home,
			depth: [gboard.admin.main.lang[clang].go_home],
			url: home_url,
			type: 'home'
		});
	// profile item 을 quick bar 에 넣는다.(고정)
	gboard.component.quickbar.pushItem({
			title: gboard.admin.main.lang[clang].my_profile,
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'profile',
			image: profile_image[0].thumb
		});
	
	// 최초 메뉴가 home 이기 때문에 indicator 를 home 으로 표시
	gboard.admin.main.model.data_body.indicator.push(gboard.admin.main.lang[clang].go_home);
	gboard.admin.main.model.data_bottom.keep_connect(gboard.admin.main.lang[clang].keep_connect);
	gboard.admin.main.action.updateKeepTime();
	
	// window-content 에 data binding
	ko.applyBindings(gboard.admin.main.model.data_body, $('#window-content').get(0));
	ko.applyBindings(gboard.admin.main.model.data_bottom, $('#window-bottom-status-bar').get(0));
	
	
	
	
	// 테스트 용으로 주욱 넣어 보자.
	gboard.component.quickbar.pushItem({
			title: 'start',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});

	gboard.component.quickbar.pushItem({
			title: 'longtitletestingalphabet',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushItem({
			title: '2',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});

	gboard.component.quickbar.pushItem({
			title: '3',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});
			
	gboard.component.quickbar.pushItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
	
	// quick bar right 에 menu 를 설정 한다.
	//gboard.component.quickbar.setStaticItem({
	//		title: '고정메뉴',
	//		type: 'profile'
	//	});
	
	
	// 화면 크기를 계산 해서 퀵바 하단에 스크롤바를 붙일 건지 판단한다.
	gboard.component.quickbar.quickBarResize();
	
	
	console.log(profile_image);
	console.log(profile_image[0].thumb);
	
/*
	setTimeout(function(){
			console.log( $('.quick-icon-profile') );
			
			$('.quick-icon-profile').css('background-image', 'url("'+profile_image[0].thumb+'")');
			
		}, 2000);
*/
	
	
	// main init 완료
	gboard.admin.main.model.initialized = true;
}

};}


//--------------------------------------------------
// Main Page Custom binding
//--------------------------------------------------
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