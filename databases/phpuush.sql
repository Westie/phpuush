SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `uploads`
-- ----------------------------
DROP TABLE IF EXISTS `uploads`;
CREATE TABLE `uploads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` int(10) unsigned NOT NULL,
  `alias` varchar(4) NOT NULL,
  `protect_alias` varchar(6) DEFAULT NULL,
  `file_name` varchar(256) NOT NULL,
  `file_location` varchar(256) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `file_hash` varchar(32) NOT NULL,
  `mime_type` varchar(256) DEFAULT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `views` int(10) unsigned DEFAULT NULL,
  `is_deleted` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uploads
-- ----------------------------

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_address` varchar(256) NOT NULL,
  `password` varchar(40) NOT NULL,
  `api_key` varchar(40) DEFAULT NULL,
  `is_deleted` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
