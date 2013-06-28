# create gbd_sites table. this table manage site url
DROP TABLE IF EXISTS  `gbd_sites`;
CREATE TABLE  `gbd_sites` (
    `site_srl` BIGINT( 11 ) NOT NULL AUTO_INCREMENT ,
    `index_module_srl` BIGINT( 11 ) NOT NULL DEFAULT  '0',
    `domain` VARCHAR( 256 ) NOT NULL ,
    `is_default` CHAR(2) DEFAULT 'N', 
    `default_language` VARCHAR( 8 ) DEFAULT NULL ,
    `image_mark` TEXT,
    `description` TEXT,
    `list_order` BIGINT( 11 ) NOT NULL DEFAULT  '1',
    `c_date` CHAR( 14 ) NOT NULL ,
    `u_date` CHAR( 14 ) DEFAULT NULL ,
    PRIMARY KEY (  `site_srl` ) ,
    KEY  `list_order` (  `list_order` )
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create gboard root site. site URL is admin.gboard.org
INSERT INTO  `gbd_sites` (`domain`, `default_language`, `c_date` ) 
VALUES ('admin.gboard.org',  'ko', NOW( ) +0);

# create gboard default service site. site URL is gboard.org
INSERT INTO  `gbd_sites` (`domain`, `is_default`, `default_language`, `list_order`, `c_date` ) 
VALUES ('gboard.org',  `Y`, 'ko', -2, NOW( ) +0);


# create gbd_member_group table. this table manage group
DROP TABLE IF EXISTS  `gbd_member_group` ;
CREATE TABLE  `gbd_member_group` (
    `group_srl` BIGINT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `site_srl` BIGINT( 11 ) NOT NULL ,
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
INSERT INTO  `gbd_member_group` (  `site_srl` ,  `title` ,  `is_root` ,  `c_date` ) 
VALUES ( 1,  "default_group_root",  "Y", NOW( ) +0 ) ;

# create gboard formal member group
INSERT INTO  `gbd_member_group` (  `site_srl` ,  `title` ,  `list_order` , `c_date` ) 
VALUES ( 2,  "default_group_formal_member",  2, NOW( ) +0 ) ;

# create gboard ready member group
INSERT INTO  `gbd_member_group` (  `site_srl` ,  `title` ,  `is_default` , `list_order` ,  `c_date` ) 
VALUES ( 2,  "default_group_ready_member",  "Y", 3, NOW( ) +0 ) ;


# create gbd_member table. this table manage member
CREATE TABLE `gbd_member` ( 
    `member_srl` bigint(11) NOT NULL AUTO_INCREMENT, 
    `user_id` varchar(128) NOT NULL, 
    `email_address` varchar(128) NOT NULL, 
    `password` varchar(128) NOT NULL, 
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
ALTER TABLE  `gbd_member` ADD INDEX (  `nick_name` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `list_order` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `c_date` ) ;

# create gboard root account
INSERT INTO `gbd_member` (
    `user_id`, 
    `email_address`, 
    `password`, 
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
    "123",
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
    `password`, 
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
    "123",
    "노바디",
    "노바디",
    -2, 
    "Y",
    NOW( ) +0,
    NOW( ) +0,
    NOW( ) +0
);


# create gbd_member_extra. this table manage member's extra information
CREATE TABLE `gbd_member_extra` (
	`member_srl` bigint(11) NOT NULL, 
    `homepage` varchar(256) DEFAULT NULL, 
    `blog` varchar(256) DEFAULT NULL, 
    `birthday` char(8) DEFAULT NULL, 
    `gender` char(2) DEFAULT NULL, 
    `country` varchar(8) DEFAULT NULL, 
    `country_phone_number` varchar(8) DEFAULT NULL, 
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
CREATE TABLE `gbd_member_group_member` (
    `group_srl` bigint(11) NOT NULL, 
    `member_srl` bigint(11) NOT NULL, 
    `site_srl` bigint(11) NOT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX( `group_srl` ),
    INDEX( `member_srl` ), 
    INDEX( `site_srl` ), 
    FOREIGN KEY( `group_srl`) REFERENCES `gbd_member_group`(`group_srl`) ON DELETE CASCADE, 
    FOREIGN KEY( `member_srl`) REFERENCES `gbd_member`(`member_srl`) ON DELETE CASCADE, 
    FOREIGN KEY( `site_srl`) REFERENCES `gbd_sites`(`site_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`group_srl`, `member_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# mapping between gboard root group and gboard root account
INSERT INTO `gbd_member_group_member`( 
    `group_srl`, 
    `member_srl`, 
    `site_srl`, 
    `c_date`
) 
VALUES (
    1, 
    1, 
    1, 
    NOW() +0
);

# mapping between gboard normal group and gboard system default account
INSERT INTO `gbd_member_group_member`( 
    `group_srl`, 
    `member_srl`, 
    `site_srl`, 
    `c_date`
) 
VALUES (
    2, 
    2, 
    1, 
    NOW() +0
);


# create gbd_files. this table manage uploaded file
CREATE TABLE `gbd_files` (
    `file_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `member_srl` bigint(11) NOT NULL, 
    `download_count` bigint(11) DEFAULT '0', 
    `file_type` varchar(128) NOT NULL, 
    `orig_name` varchar(256) DEFAULT NULL, 
    `local_path` varchar(128) DEFAULT NULL, 
    `local_url` varchar(256) DEFAULT NULL, 
    `network_url` varchar(256) DEFAULT NULL, 
    `width` int(8) DEFAULT '0', 
    `height` int(8) DEFAULT '0', 
    `file_size` bigint(11) DEFAULT '0', 
    `comment` varchar(256) DEFAULT NULL, 
    `thumbnail_local_path` varchar(128) DEFAULT NULL, 
    `thumbnail_local_url` varchar(256) DEFAULT NULL, 
    `thumbnail_network_url` varchar(256) DEFAULT NULL, 
    `thumbnail_width` int(8) DEFAULT '0', 
    `thumbnail_height` int(8) DEFAULT '0', 
    `ipaddress` varchar(32) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX( `member_srl` ), 
    FOREIGN KEY( `member_srl`) REFERENCES `gbd_member`(`member_srl`) ON DELETE CASCADE, 
    PRIMARY KEY(`file_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_country_code. this table manage country code
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
CREATE TABLE `gbd_language_code` (
    `language_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `alpha2` char(2) DEFAULT NULL, 
    `alpha3` char(3) NOT NULL, 
    `name` varchar(32) DEFAULT NULL, 
    `description1` varchar(128) DEFAULT NULL, 
    `description2` varchar(128) DEFAULT NULL, 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`alpha2`), 
    INDEX(`alpha3`), 
    PRIMARY KEY(`language_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;


# create gbd_oauth20. this table manage oauth key
CREATE TABLE `gbd_oauth20` (
    `client_srl` bigint(11) NOT NULL AUTO_INCREMENT , 
    `api_key` char(32) NOT NULL, 
    `api_secret` char(32) NOT NULL, 
    `api_version` char(11) NOT NULL, 
    `is_using_root` char(1) DEFAULT 'N', 
    `c_date` char(14) NOT NULL, 
    `u_date` char(14) DEFAULT NULL, 
    INDEX(`api_key`, `is_using_root`),
    PRIMARY KEY(`client_srl`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

# create client_id for using local system
INSERT INTO `gbd_oauth20` (
    `api_key`,
    `api_secret`, 
    `api_version`, 
    `is_using_root`, 
    `c_date`
) VALUES (
    "e44f11e891d4c8afdd6ffbf7a0c03bd3", 
    "9c10a58811c6932fd388c0959b0ec112", 
    "000.000.001", 
    "Y",
    NOW()+0
);


# create gbd_oauth20_extra. this table manage extra information of using oauth client
CREATE TABLE `gbd_oauth20_extra` (
    `client_srl` bigint(11) NOT NULL, 
    `company_name` varchar(64) NOT NULL, 
    `manager_name` varchar(64) NOT NULL, 
    `manager_email` varchar(128) NOT NULL, 
    `company_address` varchar(256) DEFAULT NULL, 
    `country` varchar(8) DEFAULT NULL, 
    `country_phone_number` varchar(8) DEFAULT NULL, 
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
    "system", 
    "루트", 
    "dhkim94@gmail.com", 
    NOW()+0
);

# create gbd_oauth20_code. authorization, access_token 발급을 관리하는 테이블
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
