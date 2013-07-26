<?php
/**
 * oauth20 에 대한 설정 파일이다.
 *
 * @author dhkim94@gmail.com
 */
$config['system_api_key'] = 'e44f11e891d4c8afdd6ffbf7a0c03bd3';		// 기본 웹서비스(local 웹페이지)에서 사용할 api key 
$config['system_api_secret'] = '9c10a58811c6932fd388c0959b0ec112';	// 기본 웹서비스(local 웹페이지)에서 사용할 api secret

$config['system_api_key_using_ip'] = ['127.0.0.1'];				// root 용 api key 를 사용할 수 있는 접속자 ip

$config['system_api_version'] = '000.000.001';					// 현재 서비스 하는 api version
$config['supported_api_version'] = ['000.000.001'];				// 지원 할 수 있는 api version. array 이기 때문에 여러개 들어갈 수 있다.

//$config['authorization_code_reuse_sec'] = 60*2;				// authorization code 발급 이후 2분이 지났지만, 사용하지 않으면 다른 사람이 재활용 하게 한다.
$config['access_token_expire_sec'] = 3600;						// access_token expire 시간은 발급 이후 1시간(60*60=3600) 임
?>