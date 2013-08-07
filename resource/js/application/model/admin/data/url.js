/**
 * URL 데이터
 */

var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.admin) { gboard.admin = {}; }
if(!gboard.admin.url) { gboard.admin.url={

admin_index:		'http://gboard.org/admin',					// admin web page
application_list:	'http://gboard.org/oauth/application_list',	// oauth 사용을 위해 등록한 application 리스트
text_list:			'http://gboard.org/lang/text_list',			// 다국어 텍스트 리스트
text_change: 		'http://gboard.org/lang/text_change',		// 다국어 텍스트 변경
file_list:			'http://gboard.org/file/file_list',			// 파일 리스트
file_detail:		'http://gboard.org/file/file_detail',		// 파일 상세 정보
member_list:		'http://gboard.org/member/member_list',		// 유저 리스트
member_detail:		'http://gboard.org/member/member_detail',	// 유저 상세 정보
	
};}

