-- Native yiiMP integration for StarShip on the custom SpaceScrypt algorithm.
-- Safe to run multiple times.

INSERT INTO `algos` (`name`, `color`, `speedfactor`, `port`, `visible`, `powlimit_bits`)
VALUES ('spacescrypt', '#91b9ff', 1.0, 12478, 1, 32)
ON DUPLICATE KEY UPDATE
    `color` = VALUES(`color`),
    `speedfactor` = VALUES(`speedfactor`),
    `port` = VALUES(`port`),
    `visible` = VALUES(`visible`),
    `powlimit_bits` = VALUES(`powlimit_bits`);

UPDATE `coins`
SET
    `symbol` = 'SPSC',
    `name` = 'StarShip',
    `algo` = 'spacescrypt',
    `rpcencoding` = 'POW',
    `dedicatedport` = 12478,
    `enable` = 1,
    `visible` = 1
WHERE `symbol` = 'STSP'
   OR (`algo` = 'spacescrypt' AND LOWER(`name`) = 'starship');

INSERT INTO `coins` (
    `symbol`, `name`, `algo`, `enable`, `visible`, `installed`, `auto_ready`,
    `rpcport`, `rpcuser`, `rpcpasswd`, `rpcencoding`, `rpchost`,
    `dedicatedport`, `hasgetinfo`, `hassubmitblock`, `block_time`
)
VALUES (
    'SPSC', 'StarShip', 'spacescrypt', 1, 1, 1, 1,
    12855, 'starshiprpc', 'change-me-live', 'POW', '127.0.0.1',
    12478, 0, 1, 96
)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `algo` = VALUES(`algo`),
    `enable` = VALUES(`enable`),
    `visible` = VALUES(`visible`),
    `installed` = VALUES(`installed`),
    `auto_ready` = VALUES(`auto_ready`),
    `rpcport` = VALUES(`rpcport`),
    `rpcuser` = VALUES(`rpcuser`),
    `rpcencoding` = VALUES(`rpcencoding`),
    `rpchost` = VALUES(`rpchost`),
    `dedicatedport` = VALUES(`dedicatedport`),
    `hasgetinfo` = VALUES(`hasgetinfo`),
    `hassubmitblock` = VALUES(`hassubmitblock`),
    `block_time` = VALUES(`block_time`);
