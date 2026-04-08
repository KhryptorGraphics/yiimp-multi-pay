<?php

require dirname(__FILE__).'/../../ui/lib/pageheader.php';

$user = getuserparam(getparam('address'));
if(!$user) return;

$this->pageTitle = $user->username.' | '.YAAMP_SITE_NAME;

$bitcoin = getdbosql('db_coins', "symbol='BTC'");

echo "<div class='main-left-box'>";
echo "<div class='main-left-title'>Transactions to $user->username</div>";
echo "<div class='main-left-inner'>";

$list = getdbolist('db_payouts', "account_id={$user->id} ORDER BY time DESC");

echo '<table class="dataGrid2">';

echo "<thead>";
echo "<tr>";
echo "<th></th>";
echo "<th>Time</th>";
echo "<th>Coin</th>";
echo "<th align=right>Amount</th>";
echo "<th>Address</th>";
echo "<th align=right>Value*</th>";
echo "<th>Tx</th>";
echo "</tr>";
echo "</thead>";

$total_value = 0;
foreach($list as $payout)
{
	$d = datetoa2($payout->time);
	$amount = bitcoinvaluetoa($payout->amount);
	$payout_coin = getdbo('db_coins', $payout->idcoin ? $payout->idcoin : $user->coinid);
	if(!$payout_coin) continue;
	$value = yaamp_convert_amount_user($payout_coin, (double) $payout->amount, $user);

	echo "<tr class='ssrow'>";
	echo '<td width=18><img width="16" src="'.$payout_coin->image.'"></td>';
	echo "<td><b>$d ago</b></td>";
	echo "<td><b>{$payout_coin->symbol}</b></td>";
	echo "<td align=right><b>$amount {$payout_coin->symbol}</b></td>";
	echo '<td style="font-family: monospace;">'.CHtml::encode(yaamp_get_account_address($user, $payout_coin->id)).'</td>';
	echo "<td align=right><b>".bitcoinvaluetoa($value)."</b></td>";

	$url = $payout_coin->createExplorerLink($payout->tx, array('txid'=>$payout->tx), array('target'=>'_blank'));
	echo '<td style="font-family: monospace;">'.$url.'</td>';

	echo "</tr>";
	$total_value += $value;
}

$total_value = bitcoinvaluetoa($total_value);

echo "<tr class='ssrow' style='border-top: 2px solid #eee;'>";
echo "<td width=18></td>";
echo "<td><b>Total</b></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td align=right><b>$total_value</b></td>";
echo "<td></td>";

echo "</tr>";

echo "</table><br>";
echo "</div></div><br>";
echo "<p style='font-size: .85em;'>* payout values are shown in the account reference currency.</p>";

