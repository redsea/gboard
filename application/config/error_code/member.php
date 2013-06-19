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
$config['member_join_invalid_homepage']			= 'E010012';		// homepage invalid. 길이가 너무 김
$config['member_join_invalid_blog']				= 'E010013';		// blog invalid. 길이가 너무 김.
$config['member_join_invalid_birthday']			= 'E010014';		// birthday invalid. 길이가 8자리가 아니거나 올바른 데이터 형태가 아님
$config['member_join_invalid_country']			= 'E010015';		// country code 가 틀림
$config['member_join_invalid_social_type']		= 'E010016';		// 가입에 대해 지원하지 않는 social_type
$config['member_join_invalid_social_id']		= 'E010017';		// social_id 값이 없음
$config['member_join_insert_member']			= 'E010018';		// member table 에 insert 실패
$config['member_join_insert_member_extra']		= 'E010019';		// member_extra table 에 insert 실패
$config['member_join_update_list_order']		= 'E010020';		// membe table 의 list_order update 실패
$config['member_join_select_default_group']		= 'E010021';		// member_group table 에서 가입시 설정할 기본 그룹 정보 가져오기 실패
$config['member_join_select_default_site']		= 'E010022';		// sites table 에서 가입시 설정할 기본 사이트 정보 가져오기 실패
$config['member_join_insert_member_group_member']='E010023';		// member_group_member table 에 insert 실패(기본 그룹 가입 실패)
$config['member_join_update_file_owner']		= 'E010024';		// files table 의 member_srl update 실패
?>