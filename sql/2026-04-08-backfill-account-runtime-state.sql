INSERT INTO `account_addresses` (`account_id`, `coinid`, `address`, `created`, `updated`)
SELECT `id`, `coinid`, `username`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `accounts`
WHERE `coinid` IS NOT NULL AND `coinid` > 0 AND `username` IS NOT NULL AND `username` != ''
ON DUPLICATE KEY UPDATE
  `address` = VALUES(`address`),
  `updated` = VALUES(`updated`);

INSERT INTO `account_balances` (`account_id`, `coinid`, `balance`, `created`, `updated`)
SELECT A.`id`, A.`coinid`, A.`balance`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `accounts` A
WHERE A.`coinid` IS NOT NULL AND A.`coinid` > 0 AND A.`balance` > 0
  AND NOT EXISTS (SELECT 1 FROM `account_balances` B WHERE B.`account_id` = A.`id`)
ON DUPLICATE KEY UPDATE
  `updated` = VALUES(`updated`);
