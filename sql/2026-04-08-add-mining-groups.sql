CREATE TABLE IF NOT EXISTS `mining_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(128) NOT NULL,
  `title` varchar(255) NOT NULL,
  `algo` varchar(32) NOT NULL,
  `mode` enum('merge','dedicated','switch') NOT NULL DEFAULT 'dedicated',
  `description` text DEFAULT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `primary_coinid` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sortorder` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `updated` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `algo_active` (`algo`,`active`),
  KEY `primary_coinid` (`primary_coinid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mining_group_coins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `coinid` int(11) NOT NULL,
  `role` enum('primary','aux','member') NOT NULL DEFAULT 'member',
  `required` tinyint(1) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `updated` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_coin` (`group_id`,`coinid`),
  KEY `coinid` (`coinid`),
  KEY `group_position` (`group_id`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @now := UNIX_TIMESTAMP();

INSERT INTO `mining_groups`
(`slug`, `title`, `algo`, `mode`, `description`, `hostname`, `port`, `primary_coinid`, `active`, `sortorder`, `created`, `updated`)
SELECT
  'scrypt-qntm-dedicated',
  'QNTM Dedicated',
  'scrypt',
  'dedicated',
  'Dedicated QNTM stratum with native per-coin payout routing.',
  NULL,
  c.dedicatedport,
  c.id,
  1,
  100,
  @now,
  @now
FROM coins c
WHERE c.symbol = 'QNTM'
  AND NOT EXISTS (SELECT 1 FROM mining_groups mg WHERE mg.slug = 'scrypt-qntm-dedicated');

INSERT INTO `mining_groups`
(`slug`, `title`, `algo`, `mode`, `description`, `hostname`, `port`, `primary_coinid`, `active`, `sortorder`, `created`, `updated`)
SELECT
  'scrypt-nxe-dedicated',
  'NXE Dedicated',
  'scrypt',
  'dedicated',
  'Dedicated NXE stratum with native per-coin payout routing.',
  NULL,
  c.dedicatedport,
  c.id,
  1,
  110,
  @now,
  @now
FROM coins c
WHERE c.symbol = 'NXE'
  AND NOT EXISTS (SELECT 1 FROM mining_groups mg WHERE mg.slug = 'scrypt-nxe-dedicated');

INSERT INTO `mining_group_coins`
(`group_id`, `coinid`, `role`, `required`, `position`, `created`, `updated`)
SELECT mg.id, c.id, 'primary', 1, 0, @now, @now
FROM mining_groups mg
INNER JOIN coins c ON c.symbol = 'QNTM'
WHERE mg.slug = 'scrypt-qntm-dedicated'
  AND NOT EXISTS (
    SELECT 1 FROM mining_group_coins mgc
    WHERE mgc.group_id = mg.id AND mgc.coinid = c.id
  );

INSERT INTO `mining_group_coins`
(`group_id`, `coinid`, `role`, `required`, `position`, `created`, `updated`)
SELECT mg.id, c.id, 'primary', 1, 0, @now, @now
FROM mining_groups mg
INNER JOIN coins c ON c.symbol = 'NXE'
WHERE mg.slug = 'scrypt-nxe-dedicated'
  AND NOT EXISTS (
    SELECT 1 FROM mining_group_coins mgc
    WHERE mgc.group_id = mg.id AND mgc.coinid = c.id
  );
