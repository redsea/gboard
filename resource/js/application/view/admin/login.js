(function($){$(document).ready(function(){

// 로그인 버튼 처리
$('#login-btn').button().addClass('login_btn').click(function(evt){
	evt.stopPropagation();
	
	// login 시도
	gboard.admin.login.action.login($('#user_id').val(), $('#password').val());
});
	
gboard.admin.login.model.init($.cookie('_u_lang_'));




});})(jQuery);