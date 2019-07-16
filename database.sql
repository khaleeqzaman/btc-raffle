SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `bitcoin`;
CREATE TABLE `bitcoin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL,
  `bitcoin_amount` decimal(9,8) NOT NULL,
  `item` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `tickets` varchar(255) NOT NULL DEFAULT '0',
  `sponsor` int(11) DEFAULT NULL,
  `hits` int(255) NOT NULL DEFAULT 0,
  `ip` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `foo` (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `transaction` varchar(255) NOT NULL,
  UNIQUE KEY `transaction` (`transaction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `winners`;
CREATE TABLE `winners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `paid_txid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;