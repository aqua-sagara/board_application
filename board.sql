CREATE TABLE `board` (
  `id_board` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(20) DEFAULT '0',
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `text` varchar(500) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  PRIMARY KEY (`id_board`),
  KEY `id_board` (`id_board`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;