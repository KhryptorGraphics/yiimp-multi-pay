-- Remove coins that are not standard full-RPC wallet implementations
-- compatible with this YiiMP deployment.

DELETE FROM coins
WHERE symbol IN ('SILVR', 'BRICS', 'NXF');
