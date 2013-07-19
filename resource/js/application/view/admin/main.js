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

if($home_url) {
	$home_url = $.trim($home_url.text());
} else {
	$home_url = false;
}

if($service_list && $service_list.length>0) {
	$service_list = eval('('+$.trim($service_list.text())+')');
} else {
	$service_list = false;
}
$('#window-buffer').empty();

gboard.admin.main.action.init($.cookie('_u_lang_'), 
	$session_expire_tm, $home_url, $profile_image, $service_list);

});})(jQuery);