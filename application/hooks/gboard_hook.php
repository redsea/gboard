<?php
/**
 * gamma board hooking class
 *
 * request 시 가장 먼저 항싱 실행 해야 할 것들 정의
 *
 * @author	dhkim94@gmail.com
 */
class Gboard_hook {//} extends CI_Hooks {
	/**
	 * 표시 해 주어야 할 언어를 확인한다.
	 * 1. 첫번째 cookie 에서 _u_lang_ 값이 있는지 체크
	 * 2. 두번째 브라우저의 언어값에서 서비스 지원하는 언어가 있는지 체크
	 * 3. 1,2 모두 체크 실패 하면 서비스 설정 기본 언어로 설정
	 */
	public function detectLanguage() {
		$loader = &load_class('Loader', 'core');
		$config = &load_class('Config', 'core');
		$input =  &load_class('Input', 'core');
		
		$config->load('my_conf/common', TRUE);
		
		$support_language = $config->item('support_language', 'my_conf/common');
		
		$lang_cookie = array(
				'name' => '_u_lang_',
				'expire' => $config->item('cookie_expire_u_lang', 'my_conf/common'),
				'domain' => $config->item('cookie_domain', 'my_conf/common'),
				'path' => $config->item('cookie_path', 'my_conf/common'),
				'secure' => $config->item('cookie_secure', 'my_conf/common')
			);
		
		$loader->helper('cookie');
		
		// 1. cookie 에서 언어 설정을 가져온다.
		$language = strtolower($input->cookie('_u_lang_', TRUE));
		if(in_array($language, $support_language)) {
			$loader->vars(array('h_lang'=>$language));
			$lang_cookie['value'] = $language;
			$input->set_cookie($lang_cookie);
			return;
		}
		
		// 2. browser 의 언어 설정을 가져온다.
		$language = $input->server('HTTP_ACCEPT_LANGUAGE');
		$language = explode(';', $language);
		foreach($language as $value) {
			$sub_lang = explode('-', $value);
			$element = strtolower($sub_lang[0]);
			if(in_array($element, $support_language)) {
				$loader->vars(array('h_lang'=>$element));
				$lang_cookie['value'] = $element;
				$input->set_cookie($lang_cookie);
				return;
			}
		}
		
		// 3. cookie, browser 에서 언어를 판단 할 수 없으면 기본 언어로 한다.
		if(!$language) {
			// 디폴트 랭퀴지 세팅으로 그냥 넘어 간다.
			$language = strtolower($config->item('language'));
			$loader->vars(array('h_lang'=>$language));
			$lang_cookie['value'] = $language;
			$input->set_cookie($lang_cookie);
			return;
		}
	}
	
	/**
	 * 공통으로 쓰이는 환경을 로딩 한다. 다음의 역할을 한다.
	 *
	 * 1. session library 를 load
	 * 2. error_code/common (공통 에러 코드 정의) 환경 파일을 로드
	 * 3. my_conf/common (공통 설정) 환경 파일을 로드
	 * 4. log key 설정
	 */
	public function preLoad() {
		$CI = &get_instance();
		
		//$CI->load->library('session');
				
		$user_id = $CI->session->userdata('user_id');
		if($user_id) { set_log_key($user_id); }
	}
}
?>