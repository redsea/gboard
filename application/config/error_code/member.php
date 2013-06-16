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
?>