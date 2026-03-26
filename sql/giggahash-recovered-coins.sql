-- GiggaHash Recovered Coins Seed
-- Created from disk recovery: /var/lib/mysql/yaamp/coins.ibd
-- Source: strings output + check-coin-sync.sh

-- ============================================================================
-- BTC (id=7) - Already exists in base export
-- ============================================================================

-- ============================================================================
-- LTC (id=8) - Already exists in base export (may need port update)
-- ============================================================================

-- ============================================================================
-- DOGE (id=9) - Scrypt pool
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip, 
                   install, auto_ready, mining) 
VALUES (9, 'DOGE', 'Dogecoin', 'scrypt', 0, 1, 0, 22555, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=22555, rpcuser='opencex', rpcpassword='opencex';

-- ============================================================================
-- DGB (id=11) - Multi-algo
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (11, 'DGB', 'DigiByte', 'sha256d', 0, 1, 0, 14022, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='sha256d', rpcport=14022;

-- DGB SCRYPT
INSERT INTO coins (symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES ('DGB', 'DigiByte', 'scrypt', 0, 1, 0, 14023, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=14023;

-- DGB QIT
INSERT INTO coins (symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES ('DGB', 'DigiByte', 'quark', 0, 1, 0, 14024, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='quark', rpcport=14024;

-- ============================================================================
-- VIA (id=411) - Viacoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (411, 'VIA', 'Viacoin', 'scrypt', 0, 1, 0, 5222, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=5222;

-- ============================================================================
-- EMC2 (id=412) - Einsteinium
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (412, 'EMC2', 'Einsteinium', 'scrypt', 0, 1, 0, 4188, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=4188;

-- ============================================================================
-- GAME (id=413) - GameCredits
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (413, 'GAME', 'GameCredits', 'scrypt', 0, 1, 0, 40001, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=40001;

-- ============================================================================
-- MONA (id=14) - Monacoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (14, 'MONA', 'Monacoin', 'lyra2re2', 0, 1, 0, 9401, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='lyra2re2', rpcport=9401;

-- ============================================================================
-- GLC (id=15) - Goldcoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (15, 'GLC', 'Goldcoin', 'scrypt', 0, 1, 0, 8122, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=8122;

-- ============================================================================
-- FLO (id=17) - Florincoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (17, 'FLO', 'Florincoin', 'scrypt', 0, 1, 0, 7313, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=7313;

-- ============================================================================
-- SOH (id=18) - Source Header
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (18, 'SOH', 'Source Header', 'x11', 0, 1, 0, 32717, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='x11', rpcport=32717;

-- ============================================================================
-- PEP (id=19) - Peoplecoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (19, 'PEP', 'Peoplecoin', 'x11', 0, 1, 0, 33873, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='x11', rpcport=33873;

-- ============================================================================
-- BELLS (id=20) - Lomocoin/BELLS
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (20, 'BELLS', 'Lomocoin', 'scrypt', 0, 1, 0, 19918, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=19918;

-- ============================================================================
-- IFC (id=410) - Infinitecoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (410, 'IFC', 'Infinitecoin', 'scrypt', 0, 1, 0, 9322, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=9322;

-- ============================================================================
-- NXE (id=414) - Nexa
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (414, 'NXE', 'Nexa', 'x11', 0, 1, 0, 29432, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='x11', rpcport=29432;

-- ============================================================================
-- CAT (id=409) - Catcoin
-- ============================================================================
INSERT INTO coins (id, symbol, name, algo, enabled, visible, balance, 
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining)
VALUES (409, 'CAT', 'Catcoin', 'scrypt', 0, 1, 0, 9335, 'opencex', 'opencex', 'UTF-8', '127.0.0.1',
        0, 0, 0)
ON DUPLICATE KEY UPDATE 
    algo='scrypt', rpcport=9335;

-- ============================================================================
-- Enable coins after confirming wallet sync
-- Example: UPDATE coins SET enabled=1, install=1 WHERE symbol='BTC';
-- ============================================================================
