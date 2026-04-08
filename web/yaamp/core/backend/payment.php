<?php

function BackendPayments()
{
	// attempt to increase max execution time limit for the cron job
	set_time_limit(300);

	$list = getdbolist('db_coins', "enable AND id IN (SELECT DISTINCT coinid FROM account_balances)");
	foreach($list as $coin)
		BackendCoinPayments($coin);

	dborun("UPDATE accounts SET balance=0 WHERE coinid=0");
}

function BackendUserCancelFailedPayment($userid, $coinid_filter=null)
{
	$user = getdbo('db_accounts', intval($userid));
	if(!$user) return false;

	$amount_failed = 0.0;
	$params = array(':uid'=>$user->id);
	$sql = "account_id=:uid AND IFNULL(tx,'') = ''";
	if (!empty($coinid_filter)) {
		$sql .= " AND idcoin=:coinid";
		$params[':coinid'] = intval($coinid_filter);
	}
	$failed = getdbolist('db_payouts', $sql, $params);
	if (empty($failed))
		return 0.0;

	foreach ($failed as $payout) {
		$coinid = (int) $payout->idcoin;
		if (empty($coinid))
			$coinid = (int) $user->coinid;

		yaamp_add_account_coin_balance($user->id, $coinid, (double) $payout->amount);
		$amount_failed += (double) yaamp_convert_amount_user(getdbo('db_coins', $coinid), (double) $payout->amount, $user);
		$payout->delete();
	}

	yaamp_refresh_account_summary_balance($user);
	return $amount_failed;
}

