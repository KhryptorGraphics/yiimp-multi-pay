<?php

if (!$coin) $this->goback();

if (!isset($wallet_live))
	$wallet_live = !empty($coin->installed) && !empty($coin->auto_ready) && !empty($coin->rpcport);

JavascriptFile("/extensions/jqplot/jquery.jqplot.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.dateAxisRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.highlighter.js");

$this->pageTitle = $coin->name." block explorer";

$start = (int) getiparam('start');
$show_history = (bool) getiparam('history');
$history_window = 8;

echo <<<END
<script type="text/javascript">
$(function() {
	$('#favicon').remove();
	$('head').append('<link href="{$coin->image}" id="favicon" rel="shortcut icon">');
});
</script>
<style type="text/css">
table.dataGrid2 { margin-top: 0; }
span.monospace { font-family: monospace; }
.main-text-input { }
.page .footer { width: auto; }
</style>
END;

// version is used for multi algo coins
// but each coin use different values...
$multiAlgos = $coin->multialgos || versionToAlgo($coin, 0) !== false;

echo '<br/>';
echo '<div class="main-left-box">';
echo '<div class="main-left-title">'.$coin->name.' Explorer</div>';
echo '<div class="main-left-inner" style="padding-left: 8px; padding-right: 8px;">';

if (!$wallet_live) {
	$height = number_format((int) $coin->block_height, 0, '.', ' ');
	$difficulty = Itoa2($coin->difficulty, 3);
	$connections = (int) $coin->connections;

	echo '<p><b>Live wallet explorer data is unavailable for this coin on this server.</b></p>';
	echo '<p>The coin is still listed, but its wallet RPC is not installed or not marked ready, so historical explorer data cannot be refreshed live.</p>';
	echo '<table class="dataGrid2">';
	echo '<tr><td>Height</td><td>'.$height.'</td></tr>';
	echo '<tr><td>Difficulty</td><td>'.$difficulty.'</td></tr>';
	echo '<tr><td>Connections</td><td>'.$connections.'</td></tr>';
	echo '</table>';
	echo '</div></div>';
	return;
}

$actionUrl = $coin->visible ? '/explorer/'.$coin->symbol : '/explorer/search?id='.$coin->id;

echo '<table class="dataGrid2">';
echo '<tr><td>Height</td><td>'.$coin->createExplorerLink($coin->block_height, array('height'=>$coin->block_height)).'</td></tr>';
echo '<tr><td>Difficulty</td><td>'.Itoa2($coin->difficulty, 3).'</td></tr>';
echo '<tr><td>Connections</td><td>'.(int) $coin->connections.'</td></tr>';
echo '<tr><td>Algo</td><td>'.$coin->algo.'</td></tr>';
echo '</table>';

echo <<<end
<div id="form" style="width: 660px; height: 50px; overflow: hidden; margin-top: 8px;">
<form action="{$actionUrl}" method="POST" style="padding-top: 4px; width: 650px;">
<input type="text" name="height" class="main-text-input" placeholder="Height" style="width: 80px;">
<input type="text" name="txid" class="main-text-input" placeholder="Transaction hash" style="width: 450px; margin: 4px;">
<input type="submit" value="Search" class="main-submit-button" >
</form>
</div>
end;

if (!$show_history) {
	echo '<p><b>Live recent block history is available on demand.</b></p>';
	echo '<p>Some wallets answer repeated explorer RPC calls slowly, so the default explorer page now loads the coin summary first.</p>';
	echo '<p>'.$coin->createExplorerLink('Load live recent blocks', array('history'=>1, 'start'=>$coin->block_height)).'</p>';
	echo '</div></div>';
	return;
}

echo '<table class="dataGrid2">';

echo "<thead>";
echo "<tr>";
echo "<th>Age</th>";
echo "<th>Height</th>";
echo "<th>Difficulty</th>";
echo "<th>Type</th>";
if ($multiAlgos) echo "<th>Algo</th>";
echo "<th>Tx</th>";
echo "<th>Conf</th>";
echo "<th>Blockhash</th>";
echo "</tr>";
echo "</thead>";

$remote = new WalletRPC($coin);
if (!$start || $start > $coin->block_height)
	$start = $coin->block_height;
for($i = $start; $i > max(1, $start-$history_window); $i--)
{
	$hash = $remote->getblockhash($i);
	if(!$hash) continue;

	$block = $remote->getblock($hash);
	if(!$block) continue;

	$d = datetoa2($block['time']);
	$confirms = isset($block['confirmations'])? $block['confirmations']: '';
	$tx = count($block['tx']);
	$diff = $block['difficulty'];
	$algo = versionToAlgo($coin, $block['version']);
	$type = '';
	if (arraySafeval($block,'nonce',0) > 0) $type = 'PoW';
	else if (isset($block['auxpow'])) $type = 'Aux';
	else if (isset($block['mint']) || strstr(arraySafeVal($block,'flags',''), 'proof-of-stake')) $type = 'PoS';

	// nonce 256bits
	if ($type == '' && $coin->symbol=='ZEC') $type = 'PoW';

//	debuglog($block);
	echo '<tr class="ssrow">';
	echo '<td>'.$d.'</td>';

	echo '<td>'.$coin->createExplorerLink($i, array('height'=>$i)).'</td>';

	echo '<td>'.$diff.'</td>';
	echo '<td>'.$type.'</td>';
	if ($multiAlgos) echo "<td>$algo</td>";
	echo '<td>'.$tx.'</td>';
	echo '<td>'.$confirms.'</td>';

	echo '<td style="overflow-x: hidden; max-width:800px;"><span class="monospace">';
	echo $coin->createExplorerLink($hash, array('hash'=>$hash));
	echo '</span></td>';

	echo "</tr>";
}

echo "</table>";

$pager = '';
if ($start <= $coin->block_height - $history_window)
	$pager  = $coin->createExplorerLink('<< Prev', array('history'=>1, 'start'=>min($coin->block_height,$start+$history_window)));
if ($start != $coin->block_height)
	$pager .= '&nbsp; '.$coin->createExplorerLink('Now', array('history'=>1));
if ($start > $history_window)
	$pager .= '&nbsp; '.$coin->createExplorerLink('Next >>', array('history'=>1, 'start'=>max(1,$start-$history_window)));

echo <<<end
<div id="pager" style="float: right; width: 200px; text-align: right; margin-right: 16px; margin-top: 8px;">$pager</div>
end;
