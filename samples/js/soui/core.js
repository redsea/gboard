/** 
  * @projectDescription soui 를 사용하기 위한 core 용 로직
  * @author dhkim94@gmail.com
  * @version 0.2.1
  */

var SO = SO || {};

SO.UI = SO.UI || {};


// soui 환경 설정
SO.UI.ENV = SO.UI.ENV || {
	_SUPPORT_LANG: ['ko', 'en'],	// 지원하는 언어셋
	_DEFAULT_LANG: 'ko',			// 기본 언어셋
	
	DEBUG:			false,			// debug mode 인지 아닌지 유무(true 이면 debug mode, false 이면 non-debug mode)
	LANG:			'ko'			// SO GUI 의 설정 언어셋
};


// soui 의 ui-system 변수
SO.UI.SYS = SO.UI.SYS || {
	_$DOC:			null,
	_OBJ:			null,		// 범위를 벗어나는 DOM 이벤트 처리를 위한 이벤트 발생 object 저장용 변수
	
	$TEMPORARY_BUFF:null,		// UI 생성을 도와 주는 임시 저장 장소
	
	DOC_RESIZE:		null,		// window resize timer 스로틀링용 제어 변수
	
	DOC_WIDTH:		0,			// window resize 대비용 document width
	DOC_HEIGHT:		0,			// window resize 대비용 document height
	
	DOC_RESIZE_DW:	0,			// window resize 가 일어 났을때 width 의 변위량
	DOC_RESIZE_DH:	0,			// window resize 가 일어 났을때 height 의 변위량
	
	SYS_PATH:		null,		// so ui library(js 파일)이 들어 있는 패스
	
	ELAPSE_TIME:	0,			// 로직 실행 시간을 측정하기 위한 시작 시간
	
	//SYNC_JOB_POOL:	{},			// callback 을 하나씩 sync 형태로 처리 하기 위한 pool
	
	Z_INDEX_SPINNER:	9999,	// spinner 의 zIndex
	Z_INDEX_BLOCK:		9000,	// block screen 은 spinner 보다 아래에 있기 때문에 spinner 보다 작아야 한다.
	
	// 지원하는 component type
	//_SUPPORT_COMPONENT: ['button', 'toolbar', 'tree', 'tabmenu', 'spinner']
};


SO.UI.THEME = SO.UI.THEME || {
	res: {
		image: {path: '/imgs/soui.png'}
	}
};

