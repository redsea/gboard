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
	keep_connect: '접속유지시간: ',							// 하단 상태바 접속 유지 시간
	keep_connect_end: '만료됨',							// 하단 상태바 접속 유지시간 기간 표시. 접속 유지 완료(세션 종료됨)
	check_keep_connect: '계산중',							// 속도가 느릴때를 대비해서 넣어 둔 값
	go_home: '홈',										// quick menu home 이름.
	my_profile: '나',									// 나의 프로필
	error_message: '서비스 점검중, 잠시 후 다시 시도 하세요',		// 기본 에러 메시지
	request_data: '데이터 요청',							// ajax 나 통신등 데이터 요청에 대한 기본 타이틀(notification 에 사용)
	page_makeup: '페이지 생성',							// 페이지 구성하기 위해서 ajax 시도 할때, notification 에 보여줄 메인 텍스트
	main_page_makeup_desc: '메인 페이지를 생성합니다',			// 메인 페이지 구성 하기 위해 ajax 시도 할때, notification 에 보여줄 설명 텍스트
},
en : {
	keep_connect: '접속유지시간: ',							// 하단 상태바 접속 유지 시간
	keep_connect_end: '만료됨',							// 하단 상태바 접속 유지시간 기간 표시. 접속 유지 완료(세션 종료됨)
	check_keep_connect: '계산중',							// 속도가 느릴때를 대비해서 넣어 둔 값
	go_home: '홈',										// quick menu home 이름.
	my_profile: '나',									// 나의 프로필
	error_message: '서비스 점검중, 잠시 후 다시 시도 하세요',		// 기본 에러 메시지
	request_data: '데이터 요청',							// ajax 나 통신등 데이터 요청에 대한 기본 타이틀(notification 에 사용)
	page_makeup: '페이지 생성',							// 페이지 구성하기 위해서 ajax 시도 할때, notification 에 보여줄 메인 텍스트
	main_page_makeup_desc: '메인 페이지를 생성합니다',			// 메인 페이지 구성 하기 위해 ajax 시도 할때, notification 에 보여줄 설명 텍스트
}
	
};}


//--------------------------------------------------
// admin login 의 model data
//--------------------------------------------------
if(!gboard.admin.main.data){gboard.admin.main.data={

// 기타값
conf: {
	language: 'ko',				// 페이지의 언어
	expire_second: 3600,		// 세션 만료 시간
	initialized: false,			// init 함수가 호출 되었는지 여부
	text: null,					// 정의된 텍스트(language 에 맞게 gboard.admin.main.lang 을 assign 한 변수)
},

// 중간 content 의 data model
body : {
	// 컨텐츠 영역의 depth indicator 관련
	indicator: ko.observableArray(),		// content 영역 indicator bar 에서 보여줄 view 의 depth 처리
	keep_connect: ko.observable(gboard.admin.main.lang.en.keep_connect),	// 접속 유지 시간
	keep_time: ko.observable('Wait...'),									// 접쇽 유지 시간의 실제 시간 표시
	
	clickMenuTabItem: function(obj, evt) {
		var $this = $(evt.target);
		var $index = null;
		
		if($this.attr('id')){ $index = $this; }
		else				{ $index = $this.parent(); }
		
		// 만일 커서 위치에 있는 것을 클릭 했다면 무시
		if($index.hasClass('menu-tab-item-on')) { return; }
		
		var $onItem = $('#view-port-menu-tab').children('.menu-tab-item-on');
		$onItem.removeClass('menu-tab-item-on').children().first().removeClass('menu-tab-item-default-icon-on');
		$index.addClass('menu-tab-item-on').children().first().addClass('menu-tab-item-default-icon-on');
		
		// TODO navigator 영역에 보여줄 것을 처리 한다.
		console.log('> request ['+$index.attr('x-data-url')+']');
	}
}

};}


