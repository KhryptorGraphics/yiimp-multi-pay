-- Normalize pool metadata for the currently integrated GiggaHash coins.
-- This is idempotent and safe to re-run.
-- Important: only QNTM currently matches a standard YiiMP wallet-backed integration model.

INSERT INTO `algos` (`name`, `color`, `speedfactor`, `port`, `visible`, `powlimit_bits`)
VALUES ('sha256d', '#d0d0f0', 0.001, 13334, 1, 32)
ON DUPLICATE KEY UPDATE
    `color` = VALUES(`color`),
    `speedfactor` = VALUES(`speedfactor`),
    `port` = VALUES(`port`),
    `visible` = VALUES(`visible`),
    `powlimit_bits` = VALUES(`powlimit_bits`);

UPDATE `coins`
SET `installed` = 1
WHERE `symbol` IN ('QNTM')
  AND IFNULL(`installed`, 0) = 0;

UPDATE `coins`
SET `block_time` = 600
WHERE `symbol` = 'BTC'
  AND (`block_time` IS NULL OR `block_time` = 0);

UPDATE `coins`
SET `block_time` = 15
WHERE `symbol` = 'DGB'
  AND (`block_time` IS NULL OR `block_time` = 0);

UPDATE `coins`
SET `block_time` = 60
WHERE `symbol` = 'QNTM'
  AND (`block_time` IS NULL OR `block_time` = 0);
