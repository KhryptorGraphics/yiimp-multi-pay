<?php

$algo = user()->getState('yaamp-algo');
//if($algo == 'all') return;

function coins_info_link_specs()
{
	return array(
		'bitcointalk' => array('label' => 'Bitcointalk Forum', 'icon' => '/images/bitcointalk.webp'),
		'site' => array('label' => 'Website', 'icon' => '/images/home.webp'),
		'discord' => array('label' => 'Discord', 'icon' => '/images/discord.png'),
		'explorer' => array('label' => 'Explorer', 'icon' => '/images/blockchain.webp'),
		'github' => array('label' => 'Github', 'icon' => '/images/Github.png'),
	);
}

function coins_info_fallback_links($symbol)
{
	static $fallback = array(
		'LTC' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=47417.0',
			'site' => 'https://www.litecoin.org/',
			'discord' => 'https://discord.com/invite/litecoin',
			'explorer' => 'https://chainz.cryptoid.info/ltc/',
			'github' => 'https://github.com/litecoin-project/litecoin',
		),
		'DOGE' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=361813.0',
			'site' => 'https://dogecoin.github.io/',
			'discord' => 'https://discord.com/invite/dogecoin',
			'explorer' => 'https://chainz.cryptoid.info/doge/',
			'github' => 'https://github.com/dogecoin/dogecoin',
		),
		'DGB' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=420477.0',
			'site' => 'https://www.digibyte.org/en-us/',
			'discord' => 'https://discord.com/invite/chhrmxcdsy',
			'explorer' => 'https://digibyteblockexplorer.com/',
			'github' => 'https://github.com/DigiByte-Core/digibyte',
		),
		'MONA' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=392436.0',
			'site' => 'https://monacoin.org/',
			'explorer' => 'https://chaintools.mona-coin.de/',
			'github' => 'https://github.com/monacoinproject/monacoin',
		),
		'GLC' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=317568.0',
			'site' => 'https://goldcoinproject.org/',
			'discord' => 'https://discord.me/goldcoin',
			'explorer' => 'https://chainz.cryptoid.info/glc/',
			'github' => 'https://github.com/goldcoin/goldcoin',
		),
		'FLO' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=251520.0',
			'site' => 'https://flo.cash/',
			'explorer' => 'https://flo.tokenview.io/',
			'github' => 'https://github.com/floblockchain/flo',
		),
		'SOH' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=5523706.0',
			'site' => 'https://stohncoin.org/',
			'discord' => 'https://discord.gg/BhjA4kXNUc',
			'explorer' => 'https://stohnexplorer.com/',
		),
		'PEP' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?action=printpage;topic=295980.0',
			'github' => 'https://github.com/PeopleCoin/PeopleCoin',
		),
		'BELLS' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=1609194.0',
			'site' => 'https://bellscoin.com/',
			'explorer' => 'https://nintondo.io/bells/mainnet/explorer',
			'github' => 'https://github.com/Nintondo/bellscoinV3',
		),
		'CAT' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=380130.0',
			'site' => 'https://catcoins.org/',
			'explorer' => 'https://chainz.cryptoid.info/cat/',
			'github' => 'https://github.com/CatcoinOfficial/CatcoinRelease',
		),
		'IFC' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=225891.980',
			'site' => 'https://infinitecoin.com/',
			'explorer' => 'https://chainz.cryptoid.info/ifc/',
			'github' => 'https://github.com/infinitecoin-project/infinitecoin',
		),
		'VIA' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=1840789.0',
			'site' => 'https://viacoin.org/',
			'explorer' => 'https://explorer.viacoin.org/',
			'github' => 'https://github.com/viacoin/viacoin',
		),
		'NXE' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=5573155.0',
			'site' => 'https://nexiacoin.org/',
			'discord' => 'https://discord.gg/Y9EkSzV2',
			'explorer' => 'https://explorer.nexiacoin.org/',
			'github' => 'https://github.com/nexia-coin/nexia-master',
		),
		'QNTM' => array(
			'bitcointalk' => 'https://bitcointalk.org/index.php?topic=5576829.0',
			'site' => 'https://quantum-token.net/',
			'discord' => 'https://discord.gg/jCzmN3CEuV',
			'explorer' => 'https://explorer.quantum-token.net/',
			'github' => 'https://github.com/QuantumTokenGit/Source',
		),
		'SPSC' => array(
			'explorer' => '/explorer/SPSC',
		),
	);

	$symbol = strtoupper(trim((string) $symbol));
	return isset($fallback[$symbol]) ? $fallback[$symbol] : array();
}

function coins_info_render_link($url, $label, $icon)
{
	$img = '<img width=16 src="'.$icon.'">';
	if (!empty($url))
		return '<a href="'.$url.'" target="_blank">'.$img.$label.'</a>';

	return '';
}

echo "<div class='main-left-box'>";
echo "<div class='main-left-title'>Coin Information ($algo)</div>";
echo "<div class='main-left-inner'>";

echo <<<END
<style type="text/css">
td.symb, th.symb {
	width: 50px;
	max-width: 50px;
	text-align: right;
}
td.symb {
	font-size: .8em;
}
</style>

<table class="dataGrid2">
<thead>
<tr>
<th></th>
<th>Name</th>
<th>Information</th>
</tr>
</thead>
END;

$main_ids = array();

$algo = user()->getState('yaamp-algo');
if ($algo == 'all')
    $list = getdbolist('db_coins', "enable and visible order by index_avg desc");
else
    $list = getdbolist('db_coins', "enable and visible and algo=:algo order by index_avg desc", array(
        ':algo' => $algo
    ));

foreach($list as $coin)
{
	$id = $coin->id;
	$name = substr($coin->name, 0, 20);
	$fallback = coins_info_fallback_links($coin->symbol);
	$links = array(
		'bitcointalk' => !empty($coin->link_bitcointalk) ? $coin->link_bitcointalk : arraySafeVal($fallback, 'bitcointalk'),
		'site' => !empty($coin->link_site) ? $coin->link_site : arraySafeVal($fallback, 'site'),
		'discord' => !empty($coin->link_discord) ? $coin->link_discord : arraySafeVal($fallback, 'discord'),
		'explorer' => !empty($coin->link_explorer) ? $coin->link_explorer : arraySafeVal($fallback, 'explorer'),
		'github' => !empty($coin->link_github) ? $coin->link_github : arraySafeVal($fallback, 'github'),
	);

	echo '<tr class="ssrow">';
	echo '<td width=18><img width=16 src="'.$coin->image.'"></td>';
	echo '<td><a href="/site/block?id='.$id.'"><b>'.$name.' </b></a></td>';
	echo '<td>';
	foreach (coins_info_link_specs() as $key => $spec) {
		$link = coins_info_render_link(arraySafeVal($links, $key), $spec['label'], $spec['icon']);
		if ($link)
			echo $link.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	echo '<a href="/explorer/peers?id='.$id.'"><img width=16 src="/images/nodes16.png">Nodes</a>';
	echo '</td>';
	echo '</tr>';
}

echo '</table>';


echo '</div>';

echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</div></div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
