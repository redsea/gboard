/** 
  * @projectDescription soui 에서 지원하는 utility 함수
  * @author dhkim94@gmail.com
  * @version 0.2.1
  */
  
(function($){

// 심플하게 만든 쿠키 함수. 위키에 추가 해야 한다.
$.SOCookie = function(action, cname, cvalue, cexdays, path, domain) {
	switch(action) {
		case 'get':
			var i,x,y,ARRcookies = document.cookie.split(";");
			for(i=0 ; i<ARRcookies.length ; i++) {
				x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
				y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
				x = x.replace(/^\s+|\s+$/g,"");
				if(x==cname) {
					return unescape(y);
				}
			}
			break;
			
		case 'set':
			var exdate = new Date();
			exdate.setDate(exdate.getDate() + cexdays);
			var c_value = escape(cvalue) + ((cexdays==null) ? '' : '; expires='+exdate.toUTCString());
			
			if(path)	{ c_value += '; path='+path; }
			if(domain)	{ c_value += '; domain='+domain; }
			
			document.cookie = cname + "=" + c_value;
			break;
	}
	
	return '';
};

$.SOGetLinearGraidentStyleValue = function(startPos, colorStart, colorEnd, colorCenter) {
	var colorValue = null;

	if(colorCenter)	{ colorValue = [startPos, ', ', colorStart, ', ', colorCenter, ', ', colorEnd].join(''); }
	else			{ colorValue = [startPos, ', ', colorStart, ', ', colorEnd].join(''); }
	
	if($.browser.mozilla) {
		return ['-moz-linear-gradient(', colorValue, ')'].join('');
	} else if($.browser.msie) {
		return ['-ms-linear-gradient(', colorValue, ')'].join('');
	} else if($.browser.opera) {
		return ['-o-linear-gradient(', colorValue, ')'].join('');
	} else {
		return ['-webkit-linear-gradient(', colorValue, ')'].join('');
	}
};

/** milli-sec 형태로 부터 시간.분 형태의 시간을 구한다.
  * PM 12.10, AM 10.11, AM 01.10, 12.10 PM, 10.11 AM 형태(soui 의 언어 설정에 따라 PM, AM 의 위치 변화됨)
  */
$.SOGetTime1 = function(value) {
	var tm = new Date(value);
	
	var h = parseInt(tm.getHours(), 10);
	var m = parseInt(tm.getMinutes(), 10);
	var ap = SO.UI.RES[SO.UI.ENV.LANG].am;
	
	if(h > 12) { ap = SO.UI.RES[SO.UI.ENV.LANG].pm;  h %= 12; }
	if(h < 10) { h = '0'+h; }
	if(m < 10) { m = '0'+m; }
	
	if(SO.UI.ENV.LANG == 'ko') { return [ap, ' ', h, '.', m].join(''); }
	return [h, '.', m, ' ', ap].join('');
};

/** milli-sec 형태로 부터 년, 월, 일, 요일 형태의 시간을 구한다.
  * 언어가 'ko', 'en' 에 따라서 리턴되는 값이 틀림.
  */
$.SOGetTime2 = function(value) {
	var tm = new Date(value);
	
	var yyyy	= tm.getFullYear();
	var mm		= tm.getMonth()+1;
	var dd		= tm.getDate();
	var week	= tm.getDay();
	
	if(SO.UI.ENV.LANG == 'ko') {
		return [yyyy, SO.UI.RES[SO.UI.ENV.LANG].year, ' ', 
			mm, SO.UI.RES[SO.UI.ENV.LANG].month, ' ',
			dd, SO.UI.RES[SO.UI.ENV.LANG].day, ' ',
			SO.UI.RES[SO.UI.ENV.LANG].week[week]].join('');
	} else {
		return [SO.UI.RES[SO.UI.ENV.LANG].week[week], ' ',
			SO.UI.RES[SO.UI.ENV.LANG].months[mm-1], ' ',
			dd, ', ', yyyy].join('');
	}
};

/** rgb( r, g, b) 스트링에서 r, g, b 값을 구해 array 로 만든다
  * [r, g, b] array 가 리턴된다.	
  */
$.SOParseColorValue = function(value) {
	if(!value) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] not exist color value');
		}
		return null;
	}
	
	var color = value;
	var pos1=0, pos2=0;
	var r, g, b;
	
	// r 을 구한다.
	if((pos1=color.indexOf('(')) < 0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] can\'t seek R color value');
		}
		return null;
	}
	if((pos2=color.indexOf(',')) < 0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] can\'t seek R color value');
		}
		return null;
	}
		
	r = parseInt(color.substring(pos1+1, pos2), 10);
	color = color.substring(pos2);
		
	// g 를 구한다.
	if(!color || color.length<=0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] can\'t seek G color value');
		}
		return null;
	}
	if((pos1=color.indexOf(',', 1)) < 0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] can\'t seek G color value');
		}
		return null;
	}
		
	g = parseInt(color.substring(1, pos1), 10);
	color = color.substring(pos1);
		
	// b 를 구한다.
	if(!color || color.length<=0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] can\'t seek B color value');
		}
		return null;
	}
		
	pos1 = color.indexOf(',', 1);
	pos2 = color.indexOf(')', 1);
		
	if(pos1<0 && pos2<0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.warn('[__SOParseColorValue] can\'t seek B color value');
		}
		return null;
	}
		
	if(pos2>0) { b = parseInt(color.substring(1, pos2), 10); }
	if(pos1>0) { b = parseInt(color.substring(1, pos1), 10); }
	
	return [r, g, b];
};

