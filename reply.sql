CREATE TABLE `reply` (
  `id_board` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `user_name` varchar(20) DEFAULT 'CURRENT_TIMESTAMP',
  `text` varchar(500) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `id_reply` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reply`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
