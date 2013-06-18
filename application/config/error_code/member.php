<?php
/**
 * Member controller 에서 발생하는 에러 코드
 *
 * @author dhkim94@gmail.com
 */
 
// 가입시 발생하는 에러 코드
$config['member_join_invalid_user_id']			= 'E010001';		// user_id invalid
$config['member_join_invalid_email_address']	= 'E010002';		// email_address invalid
$config['member_join_invalid_password']			= 'E010003';		// password invalid
$config['member_join_invalid_user_name']		= 'E010004';		// user_name invalid
$config['member_join_invalid_nick_name']		= 'E010005';		// nick_name invalid
$config['member_join_duplicated_user_id']		= 'E010006';		// 이미 존재하는 user_id 로 가입 시도
$config['member_join_duplicated_nick_name']		= 'E010007';		// 이미 존재하는 nick_name 으로 가입 시도
$config['member_join_not_supported_social']		= 'E010008';		// 가입을 지원하지 않는 social 임.
$config['member_join_invalid_image_mark']		= 'E010009';		// 가입시 받은 image_mark 파일이 존재하지 않음(여러개 중 하나만 없어도 발생)
$config['member_join_invalid_account_question']	= 'E010010';		// 계정 찾기 질문이 invalid 함
$config['member_join_invalid_account_answer']	= 'E010011';		// 계정 찾기 질문이 있는데 답변이 없음
?>