/** 보색을 구한다. value 는 rgb(r, g, b) 형식으로 들어오는 값임. javascript 에서 읽은 css color style 값. */
$.SOGetComplementColor = function(value) {
	var color = $.SOParseColorValue(value)
	
	if(color) {
		return ['rgb(', 255-color[0], ',', 255-color[1], ',', 255-color[2], ')'].join('');
	}
	return 'black';
};

/** RGB color value 에서 HSV color value 로 값을 변경 한다
  * h : hue(색상), s: 채도(saturation), v: 명도(brightness, values)
  * [h, s, v] array 가 return 된다
  * h : 360-degree
  * red being 0, yellow 60, green 120, cyan 180, blue 240, and magenta 300
  * The S and V range from 0 to 1
  */
$.SORgbToHsv = function(value) {
	var color = $.SOParseColorValue(value)
	
	if(!color) { return null; }

	color[0] /= 255;
	color[1] /= 255;
	color[2] /= 255;
	
	var minRGB = Math.min(color[0], color[1], color[2]);
	var maxRGB = Math.max(color[0], color[1], color[2]);
	
	var computedH = 0;
	var computedS = 0;
	var computedV = 0;

	// Black-gray-white
	if(minRGB==maxRGB) {
		computedV = minRGB;
		return [0, 0, computedV];
	}

	// Colors other than black-gray-white:
	var d = (color[0]==minRGB) ? color[1]-color[2] : ((color[2]==minRGB) ? color[0]-color[1] : color[2]-color[0]);
	var h = (color[0]==minRGB) ? 3 : ((color[2]==minRGB) ? 1 : 5);
	
	computedH = 60*(h - d/(maxRGB - minRGB));
	computedS = (maxRGB - minRGB)/maxRGB;
	computedV = maxRGB;
	
	return [computedH, computedS, computedV];
};

$.SOHsvToRgb = function(hsv) {
	var r, g, b;
	var i;
	var f, p, q, t;

	if(hsv[1] == 0) {
		r = g = b = hsv[2];
		return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
	}

	hsv[0] /= 60;
	i  = Math.floor(hsv[0]);
	f = hsv[0] - i;
	p = hsv[2] *  (1 - hsv[1]);
	q = hsv[2] * (1 - hsv[1] * f);
	t = hsv[2] * (1 - hsv[1] * (1 - f));

	switch( i ) {
		case 0:
			r = hsv[2];
			g = t;
			b = p;
			break;
			
		case 1:
			r = q;
			g = hsv[2];
			b = p;
			break;
			
		case 2:
			r = p;
			g = hsv[2];
			b = t;
			break;
			
		case 3:
			r = p;
			g = q;
			b = hsv[2];
			break;
			
		case 4:
			r = t;
			g = p;
			b = hsv[2];
			break;
			
		default:        // case 5:
			r = hsv[2];
			g = p;
			b = q;
			break;
	}
	
	return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
};

/** 테스트용 elapsed-time 시작 */
$.SOStartElapseTime = function() { SO.UI.SYS.ELAPSE_TIME = $.now(); };

/** 테스트용 elapsed-time 출력 */
$.SOShowElapseTime = function() {
	if(SO.UI.SYS.ELAPSE_TIME == 0) {
		if(SO.UI.ENV.DEBUG) {
			if(console) console.log('[Elapse-Time] can\`t calculate elapse time. call \`SOStartElapseTime\` for init');
		}
	}
	if(SO.UI.ENV.DEBUG) {
		if(console) console.log('[Elapse-Time] start=['+SO.UI.SYS.ELAPSE_TIME+'], elapse=['+($.now()-SO.UI.SYS.ELAPSE_TIME)+']');
	}
};

/** 테스트용 elapsed-time 중지 */
$.SOStopElapseTime = function() { SO.UI.SYS.ELAPSE_TIME = 0; }

})(jQuery);