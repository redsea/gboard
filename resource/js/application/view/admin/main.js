(function($){$(document).ready(function(){

var $profile_image = $('#window-hidden-profile-image'),
	$session_expire_tm = $('#window-hidden-session-expire-time'),
	$home_url = $('#window-hidden-home-url'),
	$service_list = $('#window-hidden-service-list');
	
if($profile_image && $profile_image.length>0) {
	$profile_image = eval('('+$.trim($profile_image.text())+')');
} else {
	$profile_image = false;
}

if($session_expire_tm && $session_expire_tm.length>0) {
	$session_expire_tm = $.trim($session_expire_tm.text());
} else {
	$session_expire_tm = false;
}

if($service_list && $service_list.length>0) {
	$service_list = eval('('+$.trim($service_list.text())+')');
} else {
	$service_list = false;
}
$('#window-buffer').empty();

// ajax 호출할 domain name 설정
gboard.ajax.init('gboard.org');
// 페이지 시작
gboard.admin.main.action.init($.cookie('_u_lang_'), 
	$session_expire_tm, $profile_image, $service_list);

});})(jQuery);