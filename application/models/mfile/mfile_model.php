<?php
/**
 * MFile model
 *
 * @author dhkim94@gmail.com
 */
class Mfile_model extends CI_Model {
	function __construct() {
		parent::__construct();
		
		$this->config->load('my_conf/common', TRUE);
		$this->config->load('my_conf/file', TRUE);
		$this->config->load('error_code/common', TRUE);
		$this->config->load('error_code/mfile', TRUE);
		
		//$this->yes = $this->config->item('yes', 'my_conf/common');
		//$this->no = $this->config->item('no', 'my_conf/common');
				
		$this->table_prefix = $this->config->item('table_prefix', 'my_conf/common');
		$this->success_code = $this->config->item('common_success', 'error_code/common');
	}
	
	/**
	 * amazon s3 에 파일을 업로드 한다.
	 * 이미지 파일을 업로드시 thumbnail 이 존재하면 thumbnail 도 같이 업로드 한다.
	 * 즉, 2번의 연동을 하며 한번이라도 실패하면 실패 처리 한다.
	 *
	 * @param file_info {array} local 에 올린 파일 정보
	 * @param thumbnail_info {array} thumbnail 이 존재할때 thumbnail 정보
	 * @param member_srl {number} 파일을 올린 member_srl
	 * @return 실패하면 FALSE, 성공하면 amazon s3 에 접속 할 수 있는 URL 을 리턴한다.
	 */
	private function _upload_to_amazon_s3($file_info, $thumbnail_info=FALSE, $member_srl=FALSE) {
		require_once('application/third_party/aws/sdk.class.php');
		
		$s3 = new AmazonS3();
		
		$region = $this->config->item('amazon_s3_region', 'my_conf/file');
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
			log_message('error', 'failed change amaozon s3 region');
			return FALSE;
		}
		log_message('info', 'change amaozon s3 region');
		
		if(!$member_srl) { $member_srl = $this->config->system_member_srl(); }
		
		$s3_path = $member_srl.DIRECTORY_SEPARATOR.$file_info['file_name'];
		
		try {
			$response = $s3->create_mpu_object($this->config->item('amazon_s3_bucket_file', 'my_conf/file'), 
					$s3_path, array(
						'fileUpload'  => $file_info['full_path'],
						//'fileUpload'  => './files/attach/'.$file_info['file_name'],
						'contentType' => $file_info['file_type'],
						'acl'         => AmazonS3::ACL_PUBLIC,
						'storage'     => AmazonS3::STORAGE_STANDARD
					)
				);
		} catch(Exception $e) {
			log_message('error', '_upload_to_amazon_s3 exception upload(orig) to amazon s3. e['.json_encode($e).']');
			return FALSE;
		}
		
		if(!$response->isOK()) {
			log_message('error', '_upload_to_amazon_s3 failed upload(orig) to amazon s3. code['.$response->body->Code.
					'], message['.$response->body->Message.']');
			return FALSE;
		}
		log_message('info', 'upload(orig) to amazon s3. url['.$response->header['_info']['url'].']');
		
		$result = array();
		$result['orig_url'] = $response->header['_info']['url'];
		
		// thumbnail 이 존재하면 thumbnail 도 같이 올린다.
		if($thumbnail_info) {
			$s3_path = $member_srl.DIRECTORY_SEPARATOR.$thumbnail_info['file_name'];
			
			try {
				$response = $s3->create_mpu_object($this->config->item('amazon_s3_bucket_file', 'my_conf/file'), 
						$s3_path, array(
							'fileUpload'  => $thumbnail_info['full_path'],
							//'fileUpload'  => './files/attach/'.$file_info['file_name'],
							'contentType' => $file_info['file_type'],
							'acl'         => AmazonS3::ACL_PUBLIC,
							'storage'     => AmazonS3::STORAGE_STANDARD
						)
					);
			} catch(Exception $e) {
				log_message('error', '_upload_to_amazon_s3 exception upload(thumbnail) to amazon s3. e['.json_encode($e).']');
				return FALSE;
			}
			
			if(!$response->isOK()) {
				log_message('error', '_upload_to_amazon_s3 failed upload(thumbnail) to amazon s3. code['.$response->body->Code.
						'], message['.$response->body->Message.']');
				return FALSE;
			}
			log_message('info', 'upload(thumbnail) to amazon s3. url['.$response->header['_info']['url'].']');
			
			$result['thumbnail_url'] = $response->header['_info']['url'];
		}
		
