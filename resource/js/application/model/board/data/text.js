/**
 * 다국어 지원을 위한 텍스트 데이터
 */

var gboard;
if(!gboard) { gboard = {}; }
if(!gboard.board) { gboard.board = {}; }
if(!gboard.board.text) { gboard.board.text={
	
// 한글 텍스트
ko: {
	help_board:			'도큐멘트 리스트를 보여준다. 제목, 본문, 글쓴이로 통합 검색이 가능하다.',
	
	number:				'번호',
	title:				'제목',
	author:				'글쓴이', 
	cdate:				'등록일',
	read_count:			'조회수',
	like:				'좋아요'
},

// 영어 텍스트
en: {
	help_board:			'도큐멘트 리스트를 보여준다.',
	
	number:				'번호',
	title:				'제목',
	author:				'글쓴이', 
	cdate:				'등록일',
	read_count:			'조회수',
	like:				'좋아요'
}
	
};}

