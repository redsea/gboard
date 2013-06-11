/** 
  * @projectDescription soui 에서 지원하는 toolbar widget
  * @author dhkim94@gmail.com
  * @version 0.2.1
  */

(function($){
$.widget('soui.SOWToolbar', {

options: {
	type: 'horizontal',
	edge: 5,
},

/** jQuery Function Part */
_create: function() {
	this.element.attr('so-ui-type', 'toolbar');
},

_init: function() {
	this.__elementPosLT  = this.options.edge;	// 좌측 정렬을 위한 좌표값
	this.__elementPosRB  = this.options.edge;	// 우측 정렬을 위한 좌표값
	this.__elementCenter = null;				// 센터 정렬을 위한 array 
},

destroy: function() {
	$.Widget.prototype.destroy.apply(this, arguments);
	
	if(this.__elementCenter) { delete this.__elementCenter; }
	this.element.remove();
},


/** User Function Part */
/** toolbar 에 추가
  * align : 'left', 'right', 'top', 'bottom', 'center' 중 하나의 값
  */
append: function(tool, align) {
	var css = {};
	
	if(this.options.type == 'vertical') {
		css.left = '50%';
		css.right = '';
		css.marginLeft = -(tool.outerWidth()>>1);
	
		if(align == 'bottom') {
			css.bottom = this.__elementPosRB;
			css.top = '';
			this.__elementPosRB += tool.outerHeight() + this.options.edge;
			
		} else if(align == 'center') {
			css.top = '50%';
			css.bottom = '';
			
			if(!this.__elementCenter) { this.__elementCenter = []; }
			
			this.__elementCenter.push(tool);
			
			var totalHei = 0;
			for(var prop in this.__elementCenter) {
				totalHei += this.__elementCenter[prop].outerHeight();
			}
			
			var pos = -totalHei>>1;
			for(var prop in this.__elementCenter) {
				this.__elementCenter[prop].css('marginTop', pos);
				pos += this.__elementCenter[prop].outerHeight()+ this.options.edge;
			}
			
		} else {
			css.top = this.__elementPosLT;
			css.bottom = '';
			this.__elementPosLT += tool.outerHeight() + this.options.edge;
		}
		
	} else {
		css.top = '50%';
		css.bottom = '';
		css.marginTop = -(tool.outerHeight()>>1);
		
		if(align == 'right') {
			css.right = this.__elementPosRB;
			css.left = '';
			this.__elementPosRB += tool.outerWidth() + this.options.edge;
			
		} else if(align == 'center') {
			css.left = '50%';
			css.right = '';
		
			if(!this.__elementCenter) { this.__elementCenter = []; }
			
			this.__elementCenter.push(tool);
			
			var totalWid = 0;
			for(var prop in this.__elementCenter) {
				totalWid += this.__elementCenter[prop].outerWidth();
			}
			
			var pos = -totalWid>>1;
			for(var prop in this.__elementCenter) {
				this.__elementCenter[prop].css('marginLeft', pos);
				pos += this.__elementCenter[prop].outerWidth()+ this.options.edge;
			}
			
		} else {
			css.left = this.__elementPosLT;
			css.right = '';
			this.__elementPosLT += tool.outerWidth() + this.options.edge;
			
		}
		
	}
	
	tool.css(css).appendTo(this.element);
}


});
})(jQuery);