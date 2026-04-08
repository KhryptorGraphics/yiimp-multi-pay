<?php

function fetch_starshipcoin_sidecar_stats()
{
	$url = 'http://127.0.0.1:8117/stats';
	$json = false;

	if (function_exists('file_get_contents')) {
		$context = stream_context_create(array(
			'http' => array(
				'timeout' => 1.5,
			),
		));
		$json = @file_get_contents($url, false, $context);
	}

	if ($json === false && function_exists('curl_init')) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
		$json = curl_exec($ch);
		curl_close($ch);
	}

	if (!$json) return array();

	$data = json_decode($json, true);
	return is_array($data) ? $data : array();
}

function starshipcoin_last_pool_height($blocks)
{
	if (!is_array($blocks)) return 0;
	for ($index = count($blocks) - 1; $index >= 0; $index--) {
		if (is_numeric($blocks[$index])) return (int) $blocks[$index];
	}
	return 0;
}

$stats = fetch_starshipcoin_sidecar_stats();
$config = isset($stats['config']) && is_array($stats['config']) ? $stats['config'] : array();
$pool = isset($stats['pool']) && is_array($stats['pool']) ? $stats['pool'] : array();
$network = isset($stats['network']) && is_array($stats['network']) ? $stats['network'] : array();

$coinName = 'Starshipcoin';
$symbol = isset($config['symbol']) ? strtoupper($config['symbol']) : 'STC';
$algoName = isset($config['cnAlgorithm']) ? $config['cnAlgorithm'] : 'cryptonight';
$variant = isset($config['cnVariant']) ? (string) $config['cnVariant'] : '0';
$poolHost = !empty($config['poolHost']) ? $config['poolHost'] : 'pool.'.YAAMP_STRATUM_URL;
$defaultPort = 36555;
$cpuPort = 36333;
$generalPort = 36555;
$ports = isset($config['ports']) && is_array($config['ports']) ? $config['ports'] : array();
if (!empty($ports)) {
	foreach ($ports as $port) {
		$portNumber = !empty($port['port']) ? (int) $port['port'] : 0;
		$desc = !empty($port['desc']) ? strtolower($port['desc']) : '';
		if (!$portNumber) continue;
		if (strpos($desc, 'cpu') !== false || strpos($desc, 'low-diff') !== false) {
			$cpuPort = $portNumber;
		}
		if (strpos($desc, 'general') !== false) {
			$generalPort = $portNumber;
		}
	}
} else {
	$ports[] = array('port' => 36555, 'difficulty' => 5000, 'desc' => 'General mining');
}
$defaultPort = $generalPort ?: $defaultPort;

$networkHeight = isset($network['height']) ? (int) $network['height'] : 0;
$networkDifficulty = isset($network['difficulty']) ? Itoa2((double) $network['difficulty'], 2) : '0';
$miners = isset($pool['miners']) ? (int) $pool['miners'] : 0;
$workers = isset($pool['workers']) ? (int) $pool['workers'] : 0;
$poolHashrateValue = isset($pool['hashrate']) ? (double) $pool['hashrate'] : 0.0;
$poolHashrate = $poolHashrateValue > 0 ? Itoa2($poolHashrateValue, 2).'H/s' : '0 H/s';
$poolFee = isset($config['fee']) ? number_format((double) $config['fee'], 3, '.', '') : '0.800';
$minPayment = '0.5000 '.$symbol;
if (!empty($config['minPaymentThreshold']) && !empty($config['coinUnits'])) {
	$minPaymentValue = (double) $config['minPaymentThreshold'] / (double) $config['coinUnits'];
	$coinDecimals = isset($config['coinDecimalPlaces']) ? max(0, (int) $config['coinDecimalPlaces']) : 4;
	$minPayment = number_format($minPaymentValue, $coinDecimals, '.', '').' '.$symbol;
}
$lastBlockHeight = starshipcoin_last_pool_height(isset($pool['blocks']) ? $pool['blocks'] : array());
$statsReady = !empty($stats);
$statusText = $statsReady ? 'sidecar OK / wallet READY' : 'sidecar CHECK / wallet UNKNOWN';
$poolApiUrl = 'http://'.$poolHost.':8117/stats';
?>

<div class="main-left-box">
<div class="main-left-title"><?= $coinName ?> (<?= $symbol ?>) Pool</div>
<div class="main-left-inner">

<p>CryptoNote sidecar pool for <b><?= $coinName ?></b>. Mine with your <?= $symbol ?> wallet address as the username and use the password field for the worker name. The public stats feed is <a href="<?= CHtml::encode($poolApiUrl) ?>"><?= CHtml::encode($poolApiUrl) ?></a>.</p>

<table class="dataGrid2">
<thead>
<tr>
	<th>Status</th>
	<th>Network Height</th>
	<th>Difficulty</th>
	<th>Pool Hashrate</th>
	<th>Miners</th>
	<th>Workers</th>
</tr>
</thead>
<tbody>
<tr class="ssrow">
	<td><?= CHtml::encode($statusText) ?></td>
	<td align="right"><?= $networkHeight ?></td>
	<td align="right"><?= $networkDifficulty ?></td>
	<td align="right"><?= $poolHashrate ?></td>
	<td align="right"><?= $miners ?></td>
	<td align="right"><?= $workers ?></td>
</tr>
</tbody>
</table>

<table class="dataGrid2">
<thead>
<tr>
	<th>Pool Host</th>
	<th>Algorithm</th>
	<th>Variant</th>
	<th>Pool Fee</th>
	<th>Min Payout</th>
	<th>Last Block Height</th>
</tr>
</thead>
<tbody>
<tr class="ssrow">
	<td><?= CHtml::encode($poolHost) ?></td>
	<td><?= CHtml::encode($algoName) ?></td>
	<td align="right"><?= CHtml::encode($variant) ?></td>
	<td align="right"><?= $poolFee ?>%</td>
	<td align="right"><?= CHtml::encode($minPayment) ?></td>
	<td align="right"><?= $lastBlockHeight ?></td>
</tr>
</tbody>
</table>

<table class="dataGrid2">
<thead>
<tr>
	<th>Port</th>
	<th>Difficulty</th>
	<th>Description</th>
</tr>
</thead>
<tbody>
<?php foreach ($ports as $port): ?>
<tr class="ssrow">
	<td align="right"><?= !empty($port['port']) ? (int) $port['port'] : 0 ?></td>
	<td align="right"><?= isset($port['difficulty']) ? (int) $port['difficulty'] : 0 ?></td>
	<td><?= !empty($port['desc']) ? CHtml::encode($port['desc']) : '' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p><b>Example CPU miner command</b><br>
<span style="font-family: monospace;">xmrig -a <?= CHtml::encode($algoName) ?> -o stratum+tcp://<?= CHtml::encode($poolHost) ?>:<?= $cpuPort ?> -u YOUR_<?= $symbol ?>_WALLET -p cpu1</span></p>

<p><b>Example GPU miner command</b><br>
<span style="font-family: monospace;">xmrig -a <?= CHtml::encode($algoName) ?> -o stratum+tcp://<?= CHtml::encode($poolHost) ?>:<?= $generalPort ?> -u YOUR_<?= $symbol ?>_WALLET -p gpu1</span></p>

<p><b>Chain</b>: <?= $coinName ?> (<?= CHtml::encode($algoName) ?>), target <?= !empty($config['coinDifficultyTarget']) ? (int) $config['coinDifficultyTarget'] : 120 ?> seconds, payment stats served by the CryptoNote sidecar pool.</p>

</div></div>
