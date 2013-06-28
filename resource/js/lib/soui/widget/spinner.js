/** 
  * @projectDescription soui 에서 지원하는 spinner widget
  * @author dhkim94@gmail.com
  * @version 0.2.1
  */

(function($){
$.widget('soui.SOWSpinner', {


options: {
	scale:		1.0,
	color:		'#FFFFFF',
	bgColor:	null,
	basePane:	true
},

/** jQuery Function Part */
_create: function() {
	this.element.attr('so-ui-type', 'spinner');
	
	this._$box = $('<div>').addClass('soui-spinner-load-box').prependTo(this.element);
		
	for(var i=1 ; i<=8 ; i++) {
		$('<div>').addClass('soui-spinner-load-element'+i)
			.css('backgroundColor', this.options.color).appendTo(this._$box);
	}
	
	if(this.options.basePane) {
		var complement = null;
		
		if(this.options.bgColor) { complement = this.options.bgColor; }
		else { complement = $.SOGetComplementColor($('.soui-spinner-load-element1').css('backgroundColor')); }
		
		$('<div>').addClass('soui-spinner-block-pane')
			.css('backgroundColor', complement).prependTo(this.element);
	}
},

_init: function() {
	this.__animation 	= null;
	this.__deg			= 0;
	this.__playAnimate	= false;
},

destroy: function() {
	$.Widget.prototype.destroy.apply(this, arguments);
	
	this.stop();
	this.element.remove();
},


/** User Function Part */
/** spinner 를 rotate 시킨다 **/
__rotate: function() {
	var css = {
		MozTransform: ['scale(', this.options.scale, ') rotate(', this.__deg, 'deg)'].join(''),
		WebkitTransform: ['scale(', this.options.scale, ') rotate(', this.__deg, 'deg)'].join(''),
		MsTransform: ['scale(', this.options.scale, ') rotate(', this.__deg, 'deg)'].join(''),
		OTransform: ['scale(', this.options.scale, ') rotate(', this.__deg, 'deg)'].join(''),
		transform: ['scale(', this.options.scale, ') rotate(', this.__deg, 'deg)'].join('')
	};
	
	this._$box.css(css);
	this.__deg += 45;
	
	if(this.__playAnimate) {
		this.__animation = setTimeout($.proxy(this.__rotate, this), 100);
	}
},

start: function() {
	this.__deg			= 0;
	this.__playAnimate	= true;
	this.__animation	= setTimeout($.proxy(this.__rotate, this), 100);
},

stop: function() {
	this.__playAnimate = false;
	if(this.__animation) {
		clearTimeout(this.__animation);
		this.__animation = null;
	}
}


});
})(jQuery);