//--------------------------------------------------
// Ajax callback function
//--------------------------------------------------
if(!gboard.admin.main.ajax){gboard.admin.main.ajax={

/**
 * ajax 리턴되는 값의 valid 체크 공통
 */
valid_error: function(result, data, udata) {
	if(!result || !data || !data.error) {
		// 결과를 보여줄 notification item 이 있다면
		var $noti = $('#'+udata.id);
		if($noti.length > 0) {
			gboard.component.notification.changeDescription(udata.id, 
				gboard.admin.main.data.conf.text.error_message);
		} else {
			gboard.component.notification.addNotification(
				gboard.admin.main.data.conf.text.request_data, 
				gboard.admin.main.data.conf.text.error_message);
		}
		return false;
	}
	
	if(data.error != 'S000001') {
		// 결과를 보여줄 notification item 이 있다면
		var $noti = $('#'+udata.id);
		if($noti.length > 0) {
			gboard.component.notification.changeDescription(udata.id, data.message);
		} else {
			gboard.component.notification.addNotification(
				gboard.admin.main.data.conf.text.request_data, data.message);
		}
		return false;
	}
	
	return true;
},

/**
 * admin menu tree ajax request callback function
 */
cb_admin_menu_tree: function(result, jqXHR, data, textStatus, errorThrown, udata) {
	if(!gboard.admin.main.ajax.valid_error(result, data, udata)) { return; }

	console.log(data);
	console.log(udata);
	
	// 테스트 용으로 주욱 넣어 보자.
	gboard.component.tree.init(udata.content, data.data, true);
	
	var $noti = $('#'+udata.id);
	if($noti.length > 0) {
		gboard.component.notification.removeNotification(udata.id);
	}
	
	
	
	//$('body').trigger(gboard.admin.main.data.event.ajax_concurrent, [udata]);
},

};}


