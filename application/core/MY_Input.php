<?php
/**
 * MY_Input Controller
 *
 * CI_Input 을 overloading 하였음.
 * 1. request payload 문제로 기존 post function 에 기능이 추가된 post2 function 을 추가.
 *
 * @author	dhkim94@gmail.com
 */
class MY_Input extends CI_Input {
    function __construct() {
        parent::__construct();
        $this->_request_payload = null;
    }
    
    // request content-type 을 application/x-www-form-urlencoded 으로 주면 form-data 이기 때문에 $_POST 값으로 잡히지만
    // application/x-www-form-urlencoded 값이 아니면 아파치 보안 설정으로 $_POST 에 값이 잡히지 않는 경우가 있다.
    // 때문에 아파치 설정 상관 없이 값을 가져오기 위해서 CI_Input 의 post function 에 기능을 추가 하였다.
    //
    // 그 외의 나머지 동작은 CI_Input 의 post function 과 동일하다.
    function post2($index = NULL, $xss_clean = FALSE) {
	    if(isset($_POST[$index])) {
	    	return $this->post($index, $xss_clean);
	    } else {
	    	if(!$this->_request_payload && ($request_payload=file_get_contents('php://input'))) {
	    		$this->_request_payload = array();
		    	$request_payload = explode('&', $request_payload);
		    	foreach($request_payload as $value) {
		    		$tmp = explode('=', $value);
		    		if(count($tmp) > 1) {
			    		$this->_request_payload[$tmp[0]] = $tmp[1];
			    	}
		    	}
		    }
		    
		    if($index == NULL && !empty($this->_request_payload)) {
			    $post = array();

				// Loop through the full _POST array and return it
				foreach (array_keys($this->_request_payload) as $key) {
					$post[$key] = $this->_fetch_from_array($this->_request_payload, $key, $xss_clean);
				}
				return $post;
		    }
		    
		    return $this->_fetch_from_array($this->_request_payload, $index, $xss_clean);
	    }
    }
}
?>