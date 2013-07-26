<?php
/**
 * 공통 설정 사항
 *
 * @author dhkim94@gmail.com
 */
$config['yes'] = 'Y';	// yes, ok 값
$config['no']  = 'N';	// no 값

$config['table_prefix'] = 'gbd_';

$config['support_language'] = ['ko', 'en'];					// 서비스에서 지원하는 언어 코드

$config['cookie_domain'] = 'gboard.org';		// 쿠키 도메인
$config['cookie_path'] = '/';					// 쿠키 패스
$config['cookie_secure'] = FALSE;				// 쿠키 보안(https 일때 TRUE 가능)
$config['cookie_expire_u_lang'] = 31536000;		// 쿠키 유지 시간은 1년

$config['x-authorization'] = 'x-authorization';	// access_token 이 들어있는 header 값
?>