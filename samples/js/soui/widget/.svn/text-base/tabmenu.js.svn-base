/** 
  * @projectDescription soui 에서 지원하는 tabmenu widget
  * @author dhkim94@gmail.com
  * @version 0.2.1
  */

(function($){
$.widget('soui.SOWTabmenu', {


options: {
	menuCellWidth:		32,			// menu bar 에 들어가는 menu 하나의 너비
	menuHeight:			22,			// menu 의 높이. 높이는 하단 border 포함임.

	// menu 의 bg 색상값 조절
	menuStartColor:			'#dadada',
	menuEndColor:			'#bcbcbc',
	menuGradientDS:			-0.35,
	menuBorderDS:			-0.705,
	
	menuBorderColor:		'#555555',	// menu border 의 색상
	
	// menu cell 간의 분리하는 line 의 색상
	menuCellDivStartColor:	'#c3c3c3',
	menuCellDivCenterColor:	'#717171 50%',
	
	// menu cell 의 색상(선택 되었을때)
	menuCellStartColor:		'#d9d9d9',
	menuCellCenterColor:	'#b9b9b9 50%',
	
	contentColor:			'transparent',
	
	resizeTime:				10			// tabmenu 가 resize 되었을때 resize 에 따른 로직 처리를 제한할 시간
										// resize 도중 10 milli-sec 마다 한번씩 호출 된다.(이벤트 스로틀링)
},

/** jQuery Function Part */
_create: function() {
	this.element.attr('so-ui-type', 'tabmenu');
	this.element.bind('resize', $.proxy(this.__resize, this));
	
	var menuHSV = null;
	
	if(!this.options.menuEndColor) {
		if(this.options.menuGradientDS != null && this.options.menuGradientDS != undefined) {
			menuHSV = $.SORgbToHsv($('<div>').css('backgroundColor', this.options.menuStartColor)
				.css('backgroundColor'));
	
			// gradient end color 를 구한다.
			var copyHSV = menuHSV.slice();	// array copy
			copyHSV[2] += this.options.menuGradientDS;
		
			if(copyHSV[2] >= 1)		{ copyHSV[2] = 1; }
			else if(copyHSV[2] <= 0){ copyHSV[2] = 0; }
			else					{ $.noop(); }
		
			var menuGradEndRGB = $.SOHsvToRgb(copyHSV);
			this.options.menuEndColor = ['rgb(', menuGradEndRGB[0], ', ', menuGradEndRGB[1], ', ', menuGradEndRGB[2], ')'].join('');
		
			delete copyHSV;
		} else {
			this.options.menuEndColor = null;
		}
	}
	
	if(!this.options.menuBorderColor) {
		if(this.options.menuBorderDS != null && this.options.menuBorderDS != undefined) {
			// border color 를 구한다.
			if(!menuHSV) {
				menuHSV = $.SORgbToHsv($('<div>').css('backgroundColor', this.options.menuStartColor)
					.css('backgroundColor'));
			}
			var copyHSV = menuHSV.slice();
		
			copyHSV[2] += this.options.menuBorderDS;
		
			if(copyHSV[2] >= 1)		{ copyHSV[2] = 1; }
			else if(copyHSV[2] <= 0){ copyHSV[2] = 0; }
			else					{ $.noop(); }
	
			var borderRGB = $.SOHsvToRgb(copyHSV);
			this.options.menuBorderColor = ['rgb(', borderRGB[0], ', ', borderRGB[1], ', ', borderRGB[2], ')'].join('');
			
			delete copyHSV;
		} else {
			this.options.menuBorderColor = 'black';
		}
	}

	var css = {position: 'relative', width: '100%', height: this.options.menuHeight-1, 
			backgroundColor: this.options.menuStartColor, overflow: 'hidden'};
	
	if(this.options.menuEndColor) {
		css.background = $.SOGetLinearGraidentStyleValue('top', this.options.menuStartColor, this.options.menuEndColor);
	}
	
	css.borderBottom = '1px solid '+this.options.menuBorderColor;
	
	// 메뉴 bar 영역 추가
	this._$menu = $('<div id="tabmenu-menu">').css(css).appendTo(this.element);
	
	// content 영역 추가
	css.backgroundColor = this.options.contentColor;
	css.background = css.borderBottom = '';
	css.height = this.element.height()-this.options.menuHeight
	
	this._$content = $('<div>').css(css).appendTo(this.element);
},

_init: function() {
	this.__height		= this.element.height();			// widget 의 높이
	this.__resizeTimer	= null;
	this.__halfW		= this.options.menuCellWidth>>1;
	this.__tabs			= [];								// tab 이 저장될 array
	this.__views		= [];								// view 가 저장될 array
	this.__prevTabIndex	= -1;								// tab icon 선택을 위한 값
},

destroy: function() {
	$.Widget.prototype.destroy.apply(this, arguments);
	
	this.element.unbind('resize');
	this.element.find('#tabmenu-menu').children().unbind('.soui-SOWTabmenu').remove();	// tabmenu icon 에 걸려 있는 선택 이벤트 unbind
	this.element.remove();
},


/** User Function Part */
/** tabmenu 가 resize 되었을때 호출되는 callback function. __resize 에서 호출 된다 */
__resizeCB : function() {
	var hei = this.element.height();
	
	if(hei != this.__height) { this._$content.height(hei-this.options.menuHeight); }
	
	this.__resizeTimer = null;
},

/** tabmenu 가 resize 되었을때 호출되는 function. 이벤트 스로틀링 으로 __resizeCB 를 내부에서 호출 한다 */
__resize: function(e) {
	// child 에서 발생한 resize event 에 대해서 bubbling 을 막지 않는다.
	if(this.element[0] != e.target) { return true; }

	e.stopPropagation();
	
	if(!this.__resizeTimer) {
		clearTimeout(this.__resizeTimer);
		this.__resizeTimer = setTimeout($.proxy(this.__resizeCB, this), 10);
	}
},

/** tab 을 선택 했을 때 호출 되는 함수 */
__selectTab: function(e) {
	var index = 0;
	var target = $(e.target);
	
	if(target.attr('tabmenu-index') == undefined) {
		target = target.parent();
		index = target.attr('tabmenu-index');
	} else {
		index = target.attr('tabmenu-index');
	}
	
	// 현재 선택되어 있는 않은 tab-icon 을 click 했을때만 tab-menu 를 바꾼다.
	if(index != this.__prevTabIndex) {
		this.setTab(index);
	}
},

/** tab 을 추가 한다 */
appendTab: function(title, menu, content) {
	// 기존에 추가된 tab-icon 의 위치를 재조정 한다.
	var lpos = -this.__halfW * (this.__tabs.length+1);
	for(var prop in this.__tabs) {
		this.__tabs[prop].css('marginLeft', lpos);
		lpos += this.options.menuCellWidth;
	}
	
	// 신규 아이콘을 추가 한다.
	var newIconPos = -this.__halfW + this.__halfW*this.__tabs.length;	// 신규로 추가할 tab-icon 의 위치
	
	var css = {position:'absolute', left:'50%', marginLeft:newIconPos, overflow: 'hidden',
		width:this.options.menuCellWidth, height:this.options.menuHeight-1};
	
	// icon 이 들어갈 영역을 생성
	var cell = $('<a>').addClass('soui-none-select').addClass('soui-oneline')
		.css(css).attr('title', title).appendTo(this._$menu)
		.attr('tabmenu-index', this.__tabs.length)
		.bind('mouseup.soui-SOWTabmenu', $.proxy(this.__selectTab, this));
	
	// menu 를 menu area 에 넣는다.
	menu.css({position:'absolute', left:'50%', top:'50%', marginLeft:-menu.outerWidth()>>1, marginTop:-menu.outerHeight()>>1})
		.appendTo(cell);
	
	// cell 의 왼쪽 구분선을 넣는다
	css.left		= 0;
	css.marginLeft	= '';
	css.width		= 1;
	css.height		= '100%';
	css.backgroundColor = this.options.menuCellDivStartColor;
	css.background	= $.SOGetLinearGraidentStyleValue('top', 
			this.options.menuCellDivStartColor, this.options.menuCellDivStartColor, this.options.menuCellDivCenterColor);
	$('<div>').css(css).attr('so-ui-type', 'tabmenu-div-line').appendTo(cell).hide();
	
	// cell 의 오른쪽 구분선을 넣는다
	css.left		= '';
	css.right		= 0;
	$('<div>').css(css).attr('so-ui-type', 'tabmenu-div-line').appendTo(cell).hide();
	
	this.__tabs.push(cell);	// tab 에 아이콘 추가
	
	// tab 과 연결되어 있는 view 추가
	content
		.css({position:'absolute', left:0, right:'', top:0, bottom:'', width:'100%', height:'100%', margin:0, padding:0})
		.appendTo(this._$content).hide();
	
	this.__views.push(content);	// tab 아이콘 선택에 따라 보여줄 content 추가
},

/** tab 을 선택 한다 */
setTab: function(index) {
	if(index >= this.__tabs.length) {
		if(SO.UI.ENV.DEBUG) {
			if(window.console) console.warn('[SOWTabmenu] out of tabmenu index for setTab. index=['+index+']');
		}
		return this.element;
	}
	
	// tab icon 을 바꾼다.
	if(this.__prevTabIndex >= 0) {
		this.__tabs[this.__prevTabIndex].css({backgroundColor:'', background:''})
			.children('[so-ui-type="tabmenu-div-line"]').hide();
	}
	this.__tabs[index].css({background: $.SOGetLinearGraidentStyleValue('top', this.options.menuCellStartColor, 
				this.options.menuCellCenterColor, this.options.menuCellStartColor),
			backgroundColor: this.options.menuCellStartColor})
		.children('[so-ui-type="tabmenu-div-line"]').show();

	// 이전 content 를 숨기고 선택한 content 를 보여 준다
	if(this.__prevTabIndex != -1) { this.__views[this.__prevTabIndex].hide(); }
	this.__views[index].show();
	
	this.__prevTabIndex = index;
}


});
})(jQuery);