(function($){$(document).ready(function(){

// 로그인 버튼 처리. 눌렀을 때의 반응
$('#login-btn').button().addClass('login_btn').click(function(evt){
	evt.stopPropagation();
	// login 시도
	gboard.admin.login.action.login($('#user_id').val(), $('#password').val());
});

$('#user_id').keyup(function(evt){
	evt.stopPropagation();
	//if(evt.which == 13) { $('#login-btn').trigger('click'); }
	if(evt.which == 13) { $('#password').focus(); }
});
$('#password').keyup(function(evt){
	evt.stopPropagation();
	if(evt.which == 13) { $('#login-btn').trigger('click'); }
});

gboard.admin.login.model.init($.cookie('_u_lang_'));

});})(jQuery);