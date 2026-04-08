<?php

echo <<<end
<div align="right" style="margin-top: -14px; margin-bottom: 6px;">
<input class="search" type="search" data-column="all" style="width: 140px;" placeholder="Search..." />
</div>
<style type="text/css">
tr.ssrow.filtered { display: none; }
.currency { width: 120px; max-width: 180px; text-align: right; }
.red { color: darkred; }
.actions { width: 120px; text-align: right; }
table.totals { margin-top: 8px; margin-right: 16px; }
table.totals th { text-align: left; width: 100px; }
table.totals td { text-align: right; }
table.totals tr.red td { color: darkred; }
.page .footer { width: auto; }
</style>
end;

$coin_id = getiparam('id');
$coin_filter = $coin_id ? getdbo('db_coins', $coin_id) : null;

$saveSort = $coin_id ? 'false' : 'true';

showTableSorter('maintable', "{
	tableClass: 'dataGrid',
	textExtraction: {
		3: function(node, table, n) { return $(node).attr('data'); }
	},
	widgets: ['zebra','filter','Storage','saveSort'],
	widgetOptions: {
		saveSort: {$saveSort},
		filter_saveFilters: {$saveSort},
		filter_external: '.search',
		filter_columnFilters: false,
		filter_childRows : true,
		filter_ignoreCase: true
	}
}");

echo <<<end
<thead>
<tr>
<th data-sorter="" width="20"></th>
<th data-sorter="text">Coin</th>
<th data-sorter="text">Address</th>
<th data-sorter="numeric">Last block</th>
<th data-sorter="currency" class="currency">Pool</th>
<th data-sorter="currency" class="currency">Balance</th>
<th data-sorter="currency" class="currency">Immature</th>
<th data-sorter="currency" class="currency">Failed</th>
<th data-sorter="" class="actions">Actions</th>
</tr>
</thead><tbody>
end;

$sqlFilter = $coin_id ? "AND coinid={$coin_id}" : "";
$limit = $coin_id ? '' : 'LIMIT 100';

$data = dbolist("SELECT coinid, userid, SUM(amount) AS immature FROM earnings WHERE status=0 $sqlFilter GROUP BY coinid, userid");
$immature = array();
if (!empty($data)) foreach ($data as $row) {
	$immkey = $row['coinid']."-".$row['userid'];
	$immature[$immkey] = $row['immature'];
}

$failedFilter = $coin_id ? "AND idcoin={$coin_id}" : "";
$data = dbolist("SELECT account_id, IFNULL(idcoin, 0) AS coinid, SUM(amount) AS failed FROM payouts WHERE tx IS NULL AND completed=0 $failedFilter GROUP BY account_id, idcoin");
$failed = array();
if (!empty($data)) foreach ($data as $row) {
	$key = $row['account_id'].'-'.intval($row['coinid']);
	$failed[$key] = (double) $row['failed'];
}

if ($coin_id) {
	$list = getdbolist('db_accounts', "is_locked != 1 AND (".
		"id IN (SELECT DISTINCT account_id FROM account_balances WHERE coinid={$coin_id} AND balance > 0) ".
		"OR id IN (SELECT DISTINCT userid FROM earnings WHERE status=0 AND coinid={$coin_id}) ".
		"OR id IN (SELECT DISTINCT account_id FROM payouts WHERE tx IS NULL AND completed=0 AND idcoin={$coin_id})".
		") ORDER BY last_earning DESC $limit");
} else {
	$list = getdbolist('db_accounts', "is_locked != 1 AND (".
		"id IN (SELECT DISTINCT account_id FROM account_balances WHERE balance > 0) ".
		"OR id IN (SELECT DISTINCT userid FROM earnings WHERE status=0) ".
		"OR id IN (SELECT DISTINCT account_id FROM payouts WHERE tx IS NULL AND completed=0)".
		") ORDER BY last_earning DESC $limit");
}

