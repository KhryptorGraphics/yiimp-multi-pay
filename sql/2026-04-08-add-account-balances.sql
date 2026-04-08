CREATE TABLE `account_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `coinid` int(11) NOT NULL,
  `balance` double NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `updated` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_balances_account_coin` (`account_id`,`coinid`),
  KEY `account_balances_coinid` (`coinid`),
  CONSTRAINT `fk_account_balances_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_account_balances_coin` FOREIGN KEY (`coinid`) REFERENCES `coins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO `account_balances` (`account_id`, `coinid`, `balance`, `created`, `updated`)
SELECT `id`, `coinid`, `balance`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `accounts`
WHERE `balance` > 0 AND `coinid` IS NOT NULL AND `coinid` > 0
ON DUPLICATE KEY UPDATE
  `balance` = VALUES(`balance`),
  `updated` = VALUES(`updated`);