function BackendCoinPayments($coin)
{
	$remote = new WalletRPC($coin);

	$info = $remote->getinfo();
	if(!$info) {
		debuglog("payment: can't connect to {$coin->symbol} wallet");
		return;
	}

	$txfee = floatval($coin->txfee);
	$min_payout = max(floatval(YAAMP_PAYMENTS_MINI), floatval($coin->payout_min), $txfee);

	if(date("w", time()) == 0 && date("H", time()) > 18) {
		$min_payout = max($min_payout/10, $txfee);
		if($coin->symbol == 'DCR') $min_payout = 0.01005;
	}

	$rows = dbolist(
		"SELECT B.account_id, B.balance ".
		"FROM account_balances B ".
		"INNER JOIN accounts A ON A.id = B.account_id ".
		"WHERE B.coinid=:coinid AND B.balance>:min_payout ".
		"AND (A.payout_threshold IS NULL OR B.balance>A.payout_threshold) ".
		"AND A.is_locked=0 ORDER BY B.balance DESC",
		array(':coinid'=>$coin->id, ':min_payout'=>$min_payout)
	);

	if (empty($rows))
		return;

	$payment_rows = array();
	foreach($rows as $row)
	{
		$user = getdbo('db_accounts', $row['account_id']);
		if(!$user) continue;

		$address = yaamp_get_account_address($user, $coin->id);
		if(empty($address)) {
			debuglog("payment: missing {$coin->symbol} payout address for account {$user->id}");
			continue;
		}

		$amount = (double) $row['balance'];
		if($coin->symbol == 'MBC')
			$amount = round($amount, 2);
		else
			$amount = round($amount, 8);

		if($amount <= 0) continue;

		$payment_rows[] = array(
			'user' => $user,
			'address' => $address,
			'amount' => $amount,
		);

		if ($coin->symbol == 'DCR' && count($payment_rows) > 990) {
			debuglog("payment: more than 990 {$coin->symbol} users to pay, limit to top balances...");
			break;
		}
	}

	if (empty($payment_rows))
		return;

	// Coins with small payout_max or fragile wallets are still processed individually.
	if($coin->symbol == 'BITC' || $coin->symbol == 'BNODE' || $coin->symbol == 'BOD' || $coin->symbol == 'DIME' || $coin->symbol == 'BTCRY' || $coin->symbol == 'IOTS' || $coin->symbol == 'ECC' || $coin->symbol == 'ADOT' || $coin->symbol == 'SAPP' || $coin->symbol == 'CURVE' || $coin->symbol == 'CBE' || $coin->symbol == 'PEPEW' || !empty($coin->payout_max))
	{
		foreach($payment_rows as $entry)
		{
			$user = $entry['user'];
			$address = $entry['address'];
			$amount = $entry['amount'];

			while($amount > $min_payout)
			{
				debuglog("$coin->symbol sendtoaddress $address $amount");
				$tx = $remote->sendtoaddress($address, $amount);
				if(!$tx)
				{
					$error = $remote->error;
					debuglog("RPC $error, $address, $amount");
					if (stripos($error,'transaction too large') !== false || stripos($error,'invalid amount') !== false
						|| stripos($error,'insufficient funds') !== false || stripos($error,'transaction creation failed') !== false
					) {
						$coin->payout_max = min((double) $amount, (double) $coin->payout_max);
						$coin->save();
						$amount /= 2;
						continue;
					}
					break;
				}

				$payout = new db_payouts;
				$payout->account_id = $user->id;
				$payout->time = time();
				$payout->amount = bitcoinvaluetoa($amount);
				$payout->fee = 0;
				$payout->tx = $tx;
				$payout->idcoin = $coin->id;
				$payout->completed = 1;
				$payout->save();

				yaamp_add_account_coin_balance($user->id, $coin->id, -$amount);
				yaamp_refresh_account_summary_balance($user);
				break;
			}
		}

		debuglog("payment done");
		return;
	}

	$total_to_pay = 0.0;
	$coef = 1.0;
	foreach($payment_rows as $entry)
		$total_to_pay += $entry['amount'];

	if(!$total_to_pay)
		return;

	if($info['balance']-$txfee < $total_to_pay && $coin->symbol!='BTC')
	{
		$msg = "$coin->symbol: insufficient funds for payment {$info['balance']} < $total_to_pay!";
		debuglog($msg);
		send_email_alert('payouts', "$coin->symbol payout problem detected", $msg);

		$coef = 0.5;
		$total_to_pay = $total_to_pay * $coef;
		if ($info['balance']-$txfee < $total_to_pay)
			return;
	}

	$addresses = array();
	$payout_amounts = array();
	foreach($payment_rows as $index => $entry)
	{
		$payment_amount = $entry['amount'] * $coef;
		if($coin->symbol == 'MBC')
			$payment_amount = round($payment_amount, 2);
		else
			$payment_amount = round($payment_amount, 8);

		if($payment_amount <= 0) continue;

		$payment_rows[$index]['payment_amount'] = $payment_amount;
		$payout_amounts[$index] = $payment_amount;
		$address = $entry['address'];
		$addresses[$address] = arraySafeVal($addresses, $address, 0) + $payment_amount;
	}

	if(empty($addresses))
		return;

	if($coin->symbol=='BTC')
	{
		global $cold_wallet_table;

		$balance = $info['balance'];
		$renter = dboscalar("SELECT SUM(balance) FROM renters");
		$pie = $balance - $total_to_pay - $renter - 1;

		debuglog("pie to split is $pie");
		if($pie>0)
		{
			foreach($cold_wallet_table as $coldwallet=>$percent)
			{
				$coldamount = round($pie * $percent, 8);
				if($coldamount < $min_payout) break;

				debuglog("paying cold wallet $coldwallet $coldamount");

				$addresses[$coldwallet] = arraySafeVal($addresses, $coldwallet, 0) + $coldamount;
				$total_to_pay += $coldamount;
			}
		}
	}

	debuglog("paying $total_to_pay {$coin->symbol}");

	$payouts = array();
	foreach($payment_rows as $index => $entry)
	{
		if (!isset($entry['payment_amount'])) continue;

		$user = $entry['user'];
		$payment_amount = bitcoinvaluetoa($entry['payment_amount']);

		$payout = new db_payouts;
		$payout->account_id = $user->id;
		$payout->time = time();
		$payout->amount = $payment_amount;
		$payout->fee = 0;
		$payout->idcoin = $coin->id;

		if ($payout->save()) {
			$payouts[$payout->id] = $user->id;
			yaamp_add_account_coin_balance($user->id, $coin->id, -$payment_amount);
			yaamp_refresh_account_summary_balance($user);
		}
	}

	set_time_limit(120);
	$account = $coin->account;

	if (!$coin->txmessage)
		$tx = $remote->sendmany($account, $addresses);
	else
		$tx = $remote->sendmany($account, $addresses, 1, YAAMP_SITE_NAME);

	$errmsg = NULL;
	if(!$tx) {
		debuglog("sendmany: unable to send $total_to_pay {$remote->error} ".json_encode($addresses));
		$errmsg = $remote->error;
	}
	else if(!is_string($tx)) {
		debuglog("sendmany: result is not a string tx=".json_encode($tx));
		$errmsg = json_encode($tx);
	}

	foreach($payouts as $id => $uid) {
		$payout = getdbo('db_payouts', $id);
		if ($payout && $payout->id == $id) {
			$payout->errmsg = $errmsg;
			if (empty($errmsg)) {
				$payout->tx = $tx;
				$payout->completed = 1;
			}
			$payout->save();
		} else {
			debuglog("payout $id for $uid not found!");
		}
	}

	if (!empty($errmsg))
		return;

	debuglog("{$coin->symbol} payment done");

	sleep(2);

	$addresses = array();
	$payouts = array();
	$mailmsg = '';
	$mailwarn = '';
	foreach($payment_rows as $entry)
	{
		$user = $entry['user'];
		$address = $entry['address'];
		$amount_failed = 0.0;
		$failed = getdbolist('db_payouts', "account_id=:uid AND idcoin=:coinid AND IFNULL(tx,'') = '' ORDER BY time", array(
			':uid'=>$user->id,
			':coinid'=>$coin->id,
		));

		if (empty($failed))
			continue;

		if ($coin->symbol == 'CHC') {
			foreach ($failed as $payout) $amount_failed += floatval($payout->amount);
			$notice = "payment: Found buggy payout without tx for {$address}!! $amount_failed {$coin->symbol}";
			debuglog($notice);
			$mailwarn .= "$notice\r\n";
			continue;
		}

		foreach ($failed as $payout) {
			$amount_failed += floatval($payout->amount);
			$payout->delete();
		}

		if ($amount_failed <= 0.0)
			continue;

		debuglog("Found failed payment(s) for {$address}, $amount_failed {$coin->symbol}!");
		if ($coin->rpcencoding == 'DCR') {
			$data = $remote->validateaddress($address);
			if (!$data['isvalid']) {
				debuglog("Found bad address {$address}!! ($amount_failed {$coin->symbol})");
				$user->is_locked = 1;
				$user->save();
				continue;
			}
		}

		$payout = new db_payouts;
		$payout->account_id = $user->id;
		$payout->time = time();
		$payout->amount = $amount_failed;
		$payout->fee = 0;
		$payout->idcoin = $coin->id;
		if ($payout->save() && $amount_failed > $min_payout) {
			$payouts[$payout->id] = $user->id;
			$addresses[$address] = arraySafeVal($addresses, $address, 0) + $amount_failed;
			$mailmsg .= "{$amount_failed} {$coin->symbol} to {$address} - user id {$user->id}\n";
		}
	}

	if (!empty($mailwarn)) {
		send_email_alert('payouts', "{$coin->symbol} payout tx problems to check",
			"$mailwarn\r\nCheck your wallet recent transactions to know if the payment was made, the RPC call timed out."
		);
	}

	if (!empty($addresses))
	{
		if (!$coin->txmessage)
			$tx = $remote->sendmany($account, $addresses);
		else
			$tx = $remote->sendmany($account, $addresses, 1, YAAMP_SITE_NAME." retry");

		if(empty($tx)) {
			debuglog($remote->error);

			foreach ($payouts as $id => $uid) {
				$payout = getdbo('db_payouts', $id);
				if ($payout && $payout->id == $id) {
					$payout->errmsg = $remote->error;
					$payout->save();
				}
			}

			send_email_alert('payouts', "{$coin->symbol} payout problems detected\n {$remote->error}", $mailmsg);

		} else {

			foreach ($payouts as $id => $uid) {
				$payout = getdbo('db_payouts', $id);
				if ($payout && $payout->id == $id) {
					$payout->tx = $tx;
					$payout->save();
				} else {
					debuglog("payout retry $id for $uid not found!");
				}
			}

			$mailmsg .= "\ntxid $tx\n";
			send_email_alert('payouts', "{$coin->symbol} payout problems resolved", $mailmsg);
		}
	}
}
