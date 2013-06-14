# create gbd_sites table. this table manage site url
DROP TABLE IF EXISTS  `gbd_sites`;
CREATE TABLE  `gbd_sites` (
    `site_srl` BIGINT( 11 ) NOT NULL AUTO_INCREMENT ,
    `index_module_srl` BIGINT( 11 ) NOT NULL DEFAULT  '0',
    `domain` VARCHAR( 256 ) NOT NULL ,
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
INSERT INTO  `gbd_sites` (`domain`, `default_language`, `c_date` ) 
VALUES ('gboard.org',  'ko', NOW( ) +0);


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
VALUES ( 1,  "default_root_group",  "Y", NOW( ) +0 ) ;

# create gboard formal member group
INSERT INTO  `gbd_member_group` (  `site_srl` ,  `title` ,  `list_order` , `c_date` ) 
VALUES ( 2,  "default_formal_member_group",  2, NOW( ) +0 ) ;

# create gboard ready member group
INSERT INTO  `gbd_member_group` (  `site_srl` ,  `title` ,  `is_default` , `list_order` ,  `c_date` ) 
VALUES ( 2,  "default_ready_member_group",  "Y", 3, NOW( ) +0 ) ;


# create gbd_member table. this table manage member
CREATE TABLE `gbd_member` ( 
    `member_srl` bigint(11) NOT NULL AUTO_INCREMENT, 
    `user_id` varchar(128) NOT NULL, 
    `email_address` varchar(128) NOT NULL, 
    `password` varchar(64) NOT NULL, 
    `user_name` varchar(64) NOT NULL, 
    `nick_name` varchar(64) NOT NULL, 
    `social_type` varchar(8) DEFAULT NULL,
    `find_account_question` bigint(11) DEFAULT NULL, 
    `find_account_answer` varchar(256) DEFAULT NULL, 
    `homepage` varchar(256) DEFAULT NULL, 
    `blog` varchar(256) DEFAULT NULL, 
    `birthday` char(8) DEFAULT NULL, 
    `allow_mailing` char(2) NOT NULL DEFAULT 'N', 
    `allow_message` char(2) NOT NULL DEFAULT 'N', 
    `image_mark` TEXT NULL , 
    `block` char(2) NOT NULL DEFAULT 'N', 
    `description` text, 
    `list_order` bigint(11) NOT NULL DEFAULT '1', 
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
ALTER TABLE  `gbd_member` ADD INDEX (  `birthday` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `list_order` ) ;
ALTER TABLE  `gbd_member` ADD INDEX (  `c_date` ) ;

# create gboard root account
INSERT INTO `gbd_member` (
    `user_id`, 
    `email_address`, 
    `password`, 
    `user_name`, 
    `nick_name`, 
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
    "Y",
    NOW( ) +0,
    NOW( ) +0,
    NOW( ) +0
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
);

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

