
CREATE TABLE `ops_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_date` DATETIME DEFAULT NULL,
  `flight` varchar(10) DEFAULT NULL,
  `runway` varchar(3) DEFAULT NULL,
  `op` varchar(1) DEFAULT NULL,
  `tg` varchar(1) DEFAULT NULL,
  `altitude` varchar(10) DEFAULT NULL,
  `lat` varchar(10) DEFAULT NULL,
  `lon` varchar(10) DEFAULT NULL,
  `track` varchar(10) DEFAULT NULL,
  `speed` varchar(10) DEFAULT NULL,
  `vert_rate` varchar(10) DEFAULT NULL,
  `notes` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;
