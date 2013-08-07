# create gbd_member_group table. this table manage group
DROP TABLE IF EXISTS  `gbd_member_group` ;
CREATE TABLE  `gbd_member_group` (
    `group_srl` BIGINT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `title` VARCHAR( 128 ) NOT NULL ,
    `is_default` CHAR( 2 ) NOT NULL DEFAULT  'N',
    `is_root` CHAR( 2 ) NOT NULL DEFAULT  'N',
    `image_mark` TEXT NULL ,
    `description` TEXT NULL ,
    `list_order` BIGINT( 11 ) NOT NULL DEFAULT  '1',
    `c_date` CHAR( 14 ) NOT NULL ,
    `u_date` CHAR( 14 ) NULL ,
    INDEX (  `list_order` )
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create gboard root group.
INSERT INTO  `gbd_member_group` (  `title` ,  `is_root` ,  `c_date` ) 
VALUES ( "__usrLang17",  "Y", NOW( ) +0 ) ;

# create gboard formal member group
INSERT INTO  `gbd_member_group` (  `title` ,  `list_order` , `c_date` ) 
VALUES ( "__usrLang18",  2, NOW( ) +0 ) ;

# create gboard ready member group
INSERT INTO  `gbd_member_group` (  `title` ,  `is_default` , `list_order` ,  `c_date` ) 
VALUES ( "__usrLang19",  "Y", 3, NOW( ) +0 ) ;


# create gbd_member table. this table manage member
DROP TABLE IF EXISTS  `gbd_member`;
CREATE TABLE `gbd_member` ( 
    `member_srl` bigint(11) NOT NULL AUTO_INCREMENT, 
    `user_id` varchar(128) NOT NULL, 
    `email_address` varchar(128) NOT NULL, 
    `user_password` varchar(128) NOT NULL, 
    `user_name` varchar(64) NOT NULL, 
    `nick_name` varchar(64) NOT NULL, 
    `find_account_question` varchar(8) DEFAULT NULL, 
    `find_account_answer` varchar(256) DEFAULT NULL, 
    `allow_mailing` char(2) NOT NULL DEFAULT 'N', 
    `allow_message` char(2) NOT NULL DEFAULT 'N', 
    `image_mark` TEXT NULL , 
    `block` char(2) NOT NULL DEFAULT 'N', 
    `description` text, 
    `list_order` bigint(11) NOT NULL DEFAULT '-1', 
    `email_confirm` char(2) NOT NULL DEFAULT 'N', 
    `limit_date` char(14) DEFAULT NULL, 
    `last_login_date` char(14) DEFAULT NULL, 
    `change_password_date` char(14) NOT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    PRIMARY KEY (`member_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create index for gbd_member
ALTER TABLE  `gbd_member` ADD INDEX (  `user_id` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `email_address` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `user_name` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `nick_name` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `list_order` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `c_date` ) ;

# create gboard root account
INSERT INTO `gbd_member` (
    `user_id`, 
    `email_address`, 
    `user_password`, 
    `user_name`, 
    `nick_name`, 
    `list_order`, 
    `email_confirm`, 
    `last_login_date`, 
    `change_password_date`, 
    `c_date` 
) 
VALUES (
    "dhkim94@gmail.com",
    "dhkim94@gmail.com",
    "202cb962ac59075b964b07152d234b70",
    "김대희",
    "루트",
    -1,
    "Y",
    NOW( ) +0,
    NOW( ) +0,
    NOW( ) +0
);

# create gboard system default member
INSERT INTO `gbd_member` (
    `user_id`, 
    `email_address`, 
    `user_password`, 
    `user_name`, 
    `nick_name`, 
    `list_order`, 
    `email_confirm`, 
    `last_login_date`, 
    `change_password_date`, 
    `c_date` 
) 
VALUES (
    "nobody",
    "dhkim94@gmail.com",
    "202cb962ac59075b964b07152d234b70",
    "노바디",
    "노바디",
    -2, 
    "Y",
    NOW( ) +0,
    NOW( ) +0,
    NOW( ) +0
);


# create gbd_member_extra. this table manage member's extra information
DROP TABLE IF EXISTS  `gbd_member_extra`;
CREATE TABLE `gbd_member_extra` (
	`member_srl` bigint(11) NOT NULL, 
    `homepage` varchar(256) DEFAULT NULL, 
    `blog` varchar(256) DEFAULT NULL, 
    `birthday` char(8) DEFAULT NULL, 
    `gender` char(2) DEFAULT NULL, 
    `country` varchar(8) DEFAULT NULL, 
    `country_call_code` varchar(8) DEFAULT NULL, 
    `mobile_phone_number` varchar(16) DEFAULT NULL, 
    `phone_number` varchar(16) DEFAULT NULL, 
    `account_social_type` varchar(32) DEFAULT NULL, 
    `account_social_id` varchar(64) DEFAULT NULL, 
    `login_count` bigint(11) DEFAULT '0', 
    `serial_login_count` bigint(11) DEFAULT '0', 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    FOREIGN KEY( `member_srl`) REFERENCES `gbd_member`(`member_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`member_srl`) 
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create extra information of gboard root acount 
INSERT INTO `gbd_member_extra` ( 
    `member_srl`, 
    `c_date` 
) VALUES ( 
    "1", 
    NOW()+0 
);

# create extra information of gboard nobody acount 
INSERT INTO `gbd_member_extra` ( 
    `member_srl`, 
    `c_date` 
) VALUES ( 
    "2", 
    NOW()+0 
);


# create gbd_member_group_member. this table manage group of member
DROP TABLE IF EXISTS  `gbd_member_group_member`;
CREATE TABLE `gbd_member_group_member` (
    `group_srl` bigint(11) NOT NULL, 
    `member_srl` bigint(11) NOT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX( `group_srl` ),
    INDEX( `member_srl` ), 
    FOREIGN KEY( `group_srl`) REFERENCES `gbd_member_group`(`group_srl`) ON DELETE CASCADE, 
    FOREIGN KEY( `member_srl`) REFERENCES `gbd_member`(`member_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`group_srl`, `member_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# mapping between gboard root group and gboard root account
INSERT INTO `gbd_member_group_member` 
    (`group_srl`, `member_srl`, `c_date`) 
VALUES 
    (1, 1, NOW() +0),	# admin 용 group 매핑
    (2, 2, NOW() +0);	# 기본 용 group 매핑


# create gbd_files. this table manage uploaded file
DROP TABLE IF EXISTS  `gbd_files`;
CREATE TABLE `gbd_files` (
    `file_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `member_srl` bigint(11) NOT NULL, 
    `download_count` bigint(11) DEFAULT '0', 
    `file_type` varchar(128) NOT NULL, 
    `orig_name` varchar(128) DEFAULT NULL, 
    `local_path` varchar(256) DEFAULT NULL, 
    `local_url` varchar(256) DEFAULT NULL, 
    `network_url` varchar(256) DEFAULT NULL, 
    `width` int(8) DEFAULT '0', 
    `height` int(8) DEFAULT '0', 
    `file_size` bigint(11) DEFAULT '0', 
    `file_comment` varchar(256) DEFAULT NULL, 
    `thumbnail_local_path` varchar(256) DEFAULT NULL, 
    `thumbnail_local_url` varchar(256) DEFAULT NULL, 
    `thumbnail_network_url` varchar(256) DEFAULT NULL, 
    `thumbnail_width` int(8) DEFAULT '0', 
    `thumbnail_height` int(8) DEFAULT '0', 
    `ipaddress` varchar(32) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX( `member_srl` ), 
    INDEX( `orig_name` ), 
    INDEX( `file_size` ), 
    FOREIGN KEY( `member_srl`) REFERENCES `gbd_member`(`member_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`file_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_country_code. this table manage country code
DROP TABLE IF EXISTS  `gbd_country_code`;
CREATE TABLE `gbd_country_code` (
    `country_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `alpha2` char(2) NOT NULL, 
    `alpha3` char(3) NOT NULL, 
    `numberic` varchar(8) NOT NULL, 
    `name` varchar(64) NOT NULL, 
    `name_alias` varchar(64) DEFAULT NULL, 
    `country_call_code` varchar(8) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX( `alpha2` ),
    INDEX( `alpha3` ), 
    INDEX( `numberic` ), 
    INDEX( `name` ),
    INDEX( `name_alias` ),
    INDEX( `country_call_code` ),
    PRIMARY KEY(`country_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_language_code. this table manage language code
DROP TABLE IF EXISTS  `gbd_language_code`;
CREATE TABLE `gbd_language_code` (
    `language_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `alpha2` char(2) DEFAULT NULL, 
    `alpha3` char(3) NOT NULL, 
    `name` varchar(32) DEFAULT NULL, 
    `description1` varchar(128) DEFAULT NULL, 
    `description2` varchar(128) DEFAULT NULL, 
    `image_mark` TEXT NULL , 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`alpha2`), 
    INDEX(`alpha3`), 
    PRIMARY KEY(`language_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_oauth20. this table manage oauth key
DROP TABLE IF EXISTS  `gbd_oauth20`;
CREATE TABLE `gbd_oauth20` (
    `client_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `api_key` char(32) NOT NULL, 
    `api_secret` char(32) NOT NULL, 
    `api_version` char(11) NOT NULL, 
    `member_srl` bigint(11) NOT NULL, 
    `is_using_root` char(1) DEFAULT 'N', 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`api_key`, `is_using_root`), 
    INDEX(`member_srl`), 
    FOREIGN KEY( `member_srl`) REFERENCES `gbd_member`(`member_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`client_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create client_id for using local system
INSERT INTO `gbd_oauth20` (
    `api_key`,
    `api_secret`, 
    `api_version`, 
    `member_srl`, 
    `is_using_root`, 
    `c_date`
) VALUES (
    'e44f11e891d4c8afdd6ffbf7a0c03bd3', 
    '9c10a58811c6932fd388c0959b0ec112', 
    '000.000.001',
    1,  
    'Y',
    NOW()+0
);


# create gbd_oauth20_extra. this table manage extra information of using oauth client
DROP TABLE IF EXISTS  `gbd_oauth20_extra`;
CREATE TABLE `gbd_oauth20_extra` (
    `client_srl` bigint(11) NOT NULL, 
    `company_name` varchar(64) NOT NULL, 
    `manager_name` varchar(64) NOT NULL, 
    `manager_email` varchar(128) NOT NULL, 
    `company_address` varchar(256) DEFAULT NULL, 
    `country` varchar(8) DEFAULT NULL, 
    `country_call_code` varchar(8) DEFAULT NULL, 
    `mobile_phone_number` varchar(16) DEFAULT NULL, 
    `phone_number` varchar(16) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL,
    FOREIGN KEY( `client_srl`) REFERENCES `gbd_oauth20`(`client_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`client_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create oauth extra information of using local system client id
INSERT INTO `gbd_oauth20_extra` (
    `client_srl`, 
    `company_name`, 
    `manager_name`, 
    `manager_email`, 
    `c_date`
) VALUES (
    1, 
    'My Company', 
    '루트', 
    'dhkim94@gmail.com', 
    NOW()+0
);

# create gbd_oauth20_code. authorization, access_token 발급을 관리하는 테이블
DROP TABLE IF EXISTS  `gbd_oauth20_code`;
CREATE TABLE `gbd_oauth20_code` (
    `client_srl` bigint(11) NOT NULL, 
    `authorization_code` char(32) DEFAULT NULL, 
    `access_token` char(32) DEFAULT NULL, 
    `access_token_expire` char(14) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`access_token`),
    INDEX(`c_date`),
    INDEX(`client_srl`),
    FOREIGN KEY( `client_srl`) REFERENCES `gbd_oauth20`(`client_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`authorization_code`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_sessions. session table. PHP 기본 세션을 사용하지 않고 table 을 사용한다.
DROP TABLE IF EXISTS  `gbd_sessions`;
CREATE TABLE `gbd_sessions` (
    `session_id` varchar(40) DEFAULT '0' NOT NULL,
    `ip_address` varchar(16) DEFAULT '0' NOT NULL,
    `user_agent` varchar(120) NOT NULL,
    `last_activity` int(10) unsigned DEFAULT 0 NOT NULL,
    `user_data` text NOT NULL,
    PRIMARY KEY (`session_id`),
    KEY `last_activity_idx` (`last_activity`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_service. 지원하는 서비스를 관리하는 테이블
DROP TABLE IF EXISTS  `gbd_service`;
CREATE TABLE `gbd_service` (
    `service_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `service_id` char(32) NOT NULL, 
    `service_name` varchar(32) NOT NULL, 
    `controller` varchar(32) NOT NULL, 
    `controller_action` varchar(32) NOT NULL, 
    `image_mark` TEXT NULL, 
    `is_active` char(1) DEFAULT 'N', 
    `description` varchar(128) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`service_id`),
    PRIMARY KEY(`service_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# admin menu service 추가
INSERT INTO  `gbd_service` (
    `service_id`, 
    `service_name`, 
    `controller`, 
    `controller_action`, 
    `is_active`,
    `description`,
    `c_date`
) VALUES (
    '0a3a6699a06b4d52adcf2951e73bc68f',
    'Admin',  
    'admin', 
    'menu', 
    'Y', 
    'Admin menu tree', 
    NOW( ) +0
);

# member activity history service 추가
INSERT INTO  `gbd_service` (
    `service_id`, 
    `service_name`, 
    `controller`, 
    `controller_action`, 
    `is_active`,
    `description`,
    `c_date`
) VALUES (
    '729dee41711a01515177ab1c8100b431', 
    'Activity',  
    'activity', 
    'index', 
    'Y', 
    'Member acitivity history', 
    NOW( ) +0
);



# create gbd_service_group_service. member 그룹별로 service 매핑 테이블
DROP TABLE IF EXISTS  `gbd_service_group_service`;
CREATE TABLE `gbd_service_group_service` (
    `group_srl` bigint(11) NOT NULL, 
    `service_srl` bigint(11) NOT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX( `service_srl` ), 
    FOREIGN KEY( `group_srl`) REFERENCES `gbd_member_group`(`group_srl`) ON DELETE CASCADE, 
    FOREIGN KEY( `service_srl`) REFERENCES `gbd_service`(`service_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`group_srl`, `service_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# admin menu service 와 admin group 매핑
INSERT INTO `gbd_service_group_service` 
    ( `group_srl`, `service_srl`, `c_date` ) 
VALUES 
    ( 1, 1, NOW( ) +0 ),    # admin menu service 와 admin group 매핑
    ( 1, 2, NOW( ) +0 );    # member acitivity history service 와 admin group 매핑


# create gbd_menus. service 를 구성하는 menu 관리 테이블
DROP TABLE IF EXISTS  `gbd_menus`;
CREATE TABLE `gbd_menus` (
    `menu_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `menu_name` varchar(32) NOT NULL, 
    `menu_type` varchar(16) NOT NULL, 
    `menu_controller` varchar(16) DEFAULT '', 
    `menu_action` varchar(32) DEFAULT '', 
    `description` TEXT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    PRIMARY KEY(`menu_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# admin menu page insert
INSERT INTO `gbd_menus` 
    ( `menu_name`, `menu_type`, `menu_controller`, `menu_action`, `description`, `c_date` ) 
VALUES 
    ( '__usrLang1', 'folder', '', '', '__usrLang5', NOW()+0 ),                          # admin menu page insert
    ( '__usrLang2', 'dynamic', 'oauth', 'application_list', '__usrLang6', NOW()+0 ),    # admin menu page insert
    ( '__usrLang3', 'dynamic', 'oauth', 'code_list', '__usrLang7', NOW()+0 ),           # admin menu page insert
    ( '__usrLang4', 'dynamic', 'admin', 'language_texts', '__usrLang8', NOW()+0 ),      # 다국어 관리 메뉴 생성
    ( '__usrLang11', 'dynamic', 'admin', 'file_list', '__usrLang12', NOW()+0 ),         # 파일 관리 메뉴 생성
    ( '__usrLang13', 'folder', '', '', '__usrLang14', NOW()+0 ),         				# 회원 폴더 메뉴 생성
    ( '__usrLang13', 'dynamic', 'admin', 'member_list', '__usrLang14', NOW()+0 ),      	# 회원 메뉴 생성
    ( '__usrLang15', 'dynamic', 'admin', 'group_list', '__usrLang16', NOW()+0 );      	# 그룹 메뉴 생성


# create gbd_menu_tree. menu 를 보여주기 위해 menu 로 만든 tree 구조
DROP TABLE IF EXISTS  `gbd_menus_tree`;
CREATE TABLE `gbd_menus_tree` (
    `element_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `menu_srl` bigint(11) NOT NULL, 
    `service_srl` bigint(11) NOT NULL, 
    `parent_element_srl` bigint(11) NOT NULL, 
    `list_order` BIGINT( 11 ) NOT NULL DEFAULT  '1',
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`menu_srl`),
    INDEX(`service_srl`),
    FOREIGN KEY( `menu_srl`) REFERENCES `gbd_menus`(`menu_srl`) ON DELETE CASCADE, 
    FOREIGN KEY( `service_srl`) REFERENCES `gbd_service`(`service_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`element_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# admin menu item insert in tree
INSERT INTO `gbd_menus_tree` 
    ( `menu_srl`, `service_srl`, `parent_element_srl`, `list_order`, `c_date` ) 
VALUES 
    ( 1, 1, 0, 2, NOW()+0 ),  # admin menu item insert in tree
    ( 2, 1, 1, 1, NOW()+0 ),  # admin menu item insert in tree
    ( 3, 1, 1, 2, NOW()+0 ),  # admin menu item insert in tree
    ( 4, 1, 0, 3, NOW()+0 ),  # 다국어 관리 메뉴 추가
    ( 5, 1, 0, 4, NOW()+0 ),  # 파일 관리 메뉴 추가
    ( 6, 1, 0, 1, NOW()+0 ),  # 회원 폴더 메뉴 추가
    ( 7, 1, 6, 1, NOW()+0 ),  # 회원 메뉴 추가
    ( 8, 1, 6, 2, NOW()+0 );  # 회원 메뉴 추가


# create gbd_text. 다국어 텍스트 테이블
DROP TABLE IF EXISTS  `gbd_text`;
CREATE TABLE `gbd_text` (
    `text_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `name` char(37) NOT NULL, 
    `c_date` char(14) NOT NULL, 
    INDEX(`name`), 
    PRIMARY KEY(`text_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# 텍스트 메타 입력
INSERT INTO `gbd_text` 
    ( `name`, `c_date` ) 
VALUES 
    ( '__usrLang1', NOW()+0 ),  # 인증
    ( '__usrLang2', NOW()+0 ),  # 애플리케이션
    ( '__usrLang3', NOW()+0 ),  # 발급 코드
    ( '__usrLang4', NOW()+0 ),  # 다국어
    ( '__usrLang5', NOW()+0 ),  # oauth 인증 관리
    ( '__usrLang6', NOW()+0 ),  # oauth 사용을 위해 등록한 애플리케이션 리스트
    ( '__usrLang7', NOW()+0 ),  # oauth 를 위해 발급된 코드 리스트
    ( '__usrLang8', NOW()+0 ),  # 다국어 관리
    ( '__usrLang9', NOW()+0 ),  # 인증 확인을 실패 했습니다. 다시 로그인 해 주세요.
    ( '__usrLang10', NOW()+0 ), # 페이지 접근 권한이 없습니다(로그인 하지 않았거나, 접근 권한이 없는 페이지에 접근 했을때 안내 메시지)
    ( '__usrLang11', NOW()+0 ), # 파일
    ( '__usrLang12', NOW()+0 ), # 파일 관리
    ( '__usrLang13', NOW()+0 ), # 회원
    ( '__usrLang14', NOW()+0 ), # 회원 관리
    ( '__usrLang15', NOW()+0 ), # 그룹
    ( '__usrLang16', NOW()+0 ), # 그룹 관리
    ( '__usrLang17', NOW()+0 ), # 어드민
    ( '__usrLang18', NOW()+0 ), # 정회원
    ( '__usrLang19', NOW()+0 ); # 준회원


# create gbd_text_ko. 실제 텍스트가 저장되는 테이블. 각 언어 마다 테이블이 분리 됨
DROP TABLE IF EXISTS  `gbd_text_list`;
CREATE TABLE `gbd_text_list` (
	`text_list_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `text_srl` bigint(11) NOT NULL, 
    `lang_code` varchar(4) NOT NULL, 
    `text_value` varchar(128) DEFAULT NULL, 
    INDEX(`text_value`),
    INDEX(`text_srl`),
    FOREIGN KEY( `text_srl`) REFERENCES `gbd_text`(`text_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`text_list_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# 실제 텍스트 추가
INSERT INTO `gbd_text_list` 
    ( `text_srl`, `lang_code`, `text_value` )
VALUES 
    ( 1, 'ko', '인증' ),
    ( 1, 'en', 'Authorization' ),
    ( 2, 'ko', '애플리케이션' ),
    ( 2, 'en', 'Application' ),
    ( 3, 'ko', '발급 코드' ),
    ( 3, 'en', 'Authorization Code' ),
    ( 4, 'ko', '다국어' ),
    ( 4, 'en', 'Multi Language' ),
    ( 5, 'ko', 'oauth 인증 관리' ), 
    ( 5, 'en', 'oauth 인증 관리' ),
    ( 6, 'ko', 'oauth 사용을 위해 등록한 애플리케이션 리스트' ),
    ( 6, 'en', 'oauth 사용을 위해 등록한 애플리케이션 리스트' ),
    ( 7, 'ko', 'oauth 를 위해 발급된 코드 리스트' ),
    ( 7, 'en', 'oauth 를 위해 발급된 코드 리스트' ),
    ( 8, 'ko', '다국어 관리' ),
    ( 8, 'en', '다국어 관리' ),
    ( 9, 'ko', '인증 확인을 실패 했습니다. 다시 로그인 해 주세요.' ),
    ( 9, 'en', '인증 확인을 실패 했습니다. 다시 로그인 해 주세요.' ),
    ( 10, 'ko', '페이지 접근 권한이 없습니다.' ),
    ( 10, 'en', '페이지 접근 권한이 없습니다.' ),
    ( 11, 'ko', '파일' ),
    ( 11, 'en', '파일' ),
    ( 12, 'ko', '파일 관리' ),
    ( 12, 'en', '파일 관리' ),
    ( 13, 'ko', '회원' ),
    ( 13, 'en', '회원' ),
    ( 14, 'ko', '회원 관리' ),
    ( 14, 'en', '회원 관리' ),
    ( 15, 'ko', '그룹' ),
    ( 15, 'en', '그룹' ),
    ( 16, 'ko', '그룹 관리' ),
    ( 16, 'en', '그룹 관리' ),
    ( 17, 'ko', '어드민' ),
    ( 17, 'en', 'Admin' ),
    ( 18, 'ko', '정회원' ),
    ( 18, 'en', '정회원' ),
    ( 19, 'ko', '준회원' ),
    ( 19, 'en', '준회원' );


