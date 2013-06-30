<?php
//-------------------------------------------------------
// Member Controller 관련 에러 메시지
//-------------------------------------------------------
// 회원 가입시 발생하는 에러 메시지
$lang['E010001'] = '아이디가 올바르지 않습니다';							// user_id invalid
$lang['E010002'] = 'Email 주소가 올바르지 않습니다';						// email_address invalid
$lang['E010003'] = '패스워드가 올바르지 않습니다';							// password invalid
$lang['E010004'] = '이름이 올바르지 않습니다';								// user_name invalid
$lang['E010005'] = '별명이 올바르지 않습니다';								// nick_name invalid
$lang['E010006'] = '이미 존재하는 아이디 입니다';							// 이미 존재하는 user_id 로 가입 시도
$lang['E010007'] = '이미 존재하는 별명 입니다';							// 이미 존재하는 nick_name 으로 가입 시도
$lang['E010008'] = '지원 하지 않는 소셜 입니다.';							// 지원 하지 않는 소셜 타입으로 가입 시도
$lang['E010009'] = '존재하지 않는 이미지 입니다';							// 가입시 image_mark 로 사용할 file_srl 이 존재하지 않을때(여러개 중 하나라도 존재하지 않아도 발생)
$lang['E010010'] = '아이디 찾기 질문 타입이 올바르지 않습니다';				// 가입시 아이디 찾기 질문이 올바른 타입이 아님
$lang['E010011'] = '아이디 찾기 질문에 답변이 없습니다.';					// 가입시 아이디 찾기 질문이 있는데 답변이 없음
$lang['E010012'] = '홈페이지 주소의 길이가 너무 깁니다(최대 256자)';			// 홈페이지 주소의 길이가 너무 길다
$lang['E010013'] = '블로그 주소의 길이가 너무 깁니다(최대 256자)';			// 블로그 주소의 길이가 너무 길다
$lang['E010014'] = '생일이 올바르지 않습니다. \'년월일(yyyymmdd)\' 로 8자리 입니다';	// 생일의 길이가 틀렸거나, 올바르지 않은 데이터
$lang['E010015'] = '국가 코드가 올바르지 않습니다';							// 국가 코드가 틀림
$lang['E010016'] = '지원 하지 않는 소셜 입니다';							// 지원하지 않는 소셜 타입
$lang['E010017'] = '소셜 아이디가 올바르지 않습니다';						// 소셜 아이디가 없거나 길이가 64 보다 큼
$lang['E010018'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// member table 에 insert 실패
$lang['E010019'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// member_extra table 에 insert 실패
$lang['E010020'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// member table 의 list_order update 실패
$lang['E010021'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// member_group table 에서 가입시 설정한 기본 그룹 정보 가져오기 실패
$lang['E010022'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// sites table 에서 가입시 설정할 기본 사이트 정보 가져오기 실패
$lang['E010023'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// member_group_member table 에 insert 실패(기본 그룹 가입 실패)
$lang['E010024'] = '서비스 내부 점검으로 회원 가입을 실패 했습니다';			// files table 의 member_srl update 실패
$lang['E010025'] = '국가 전화번호가 올바르지 않습니다';						// contry call code 가 올바르지 않음

// 로그인 시 발생하는 에러 메시지
$lang['E011001'] = '가입되지 않은 아이디 입니다';							// 가입되지 않은 id 로 로그인 시도
$lang['E011002'] = '패스워드가 틀렸습니다';								// member 의 password 가 틀림
$lang['E011003'] = '그룹 정보를 구할 수 없습니다';							// member 의 group, site 정보를 구할 수 없음
$lang['E011004'] = '사용 정지된 아이디 입니다';							// block 된 user id
$lang['E011005'] = '사용 제한 시간이 지난 아이디 입니다';					// 사용 가능 제한 시간이 지난 user_id


// 계정 찾기 질문
$lang['FCQ1'] = '다른 이메일 주소는?';
$lang['FCQ2'] = '나의 보물 1호는?';
$lang['FCQ3'] = '나의 출신 초등학교는?';
$lang['FCQ4'] = '나의 출신 고향은?';
$lang['FCQ5'] = '나의 이샹형은?';
$lang['FCQ6'] = '어머니 성함은?';
$lang['FCQ7'] = '아버지 성함은?';
$lang['FCQ8'] = '가장 좋아하는 색깔은?';
$lang['FCQ9'] = '가장 좋아하는 음식은?';
?>