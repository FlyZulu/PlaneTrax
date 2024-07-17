CREATE DATABASE adsb;

USE adsb;

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



CREATE TABLE `ops_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_date` DATETIME DEFAULT NULL,
  `now` varchar(100) DEFAULT NULL,
  `hex` varchar(100) DEFAULT NULL,
  `flight` varchar(100) DEFAULT NULL,
  `runway` varchar(3) DEFAULT NULL,
  `op` varchar(1) DEFAULT NULL,
  `tg` varchar(1) DEFAULT NULL,
  `distance` varchar(100) DEFAULT NULL,
  `altitude` varchar(100) DEFAULT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL,
  `track` varchar(100) DEFAULT NULL,
  `speed` varchar(100) DEFAULT NULL,
  `vert_rate` varchar(100) DEFAULT NULL,
  `seen_pos` varchar(100) DEFAULT NULL,
  `seen` varchar(100) DEFAULT NULL,
  `rssi` varchar(100) DEFAULT NULL,
  `messages` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `squawk` varchar(100) DEFAULT NULL,
  `nucp` varchar(100) DEFAULT NULL,
  `mlat` varchar(100) DEFAULT NULL,
  `tisb` varchar(100) DEFAULT NULL,
  `rec_msg_sec` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;
