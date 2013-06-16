<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
class Myutil {
	/**
	 * json 으로 출력 하기 위해 정해진 형태의 포맷으로 만든다.
	 * @param result_code {Number} 결과 코드(이 값에 따라서 내부에서 message 를 생성함)
	 * @param other {Array} 기본값(error, message 외에 추가할 값)
	 * @param controller {string} controller 의 이름. 이것을 바탕으로 언어를 로딩한다.
	 */
	public function result_for_json($result_code=TRUE, $other=FALSE, $controller=FALSE) {
		$CI = &get_instance();
		
		$CI->lang->load('error_message/common');
		
		// controller 파라미터가 valid 하면 controller 에 맞는 메시지를 로딩 한다.
		$error_message_file = FALSE;
		if($controller) {
			$error_message_file = 'error_message/'.$controller;
			$CI->lang->load($error_message_file);
		}
		
		$CI->config->load('error_code/common', TRUE);
		
		if($result_code===TRUE) { $result_code = $CI->config->item('common_success', 'error_code/common'); }
		
		$ret = new stdClass();
		$ret->error = $result_code;
		
		$ret->message = $CI->lang->line($result_code);
		
		// 에러 코드에 맞게 정의 된 메시지가 없다면 기본 실패 메시지로
		if($ret->message == FALSE) {
			$ret->message = $CI->lang->line(
				$CI->config->item('common_fail', 'error_code/common'));
		}
		
		// tid 를 넣는다.
		$tid = $CI->input->get_post2('tid', TRUE);
		if(!$tid) { $tid = ''; }
		
		$ret->tid = $tid;
		
		if($other) {
			foreach($other as $key=>$value) {
				$ret->{$key} = $value;
			}
		}
		
		return $ret;
	}
}
/* End of file Myutil.php */