-- ============================================================================
-- Add New 2026 PoW Coins
-- Date: 2026-03-22
-- Coins: SILVR, BricsCoin, QNTM, NexaFlow
-- ============================================================================

-- ============================================================================
-- SILVR (SILVR) - SHA-256d Proof-of-Work
-- ANN: https://bitcointalk.org/index.php?topic=5577498.0
-- GitHub: https://github.com/him-SILVR/SILVR
-- Block time: 300 seconds, P2P Port: 8633, Chain ID: 2026
-- ============================================================================
INSERT INTO coins (symbol, name, algo, enabled, visible, balance,
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining, hasgetinfo)
VALUES ('SILVR', 'Silver Protocol', 'sha256d', 0, 1, 0,
        17333, 'silvr', 'S1lv3r#Rpc$K9x2mNq8', 'UTF-8', '127.0.0.1',
        1, 1, 0, 1)
ON DUPLICATE KEY UPDATE
    name='Silver Protocol',
    algo='sha256d',
    rpcport=17333,
    rpcpassword='S1lv3r#Rpc$K9x2mNq8',
    hasgetinfo=1;

-- ============================================================================
-- BricsCoin (BRICS) - SHA-256 Proof-of-Work
-- ANN: https://bitcointalk.org/index.php?topic=5577339.0
-- Source: https://codeberg.org/Bricscoin_26/Bricscoin
-- Algorithm: SHA-256 PoW, Max Supply: 21,000,000 BRICS
-- ============================================================================
INSERT INTO coins (symbol, name, algo, enabled, visible, balance,
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining, hasgetinfo)
VALUES ('BRICS', 'BricsCoin', 'sha256', 0, 1, 0,
        17334, 'brics', 'Br1csC0in#Rpc$J5k9mNv2', 'UTF-8', '127.0.0.1',
        1, 1, 0, 1)
ON DUPLICATE KEY UPDATE
    name='BricsCoin',
    algo='sha256',
    rpcport=17334,
    rpcpassword='Br1csC0in#Rpc$J5k9mNv2',
    hasgetinfo=1;

-- ============================================================================
-- QNTM - Quantum Token (Scrypt)
-- ANN: https://bitcointalk.org/index.php?topic=5576829.0
-- Website: https://quantum-token.net/
-- GitHub: https://github.com/QuantumTokenGit/Source
-- Algorithm: Scrypt PoW, Block Time: 60 seconds, Max Supply: 50,000,000
-- Wallet Downloads: https://quantum-token.net/
-- ============================================================================
INSERT INTO coins (symbol, name, algo, enabled, visible, balance,
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining, hasgetinfo)
VALUES ('QNTM', 'Quantum Token', 'scrypt', 0, 1, 0,
        17335, 'qntm', 'Qn7mT0k3n#Rpc$W8x3pLv1', 'UTF-8', '127.0.0.1',
        1, 1, 0, 1)
ON DUPLICATE KEY UPDATE
    name='Quantum Token',
    algo='scrypt',
    rpcport=17335,
    rpcpassword='Qn7mT0k3n#Rpc$W8x3pLv1',
    hasgetinfo=1;

-- ============================================================================
-- NXF - NexaFlow (double-SHA-256 for Programmable Micro Coins)
-- ANN: https://bitcointalk.org/index.php?topic=5576225.0
-- Website: https://nfledger.com/
-- GitHub: https://github.com/nexaflow-ledger
-- Algorithm: double-SHA-256 PoW (for PMC mining)
-- Note: Main NXF uses RPCA/BFT consensus. PMC uses double-SHA256 PoW mining.
-- ============================================================================
INSERT INTO coins (symbol, name, algo, enabled, visible, balance,
                   rpcport, rpcuser, rpcpassword, rpcencoding, rpcip,
                   install, auto_ready, mining, hasgetinfo)
VALUES ('NXF', 'NexaFlow', 'sha256d', 0, 1, 0,
        17336, 'nxf', 'N3x4Fl0w#Rpc$M2k7qRv9', 'UTF-8', '127.0.0.1',
        1, 1, 0, 1)
ON DUPLICATE KEY UPDATE
    name='NexaFlow',
    algo='sha256d',
    rpcport=17336,
    rpcpassword='N3x4Fl0w#Rpc$M2k7qRv9',
    hasgetinfo=1;

-- ============================================================================
-- Update stratum config for sha256dt to support double-SHA256 coins
-- The sha256d stratum already exists at port 3338, we just enable it
-- ============================================================================

-- ============================================================================
-- Notes for pool operator:
-- 
-- SILVR (SHA-256d):
--   - P2P Port: 8633
--   - Uses standard getblocktemplate RPC
--   - Compatible with SHA-256 ASICs
--
-- BricsCoin (SHA-256):
--   - Uses SHA-256 PoW like Bitcoin
--   - Compatible with standard SHA-256 miners
--
-- QNTM (Scrypt):
--   - Scrypt PoW like Litecoin/Dogecoin
--   - Compatible with Scrypt ASICs
--   - Block reward: 100 QNTM
--   - Block time: 60 seconds
--
-- NexaFlow NXF (double-SHA-256 for PMC):
--   - Main coin uses RPCA consensus (no mining)
--   - Programmable Micro Coins (PMC) use double-SHA256 PoW
--   - PMC mining requires wallet-side stratum server
--
-- To enable mining after wallet setup:
--   UPDATE coins SET mining=1 WHERE symbol IN ('SILVR','BRICS','QNTM','NXF');
--   UPDATE coins SET enabled=1 WHERE symbol IN ('SILVR','BRICS','QNTM','NXF');
-- ============================================================================
