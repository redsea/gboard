/**
 * quick bar item 의 타입은 아래와 같이 지원한다.
 * 만일 추가 하고 싶으면 quick-bar.css 파일의 .quick-icon-profile 등등을 참고 하여
 * .quick-icon-xxx 이름으로 추가 하면 된다.
 *
 * - home
 * - profile
 * - normal 
 */
 
var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.component) { gboard.component = {}; }
if(!gboard.component.quickbar) {gboard.component.quickbar={

cb_cursor_move_end: null,					// quick bar 커서 이동 완료 이후 호출 할 callback function

scroll:{
	ratio: 1,				// scroll-bar indicator 의 움직임에 따라 원본이 이동할 거리의 비율
	spot_sx: 0,				// scroll-bar indicator 의 left 위치가 scroll-bar 내에서 왼쪽으로 움직일 수 있는 최소 위치
	spot_ex: 0,				// scroll-barkindicator 의 left 위치가 scroll-bar 내에서 오른쪽으로 움직일 수 있는 최대 위치
	content_sx: 0,			// scroll 되는 대상의 left 위치가 왼쪽으로 움직일 수 있는 최소 위치
	content_ex: 0,			// scroll 되는 대상의 left 위치가 오른쪽으로 움직일 수 있는 최대 위치
	scroll_prev_width: 0,	// windows resize 될때 scroll-bar 의 prev width
	spot_prev_left: 0		// 스크롤바 좌,우 에서 의미 없는 스크롤 이벤트를 막기 위해서 이전 스크롤 indicator 의 위치를 저장
},

initialized: false,							// quick bar init 함수 호출 한번만 해 주기 위해서
$view_target: null,							// quick item 선택 했을때 내용이 보여질 target(jQuery object)
$current_view_content: null,				// view target 에 현재 보여지고 있는 view content
_ids: 0,									// quick item 에 id 발급을 위한 index
cursor_close_flag: true,					// 커서 이동시 quick item 닫기 버튼을 자동으로 show/hide 하기 위한 flag
											// quick item 을 클릭해서 커서 이동일때는 닫기 버튼을 보여주지만, 
											// 그 외의 이동에서는 닫기 버튼을 보여주지 말아야 한다.

domain: 'gboard.org',
center_items_content: [],					// quick bar 의 center items 와 매핑되는 실제 content view

data: {
	// item 은 {title, depth, url, type} 로 구성 된다.
	// title : 보여줄 이름
	// depth : indicator 에서 보여줄 depth string
	// url : 클릭 했을때 호출 할 url
	// type : quick item 이미지의 타입
	left_items: ko.observableArray(),					// quick left 에 들어갈 items
	center_items: ko.observableArray(),					// quick center 에 들어갈 items
	right_item: ko.observableArray(['none', '', '']),	// quick right 에 들어갈 item
														// 0 : display 여부
														// 1 : title
														// 2 : class name
	
	cursor_pos: ko.observable(),		// 상단 quick-bar 커서 위치(quick menu item 의 id 로 표시함)
	
	
	
	// quick bar scroll 관련
	center_x: ko.observable(0),							// quick center 영역의 left 위치(스크롤을 위해서)
	center_width: ko.observable(0),						// quick center 영역의 실제 크기(스크롤을 위해서)
	center_right_shadow: ko.observable(0),				// 센터의 오른쪽 그림자(오른쪽으로 삐져 나갔는가?). 0이면 아님, 1이면 삐져나갔음
	center_left_shadow: ko.observable(0),				// 센터의 왼쪽쪽 그림자 여부(왼쪽으로 삐져 나갔는가?). 0이면 아님, 1이면 삐져나갔음
	scroll_indicator_width: ko.observable(0)			// scroll-bar indicator 의 너비
	
},

/**
 * quick bar item click 했을 때의 반응
 * 
 * @param obj {object} click 한 것의 값. left_items, center_items 에 포함된 하나의 요소 object 값임
 * @param evt {Event object} jQuery event object
 */
quickItemClick: function(obj, evt) {			// 상단 quick-bar 아이템 click 했을 때의 반응
	evt.stopPropagation();
	evt.preventDefault();
	
	var $this = $(evt.target);
	var index = null;
		
	if($this.attr('id')){ index = $this.attr('id'); }
	else				{ index = $this.parent().attr('id'); }
	
	// cursor 를 이동 시킨다.
	gboard.component.quickbar.data.cursor_pos(index);
},

/**
 * quick bar item close 를 click 했을 때의 반응
 * @param obj {object} click 한 것의 값. center_items 에 포함된 하나의 요소 object 값임
 * @param evt {Event object} jQuery event object
 */
quickItemClose: function(obj, evt) {
	console.log('> click close button');
	
	evt.stopPropagation();
	evt.preventDefault();
	
	console.log(obj);
},

/**
 * 윈도우 크기 변화에 따른 scroll bar 를 설정한다.
 *
 * @param scroll_bar_width {number} scroll-bar 의 너비
 * @param origin_width {number} scroll 되는 대상의 너비
 * @param view_port_width {number} scroll 되는 대상의 view port 의 너비
 * @param $scroll_indicator {JQuery object} scroll-bar indicator
 * @param $content {JQuery object} scroll 되는 대상
 * @param $view_port {JQuery object} scroll 되는 대상의 view port
 */
_setupScrollBar: function(scroll_bar_width, origin_width, view_port_width, 
		$scroll_indicator) {
		
	var cursor_min_width = 30;	// 커서의 최소 사이즈
	var scroll_space = origin_width - view_port_width;
	var cursor_width = scroll_bar_width - scroll_space;
	
	// 0. right shadow 를 준다.(오른쪽에서 왼쪽으로 view port 가 밀고 들어오는 것)
	if(gboard.component.quickbar.scroll.scroll_prev_width > scroll_bar_width) {
		gboard.component.quickbar.data.center_right_shadow(1);
	}
		
	// 1. scroll-bar indicator 의 크기 설정
	if(cursor_width < cursor_min_width) {
		cursor_width = cursor_min_width;
		// scroll-bar indicator 의 움직임에 따른 원본의 움직임 비율 계산
		gboard.component.quickbar.scroll.ratio = scroll_space/(scroll_bar_width-cursor_width);
	} else {
		// scroll-bar indicator 의 움직임에 따른 원본의 움직임 비율 계산
		gboard.component.quickbar.scroll.ratio = 1;
	}
	
	
	// scroll-bar indicator 가 오른쪽으로 움직 일 수 있는 최대 위치
	gboard.component.quickbar.scroll.content_sx = -scroll_space;
	// scroll 되는 대상이 오른쪽으로 움직 일 수 있는 최대 위치 설정
	gboard.component.quickbar.scroll.content_ex = 0;
	// scroll-bar indicator 가 왼쪽으로 움직 일 수 있는 최대 위치 설정
	gboard.component.quickbar.scroll.spot_sx = 0;
	// scroll-bar indicator 가 오른쪽으로 움직 일 수 있는 최대 위치 설정
	gboard.component.quickbar.scroll.spot_ex = scroll_bar_width - cursor_width;
	
	// 2. 오른쪽으로 삐져 나간게 얼마 만큼 되는지 확인 해서 처리한다.
	var content_out_left = -gboard.component.quickbar.data.center_x();
	var out_of_right = origin_width - (content_out_left + view_port_width);
	if(out_of_right <= 0) {
		// 왼쪽으로 들어간게 있다면, 오른쪽으로 땡긴다.
		if(gboard.component.quickbar.data.center_left_shadow() == 1) {
			if(gboard.component.quickbar.data.center_right_shadow() != 0) {
				gboard.component.quickbar.data.center_right_shadow(0);
			}
			var new_pos = gboard.component.quickbar.data.center_x() - out_of_right;
			gboard.component.quickbar.data.center_x(new_pos);
			
			// 커서가 center 영역에 존재하면 커서도 같이 이동 시켜야 한다.
			var $cursor_element = $('#quick-element-cursor');
			if($cursor_element.parent().attr('id') == 'quick-bar-part-center') {
				var opos = parseInt($cursor_element.css('left'), 10);
				$cursor_element.css('left', opos-out_of_right);
			}
		}
	}
	
	// 3. 윈도우 리사이즈에 따른 커서 위치를 구한다.
	// 윈도우가 resize 되면 커서 위치를 바탕으로 커서 위치를 구하려고 하지 말고
	// 원본 위치에서 커서 위치를 꺼꾸로 구하면 된다...후...이거 진짜....이렇게 간단한 걸 하루 죙일 했다니....
	var new_cursor_left = -(gboard.component.quickbar.data.center_x() / gboard.component.quickbar.scroll.ratio);
	$scroll_indicator.css('left', new_cursor_left);
	
	gboard.component.quickbar.scroll.scroll_prev_width = scroll_bar_width;
	gboard.component.quickbar.scroll.spot_prev_left = 0;
	
	// scroll-bar indicator 의 너비 반영
	gboard.component.quickbar.data.scroll_indicator_width(cursor_width);
},

/**
 * @param item {object} 추가할 아이템 상세. obj 의 구조는 다음과 같다.{title, depth, url, type}
 * 				title : icon 의 이름
 *				depth : 해당 메뉴의 depth(indicator 바에서 보여지는 텍스트 depth). array 임
 *				url : 해당 메뉴의 url
 *				type : 해당 메뉴의 type( 현재는 home, profile, normal 만 지원됨)
 *				image : 기본 이미지를 원하는 이미지로 바꿀때 이미지 URL 값
 *
 *				위의 항목 외의 내부에서 다음 값을 설정 한다.
 *				clickThis : click 했을 때 호출 될 함수
 *				id: DOM id attribute name
 *				pos : css left 위치
 *				className : 사용할 css class
 *				is_image_custom : custom image 를 사용할지 여부(image 값이 있으면 true 가 된다. 아님 false)
 */
_showContentPage: function(item) {
	// 건네 받은 url 에서 '/' 를 '-' 로 바꾸고, prefix 로 'content-' 를 붙인다.
	// 추후 원본을 교환 할때 기억 해야 한다.
	url = 'content-'+item.url.replace(/\//gi, '-');
	
	var $child = gboard.component.quickbar.$view_target.children('#'+url);
	if($child.length >= 1) {
		// 기존에 보여 주고 있는 것이 있다면 숨긴다.
		if(gboard.component.quickbar.$current_view_content) {
			gboard.component.quickbar.$current_view_content.hide();
		}
		gboard.component.quickbar.$current_view_content = $child;
		$child.show();
		
	} else {
		var nid = gboard.component.notification.addNotification(
				item.title, 
				gboard.admin.main.data.conf.text.page_makeup
			);
			
		var $child = $('<iframe>')
			.addClass('view-content-display')
			.attr({'seamless': 'seamless', 'id': url,});
			
		$child
			.ready(function(){
				gboard.component.notification.removeNotification(nid);
				gboard.component.quickbar.$view_target.append($child);
				
				// 기존에 보여 주고 있는 것이 있다면 숨긴다.
				if(gboard.component.quickbar.$current_view_content) {
					gboard.component.quickbar.$current_view_content.hide();
				}
				gboard.component.quickbar.$current_view_content = $child;
			})
			.attr('src', 'http://'+gboard.component.quickbar.domain+item.url);
	}
},

/**
 * window resize 될때 호출 되는 event callback function. 다음 기능을 한다.
 * 1. quick bar 의 scroll 처리를 한다.
 */
quickBarResize: function(evt) {
	var $quick_bar				= $('#window-top-quick-bar');
	var $quick_center			= $('#quick-bar-part-center');
	var $scroll_spot			= $('#quick-bar-scroll-bar');
	var width					= $quick_center.width();			// center 의 size
	var children_width			= gboard.component.quickbar.data.center_width();
	
	// XXX quick-bar 아래 DOM Element 가 DOM 구조상으로 앞에 있기 때문에 prev 하였음
	//     만일 다음에 있다면 next 를 하면 됨.
	var $quick_bar_prev			= $quick_bar.prev();
	var contentTopY				= parseInt($quick_bar_prev.css('top'), 10);
	
	// screenTop, screenLeft 는 firefox 에서 지원하지 않는다.
	//if(evt) {
	//	console.log('screenLeft['+evt.target.screenLeft+']');
	//	console.log('screenTop['+evt.target.screenTop+']');
	//}
	
	if(children_width > width) {
		if(contentTopY < 88) {
			$quick_bar_prev.css('top', 88);
			gboard.component.quickbar.data.center_right_shadow(1);
			
			// scroll-bar 를 크기에 맞게 설정 하고, scroll-bar spot 의 너비를 설정한다.
			gboard.component.quickbar._setupScrollBar(
					$quick_bar.width(), children_width, width, $scroll_spot);

			$('#view-port-quick-bar-scroll').show();
			$scroll_spot.show();
			
		} else if(contentTopY == 88) {
			// scroll-bar 를 크기에 맞게 설정 하고, scroll-bar spot 의 너비를 설정한다.
			gboard.component.quickbar._setupScrollBar(
					$quick_bar.width(), children_width, width, $scroll_spot);
		}
	} else {
		if(contentTopY > 78) {
			// 컨텐츠의 마지막 보정과 정리를 한번 해 준다.
			gboard.component.quickbar.data.center_x(gboard.component.quickbar.scroll.content_ex);
			gboard.component.quickbar.data.scroll_indicator_width(0);
			gboard.component.quickbar.scroll.ratio = 1;
			gboard.component.quickbar.scroll.spot_sx = 0;
			gboard.component.quickbar.scroll.spot_ex = 0;
			gboard.component.quickbar.scroll.content_sx = 0;
			gboard.component.quickbar.scroll.content_ex = 0;
			gboard.component.quickbar.scroll.scroll_prev_width = 0;
			gboard.component.quickbar.scroll.spot_prev_left = 0;
			
			$quick_bar_prev.css('top', 78);
			gboard.component.quickbar.data.center_right_shadow(0);
			gboard.component.quickbar.data.center_left_shadow(0);
			
			// quick bar 의 scroll bar 숨기기
			$('#view-port-quick-bar-scroll').hide();
			$scroll_spot.hide();
		}
	}
},

/**
 * quick bar 를 초기화 한다.
 * @param cb_cursor_move_end {function} quick bar 의 cursor 를 이동 완료 이후 호출 할 함수
 */
init: function(target, cb_cursor_move_end) {
	if(gboard.component.quickbar.initialized) { return; }
	
	// quick bar 에 binding 건다.
	ko.applyBindings(gboard.component.quickbar.data, 
		$('#window-top-quick-bar').get(0));
		
	// 기본 content view target 을 지정한다.
	gboard.component.quickbar.$view_target = target;
	
	gboard.component.quickbar.cb_cursor_move_end = cb_cursor_move_end;
	
	var $cursor_element = $('#quick-element-cursor');
	
	$('#quick-bar-scroll-bar').draggable({
			axis: 'x',
			containment: 'parent',
			drag: function(evt, ui) {
				evt.stopPropagation();
				//evt.preventDefault();	// 이거 넣으면 안됨. 이벤트 trigger 하는 것이 있는 듯함.
				
				var indicator_x = ui.position.left;
				
				// 스크롤 좌,우 끝에서 의미 없는 처리를 막는다.
				if(gboard.component.quickbar.scroll.spot_prev_left == indicator_x) {
					return;
				}
				
				// scroll-bar indicator 위치 변경에 따른 원본의 변경 위치를 구한다.
				var content_x = indicator_x * gboard.component.quickbar.scroll.ratio;
				// 비율이 1이 아닐 경우 가장 마지막에 보정 한다.
				if(gboard.component.quickbar.scroll.ratio != 1) {
					// 오른쪽 끝일 때 보정
					if(indicator_x >= gboard.component.quickbar.scroll.spot_ex) {
						content_x = -gboard.component.quickbar.scroll.content_sx;
					}
					// 왼쪽 끝일 때 보정
					else if(indicator_x <= gboard.component.quickbar.scroll.spot_sx) {
						content_x = gboard.component.quickbar.scroll.content_ex;
					}
				}
				
				gboard.component.quickbar.data.center_x(-content_x);
				
				// 커서가 center 영역에 존재하면 커서도 같이 이동 시켜야 한다.
				if($cursor_element.parent().attr('id') == 'quick-bar-part-center') {
					var opos = parseInt($cursor_element.attr('x-opos'), 10);
					$cursor_element.css('left', opos-content_x);
				}
				
				// scroll-bar indicator 의 움직임에 따라 원본 view port 의 좌, 우 shadow 를 on/off 한다.
				if(content_x != 0) {
					gboard.component.quickbar.data.center_left_shadow(1);
					if(-content_x == gboard.component.quickbar.scroll.content_sx) {
						gboard.component.quickbar.data.center_right_shadow(0);
					} else {
						gboard.component.quickbar.data.center_right_shadow(1);
					}
				} else {
					gboard.component.quickbar.data.center_left_shadow(0);
				}
				
				gboard.component.quickbar.scroll.spot_prev_left = indicator_x;
			}
		});
		
	// 커서에 hover 반응(문서 닫기)를 건다. action 은 추가 해야 한다.
	$cursor_element.hover(
			function(evt) {
				evt.stopPropagation();
				evt.preventDefault();
				var $this = $(this);
				if($this.parent().attr('id') == 'quick-bar-part-center') {
					$this.children().show();
				}
			},
			function(evt) {
				evt.stopPropagation();
				evt.preventDefault();
				var $this = $(this);
				if($this.parent().attr('id') == 'quick-bar-part-center') {
					$this.children().hide();
				}
			}
		);
	$cursor_element.children().first().click(function(evt){
			evt.stopPropagation();
			evt.preventDefault();
			console.log('> click cursor close');
			
			// TODO cursor 의 닫기 버튼을 클릭 했을때, item 을 닫아 줘야 한다.
		});
		
		
	// XXX window resize event 처리.
	//     30 milli-sec 마다 window-resize event 를 처리 하도록 한다.
	//     만일 성능의 문제가 생기면 시간 간격을 늘이자
	var wnd_resize_timer = 0;
	$(window).bind('resize', function(evt){
			evt.stopPropagation();
			evt.preventDefault();
			//if(wnd_resize_timer) { clearTimeout(wnd_resize_timer); }
			if(!wnd_resize_timer) {
				wnd_resize_timer = setTimeout(function(){
						gboard.component.quickbar.quickBarResize(evt);
						wnd_resize_timer = 0;
					}, 30);
			}
		});
		
	gboard.component.quickbar.initialized = true;
},

/**
 * quick bar left 영역 에 quick item 을 추가 한다.
 *
 * @param obj {object} 추가할 아이템 상세. obj 의 구조는 다음과 같다.{title, depth, url, type}
 * 				title : icon 의 이름
 *				depth : 해당 메뉴의 depth(indicator 바에서 보여지는 텍스트 depth). array 임
 *				url : 해당 메뉴의 url
 *				type : 해당 메뉴의 type( 현재는 home, profile, normal 만 지원됨)
 *				image : 기본 이미지를 원하는 이미지로 바꿀때 이미지 URL 값
 *
 *				위의 항목 외의 내부에서 다음 값을 설정 한다.
 *				clickThis : click 했을 때 호출 될 함수
 *				id: DOM id attribute name
 *				pos : css left 위치
 *				className : 사용할 css class
 *				is_image_custom : custom image 를 사용할지 여부(image 값이 있으면 true 가 된다. 아님 false)
 */
pushLeftItem: function(obj) {
	var leftEdge = 10;
	var length = gboard.component.quickbar.data.left_items().length;
	// left item 은 2 개만 넣을 수 있음
	if(length >= 2) { return; }
	
	obj.className = 'quick-icon-'+obj.type;
	obj.id = 'quick-menu-left-'+length;
	obj.clickThis = gboard.component.quickbar.quickItemClick;
	obj.clickClose = null;
	obj.use_hover = false;
	
	if(obj.image) {
		obj.is_image_custom = true;
		obj.image = "url('"+obj.image+"')";
	} else {
		obj.is_image_custom = false;
		obj.image = '';
	}
	
	obj.pos = (length+1)*leftEdge + length*40;
	gboard.component.quickbar.data.left_items.push(obj);
},

/**
 * quick bar 에 quick item 을 추가 한다.(quick bar left, center 에 추가 한다)
 *
 * @param obj {object} 추가할 아이템 상세. obj 의 구조는 다음과 같다.{title, depth, url, type}
 * 				title : icon 의 이름
 *				depth : 해당 메뉴의 depth(indicator 바에서 보여지는 텍스트 depth). array 임
 *				url : 해당 메뉴의 url
 *				type : 해당 메뉴의 type( 현재는 home, profile, normal 만 지원됨)
 *				image : 기본 이미지를 원하는 이미지로 바꿀때 이미지 URL 값
 *
 *				위의 항목 외의 내부에서 다음 값을 설정 한다.
 *				clickThis : click 했을 때 호출 될 함수
 *				id: DOM id attribute name
 *				pos : css left 위치
 *				className : 사용할 css class
 *				is_image_custom : custom image 를 사용할지 여부(image 값이 있으면 true 가 된다. 아님 false)
 * @param hasCursor {boolean} center list 에 item 을 insert 할때 insert 와 동시에 커서를 가질 것인지 여부
 *                            true 이면 insert 와 동시에 커서를 가진다. 
 */
pushCenterItem: function(obj, hasCursor) {
	var center_items = gboard.component.quickbar.data.center_items();
	
	for(var i=0 ; i<center_items.length ; i++) {
		// 이미 존재하는 것이 있으므로 추가 하지 않고 커서를 이동 시킨다.
		if(center_items[i].url == obj.url) {
			// 커서도 동일한 위치에 있다면 아무런 짓도 하지 않고 그냥 리턴
			if(center_items[i].id == gboard.component.quickbar.data.cursor_pos()) {
				return;
			}
		
			// 커서 이동 후 quick item 의 닫기 버튼을 보여주지 않도록 한다.
			gboard.component.quickbar.cursor_close_flag = false;
			gboard.component.quickbar.data.cursor_pos(center_items[i].id);
			
			// TODO content 내용을 바꿔야 한다.
			
			// TODO 커서 위치에 따라 quick menu 를 스크롤 시켜야 한다. 스크롤 해야 하나?
			
			return;
		}
	}

	var leftEdge = 10;
	var center_length = gboard.component.quickbar.data.center_items().length;
	
	obj.className = 'quick-icon-'+obj.type;
	obj.id = 'quick-menu-center'+(gboard.component.quickbar._ids++);
	obj.clickThis = gboard.component.quickbar.quickItemClick;
	obj.clickClose = gboard.component.quickbar.quickItemClose;
	obj.use_hover = true;
	
	if(obj.image) {
		obj.is_image_custom = true;
		obj.image = "url('"+obj.image+"')";
	} else {
		obj.is_image_custom = false;
		obj.image = '';
	}
	
	obj.pos = 5 + center_length*leftEdge + center_length*40;
	gboard.component.quickbar.data.center_width(obj.pos+45);
	gboard.component.quickbar.data.center_items.push(obj);

	// cursor 를 이동 시킨다.
	if(hasCursor) {
		// 커서 이동 후 quick item 의 닫기 버튼을 보여주지 않도록 한다.
		gboard.component.quickbar.cursor_close_flag = false;
		gboard.component.quickbar.data.cursor_pos(obj.id);
	}
	
	gboard.component.quickbar.quickBarResize();
},

/**
 * 고정된 특정 메뉴로 사용 할 수 있는 quick bar right 영역에 item 을 설정 한다.
 * quick bar right item 은 하나만 추가 가능하다.
 * TODO - obj 의 상세 항목은 개발 하면서 정의 필요 하다.
 *
 * @param obj 추가할 아이템 상세. obj 의 구조는 다음과 같다.{title, ...}
 * 				title : icon 의 이름
 *				type : 해당 메뉴의 type( 현재는 home, profile, normal 만 지원됨)
 */
setRightItem: function(obj) {
	var static_item = [];
	static_item[1] = 'display';
	static_item[1] = obj.title;
	static_item[2] = 'quick-icon-'+obj.type;
	gboard.component.quickbar.data.right_item(static_item);
},

// quick bar 에서 item remove
removeItem: function() {
	
},



	
};}


//--------------------------------------------------
// Quick Bar Custom binding
//--------------------------------------------------
// 상단 quick bar cursor 의 binding handler
ko.bindingHandlers.m_quick_cursor = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
		
		var items = null;
		var is_center = false;
		
		if(!valueUnwrapped) { return false; }
		
		if(valueUnwrapped.indexOf('quick-menu-left') == -1) {
			items = gboard.component.quickbar.data.center_items();
			is_center = true;
		} else {
			items = gboard.component.quickbar.data.left_items();
		}
		
		var index = -1;
		for(var i=0 ; i<items.length ; i++) {
			if(items[i].id == valueUnwrapped) {
				index = i;
				break;
			}
		}
		
		if(index == -1) { return; }
		
		var $item = $('#'+valueUnwrapped);
		var $this = $(element);
		var pos = $item.position();
		
		// XXX center_items 에 push 하는 것과 cursor_pos 값을 바꾸는 것에 특정한 문제점(?)-뭔지는 모름-
		//     때문에 m_quick_cursor 가 두 번 호출 되는 문제가 있다. 이것 때문에 x-prev-pos 값으로 예외 상황을 추가 하였음.
		var prev_pos = $this.attr('x-prev-pos');
		if(prev_pos && prev_pos == valueUnwrapped) {
			return;
		}
		$this.attr('x-prev-pos', valueUnwrapped);
		
		if($item.length <= 0) { return; }
		
		// cursor 의 위치 변경
		if(is_center)	{
			if($this.parent().attr('id') != 'quick-bar-part-center') {
				$('#quick-bar-part-center').append($this);
			}
			// center 일 경우 닫기 버튼을 hide 시킨다.
			$item.children().last().hide();
			
			// 커서가 이동 했으므로 커서 위에 닫기 버튼을 show 시킨다.
			if(gboard.component.quickbar.cursor_close_flag) {
				$this.children().show();
			} else {
				$this.children().hide();
				gboard.component.quickbar.cursor_close_flag = true;
			}
			
			var center_area_position = $item.parent().position();
			$this.attr('x-opos', pos.left-4)
				.css('left', pos.left-4+center_area_position.left);
			
		} else {
			if($this.parent().attr('id') != 'quick-bar-part-left') {
				$('#quick-bar-part-left').append($this);
			}
			$this.attr('x-opos', '-1').css('left', pos.left-4);
		}
		
		// tooltip 을 변경
		var $child = $item.children().first();
		var title = $child.attr('title');
		$this.attr('title', $child.attr('title'));
		
		// content target 에 내용을 반영한다.
		gboard.component.quickbar._showContentPage(items[index]);
		
		// 커서 이동 후, 해야 할 일이 있으면 호출 한다.
		if(gboard.component.quickbar.cb_cursor_move_end) {
			if(is_center) {
				(gboard.component.quickbar.cb_cursor_move_end)(
					gboard.component.quickbar.data.center_items()[index].depth);
			} else {
				(gboard.component.quickbar.cb_cursor_move_end)(
					gboard.component.quickbar.data.left_items()[index].depth);
			}
		}
	}
};

// quick bar item 의 close button show/hide binding handler
ko.bindingHandlers.m_quick_item_hover = {
	//init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
	update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
		var value = valueAccessor();
		var valueUnwrapped = ko.utils.unwrapObservable(value);
	
		if(valueUnwrapped) {
			var $element = $(element);
			var is_close = $element.hasClass('quick-icon-close');
			
			if(is_close) {
				$element.hover(
						function(evt) {
							evt.stopPropagation();
							evt.preventDefault();
							$element.show();
						},
						function(evt) {
							evt.stopPropagation();
							evt.preventDefault();
							$element.hide();
						}
					);
			} else {
				var $close = $element.next().next();
			
				$element.hover(
						function(evt){
							evt.stopPropagation();
							evt.preventDefault();
							$close.show();
						},
						function(evt){
							evt.stopPropagation();
							evt.preventDefault();
							$close.hide();
						}
					);
			}
		}
	}
};


