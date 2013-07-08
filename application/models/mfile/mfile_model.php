<?php
/**
 * MFile model
 *
 * @author dhkim94@gmail.com
 */
class Mfile_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->load->helper('date');
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('my_conf/mfile', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/mfile', TRUE);
		
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
		
		$this->master_db = FALSE;
		//$this->slave_db = FALSE;
	}
	
	/**
	 * amazon s3 에 파일을 업로드 한다.
	 * 이미지 파일을 업로드시 thumbnail 이 존재하면 thumbnail 도 같이 업로드 한다.
	 * 즉, 2번의 연동을 하며 한번이라도 실패하면 실패 처리 한다.
	 *
	 * @param ret {&array} 호출한 함수에서 사용할 reference array
	 * @param file_info {array} local 에 올린 파일 정보
	 * @param thumbnail_info {array} thumbnail 이 존재할때 thumbnail 정보
	 * @param member_srl {number} 파일을 올린 member_srl
	 * @param file_type {string} mfile.config 에 설정 되어 있는 업로드 되는 파일이 사용되는 종류
	 * @return {string} return error code
	 */
	private function upload_to_amazon_s3(&$ret, $file_info, $member_srl,
			$file_config, $thumbnail_info=FALSE) {
		require_once('application/third_party/aws/sdk.class.php');
		
		$s3 = new AmazonS3();
		
		$region = $file_config['amazon_s3_region'];
		switch($region) {
			case 'us_w1':
			case 'california':
				$region = AmazonS3::REGION_US_W1;
				break;
				
			case 'us_w2':
			case 'oregon':
				$region = AmazonS3::REGION_US_W2;
				break;
				
			case 'eu_w1':
			case 'ireland':
				$region = AmazonS3::REGION_EU_W1;
				break;
				
			case 'apac_se1':
			case 'singapore':
				$region = AmazonS3::REGION_APAC_SE1;
				break;
				
			case 'apac_se2':
			case 'sydney':
				$region = AmazonS3::REGION_APAC_SE2;
				break;
				
			case 'apac_ne1':
			case 'tokyo':
				$region = AmazonS3::REGION_APAC_NE1;
				break;
				
			case 'sa_e1':
			case 'sao_paulo':
				$region = AmazonS3::REGION_SA_E1;
				break;
				
			case 'us_gov1':
				$region = AmazonS3::REGION_US_GOV1;
				break;
				
			case 'us_gov1_fips':
				$region = AmazonS3::REGION_US_GOV1_FIPS;
				break;
		
			case 'us_e1':
			case 'virginia':
			case 'us_standard':
			default:
				$region = AmazonS3::REGION_US_E1;
				break;
		}
		
		// get bucket region(미국 아니면 다 해줘야 하는듯. 그냥 다 해주자)
		// aws/services/s3.class.php 에서 region DEFAULT_URL 을 원하는 값으로 바꾸면 될 듯 하다.
		$response = $s3->set_region($region);
		if(!$response->parse_the_response) {
			$error_code = $this->config->item('mfile_invalid_amazons3_region', 'error_code/mfile');
			log_message('error', "upload_to_amazon_s3 E[$error_code] failed change amaozon s3 region");
			return $error_code;
		}
		log_message('info', 'change amaozon s3 region');
		
		$s3_path = $file_config['type_name'].DIRECTORY_SEPARATOR.md5($member_srl).
				DIRECTORY_SEPARATOR.$file_info['file_name'];
		try {
			$response = $s3->create_mpu_object($file_config['amazon_s3_bucket'], 
					$s3_path, array(
						'fileUpload'  => $file_info['full_path'],
						//'fileUpload'  => './files/attach/'.$file_info['file_name'],
						'contentType' => $file_info['file_type'],
						'acl'         => AmazonS3::ACL_PUBLIC,
						'storage'     => AmazonS3::STORAGE_STANDARD
					)
				);
		} catch(Exception $e) {
			$error_code = $this->config->item('mfile_upload_to_amazons3', 'error_code/mfile');
			log_message('error', "upload_to_amazon_s3 E[$error_code] exception upload(".$file_config['type_name'].
					") to amazon s3. e[".json_encode($e)."]");
			return $error_code;
		}
		
		if(!$response->isOK()) {
			$error_code = $this->config->item('mfile_upload_to_amazons3', 'error_code/mfile');
			log_message('error', "upload_to_amazon_s3 E[$error_code] failed upload(".$file_config['type_name'].
					") to amazon s3. code[".
					$response->body->Code."], message[".$response->body->Message."]");
			return $error_code;
		}
		log_message('info', "upload(".$file_config['type_name'].") to amazon s3. url[".
				$response->header['_info']['url']."]");
		
		$ret['orig_url'] = $response->header['_info']['url'];
		
		// thumbnail 이 존재하면 thumbnail 도 같이 올린다.
		if($thumbnail_info) {
			$s3_path = $file_config['type_name'].DIRECTORY_SEPARATOR.md5($member_srl).
					DIRECTORY_SEPARATOR.$thumbnail_info['file_name'];
			try {
				$response = $s3->create_mpu_object($file_config['amazon_s3_bucket'], 
						$s3_path, array(
							'fileUpload'  => $thumbnail_info['full_path'],
							//'fileUpload'  => './files/attach/'.$file_info['file_name'],
							'contentType' => $file_info['file_type'],
							'acl'         => AmazonS3::ACL_PUBLIC,
							'storage'     => AmazonS3::STORAGE_STANDARD
						)
					);
			} catch(Exception $e) {
				$error_code = $this->config->item('mfile_upload_to_amazons3', 'error_code/mfile');
				log_message('error', "upload_to_amazon_s3 E[$error_code] exception upload(".$file_config['type_name'].
						" thumbnail) to amazon s3. e[".json_encode($e)."]");
				return $error_code;
			}
			
			if(!$response->isOK()) {
				$error_code = $this->config->item('mfile_upload_to_amazons3', 'error_code/mfile');
				log_message('error', "upload_to_amazon_s3 E[$error_code] failed upload(".$file_config['type_name'].
						" thumbnail) to amazon s3. code[".
						$response->body->Code."], message[".$response->body->Message."]");
				return $error_code;
			}
			log_message('info', "upload(".$file_config['type_name']." thumbnail) to amazon s3. url[".
					$response->header['_info']['url']."]");
			
			$ret['thumbnail_url'] = $response->header['_info']['url'];
		}
		
		return $this->success_code;
	}
	
	/**
	 * file upload 시 파일이 어떤 서비스(profile, attach 등등)에서 사용할 수 있는지 체크 한다.
	 * 
	 * @param ret {&array} 호출한 함수에서 사용할 reference array
	 * @param member_srl {string} member 의 srl
	 * @param file_type {string} upload 된 파일이 사용되는 곳(profile, attach 등등)
	 * @return {string} return error code
	 */
	private function validFileType(&$ret, $member_srl=FALSE, $file_type=FALSE) {
		if(!$file_type) { $file_type = $this->input->post2('type', TRUE); }
		if(!$file_type || !in_array($file_type, $this->config->item('support_data_type', 'my_conf/mfile'))) {
			$error_code = $this->config->item('mfile_not_support_data_type', 'error_code/mfile');
			log_message('error', "validFileType E[$error_code] not support file type[$file_type]");
			return $error_code;
		}
	
		if(!$member_srl) { $member_srl = $this->config->system_member_srl(); }
		
		$file_config = $this->config->item($file_type, 'my_conf/mfile');
		if(!$file_config) {
			$error_code = $this->config->item('mfile_not_config_data_type', 'error_code/mfile');
			log_message('error', "validFileType E[$error_code] not set file type config[$file_type]");
			return $error_code;
		}
		
		$ret['member_srl'] = $member_srl;
		$ret['file_config'] = $file_config;
		
		return $this->success_code;
	}
	
	
	/** 
	 * upload 된 파일을 저장한다.
	 *
	 * @param ret {&array} 호출하는 함수로 넘겨줄 값
	 * @param member_srl {string} 파일을 업로드 시킨 member srl
	 *							  프로필 이미지는 member_srl 이 없는데, 이런 경우는 nobody member_srl 로 설정된다.
	 * @param file_type {string} mfile.config 에 설정 되어 있는 업로드 되는 파일이 사용되는 종류
	 *							 support_data_type 값중 하나
	 * @return {string} return error code
	 */
	public function save_file_in_local(&$ret, $member_srl=FALSE, $file_type=FALSE) {
		$ret_valid = array();
		$result_code = $this->validFileType($ret_valid, $member_srl, $file_type);
		if($result_code != $this->success_code) { return $result_code; }
		$file_config = $ret_valid['file_config'];
		
		$config['upload_path'] = $file_config['file_directory'];
		if($config['upload_path']) {
			if(substr($config['upload_path'], strlen($config['upload_path'])-1) != DIRECTORY_SEPARATOR) {
				$config['upload_path'] = $config['upload_path'] . DIRECTORY_SEPARATOR;
			}
		} else {
			$error_code = $this->config->item('mfile_file_upload_no_upload_config', 'error_code/mfile');
			log_message('error', "save_file_in_local E[$error_code] non-config local_file_directory in my_conf/mfile");
			return $error_code;
		}
		
		// 1. profile 디렉토리 생성
		$config['upload_path'] = $config['upload_path'];
		if(!is_dir($config['upload_path'])) {
			if(!mkdir($config['upload_path'], 0777)) {
				$error_code = $this->config->item('mfile_file_upload_mkdir', 'error_code/mfile');
				log_message('error', "save_file_in_local E[$error_code] can't create directory[".$config['upload_path']."]");
				return $error_code;
			}
		}
		
		// 2. member directory 생성
		$config['upload_path'] = $config['upload_path'].md5($ret_valid['member_srl']);
		if(!is_dir($config['upload_path'])) {
			if(!mkdir($config['upload_path'], 0777)) {
				$error_code = $this->config->item('mfile_file_upload_mkdir', 'error_code/mfile');
				log_message('error', "save_file_in_local E[$error_code] can't create directory[".$config['upload_path']."]");
				return $error_code;
			}
		}
		
		$config['allowed_types'] = $file_config['file_type'];
		$config['max_size']	= $file_config['max_size'];
		$config['max_width']  = $file_config['max_width'];
		$config['max_height']  = $file_config['max_height'];
		$config['encrypt_name'] = $file_config['encrypt_name'];
		$config['remove_spaces'] = $file_config['remove_space'];
		
		$this->load->library('upload', $config);
		
		$result = $this->upload->do_upload($file_config['form_name']);
		if($result != $this->success_code) { return $result; }
		
		$file_info = $this->upload->data();
		if(!chmod($file_info['full_path'], 0666)) {
			log_message('warn', 'save_file_in_local can\'t change permission 0646 file['.$file_info['full_path'].']');
		}
		foreach($file_info as $key=>$value) { $ret[$key] = $value; }
		
		return $this->success_code;
	}
	
	/**
	 * thumbnail 파일을 생성한다. thumbnail 을 생성할 원본이 이미지 파일인지 체크 하지 않기 때문에
	 * 함수 호출전에 이미지 파일인지 반듯이 체크가 필요하다.
	 * thumbnail 을 생성 할때 ratio 로 생성한다면 원하는 크기로 딱 맞추어 주는 것이 아니라 최대한 맞추어 준다.
	 * 즉 200x200 으로 설정 하더라도 190x200 으로 생성 될 수 있다.
	 *
	 * @param ret {&array} 호출하는 함수로 넘겨줄 값
	 * @param file_info {object} do_upload 를 통해 업로드 된 파일 정보
	 * @param member_srl {number} 파일을 생성 할 member_srl
	 * @param file_type {string} mfile.config 에 설정 되어 있는 업로드 되는 파일이 사용되는 종류
	 * @return {string} return error code
	 */
	public function save_thumbnail_file_in_local(&$ret, $file_info, $member_srl=FALSE, $file_type=FALSE) {
		$ret_valid = array();
		$result_code = $this->validFileType($ret_valid, $member_srl, $file_type);
		if($result_code != $this->success_code) { return $result_code; }
		$file_config = $ret_valid['file_config'];
		
		// 생성할 thumbnail type 체크
		$thumbnail_type = $this->input->post2('thumbnail_type', TRUE);
		if($thumbnail_type != 'crop') { $thumbnail_type = 'ratio'; }
		
		// thumbnail 사이즈 체크
		$thumbnail_width = $this->input->post2('thumbnail_width', TRUE);
		$thumbnail_height = $this->input->post2('thumbnail_height', TRUE);
		
		if(!$thumbnail_width || $thumbnail_width > $file_config['thumbnail_max_width']) {
			log_message('info', 'save_thumbnail_file_in_local user thumbnail size wrong['.
					($thumbnail_width?$thumbnail_width:0).'x'.
					($thumbnail_height?$thumbnail_height:0).', set default thumbnail size['.
					$file_config['thumbnail_max_width'].'x'.
					$file_config['thumbnail_max_height'].']');
		
			$thumbnail_width = $file_config['thumbnail_max_width'];
			$thumbnail_height = $file_config['thumbnail_max_height'];
		}
		
		if(!$thumbnail_height || $thumbnail_height > $file_config['thumbnail_max_height']) {
			log_message('info', 'save_thumbnail_file_in_local user thumbnail size wrong['.
					($thumbnail_width?$thumbnail_width:0).'x'.
					($thumbnail_height?$thumbnail_height:0).', set default thumbnail size['.
					$file_config['thumbnail_max_width'].'x'.
					$file_config['thumbnail_max_height'].']');
			
			$thumbnail_width = $file_config['thumbnail_max_width'];
			$thumbnail_height = $file_config['thumbnail_max_height'];
		}
		
		$config['image_library'] = 'gd2';
		$config['source_image'] = $file_info['full_path'];
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $thumbnail_width;
		$config['height'] = $thumbnail_height;
		
		$this->load->library('image_lib', $config);
		
		$result = FALSE;
		if($thumbnail_type == 'ratio')	{ $result = $this->image_lib->resize(); }
		else							{ $result = $this->image_lib->crop(); }
		
		if(!$result) {
			$error_code = $this->config->item('mfile_create_thumbnail', 'error_code/mfile');
			log_message('error', "save_thumbnail_file_in_local E[$error_code] can\'t create thumbnail for file[".
					$file_info['full_path']."]");
			return $error_code;
		}
		
		$ret['file_name'] = $file_info['raw_name'].'_thumb'.$file_info['file_ext'];
		$ret['full_path'] = $file_info['file_path'].$ret['file_name'];
		$ret['image_width'] = $thumbnail_width;
		$ret['image_height'] = $thumbnail_height;
		
		return $this->success_code;
	}
	
	/**
	 * 원격 디스크에 파일을 저장한다.
	 *
	 * @param ret {&array} 호출하는 함수로 넘겨줄 값
	 * @param file_info {array} local 에 올린 파일 정보
	 * @param thumbnail_info {array} thumbnail 이 존재할 때 thumbnail 의 정보
	 * @param member_srl {number} 파일을 올린 member_srl
	 * @param file_type {string} mfile.config 에 설정 되어 있는 업로드 되는 파일이 사용되는 종류
	 * @return -1 : 지원하지 않는 원격 디스크로 설정 되어 있음
	 *         FALSE : 업로드 실패
	 *         나머지 : 원격 디스크에 업로드 한 정보(접속 할 수 있는 url)
	 */
	public function save_file_to_network_disk(&$ret, $file_info, $thumbnail_info=FALSE, 
			$member_srl=FALSE, $file_type=FALSE) {
			
		$ret_valid = array();
		$result_code = $this->validFileType($ret_valid, $member_srl, $file_type);
		if($result_code != $this->success_code) { return $result_code; }
		$file_config = $ret_valid['file_config'];
			
		$disk_type = $file_config['network_disk_type'];
		switch($disk_type) {
			case 'amazon_s3':
				return $this->upload_to_amazon_s3($ret, $file_info, $ret_valid['member_srl'], 
						$file_config, $thumbnail_info);
			default:
				$error_code = $this->config->item('mfile_upload_to_network_disk_fail', 'error_code/mfile');
				log_message('error', "save_file_to_network_disk E[$error_code] not supported network disk type[$disk_type]");
				return $error_code;
		}
	}
	
	/**
	 * 파일 meta 정보를 테이블에 저장한다.
	 * network_url 이 존재하면 local_url 과 local_path 가 존재하지 않도록 한다.
	 * 반대로 network_url 이 존재하지 않으면 local_url 과 local_path 가 존재한다.
	 *
	 * @param ret {&array} 호출하는 함수로 넘겨줄 값
	 * @param file_info {array} upload 된 파일 정보
	 * @param thumbnail_info {array} 생성된 thumbnail 의 정보(FALSE 이면 thumbnail 없음)
	 * @param member_srl {number} 파일을 등록한 member_srl
	 * @param file_type {string} mfile.config 에 설정 되어 있는 업로드 되는 파일이 사용되는 종류
	 * @param comment {string} 파일의 comment
	 * @param network_url {string} network disk 의 url
	 * @return {string} return error code
	 */
	public function save_file_in_db(&$ret, $file_info, $thumbnail_info=FALSE, $member_srl=FALSE, 
				$file_type=FALSE, $comment=FALSE, $network_url=FALSE) {
		$ret_valid = array();
		$result_code = $this->validFileType($ret_valid, $member_srl, $file_type);
		if($result_code != $this->success_code) { return $result_code; }
		$file_config = $ret_valid['file_config'];
		
		if(!$this->master_db) { $this->master_db = $this->load->database('master', TRUE); }
		
		$this->load->helper('url');
		
		$local_image_dir_path = $file_config['file_directory'];
		if($local_image_dir_path) {
			$local_image_dir_path = substr($local_image_dir_path, 2);
			if(substr($local_image_dir_path, strlen($local_image_dir_path)-1) != DIRECTORY_SEPARATOR) {
				$local_image_dir_path = $local_image_dir_path . DIRECTORY_SEPARATOR;
			}
		} else {
			$local_image_dir_path = '';
		}
		
		if(!$comment) { $comment = $this->input->post2('comment', TRUE); }
		
		$local_url = $thumbnail_local_url = '';
		if(!$network_url) {
			$local_url = image_url().$local_image_dir_path.md5($ret_valid['member_srl']).
					DIRECTORY_SEPARATOR.$file_info['file_name'];
			if($thumbnail_info) {
				$thumbnail_local_url = image_url().$local_image_dir_path.
						md5($ret_valid['member_srl']).DIRECTORY_SEPARATOR.$thumbnail_info['file_name'];
			}
		}
		
		$ret = array(
				'member_srl' => $ret_valid['member_srl'], 
				'file_type' => $file_info['file_type'],
				'orig_name' => $file_info['orig_name'],
				'local_path' => $network_url ? '' : $file_info['full_path'],
				'local_url' => $local_url,
				'network_url' => $network_url ? $network_url['orig_url'] : '',
				'width' => $file_info['image_width'] ? $file_info['image_width'] : 0,
				'height' => $file_info['image_height'] ? $file_info['image_height'] : 0,
				'file_size' => round($file_info['file_size']),
				'comment' => $comment ? $comment : '',
				'thumbnail_local_path' => ($network_url || !$thumbnail_info) ? '' : $thumbnail_info['full_path'],
				'thumbnail_local_url' => $thumbnail_local_url,
				'thumbnail_network_url' => $network_url ? $network_url['thumbnail_url'] : '',
				'thumbnail_width' => $thumbnail_info ? $thumbnail_info['image_width'] : 0,
				'thumbnail_height' => $thumbnail_info ? $thumbnail_info['image_height'] : 0,
				'ipaddress' => $this->input->ip_address(),
				'c_date' => mdate('%Y%m%d%H%i%s')
			);
		
		$result = $this->master_db->insert($this->table_prefix.'files', $ret);
		if(!$result) {
			$error_code = $this->config->item('mfile_insert_table_fail', 'error_code/mfile');
			log_message('error', "save_file_in_db E[$error_code] insert meta-data files table failed");
			return $error_code;
		}
		
		// row_count 체크를 할 필요가 있나?
		//$row_count = $this->master_db->affected_rows();
		//if($row_count <= 0) {
		//	log_message('error', 'save_file_in_db insert file meta-data, but not found affected row');
		//	return FALSE;
		//}
		
		// 마지막으로 사용된 auto_increment 값을 가져온다.
		// insert_id 가 내부에서 mysql_insert_id 을 사용하고 있음.
		// mysql_insert_id 는 thread safe 하니, insert query 와 mysql_insert_id 사이에
		// 또 다른 insert query 가 와도 상관 없다. 만쉐이~ 이거 못 믿겨서 sleep 으로 테스트 해 보았음.
		$ret['file_srl'] = $this->master_db->insert_id();
		
		return $this->success_code;
	}
	
}
?>