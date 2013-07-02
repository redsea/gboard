(function($){$(document).ready(function(){

$('#quick-home').click(function(evt){
	console.log('> move to home');
});


// TODO quick bar 의 center 영역 overflow detect 해서 scroll bar 넣어 주는 것 진행 해야 한다.

$('#quick-bar-part-center').bind('overflow', function(evt){
	console.log('overflow');
});

gboard.admin.main.model.init($.cookie('_u_lang_'), 
	$('body').attr('x-data-expire'), $('body').attr('x-data-home-url'));

});})(jQuery);