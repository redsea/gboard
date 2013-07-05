<?php
/**
 * 파일 저장에 대한 설정. 네트워크 저장소(Amazon S3 등등)에 대한 설정도 포함 한다.
 * 네트워크 저장소는 현재 amazon s3 만 지원 한다.
 *
 * amazone s3 의 region 선택 값은 다음과 같다.
 *	- us_e1			: us e1
 *	- virginia		: us_e1 과 동일
 *	- us_standard 	: us_e1 과 동일
 * 	- us_w1			: us_w1
 * 	- california	: us_w1 과 동일
 *	- us_w2			: us_w2
 *	- oregon		: us_w2 와 동일
 *	- eu_w1			: eu_w1
 *	- ireland		: eu_w1 과 동일
 *	- apac_se1		: apac_se1
 *	- singapore		: apac_se1 과 동일
 *	- apac_se2		: apac_se2
 *	- sydney		: apac_se2 와 동일
 *	- apac_ne1		: apac_ne1
 *	- tokyo			: apac_ne1 과 동일
 *	- sa_e1			: sa_e1
 *	- sao_paulo		: sa_e1 과 동일
 *	- us_gov1		: us_gov1
 *	- us_gov1_fips	: us_gov1_fips
 *	- default		: us_e1 과 동일
 *
 * @author dhkim94@gmail.com
 */
 
// 파일 업로드 지원하는 서비스 데이터 타입
// 서비스 데이터 타입이 추가 되면 아래의 do_upload function 설정에도 값이 추가 되어야 한다.
// - profile : 프로필 이미지로 사용할 파일
$config['support_data_type'] = array('profile');


// 업로드 되는 프로필 이미지 파일에 대한 설정
// - file_directory	: 로컬 서버에 저장될 디렉토리 윛
// - file_type		: 파일 타입(이미지만 받아 들임)
$config['profile']['type_name'] = 'profile';				// 파일 타입 이름(network disk 에 업로드시 사용)
$config['profile']['file_directory'] = './files/profile/';	// 프로필로 사용할 이미지가 저장될 디렉토리
$config['profile']['file_type'] = 'gif|jpg|png|jpeg|jpe';	// 이미지 파일 업로드시 받아 들일 수 있는 파일 타입
$config['profile']['max_size'] = 2000;						// 업로드 된 파일의 최대 크기(KB 단위임)
$config['profile']['max_width'] = 0;						// 업로드 된 파일의 최대 너비(픽셀 단위) 0 이면 제한 없음
$config['profile']['max_height'] = 0;						// 업로드 된 파일의 최대 높이(픽셀 단위) 0 이면 제한 없음
$config['profile']['encrypt_name'] = TRUE;					// 업로드 된 파일의 이름 자동 변경 여부(TRUE 이면 랜덤하게 문자열로 변환됨)
$config['profile']['remove_space'] = TRUE;					// 업로드 된 파일의 이름에서 공백을 밑줄로 자동 변경(TRUE 이면 변경함)
$config['profile']['form_name'] = 'gboard_profile';			// 업로드 form 에서 input-file tag 의 name
$config['profile']['thumbnail_max_width'] = 40;				// 이미지 파일 thumbnail 생성시 thumbnail 의 최대 너비
$config['profile']['thumbnail_max_height'] = 40;			// 이미지 파일 thumbnail 생성시 thumbnail 의 최대 높이
$config['profile']['network_disk_use'] = FALSE;				// 네트워크 디스크 사용 여부.
$config['profile']['network_disk_type'] = 'amazon_s3';		// network disk 는 amazon s3 를 사용함
$config['profile']['amazon_s3_bucket'] = 'org.gboard.img';	// 파일이 업로드 될 amazone s3 의 bucket
$config['profile']['amazon_s3_region'] = 'tokyo';			// amazon s3 의 지역 설정

?>