<?php

if (!$coin) $this->goback();

if (!isset($wallet_live))
	$wallet_live = !empty($coin->installed) && !empty($coin->auto_ready) && !empty($coin->rpcport);

$show_live_peers = (bool) getiparam('live');

require dirname(__FILE__).'/../../ui/lib/pageheader.php';

$this->pageTitle = 'Peers - '.$coin->name;

//////////////////////////////////////////////////////////////////////////////////////

echo <<<end
<style type="text/css">
body { margin: 4px; }
pre { margin: 0 4px; }
</style>

<div class="main-left-box">
<div class="main-left-title">{$this->pageTitle}</div>
<div class="main-left-inner">
end;

if (!$wallet_live) {
	echo '<p><b>Live peer data is unavailable for this coin on this server.</b></p>';
	echo '<p>The wallet RPC is not installed or not marked ready, so the peer list cannot be queried locally.</p>';
	echo '</div>';
	echo '</div>';
	return;
}

if (!$show_live_peers) {
	echo '<p><b>Live peer data is available on demand.</b></p>';
	echo '<p>Some wallets answer peer-list RPC calls slowly, so this popup now opens with a fast summary first.</p>';
	echo '<table class="dataGrid2">';
	echo '<tr><td>Connections</td><td>'.(int) $coin->connections.'</td></tr>';
	echo '</table>';
	echo '<p>'.CHtml::link('Load live peer list', '/explorer/peers?id='.$coin->id.'&live=1').'</p>';
	echo '</div>';
	echo '</div>';
	return;
}

$remote = new WalletRPC($coin);
$info = $remote->getinfo();

$addnode = array();
$version = '';
$localheight = arraySafeVal($info, 'blocks');

$list = $remote->getpeerinfo();

if(!empty($list))
foreach($list as $peer)
{
	$node = arraySafeVal($peer,'addr');
	if (strstr($node,'127.0.0.1')) continue;
	if (strstr($node,'192.168.')) continue;
	if (strstr($node,'yiimp')) continue;

	$addnode[] = ($coin->rpcencoding=='DCR' ? 'addpeer=' : 'addnode=') . $node;

	$peerver = trim(arraySafeVal($peer,'subver'),'/');
	$version = max($version, $peerver);
}

asort($addnode);

echo '<pre>';
echo implode("\n",$addnode);
echo '</pre>';

echo '</div>';
echo '</div>';
