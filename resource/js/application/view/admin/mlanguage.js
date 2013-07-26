(function($){$(document).ready(function(){

var $support_language = $('#window-hidden-language');
	
if($support_language && $support_language.length>0) {
	$support_language = eval('('+$.trim($support_language.text())+')');
} else {
	$support_language = false;
}
$('#window-buffer').empty();

// ajax 호출할 domain name 설정
//gboard.ajax.init('gboard.org');
// 페이지 시작
gboard.content.main.action.init($.cookie('_u_lang_'), $support_language);

});})(jQuery);