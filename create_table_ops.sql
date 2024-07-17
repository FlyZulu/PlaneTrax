
CREATE TABLE `ops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `op_date` DATETIME DEFAULT NULL,
  `flight` varchar(7) DEFAULT NULL,
  `runway` varchar(3) DEFAULT NULL,
  `op` varchar(1) DEFAULT NULL,
  `tg` varchar(1) DEFAULT NULL,
  `exp` bool DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;
