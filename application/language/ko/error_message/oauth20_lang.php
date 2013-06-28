<?php
//-------------------------------------------------------
// oauth20 Controller 관련 에러 메시지
//-------------------------------------------------------
$lang['E030001'] = 'API Key 가 존재하지 않습니다.';				// api_key 값이 없음
$lang['E030002'] = 'API Key 가 존재하지 않습니다.';				// table 에 api_key 값이 없음
$lang['E030003'] = '사용할 수 없는 api key 입니다';				// system 에서만 사용해야 될 api key 로 system 외에서 접근 하였음
$lang['E030004'] = '인증키 발급을 실패 했습니다';					// authorization 에서 authorization code 발급 후 table update 실패
$lang['E030005'] = '더 이상 지원하지 않는 API Key 입니다.';		// 현재 사용중인 api key 는 더 이상 지원하지 않는 api version 을 사용하고 있음. api key 재발급 필요.
$lang['E030006'] = 'API Secret 가 존재하지 않습니다.';			// api_secret 값이 없음
$lang['E030007'] = 'Authorization Code 가 존재하지 않습니다.';	// authorization code 가 없음
$lang['E030008'] = '잘못된 요청 입니다.';						// access_token 발급시, api_key, api_secret, authorization_code 로 authorization code 를 확인 할 수 없음.
$lang['E030009'] = '폐기된 Authorization Code 입니다.';			// 이미 폐기된 authorization code 임. 해당 code 로 access_token 발급 하였음.
$lang['E030010'] = 'access token 발급을 실패 했습니다.';			// access_token 발급 실패(oauth20_code 테이블에 update 실패함)
$lang['E030011'] = 'access token 이 없습니다.';				// refresh token 시에 access_token 이 없음
$lang['E030012'] = '잘못된 요청 입니다.';						// refresh token 시을 위한 select 데이터 없음. api_key, api_secret, access_token 조합으로 select 하니 없음.
$lang['E030013'] = '이미 폐기된 access token 입니다';			// expire 시간이 지나서 refresh token 을 요청 하였음
?>