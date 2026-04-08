<?php
$algo = user()->getState('yaamp-algo');

JavascriptFile("/extensions/jqplot/jquery.jqplot.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.dateAxisRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.barRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.highlighter.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.cursor.js");
JavascriptFile('/yaamp/ui/js/auto_refresh.js');

$height = '240px';

$min_payout = floatval(YAAMP_PAYMENTS_MINI);
$min_sunday = $min_payout / 10;

$payout_freq = (YAAMP_PAYMENTS_FREQ / 3600) . " hours";
$publicHostInfo = getFullServerName();
?>

<div id='resume_update_button' style='color: #444; background-color: #ffd; border: 1px solid #eea;
    padding: 10px; margin-left: 20px; margin-right: 20px; margin-top: 15px; cursor: pointer; display: none;'
    onclick='auto_page_resume();' align=center>
    <b>Auto refresh is paused - Click to resume</b></div>

<table cellspacing=20 width=100%>
<tr><td valign=top width=50%>

<!--  -->

<div class="main-left-box">
<div class="main-left-title"><?=YAAMP_SITE_URL?></div>
<div class="main-left-inner">

<ul>

<li>Welcome to <?=YAAMP_SITE_URL?>! </li>
<li>This fork was based on the yaamp source code and is now an open source project.</li>
<li>No registration is required, we do payouts in the currency you mine. Use your wallet address as the username.</li>
<li>Payouts are made automatically every <?= $payout_freq ?> for all balances above <b><?= $min_payout ?></b>, or <b><?= $min_sunday ?></b> on Sunday.</li>
<li>For some coins, there is an initial delay before the first payout, please wait at least 6 hours before asking for support.</li>
<li>Blocks are distributed proportionally among valid submitted shares.</li>
<li>Use the <a href="/site/mining_groups">Mining Groups</a> page to see which coins can be mined together, which are dedicated, and which payout addresses are still missing from your session.</li>

<br/>

</ul>
</div></div>
<br/>

<!-- Stratum Auto generation code, will automatically add coins when they are enabled and auto ready -->

<div class="main-left-box">
<div class="main-left-title">How to mine with <?=YAAMP_SITE_URL?></div>
<div class="main-left-inner">

<table>
	<thead>
		<tr>
			<th>Stratum Location</th>
			<th>Choose Coin</th>
			<th>Your Wallet Address</th>
			<th>Rig (opt.)</th>
			<th>Type</th>
		</tr>
	</thead>

<tbody>
	<tr>
		<td>
			<select id="drop-stratum" style="border-style:solid; padding: 3px; font-family: monospace; border-radius: 5px;" onchange="generate()">

			<!-- Add your stratum locations here -->
			<option value="">Main Stratum</option>
			<!--<option value="mine.">Asia Stratum</option>
			<option value="eu.">Europe Stratum</option>
			<option value="cad.">CAD Stratum</option>
			<option value="uk.">UK Stratum</option> -->
			</select>
		</td>

		<td>
			<select id="drop-coin" style="border-style:solid; padding: 3px; font-family: monospace; border-radius: 5px;" onchange="generate()">
       <?php
$coinOptions = array(
	array(
		'label' => 'Starshipcoin (STC)',
		'symbol' => 'STC',
		'algo' => 'cryptonight',
		'port' => 36555,
		'template' => 'xmrig -a cryptonight -o stratum+tcp://__HOST__:__PORT__ -u __WALLET__ -p __WORKER_OR_X____EXTRAS__',
		'supportsSolo' => 0,
		'rigMode' => 'password',
	),
);

$list = getdbolist('db_coins', "enable and visible and auto_ready order by algo asc");