		return $result;
	}
	
	/** 
	 * upload 된 파일을 저장한다.
	 *
	 * @return {string|objet} 성공하면 업로드 된 파일 정보를 리턴하고, 실패하면 error_code/mfile.php 에서 설정된 에러 코드를 리턴한다.
	 */
	public function save_file_in_local($member_srl=FALSE) {
		if(!$member_srl) { $member_srl = $this->config->system_member_srl(); }
		
		$config['upload_path'] = $this->config->item('local_file_directory', 'my_conf/file');
		if($config['upload_path']) {
			if(substr($config['upload_path'], strlen($config['upload_path'])-1) != DIRECTORY_SEPARATOR) {
				$config['upload_path'] = $config['upload_path'] . DIRECTORY_SEPARATOR;
			}
		} else {
			log_message('error', 'save_file_in_local non-config local_file_directory in my_conf/file');
			return $this->config->item('mfile_file_upload_no_upload_config', 'error_code/mfile');
		}
		
		$config['upload_path'] = $config['upload_path'].$member_srl.DIRECTORY_SEPARATOR;
		
		if(!is_dir($config['upload_path'])) {
			if(!mkdir($config['upload_path'], 0777)) {
				log_message('error', 'save_file_in_local can\'t create directory['.$config['upload_path'].']');
				return $this->config->item('mfile_file_upload_mkdir', 'error_code/mfile');
			}
		}
		
		$config['allowed_types'] = $this->config->item('supported_file_type', 'my_conf/file');
		$config['max_size']	= $this->config->item('supported_file_max_size', 'my_conf/file');
		$config['max_width']  = $this->config->item('supported_file_max_width', 'my_conf/file');
		$config['max_height']  = $this->config->item('supported_file_max_height', 'my_conf/file');
		$config['encrypt_name'] = $this->config->item('supported_file_encrypt_name', 'my_conf/file');
		$config['remove_spaces'] = $this->config->item('supported_file_remove_space', 'my_conf/file');
		
		$this->load->library('upload', $config);
		
		$result = $this->upload->do_upload($this->config->item('upload_form_name', 'my_conf/file'));
		
		if($result == $this->success_code) {
			$file_info = $this->upload->data();
			if(!chmod($file_info['full_path'], 0666)) {
				log_message('warn', 'save_file_in_local can\'t change permission 0646 file['.$file_info['full_path'].']');
			}
			return $file_info;
		}
		
		return $result;
	}
	
	/**
	 * thumbnail 파일을 생성한다. thumbnail 을 생성할 원본이 이미지 파일인지 체크 하지 않기 때문에
	 * 함수 호출전에 이미지 파일인지 반듯이 체크가 필요하다.
	 * thumbnail 을 생성 할때 ratio 로 생성한다면 원하는 크기로 딱 맞추어 주는 것이 아니라 최대한 맞추어 준다.
	 * 즉 200x200 으로 설정 하더라도 190x200 으로 생성 될 수 있다.
	 *
	 * @param file_info {object} do_upload 를 통해 업로드 된 파일 정보
	 * @param member_srl {number} 파일을 생성 할 member_srl
	 * @return thumbnail 생성 성공하면 thumbnail 의 정보, 실패하면 FALSE 를 리턴 한다.
	 */
	public function save_thumbnail_file_in_local($file_info, $member_srl=FALSE) {
		if(!$member_srl) { $member_srl = $this->config->system_member_srl(); }
		
		// 생성할 thumbnail type 체크
		$thumbnail_type = $this->input->post2('thumbnail_type', TRUE);
		if($thumbnail_type != 'crop') { $thumbnail_type = 'ratio'; }
		
		// thumbnail 사이즈 체크
		$thumbnail_width = $this->input->post2('thumbnail_width', TRUE);
		$thumbnail_height = $this->input->post2('thumbnail_height', TRUE);
		
		if(!$thumbnail_width || $thumbnail_width > $this->config->item('thumbnail_max_width', 'my_conf/file')) {
			log_message('warn', 'save_thumbnail_file_in_local user thumbnail size wrong['.
					$thumbnail_width.'x'.$thumbnail_height.', set default thumbnail size['.
					$this->config->item('thumbnail_max_width', 'my_conf/file').'x'.
					$this->config->item('thumbnail_max_height', 'my_conf/file').']');
		
			$thumbnail_width = $this->config->item('thumbnail_max_width', 'my_conf/file');
			$thumbnail_height = $this->config->item('thumbnail_max_height', 'my_conf/file');
		}
		
		if(!$thumbnail_height || $thumbnail_height > $this->config->item('thumbnail_max_height', 'my_conf/file')) {
			log_message('warn', 'save_thumbnail_file_in_local user thumbnail size wrong['.
					$thumbnail_width.'x'.$thumbnail_height.', set default thumbnail size['.
					$this->config->item('thumbnail_max_width', 'my_conf/file').'x'.
					$this->config->item('thumbnail_max_height', 'my_conf/file').']');
			
			$thumbnail_width = $this->config->item('thumbnail_max_width', 'my_conf/file');
			$thumbnail_height = $this->config->item('thumbnail_max_height', 'my_conf/file');
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
		
		if(!$result) { return $result; }
		
		$result = array();
		$result['file_name'] = $file_info['raw_name'].'_thumb'.$file_info['file_ext'];
		$result['full_path'] = $file_info['file_path'].$result['file_name'];
		$result['image_width'] = $thumbnail_width;
		$result['image_height'] = $thumbnail_height;
		
		return $result;
	}
	
	/**
	 * 원격 디스크에 파일을 저장한다.
	 *
	 * @param file_info {array} local 에 올린 파일 정보
	 * @param thumbnail_info {array} thumbnail 이 존재할 때 thumbnail 의 정보
	 * @param member_srl {number} 파일을 올린 member_srl
	 * @return -1 : 지원하지 않는 원격 디스크로 설정 되어 있음
	 *         FALSE : 업로드 실패
	 *         나머지 : 원격 디스크에 업로드 한 정보(접속 할 수 있는 url)
	 */
	public function save_file_to_network_disk($file_info, $thumbnail_info=FALSE, $member_srl=FALSE) {
		$disk_type = $this->config->item('network_disk_type', 'my_conf/file');
		switch($disk_type) {
			case 'amazon_s3':
				return $this->_upload_to_amazon_s3($file_info, $thumbnail_info, $member_srl);
			default:
				log_message('error', "save_file_to_network_disk not supported network disk type[$disk_type]");
				return FALSE;
		}
	}
	
	/**
	 * 파일 meta 정보를 테이블에 저장한다.
	 * network_url 이 존재하면 local_url 과 local_path 가 존재하지 않도록 한다.
	 * 반대로 network_url 이 존재하지 않으면 local_url 과 local_path 가 존재한다.
	 *
	 * @param file_info {array} upload 된 파일 정보
	 * @param thumbnail_info {array} 생성된 thumbnail 의 정보(FALSE 이면 thumbnail 없음)
	 * @param member_srl {number} 파일을 등록한 member_srl
	 * @param comment {string} 파일의 comment
	 * @param network_url {string} network disk 의 url
	 */
	public function save_file_in_db($file_info, $thumbnail_info=FALSE, $member_srl=FALSE, $comment=FALSE, $network_url=FALSE) {
		$this->load->database();
		
		$this->load->helper('url');
		$this->load->helper('date');
		
		$local_image_dir_path = $this->config->item('local_file_directory', 'my_conf/file');
		if($local_image_dir_path) {
			$local_image_dir_path = substr($local_image_dir_path, 2);
			if(substr($local_image_dir_path, strlen($local_image_dir_path)-1) != DIRECTORY_SEPARATOR) {
				$local_image_dir_path = $local_image_dir_path . DIRECTORY_SEPARATOR;
			}
		} else {
			$local_image_dir_path = '';
		}
		
		if(!$comment) { $comment = $this->input->post2('comment', TRUE); }
		
		$data = array(
				'member_srl' => $member_srl ? $member_srl : $this->config->system_member_srl(), 
				'file_type' => $file_info['file_type'],
				'orig_name' => $file_info['orig_name'],
				'local_path' => $network_url ? '' : $file_info['full_path'],
				'local_url' => $network_url ? '' : image_url().$local_image_dir_path.$file_info['file_name'],
				'network_url' => $network_url ? $network_url['orig_url'] : '',
				'width' => $file_info['image_width'] ? $file_info['image_width'] : 0,
				'height' => $file_info['image_height'] ? $file_info['image_height'] : 0,
				'file_size' => round($file_info['file_size']),
				'comment' => $comment ? $comment : '',
				'thumbnail_local_path' => ($network_url || !$thumbnail_info) ? '' : $thumbnail_info['full_path'],
				'thumbnail_local_url' => ($network_url || !$thumbnail_info) ? '' : image_url().$local_image_dir_path.$thumbnail_info['file_name'],
				'thumbnail_network_url' => $network_url ? $network_url['thumbnail_url'] : '',
				'thumbnail_width' => $thumbnail_info ? $thumbnail_info['image_width'] : 0,
				'thumbnail_height' => $thumbnail_info ? $thumbnail_info['image_height'] : 0,
				'ipaddress' => $this->input->ip_address(),
				'c_date' => mdate('%Y%m%d%h%i%s')
			);
		
		$result = $this->db->insert($this->table_prefix.'files', $data);
		if(!$result) {
			log_message('error', 'save_file_in_db insert file meta-data failed');
			return FALSE;
		}
		
		$row_count = $this->db->affected_rows();
		if($row_count <= 0) {
			log_message('error', 'save_file_in_db insert file meta-data, but not found affected row');
			return FALSE;
		}
		
		// 마지막으로 사용된 auto_increment 값을 가져온다.
		// insert_id 가 내부에서 mysql_insert_id 을 사용하고 있음.
		// mysql_insert_id 는 thread safe 하니, insert query 와 mysql_insert_id 사이에
		// 또 다른 insert query 가 와도 상관 없다. 만쉐이~ 이거 못 믿겨서 sleep 으로 테스트 해 보았음.
		$data['file_srl'] = $this->db->insert_id();
		
		return $data;
	}
	
}
?>