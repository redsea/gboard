<?php
/**
 * 공통 에러 코드
 *
 * @author dhkim94@gmail.com
 */
$config['common_success']				= 'S000001';		// 성공
$config['common_fail']					= 'E000001';		// 실패
$config['common_no_auth']				= 'E000002';		// access_token 체크 실패(header 에 access_token 이 없음)
$config['common_invalid_access_token']	= 'E000003';		// access_token 체크 실패(table 에 access_token 이 없거나 이미 폐기 되었음)
$config['common_au_expire_access_token_']='E000004';		// access_token 만료 기간 자동 연장 실패
?>