if ($list) {
	foreach ($list as $coin) {
		$name = substr($coin->name, 0, 18);
		$symbol = $coin->getOfficialSymbol();
		$algo = $coin->algo;
		$auto_exchange = isset($coin->auto_exchange) ? $coin->auto_exchange : 1;

		$port_db = getdbosql('db_stratums', "algo=:algo and symbol=:symbol and time>:cutoff ORDER BY time DESC, started DESC", array(
			':algo' => $algo,
			':symbol' => $symbol,
			':cutoff' => time() - 600,
		));

		if ($port_db && $port_db->port) {
			$port = $port_db->port;
		} else if (!empty($coin->dedicatedport)) {
			$port = $coin->dedicatedport;
		} else {
			$port = getAlgoPort($algo);
		}

		$mc_param = ($auto_exchange == 0) ? ",mc=$symbol" : "";
		$template = "-a $algo -o stratum+tcp://__HOST__:__PORT__ -u __WALLET__.__WORKER__ -p c=$symbol".$mc_param."__SOLO____EXTRAS__";
		if ($algo === 'spacescrypt' || $symbol === 'SPSC') {
			$template = "spacescrypt-cpuminer -a $algo -o stratum+tcp://__HOST__:__PORT__ -u __WALLET__.__WORKER__ -p c=$symbol".$mc_param."__SOLO____EXTRAS__";
		}

		$coinOptions[] = array(
			'label' => "$name ($symbol)",
			'symbol' => $symbol,
			'algo' => $algo,
			'port' => (int) $port,
			'template' => $template,
			'supportsSolo' => 1,
			'rigMode' => 'username',
		);
	}
}

usort($coinOptions, function($left, $right) {
	$algoCompare = strcasecmp($left['algo'], $right['algo']);
	if ($algoCompare !== 0) return $algoCompare;
	return strcasecmp($left['label'], $right['label']);
});

if (empty($coinOptions)) {
	echo "<option disabled>No Coins Available</option>";
} else {
	$currentAlgo = '';
	$selected = false;
	foreach ($coinOptions as $option) {
		if ($option['algo'] !== $currentAlgo) {
			$currentAlgo = $option['algo'];
			echo "<option disabled='disabled'>".CHtml::encode($currentAlgo)."</option>";
		}

		$selectedAttr = !$selected ? " selected='selected'" : '';
		$selected = true;
		echo "<option value='".CHtml::encode($option['symbol'])."'"
			." data-port='".(int) $option['port']."'"
			." data-symbol='".CHtml::encode($option['symbol'])."'"
			." data-template='".CHtml::encode($option['template'])."'"
			." data-supports-solo='".(int) $option['supportsSolo']."'"
			." data-rig-mode='".CHtml::encode($option['rigMode'])."'"
			.$selectedAttr
			.">".CHtml::encode($option['label'])."</option>";
	}
}
?>

			</select>
		</td>
		<td>
			<input id="text-wallet" type="text" size="30" placeholder="RF9D1R3Vt7CECzvb1SawieUC9cYmAY1qoj" style="border-style:solid; padding: 3px; font-family: monospace; border-radius: 5px;" onkeyup="generate()">
		</td>
		<td>
			<input id="text-rig-name" type="text" size="10" placeholder="001" style="border-style:solid; padding: 3px; font-family: monospace; border-radius: 5px;" onkeyup="generate()">
		</td>
		<td>
			<select id="drop-solo" style="border-style:solid; padding: 3px; font-family: monospace; border-radius: 5px;" onchange="generate()">
			<option value="">Shared</option>
			<option value=",m=solo">Solo</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="5" style="padding-top: 8px;">
			<label style="display: block; font-size: .85em; margin-bottom: 4px;"><b>Extra Coin Payouts (opt.)</b></label>
			<input id="text-extra-addresses" type="text" size="90" placeholder="addr_DOGE=D...,addr_LTC=L...,addr_FLO=F..." style="width: 100%; border-style:solid; padding: 3px; font-family: monospace; border-radius: 5px;" onkeyup="generate()">
		</td>
	</tbody>
<tbody>
	<tr>
		<td colspan="5"><p class="main-left-box" style="padding: 3px; background-color: #ffffee; font-family: monospace;" id="output">loading...</p></td>
	</tr>
