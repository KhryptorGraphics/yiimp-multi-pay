-- Normalize live coin metadata names and public links for frontend/API use.

UPDATE coins
SET
	name = 'StohnCoin',
	link_site = 'https://stohncoin.org/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=5523706.0',
	link_explorer = 'https://stohnexplorer.com/',
	link_discord = 'https://discord.gg/BhjA4kXNUc'
WHERE symbol = 'SOH';

UPDATE coins
SET
	name = 'Bellscoin',
	link_site = 'https://bellscoin.com/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=1609194.0',
	link_github = 'https://github.com/Nintondo/bellscoinV3',
	link_explorer = 'https://nintondo.io/bells/mainnet/explorer'
WHERE symbol = 'BELLS';

UPDATE coins
SET
	link_site = 'https://www.digibyte.org/en-us/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=420477.0',
	link_github = 'https://github.com/DigiByte-Core/digibyte',
	link_explorer = 'https://digibyteblockexplorer.com/',
	link_discord = 'https://discord.com/invite/chhrmxcdsy'
WHERE symbol = 'DGB';

UPDATE coins
SET
	link_site = 'https://goldcoinproject.org/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=317568.0',
	link_github = 'https://github.com/goldcoin/goldcoin',
	link_explorer = 'https://chainz.cryptoid.info/glc/',
	link_discord = 'https://discord.me/goldcoin'
WHERE symbol = 'GLC';

UPDATE coins
SET
	link_site = 'https://flo.cash/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=251520.0',
	link_github = 'https://github.com/floblockchain/flo',
	link_explorer = 'https://flo.tokenview.io/'
WHERE symbol = 'FLO';

UPDATE coins
SET
	link_bitcointalk = 'https://bitcointalk.org/index.php?action=printpage;topic=295980.0',
	link_github = 'https://github.com/PeopleCoin/PeopleCoin'
WHERE symbol = 'PEP';

UPDATE coins
SET
	link_site = 'https://infinitecoin.com/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=225891.980',
	link_github = 'https://github.com/infinitecoin-project/infinitecoin',
	link_explorer = 'https://chainz.cryptoid.info/ifc/'
WHERE symbol = 'IFC';

UPDATE coins
SET
	link_site = 'https://viacoin.org/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=1840789.0',
	link_github = 'https://github.com/viacoin/viacoin',
	link_explorer = 'https://explorer.viacoin.org/'
WHERE symbol = 'VIA';

UPDATE coins
SET
	link_site = 'https://nexiacoin.org/',
	link_bitcointalk = 'https://bitcointalk.org/index.php?topic=5573155.0',
	link_github = 'https://github.com/nexia-coin/nexia-master',
	link_explorer = 'https://explorer.nexiacoin.org/',
	link_discord = 'https://discord.gg/Y9EkSzV2'
WHERE symbol = 'NXE';
