(function($){$(document).ready(function(){

// soui 사용 prepare 완료(리소스-이미지등- 로딩)
$(document).bind('soevt-ui-ready', function(){
	//$.SOPreloadImage(s1.res.img, 's1-image-ready');
	$.SOPreloadImage(null, 's1-image-ready');
});

// 이미지 로딩이 끝나면 화면을 보여 준다.
$(document).bind('s1-image-ready', function(){
	console.log('start');

	var $a = $('<div>').css({width:'100%', height:100, backgroundColor:'red'});
	
	$('body').append(	$a);
	
	$a.SOWToolbar();
});

//$.SOPrepareUI(s1.config.lang, null, s1.config.debug);
$.SOPrepareUI('ko', null, true);

});})(jQuery);