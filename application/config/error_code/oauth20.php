<?php
/**
 * oauth20 사용시 발생하는 에러 코드
 *
 * @author dhkim94@gmail.com
 */
$config['oauth20_no_api_key']				= 'E030001';		// api_key 값이 없음
$config['oauth20_not_found_api_key']		= 'E030002';		// table 에 api_key 값이 없음
$config['oauth20_not_permitted_api_key']	= 'E030003';		// system 에서만 사용해야 될 api key 로 system 외에서 접근 하였음
$config['oauth20_create_auth_code']			= 'E030004';		// authorization 에서 authorization code 발급 실패(insert 실패)
$config['oauth20_not_supported_api_version']= 'E030005';		// 현재 사용중인 api key 는 더 이상 지원하지 않는 api version 을 사용하고 있음. api key 재발급 필요.
$config['oauth20_no_api_secret']			= 'E030006';		// api_secret 값이 없음
$config['oauth20_no_auth_code']				= 'E030007';		// authorization code 가 없음
$config['oauth20_invalid_data_access_token']= 'E030008';		// access_token 발급시, api_key, api_secret, authorization_code 로 authorization code 를 확인 할 수 없음.
$config['oauth20_dissolved_auth_code']		= 'E030009';		// 이미 폐기된 authorization code 임. 해당 code 로 access_token 발급 하였음.
$config['oauth20_create_access_token']		= 'E030010';		// access_token 발급 실패(oauth20_code 테이블에 update 실패함)
$config['oauth20_no_access_token']			= 'E030011';		// refresh token 시에 access_token 이 없음
$config['oauth20_invalid_data_refresh_token']='E030012';		// refresh token 을 위한 select 데이터 없음. api_key, api_secret, access_token 조합으로 select 하니 없음.
$config['oauth20_expired_access_token']		= 'E030013';		// expire 시간이 지나서 refresh token 을 요청 하였음
$config['oauth20_refresh_access_token']		= 'E030014';		// refresh token 실패(oauth20_code 테이블에 update 실패함)
?>