</tbody>
</table>

<ul>
<li>&lt;WALLET_ADDRESS&gt; must be valid for the currency you mine. <b>DO NOT USE a BTC address here, the auto exchange is disabled on these stratums</b>!</li>
<!-- <li><b>Our stratums are now NiceHASH compatible and ASICBoost enabled, please message support if you have any issues.</b></li> -->
<li>The coin selector includes both <b>Starshipcoin (STC)</b> on the CryptoNote sidecar pool and <b>StarShip (SPSC)</b> on the native yiimp SpaceScrypt pool.</li>
<li>For <b>Starshipcoin (STC)</b>, keep the wallet in the username field and use the rig name as the password. For yiimp coins like <b>SPSC</b>, the rig name is appended to the username as <span style="font-family: monospace;">wallet.worker</span>.</li>
<li>For native multi-pay, keep one primary address in <span style="font-family: monospace;">-u</span> and append extra payout flags in the password such as <span style="font-family: monospace;">addr_DOGE=D...,addr_LTC=L...</span>.</li>
<li>The stratum will only assign a coin to your session if that session provided a valid payout address for it.</li>
<li>For grouped mining bundles, open <a href="/site/mining_groups">/site/mining_groups</a> and copy the bundle-specific host, username, and password flags.</li>
<li>Payouts are made automatically every hour for all balances above <b><?=$min_payout?></b>, or <b><?=$min_sunday?></b> on Sunday.</li>
<br>
</ul>
</div></div><br>

<!-- End new stratum generation code  -->

<div class="main-left-box">
<div class="main-left-title"><?=YAAMP_SITE_URL?> Links</div>
<div class="main-left-inner">

<ul>

<li><b>API</b> - <a href='/site/api'><?= $publicHostInfo ?>/site/api</a></li>
<li><b>Mining Groups</b> - <a href='/site/mining_groups'><?= $publicHostInfo ?>/site/mining_groups</a></li>
<li><b>Difficulty</b> - <a href='/site/diff'><?= $publicHostInfo ?>/site/diff</a></li>
<?php
if (YIIMP_PUBLIC_BENCHMARK):
?>
<li><b>Benchmarks</b> - <a href='/site/benchmarks'><?= $publicHostInfo ?>/site/benchmarks</a></li>
<?php
endif;
?>

<?php
if (YAAMP_ALLOW_EXCHANGE):
?>
<li><b>Algo Switching</b> - <a href='/site/multialgo'><?= $publicHostInfo ?>/site/multialgo</a></li>
<?php
endif;
?>

<br>

</ul>
</div></div><br>

<div class="main-left-box">
<div class="main-left-title"><?=YAAMP_SITE_URL?> Support</div>
<div class="main-left-inner">

<ul class="social-icons">
<!--    <li><a href="http://www.facebook.com"><img src='/images/Facebook.png' /></a></li>
    <li><a href="http://www.twitter.com"><img src='/images/Twitter.png' /></a></li>
    <li><a href="http://www.youtube.com"><img src='/images/YouTube.png' /></a></li>
    <li><a href="http://www.github.com"><img src='/images/Github.png' /></a></li> -->
    <li><a href="https://discord.gg/DrsrWQh3qC"><img src='/images/discord.png' /></a></li>
</ul>

</div></div><br>
</td><td valign=top>
<!--  -->

<?php $this->renderPartial('results/starshipcoin_results'); ?>

<div id='pool_current_results'>
<br><br><br><br><br><br><br><br><br><br>
</div>

<div id='pool_history_results'>
<br><br><br><br><br><br><br><br><br><br>
</div>

<div id='pool_coins_info'>
<br><br><br><br><br><br><br><br><br><br>
</div>

</td></tr></table>

<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>

<script>

function page_refresh()
{
    pool_current_refresh();
    pool_history_refresh();
	pool_coins_info_refresh();

}

