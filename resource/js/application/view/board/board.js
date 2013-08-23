(function($){$(document).ready(function(){

var $board_id = $('#window-hidden-board-id'),
	$category_info = $('#window-hidden-category-info');
	
if($board_id && $board_id.length>0) {
	$board_id = $.trim($board_id.text());
} else {
	$board_id = false;
}

if($category_info && $category_info.length>0) {
	$category_info = eval('('+$.trim($category_info.text())+')');
} else {
	$category_info = false;
}
$('#window-buffer').empty();

gboard.content.main.action.init($.cookie('_u_lang_'), $board_id, $category_info);

});})(jQuery);