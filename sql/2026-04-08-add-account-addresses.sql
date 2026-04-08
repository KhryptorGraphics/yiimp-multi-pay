CREATE TABLE `account_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `coinid` int(11) NOT NULL,
  `address` varchar(128) NOT NULL,
  `created` int(11) NOT NULL DEFAULT 0,
  `updated` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_coin` (`account_id`,`coinid`),
  KEY `coin_address` (`coinid`,`address`),
  CONSTRAINT `fk_account_addresses_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_account_addresses_coin` FOREIGN KEY (`coinid`) REFERENCES `coins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
