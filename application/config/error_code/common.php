<?php
/**
 * 공통 에러 코드
 *
 * @author dhkim94@gmail.com
 */
$config['common_success']				= 'S000001';		// 성공
$config['common_fail']					= 'E000001';		// 실패
$config['common_no_auth']				= 'E000002';		// access_token 체크 실패(access_token 이 없음)
$config['common_invalid_access_token']	= 'E000003';		// access_token 체크 실패(table 에 access_token 이 없거나 이미 폐기 되었음)
$config['common_au_expire_access_token']= 'E000004';		// access_token 만료 기간 자동 연장 실패
$config['common_not_logined']			= 'E000005';		// session 에 login 정보 없음. login 하지 않았거나, 세션 만료 되었음
$config['common_use_only_root']			= 'E000006';		// 사용 권한 없음
$config['common_no_service_id']			= 'E000007';		// service id 파라미터가 없음(gbd_service 의 service_id 임)
$config['common_invalid_service_id'] 	= 'E000008';		// service id 에 해당하는 menu 값들이 없음(menu tree 가 없음)
$config['common_no_tree_element']		= 'E000009';		// 서비스 tree 에 존재하지 않는 tree parent element srl
?>