$total = 0.; $totalimmat = 0.; $totalfailed = 0.;
foreach($list as $user)
{
	$coin = $coin_filter ? $coin_filter : getdbo('db_coins', $user->coinid);
	$d = datetoa2($user->last_earning);
	$address_rows = yaamp_get_account_address_rows($user);
	$address_count = count($address_rows);
	$extra_payouts = max(0, $address_count - 1);

	echo '<tr class="ssrow">';

	if($coin) {
		$coinbalance = $coin->balance ? bitcoinvaluetoa($coin->balance) : '';
		echo '<td><img width="16" src="'.$coin->image.'"></td>';
		echo '<td><b><a href="/admin/coin?id='.$coin->id.'">'.$coin->name.'</a></b>&nbsp;('.$coin->symbol_show.')</td>';
		$immkey = "{$coin->id}-{$user->id}";
	} else {
		$coinbalance = '-';
		echo '<td></td>';
		echo '<td></td>';
		$immkey = "0-{$user->id}";
	}

	$display_address = CHtml::encode($user->username);
	if ($coin && yaamp_get_account_address($user, $coin->id))
		$display_address = CHtml::encode(yaamp_get_account_address($user, $coin->id));
	if ($extra_payouts > 0)
		$display_address .= '<br/><span style="font-size: .75em; color: #666;">+'.intval($extra_payouts).' extra payout'.($extra_payouts > 1 ? 's' : '').'</span>';

	echo '<td><a href="/?address='.$user->username.'"><b>'.$display_address.'</b></a></td>';
	echo '<td>'.$d.'</td>';

	echo '<td class="currency">'.$coinbalance.'</td>';

	$balance_value = $coin ? yaamp_get_account_coin_balance($user->id, $coin->id) : yaamp_user_balance_summary($user);
	if (!$coin_id && $coin)
		$balance_value = yaamp_user_balance_summary($user);
	$balance = $balance_value ? bitcoinvaluetoa($balance_value) : '';
	$total += (double) $balance_value;
	echo '<td class="currency">'.$balance.'</td>';

	$immbalance = arraySafeVal($immature, $immkey, 0);
	if (!$coin_id)
		$immbalance = yaamp_convert_earnings_user($user, "status=0");
	$totalimmat += (double) $immbalance;
	$immbalance = $immbalance ? bitcoinvaluetoa($immbalance) : '';
	echo '<td class="currency">'.$immbalance.'</td>';

	$failed_key = $coin ? ($user->id.'-'.$coin->id) : ($user->id.'-0');
	$failbalance = arraySafeVal($failed, $failed_key, 0);
	if (!$coin_id)
		$failbalance = yaamp_convert_failed_payouts_user($user, "account_id={$user->id}");
	$totalfailed += (double) $failbalance;
	$failbalance = $failbalance ? bitcoinvaluetoa($failbalance) : '';
	echo '<td class="currency red">'.$failbalance.'</td>';

	echo '<td class="actions">';
	if ($failbalance != '-')
		echo '<a href="/admin/cancelUserPayment?id='.$user->id.($coin_id ? '&coinid='.$coin_id : '').'">[add to balance]</a>';
	echo ' <a href="/admin/usermultipay?id='.$user->id.'">[multi-pay]</a>';
	echo '</td>';

	echo "</tr>";
}

echo '</tbody><tfoot>';
echo '<tr><th colspan="9">';
echo count($list).' users';
if (count($list) == 100) echo " ($limit)";
echo '</th></tr>';
echo '</tfoot></table>';

if ($coin_id) {
	$coin = getdbo('db_coins', $coin_id);
	$symbol = $coin->symbol;
	echo '<div class="totals" align="right">';
	echo '<table class="totals">';
	echo '<tr><th>Balances</th><td>'.bitcoinvaluetoa($total)." $symbol</td></tr>";
	echo '<tr><th>Immature</th><td>'.bitcoinvaluetoa($totalimmat)." $symbol</td></tr>";
	if ($totalfailed) {
		echo '<tr class="red"><th>Failed</th><td>'.bitcoinvaluetoa($totalfailed)." $symbol</td></tr>";
		echo '<tr><td colspan="2">'.'<a href="/admin/cancelUsersPayment?id='.$coin_id.'" title="Add to balance all failed payouts">Reset all failed</a></td></tr>';
	}
	echo '</tr></table>';
	echo '</div>';
}
