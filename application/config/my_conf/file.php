<?php
/**
 * 파일 저장에 대한 설정. 네트워크 저장소(Amazon S3 등등)에 대한 설정도 포함 한다.
 * 네트워크 저장소는 현재 amazon s3 만 지원 한다.
 *
 * @author dhkim94@gmail.com
 */
$config['local_file_directory'] = './files/attach/';	// 파일 업로드시 저장될 디렉토리
														// ./ 로 시작하는 상대 경로를 사용해야 함. 
														// ./../../ 이런건 안됨.
														// 만일 파일 서버가 다른 장비에 있다면 다른 장비의 경로도 위와 같아야 함.
														// 즉, apache 접속 홈 디렉토리에서 ./files/attach 로 저장되어야 함.
														
$config['supported_file_type'] = 'gif|jpg|png|zip';			// 파일 업로드시 받아 들일 수 있는 파일 타입
$config['supported_file_max_size'] = 2000;				// 업로드 된 파일의 최대 크기(KB 단위임)
$config['supported_file_max_width'] = 0;				// 업로드 된 파일의 최대 너비(픽셀 단위) 0 이면 제한 없음
$config['supported_file_max_height'] = 0;				// 업로드 된 파일의 최대 높이(픽셀 단위) 0 이면 제한 없음
$config['supported_file_encrypt_name'] = TRUE;			// 업로드 된 파일의 이름 자동 변경 여부(TRUE 이면 랜덤하게 문자열로 변환됨)
$config['supported_file_remove_space'] = TRUE;			// 업로드 된 파일의 이름에서 공백을 밑줄로 자동 변경(TRUE 이면 변경함)

$config['upload_form_name'] = 'gboard_file';			// 업로드 form 에서 input-file tag 의 name

$config['thumbnail_max_width'] = 200;					// 이미지 파일 thumbnail 생성시 thumbnail 의 최대 너비
$config['thumbnail_max_height'] = 200;					// 이미지 파일 thumbnail 생성시 thumbnail 의 최대 높이

$config['network_disk_use'] = FALSE;						// 네트워크 디스크 사용 여부.
														// 네트워크 디스크를 사용한다면 로컬은 네트워크 디스크로 올리는 임시 저장소 역할만 한다.
$config['network_disk_type'] = 'amazon_s3';				// 현재는 amazon s3 만 지원함.

// amazon s3 에 대한 설정
$config['amazon_s3_bucket_file'] = 'org.gboard.img';

/*
|--------------------------------------------------------------------------
| amazon s3 의 지역 설정
|--------------------------------------------------------------------------
|
| amazon s3 저장소 지역
|
|	- us_e1			: us e1
|	- virginia		: us_e1 과 동일
|	- us_standard 	: us_e1 과 동일
|	- us_w1			: us_w1
|	- california	: us_w1 과 동일
|	- us_w2			: us_w2
|	- oregon		: us_w2 와 동일
|	- eu_w1			: eu_w1
|	- ireland		: eu_w1 과 동일
|	- apac_se1		: apac_se1
|	- singapore		: apac_se1 과 동일
|	- apac_se2		: apac_se2
|	- sydney		: apac_se2 와 동일
|	- apac_ne1		: apac_ne1
|	- tokyo			: apac_ne1 과 동일
|	- sa_e1			: sa_e1
|	- sao_paulo		: sa_e1 과 동일
|	- us_gov1		: us_gov1
|	- us_gov1_fips	: us_gov1_fips
|	- default		: us_e1 과 동일
|
*/
$config['amazon_s3_region'] = 'tokyo';
?>