(function($){

/** soui 에서 사용하는 언어를 설정 한다 */
$.SOSetLang = function(lang) {
	if(!lang) { return (SO.UI.ENV.LANG = SO.UI.ENV._DEFAULT_LANG); }
	
	for(var support in SO.UI.ENV._SUPPORT_LANG) {
		if(lang == SO.UI.ENV._SUPPORT_LANG[support]) {
			return (SO.UI.ENV.LANG = lang);
		}
	}
	
	return (SO.UI.ENV.LANG = SO.UI.ENV._DEFAULT_LANG);
};

/** 이미지 패스를 포함하고 있는 정해진 형태의 object 를 이용하여 이미지를 미리 로딩한다 */
$.SOPreloadImage = function(res, evtName) {
	if(res == null) {
		$(document).trigger(evtName);
		return;
	}

	var totalCount = 0;
	for(var prop in res) { totalCount ++; }
		
	var loadCount = 0;
	for(var prop in res) {
		res[prop].img = new Image();
		res[prop].img.src = res[prop].path;
		res[prop].img.onload = function(ev) {
			loadCount++;
			if(loadCount >= totalCount) {
				if(evtName) {
					if(SO.UI.ENV.DEBUG) {
						if(window.console)console.log('[SOPreloadImage] image loaded. call \''+evtName+'\' event');
					}
					$(document).trigger(evtName);
				}
			}
		};
	}
		
	return res;
};

$.SOSetVisibleStaticBuffer = function(attr) {
	SO.UI.SYS.$TEMPORARY_BUFF.attr('so-ui-turnoff', attr?'true':'false');
	if(!attr) {
		SO.UI.SYS.$TEMPORARY_BUFF.show();
	}
	return SO.UI.SYS.$TEMPORARY_BUFF;
};

$.SOSetVisibleBuffer = function(visible) {
	if(!visible) {
		if(SO.UI.SYS.$TEMPORARY_BUFF.attr('so-ui-turnoff') == 'false') {
			return SO.UI.SYS.$TEMPORARY_BUFF;
		}
		return SO.UI.SYS.$TEMPORARY_BUFF.hide();
	}
	
	return SO.UI.SYS.$TEMPORARY_BUFF.show();
};

/** soui 를 사용하기 위한 설정을 한다 */
$.SOPrepareUI = function(lang, path, debug) {
	// 태그 생성 및 특정 용도로 임시로 사용하기 위한 장소 생성
	SO.UI.SYS.$TEMPORARY_BUFF = $('<div>').addClass('soui-buffer').attr('so-ui-turnoff', 'true').appendTo('body');
	SO.UI.SYS.$TEMPORARY_BUFF.hide();
		
	SO.UI.SYS.DOC_WIDTH = $(window).width();
	SO.UI.SYS.DOC_HEIGHT = $(window).height();
	
	SO.UI.SYS._$DOC = $(document);
	
	if(!path) { path = '/js/lib/soui'; }
	SO.UI.SYS.SYS_PATH = path;
		
	// 기본 이미지의 path 를 재지정
	for(var prop in SO.UI.THEME.res) {
		SO.UI.THEME.res[prop].path = SO.UI.SYS.SYS_PATH+SO.UI.THEME.res[prop].path;
	}
		
	$.SOSetLang(lang);						// 언어 설정
	SO.UI.ENV.DEBUG = debug ? true : false;	// debug mode 설정
		
	// window resize 가 발생 했을때, component resize 를 처리 하기 위해서
	// so ui 용 이벤트를 발생 시킨다.
	$(window).resize(function(e){
		if(e.target != window) { return false; }
	
		if(!SO.UI.SYS.DOC_RESIZE) {
			clearTimeout(SO.UI.SYS.DOC_RESIZE);
			
			SO.UI.SYS.DOC_RESIZE = setTimeout(function(){
				SO.UI.SYS.DOC_RESIZE_DW = $(window).width() - SO.UI.SYS.DOC_WIDTH;
				SO.UI.SYS.DOC_RESIZE_DH = $(window).height() - SO.UI.SYS.DOC_HEIGHT;
				
				SO.UI.SYS.DOC_WIDTH = $(window).width();
				SO.UI.SYS.DOC_HEIGHT = $(window).height();
				
				SO.UI.SYS.DOC_RESIZE = null;
					
				if(SO.UI.ENV.DEBUG) { if(window.console)console.log('[SOPrepareUI] window resize. call \'soevt-ui-resize\' event'); }
					
				SO.UI.SYS._$DOC.trigger('soevt-ui-resize');
			}, 100);
		}
	});
		
	$.SOPreloadImage(SO.UI.THEME.res, 'soevt-ui-ready');
};

/** window 가 resize 되어 soevt-ui-resize 발생 했을때, 반응할 로직을 등록한다 */
// TODO unbind 가 없음. unbind 를 만들어야 한다. 이럴려면 lamda function 을 non-lamda function 으로 만드는 것이 필요하다.
//      이거 검토해 봐야 한다...문제가 있을듯 event bubbling 으로 인해 일반 tag 에서 발생한 resize 도 처리를 하게 된다.
//      때문에 일반 tag 는 각각 다르게 걸고, 해당 함수는 window resize 로 공용으로 처리 해야 할 것으로 보인다.
$.fn.SOAddWindowResizeAction = function(fn) {
	if(fn) { SO.UI.SYS._$DOC.bind('soevt-ui-resize', fn); }
	return $(this);
};


})(jQuery);