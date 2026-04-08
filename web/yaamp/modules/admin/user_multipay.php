<?php

echo getAdminSideBarLinks();

$all_coins = getdbolist('db_coins', "enable AND installed ORDER BY symbol");
$primary_coin = getdbo('db_coins', intval($user->coinid));
$coin_options = "<option value=''>Select coin</option>";
foreach($all_coins as $coin) {
	$coin_options .= '<option value="'.$coin->id.'">'.$coin->symbol.' - '.$coin->name.'</option>';
}

$wallet_url = '/?address='.urlencode($user->username);
$total_balance = bitcoinvaluetoa(yaamp_user_balance_summary($user));

echo "<a href='/admin/user'>Users</a> &gt; ";
echo "<a href='/admin/usermultipay?id={$user->id}'>Account {$user->id}</a> &nbsp; ";
echo CHtml::link('public wallet', $wallet_url);
echo "<br/><br/>";

if (user()->hasFlash('error'))
	echo "<div class='flash-error' style='color: darkred; margin-bottom: 10px;'>".user()->getFlash('error')."</div>";
if (user()->hasFlash('warning'))
	echo "<div class='flash-notice' style='color: #8a6d3b; margin-bottom: 10px;'>".user()->getFlash('warning')."</div>";
if (user()->hasFlash('message'))
	echo "<div class='flash-success' style='color: darkgreen; margin-bottom: 10px;'>".user()->getFlash('message')."</div>";

echo "<div class='main-left-box'>";
echo "<div class='main-left-title'>Multi-Pay Account Management</div>";
echo "<div class='main-left-inner'>";
echo "<p><b>Primary login address:</b> <span style='font-family: monospace;'>".CHtml::encode($user->username)."</span><br/>";
echo "<b>Primary coin:</b> ".($primary_coin ? $primary_coin->symbol.' - '.$primary_coin->name : intval($user->coinid))." &nbsp; <b>Total balance:</b> {$total_balance}</p>";
echo "<p style='font-size: .9em; color: #666;'>".
	"Edit one payout address per coin. Leaving a non-primary row blank removes it only if that coin has no unpaid balance left. ".
	"The primary coin row stays mirrored into <span style='font-family: monospace;'>accounts.username</span> for backwards compatibility.".
	"</p>";

echo "<form method='post' action='/admin/usermultipay?id={$user->id}'>";
echo "<table class='dataGrid'>";
echo "<thead><tr>";
echo "<th>Coin</th>";
echo "<th>Payout Address</th>";
echo "<th align='right'>Immature</th>";
echo "<th align='right'>Confirmed</th>";
echo "<th align='right'>Balance</th>";
echo "<th align='right'>Paid</th>";
echo "<th align='right'>Value*</th>";
echo "<th>Notes</th>";
echo "</tr></thead><tbody id='mapping-rows'>";

$row_index = 0;
foreach($rows as $row) {
	$coin = $row['coin'];
	if (!$coin) continue;
	$coinid = intval($row['coinid']);
	$address = CHtml::encode((string) arraySafeVal($row, 'address', ''));
	$note = ($coinid == intval($user->coinid)) ? 'Primary login coin' : 'Blank address removes this row';

	echo "<tr>";
	echo "<td>";
	echo "<select name='mapping[{$row_index}][coinid]'>";
	foreach($all_coins as $option_coin) {
		$selected = ($option_coin->id == $coinid) ? " selected" : "";
		echo "<option value='{$option_coin->id}'{$selected}>{$option_coin->symbol} - {$option_coin->name}</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td><input type='text' name='mapping[{$row_index}][address]' value='{$address}' style='width: 100%; font-family: monospace;' maxlength='128'/></td>";
	echo "<td align='right'>".altcoinvaluetoa((double) arraySafeVal($row, 'immature', 0))."</td>";
	echo "<td align='right'>".altcoinvaluetoa((double) arraySafeVal($row, 'confirmed', 0))."</td>";
	echo "<td align='right'>".altcoinvaluetoa((double) arraySafeVal($row, 'balance', 0))."</td>";
	echo "<td align='right'>".altcoinvaluetoa((double) arraySafeVal($row, 'paid', 0))."</td>";
	echo "<td align='right'>".bitcoinvaluetoa((double) arraySafeVal($row, 'value', 0))."</td>";
	echo "<td>{$note}</td>";
	echo "</tr>";
	$row_index++;
}

echo "</tbody></table>";
echo "<br/>";
echo "<button type='button' onclick='addMappingRow()'>Add Coin Mapping</button> ";
echo "<input type='submit' value='Save Mappings'/>";
echo "</form>";

echo "<div style='display:none'>";
echo "<table><tbody><tr id='mapping-row-template'>";
echo "<td><select data-name='coinid'>{$coin_options}</select></td>";
echo "<td><input type='text' data-name='address' value='' style='width: 100%; font-family: monospace;' maxlength='128'/></td>";
echo "<td align='right'>0.00000000</td>";
echo "<td align='right'>0.00000000</td>";
echo "<td align='right'>0.00000000</td>";
echo "<td align='right'>0.00000000</td>";
echo "<td align='right'>0.00000000</td>";
echo "<td>Blank address removes this row</td>";
echo "</tr></tbody></table>";
echo "</div>";

echo "<script>
var mappingRowIndex = {$row_index};
function addMappingRow() {
	var template = document.getElementById('mapping-row-template');
	var clone = template.cloneNode(true);
	clone.removeAttribute('id');
	var controls = clone.querySelectorAll('[data-name]');
	for (var i = 0; i < controls.length; i++) {
		var field = controls[i].getAttribute('data-name');
		controls[i].setAttribute('name', 'mapping[' + mappingRowIndex + '][' + field + ']');
	}
	mappingRowIndex += 1;
	document.getElementById('mapping-rows').appendChild(clone);
}
</script>";

echo "</div></div><br/>";
echo "<p style='font-size: .85em;'>* value is shown in the account reference currency using current conversion logic.</p>";
