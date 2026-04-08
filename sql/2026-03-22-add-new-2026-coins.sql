-- ============================================================================
-- Add Supported 2026 PoW Coins
-- Date: 2026-03-22
-- Coin: QNTM
-- ============================================================================

-- ============================================================================
-- QNTM - Quantum Token (Scrypt)
-- ANN: https://bitcointalk.org/index.php?topic=5576829.0
-- Website: https://quantum-token.net/
-- GitHub: https://github.com/QuantumTokenGit/Source
-- Algorithm: Scrypt PoW, Block Time: 60 seconds, Max Supply: 50,000,000
-- Wallet Downloads: https://quantum-token.net/
-- ============================================================================
INSERT INTO coins (symbol, name, algo, enable, visible, balance,
                   rpcport, rpcuser, rpcpasswd, rpcencoding, rpchost,
                   installed, auto_ready, hasgetinfo, block_time)
VALUES ('QNTM', 'Quantum Token', 'scrypt', 0, 1, 0,
        17335, 'qntm', 'Qn7mT0k3n#Rpc$W8x3pLv1', 'UTF-8', '127.0.0.1',
        1, 1, 1, 60)
ON DUPLICATE KEY UPDATE
    name='Quantum Token',
    algo='scrypt',
    rpcport=17335,
    rpcpasswd='Qn7mT0k3n#Rpc$W8x3pLv1',
    installed=1,
    hasgetinfo=1,
    block_time=60;

-- ============================================================================
-- Notes for pool operator:
--
-- QNTM (Scrypt):
--   - Scrypt PoW like Litecoin/Dogecoin
--   - Compatible with Scrypt ASICs
--   - Block reward: 100 QNTM
--   - Block time: 60 seconds
--
-- To enable the wallet rows after wallet setup:
--   UPDATE coins SET enable=1, installed=1, auto_ready=1
--   WHERE symbol = 'QNTM';
-- ============================================================================