function select_algo(algo)
{
    window.location.href = '/site/algo?algo='+algo+'&r=/';
}

function pool_current_ready(data)
{
    $('#pool_current_results').html(data);
}

function pool_current_refresh()
{
    var url = "/site/current_results";
    $.get(url, '', pool_current_ready);
}

////////////////////////////////////////////////////

function pool_history_ready(data)
{
    $('#pool_history_results').html(data);
}

function pool_history_refresh()
{
    var url = "/site/history_results";
    $.get(url, '', pool_history_ready);
}

////////////////////////////////////////////////////

function pool_coins_info_ready(data)
{
    $('#pool_coins_info').html(data);
}

function pool_coins_info_refresh()
{
    var url = "/site/coins_info";
    $.get(url, '', pool_coins_info_ready);
}

</script>

<script>
function getSelectedCoinOption() {
    var coin = document.getElementById('drop-coin');
    if (!coin || !coin.options.length) return null;

    var option = coin.options[coin.selectedIndex];
    if (option && option.dataset && option.dataset.template) return option;

    for (var i = 0; i < coin.options.length; i++) {
        if (coin.options[i].dataset && coin.options[i].dataset.template) {
            coin.selectedIndex = i;
            return coin.options[i];
        }
    }

    return null;
}

function updateGeneratorUi(option) {
    var rigName = document.getElementById('text-rig-name');
    var solo = document.getElementById('drop-solo');
    if (!option) return;

    var rigMode = option.dataset.rigMode || 'username';
    rigName.placeholder = rigMode === 'password' ? 'worker1' : '001';

    var supportsSolo = option.dataset.supportsSolo === '1';
    solo.disabled = !supportsSolo;
    if (!supportsSolo) solo.value = '';
}

function normalizeExtraAddresses(raw) {
    if (!raw) return '';

    var tokens = raw.split(/[,\n;]+/);
    var normalized = [];
    for (var i = 0; i < tokens.length; i++) {
        var token = tokens[i].trim();
        if (!token) continue;

        var parts = token.split('=');
        if (parts.length < 2) continue;

        var key = parts.shift().trim();
        var value = parts.join('=').trim();
        if (!value) continue;

        if (key.toLowerCase().indexOf('addr_') !== 0) {
            key = 'addr_' + key.toUpperCase();
        } else {
            key = 'addr_' + key.substring(5).toUpperCase();
        }

        normalized.push(key + '=' + value);
    }

    return normalized.join(',');
}

function getLastUpdated(){
    var stratum = document.getElementById('drop-stratum');
    var solo = document.getElementById('drop-solo');
    var wallet = document.getElementById('text-wallet').value.trim();
    var rigName = document.getElementById('text-rig-name').value.trim();
    var extraAddresses = normalizeExtraAddresses(document.getElementById('text-extra-addresses').value);
    var option = getSelectedCoinOption();
    if (!option) return 'Select a coin to generate a miner command.';

    updateGeneratorUi(option);

    var template = option.dataset.template || '';
    var supportsSolo = option.dataset.supportsSolo === '1';
    var host = stratum.value + '<?=YAAMP_STRATUM_URL?>';
    var workerName = rigName ? rigName : 'WORKER_NAME';
    var workerOrX = rigName ? rigName : 'x';

    return template
        .replace(/__HOST__/g, host)
        .replace(/__PORT__/g, option.dataset.port || '0')
        .replace(/__WALLET__/g, wallet ? wallet : 'WALLET_ADDRESS')
        .replace(/__WORKER__/g, workerName)
        .replace(/__WORKER_OR_X__/g, workerOrX)
        .replace(/__SOLO__/g, supportsSolo ? solo.value : '')
        .replace(/__EXTRAS__/g, extraAddresses ? ',' + extraAddresses : '');
}

function generate(){
    var result = getLastUpdated();
    document.getElementById('output').innerHTML = result;
}
generate();
</script>