//--------------------------------------------------
// Login 에서의 actions
//--------------------------------------------------
if(!gboard.admin.main.action){gboard.admin.main.action={

keepTimeLoopId: null,					// session 유지 시간 표시 timer id

updateKeepTime: function() {
	if(gboard.admin.main.data.conf.expire_second <= 0) {
		if(gboard.admin.main.action.keepTimeLoopId) {
			clearTimeout(gboard.admin.main.action.keepTimeLoopId);
		}
		gboard.admin.main.data.body.keep_time(
			gboard.admin.main.data.conf.text.keep_connect_end);
		return;
	}

	var hour = Math.floor(gboard.admin.main.data.conf.expire_second/3600)+'';
	var min  = Math.floor((gboard.admin.main.data.conf.expire_second%3600)/60)+'';
	var sec  = Math.floor((gboard.admin.main.data.conf.expire_second%3600)%60)+'';
	
	if(hour.length == 1) { hour = '0'+hour; }
	if(min.length == 1)  { min  = '0'+min; }
	if(sec.length == 1)  { sec  = '0'+sec; }
	
	gboard.admin.main.data.body.keep_time(hour+':'+min+':'+sec);
	gboard.admin.main.action.triggerKeepTime();
},

/**
 * 인증만료시간 을 표시 하기 위한 timeout trigger
 */
triggerKeepTime: function() {
	if(gboard.admin.main.action.keepTimeLoopId) {
		clearTimeout(gboard.admin.main.action.keepTimeLoopId);
	}

	gboard.admin.main.data.conf.expire_second--;
	gboard.admin.main.action.keepTimeLoopId = setTimeout(gboard.admin.main.action.updateKeepTime, 1000);
},

/**
 * 화면 전체를 block 한다.
 */
showBlockWindows: function() {
	var $block = $('#window-block-all');
	if($block.is(':visible')) { return; }
	$block.fadeIn('fast');
},

/**
 * block 화면을 닫는다.
 */
hideBlockWindows: function() {
	var $block = $('#window-block-all');
	if(!$block.is(':visible')) { return; }
	$block.fadeOut('fast');
},

/**
 * 상단 quick menu 의 커서 이동 완료 후 호출 될 callback function
 * 
 * @param depth {array} content 영역 indicator bar 에 표시 될 depth label
 */
cb_quick_cursor_move_end: function(depth) {
	// content depth indicator 변경
	gboard.admin.main.data.body.indicator.removeAll();
	var new_indicator = [];
	if(depth.length > 0) {
		for(var i=0 ; i<depth.length ; i++) {
			new_indicator.push(depth[i]);
		}
	}
	
	gboard.admin.main.data.body.indicator(new_indicator);
},


/**
 * 좌측 상단의 service tab 을 초기화 한다.
 *
 * @param service_list service tab menu list
 * @return service tab menu 의 최소 너비
 */
initServiceTab: function(service_list) {
	// service tab menu 의 갯수에 따라 구성요소 사이즈 재조정
	var min_width = service_list.length*38+2;
	var $window_menu = $('#window-menu');
	
	if(min_width > $window_menu.width()) {
		var $window_content_display = $('#window-content-display');
		$window_menu.width(min_width);
		$window_content_display.css('left', min_width+1);
	}
	
	var x_pos = 0;
	var view_port_menu_width = $window_menu.width();
	
	if(service_list%2) {
		// service tab 의 갯수가 홀수 일때
		x_pos = -19 -38*((service_list.length-1)/2);
	} else {
		// service tab 의 갯수가 짝수 일때
		x_pos = -37 -38*((service_list.length-2)/2);
	}
	
	var $content_view_port = $('#view-port-menu-content');
	
	for(var i=0 ; i<service_list.length ; i++) {
		var $element = $('<div>')
			.attr({
					'id': 'menu-tab-item-index'+i,
					'data-bind': 'click:clickMenuTabItem',
					'x-data-url': service_list[i].url
				})
			.addClass('menu-tab-item');
		if(i== 0) {
			$element.css('margin-left', 'calc(50% + '+x_pos+'px)');
			$element.css('margin-left', '-moz-calc(50% + '+x_pos+'px)');
			$element.css('margin-left', '-webkit-calc(50% + '+x_pos+'px)');
		}
		
		// menu tab icon 을 추가 한다.
		var $icon = $('<div>').addClass('menu-tab-item-default-icon-off')
				.attr({title: service_list[i].service_name, 'x-service-id':service_list[i].service_id});
		
		// 만일 사용할 이미지가 있다면 이미지를 사용한다.
		// XXX 아이콘의 규격은 위키 "[API] service index" 를 참조
		if(service_list[i].service_icon) {
			$icon.css('background-image', 'url("'+service_list[i].service_icon+'")');
		}
		
		$element.append($icon);
		
		$('#view-port-menu-tab').append($element);
		
		// service content 를 보여줄 view 를 추가 한다.
		var $content_view = $('<div>').addClass('menu-content-view')
			.attr('id', 'service-'+service_list[i].service_id);
		$content_view_port.append($content_view);
	}
	
	// 첫번째 요소에 커서를 위치 시킨다.
	var $current = $('#menu-tab-item-index0').addClass('menu-tab-item-on');
	$current.children().first().addClass('menu-tab-item-default-icon-on');
	
	
	
	return min_width;
},

/**
 * 페이지 로딩 이후, 실제 내용을 채우기 위해서 ajax 로 데이터를 가져온다.
 * 
 * @param menu_url {string} 왼쪽 menu tree 화면을 채우기 위한 admin menu tree request url
 */
initLoadPage: function(service_menu) {
	// 서비스 메뉴 트리를 요청 한다.
	var nid = gboard.component.notification.addNotification(
			gboard.admin.main.data.conf.text.page_makeup, 
			gboard.admin.main.data.conf.text.main_page_makeup_desc
		);
	
	// max tree depth 만큼 가져온다. depth 에 -1 으로 주면 서버에 설정된 max tree depth 로 변경 된다.
	gboard.ajax.admin_menu_tree(gboard.admin.main.ajax.cb_admin_menu_tree, 
		service_menu.url+'/0/-1', {id:nid, content:'service-'+service_menu.service_id});
	
	
	
	
	
},


/**
 * main 페이지를 초기화 한다.
 *
 * @param clang {string} 사용자 언어 설정값
 * @param expire_second {number} 페이지 인증 만료 시간 카운트용
 * @param home_url {string} home page 의 URL 
 * @param profile_image {array} 유저의 프로필 이미지
 */
init: function(clang, expire_second, home_url, profile_image, service_list) {
	if(gboard.admin.main.data.conf.initialized) { return; }
	
	gboard.admin.main.data.conf.expire_second = expire_second;
	gboard.admin.main.data.conf.language = clang;
	gboard.admin.main.data.conf.text = gboard.admin.main.lang[clang];
	
	// menu tab 을 넣는다.
	var menu_tab_min_width = gboard.admin.main.action.initServiceTab(service_list);
	

	// menu 영역 resize 대응
	$('#window-menu').resizable({
		handles: 'e', 
		containment: 'parent',
		minWidth: menu_tab_min_width,
		resize: function(evt, ui) {
			$('#window-content-display').css('left', ui.size.width+1);
		}
	});
	
	// quick bar 사용을 시작 한다.
	gboard.component.quickbar.init(gboard.admin.main.action.cb_quick_cursor_move_end);
	
	/*
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
			image: (profile_image&&profile_image[0]&&profile_image[0].thumb)?profile_image[0].thumb:''
		});
	*/	
	
	
	// home item 을 quick bar 에 넣는다.(고정)
	gboard.component.quickbar.pushLeftItem({
			title: gboard.admin.main.lang[clang].go_home,
			depth: [gboard.admin.main.lang[clang].go_home],
			url: home_url,
			type: 'home'
		});
	// profile item 을 quick bar 에 넣는다.(고정)
	gboard.component.quickbar.pushLeftItem({
			title: gboard.admin.main.lang[clang].my_profile,
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'profile',
			image: (profile_image&&profile_image[0]&&profile_image[0].thumb)?profile_image[0].thumb:''
		});
		
		
	
	// 최초 메뉴가 home 이기 때문에 indicator 를 home 으로 표시
	gboard.admin.main.data.body.indicator.push(gboard.admin.main.lang[clang].go_home);
	gboard.admin.main.data.body.keep_connect(gboard.admin.main.lang[clang].keep_connect);
	gboard.admin.main.action.updateKeepTime();
	
	// notification 사용을 시작 한다.
	gboard.component.notification.init();
	
	// window-content 에 data binding
	ko.applyBindings(gboard.admin.main.data.body, $('#window-content').get(0));
	
	
	
	
	
	/*
	gboard.component.quickbar.pushCenterItem({
			title: 'start',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});

	gboard.component.quickbar.pushCenterItem({
			title: 'longtitletestingalphabet',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: '2',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});

	gboard.component.quickbar.pushCenterItem({
			title: '3',
			depth: [gboard.admin.main.lang[clang].my_profile],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
		
	gboard.component.quickbar.pushCenterItem({
			title: 'end',
			depth: [gboard.admin.main.lang[clang].my_profile, 'dep2', 'dep3', 'dep4', 'dep5'],
			url: '',
			type: 'normal'
		});
	*/
	
	// quick bar right 에 menu 를 설정 한다.
	//gboard.component.quickbar.setRightItem({
	//		title: '고정메뉴',
	//		type: 'profile'
	//	});
	
	
	
	// 화면 크기를 계산 해서 퀵바 하단에 스크롤바를 붙일 건지 판단한다.
	//gboard.component.quickbar.quickBarResize();
	
	// 최초 화면을 채우기 위해서 ajax 로 데이터를 가져온다.
	gboard.admin.main.action.initLoadPage(service_list[0]);
	
	// main init 완료
	gboard.admin.main.data.conf.initialized = true;
}

};}


//--------------------------------------------------
// Main Page Custom binding
//--------------------------------------------------
// tree 의 binding 은 tree 에서 하기 위해서 stopBinding 을 걸어 준다.
ko.bindingHandlers.stopBinding = {
    init: function() {
        return { controlsDescendantBindings: true };
    }
};
ko.virtualElements.allowedBindings.stopBinding = true;

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

