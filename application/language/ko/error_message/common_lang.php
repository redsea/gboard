<?php
//-------------------------------------------------------
// 기본 에러 메시지
//-------------------------------------------------------
$lang['S000001'] = '성공';
$lang['E000001'] = '실패';
$lang['E000002'] = '인증을 하지 않았습니다';		// access_token 체크 실패(access_token 이 없음)
$lang['E000003'] = '인증키가 만료 되었습니다';		// access_token 체크 실패(table 에 access_token 이 없거나 이미 폐기 되었음)
$lang['E000004'] = '인증키 연장을 실패 했습니다';	// access_token 만료 기간 자동 연장 실패
$lang['E000005'] = '로그인을 하지 않았습니다';		// session 에 login 정보 없음. login 하지 않았거나, 세션 만료 되었음
$lang['E000006'] = '사용 권한이 없습니다';		// 사용 권한 없음
?>