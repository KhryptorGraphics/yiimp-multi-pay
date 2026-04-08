<?php

function BackendClearEarnings($coinid = NULL)
{
	// debuglog(__FUNCTION__);

	if (YAAMP_ALLOW_EXCHANGE)
		$delay = time() - (int) YAAMP_PAYMENTS_FREQ;
	else
		$delay = time() - (YAAMP_PAYMENTS_FREQ / 2);
	$total_cleared = 0.0;

	$sqlFilter = $coinid ? " AND coinid=".intval($coinid) : '';
	
	$list = getdbolist('db_earnings', "status=1 AND mature_time<$delay $sqlFilter");
	$refresh_users = array();
	
	foreach($list as $earning)
	{
		$user = getdbo('db_accounts', $earning->userid);
		if(!$user)
		{
			$earning->delete();
			continue;
		}

		$coin = getdbo('db_coins', $earning->coinid);
		if(!$coin)
		{
			$earning->delete();
			continue;
		}

		$earning->status = 2;		// cleared
		$earning->price = ($coin->auto_exchange) ? $coin->price : 0 ;
		$earning->save();

		$target_coinid = (int) $coin->id;
		$value = (double) $earning->amount;

		if (!yaamp_user_has_coin_address($user, $target_coinid)) {
			$target_coinid = (int) $user->coinid;
			if (empty($target_coinid) && YAAMP_ALLOW_EXCHANGE) {
				$btc = getdbosql('db_coins', "symbol='BTC'");
				$target_coinid = $btc ? (int) $btc->id : (int) $coin->id;
			}

			$target_coin = getdbo('db_coins', $target_coinid);
			if ($target_coin && $target_coin->id != $coin->id && $coin->price > 0 && $target_coin->price > 0) {
				$value = $earning->amount * $coin->price / $target_coin->price;
			} else if (!$target_coin) {
				debuglog("clear: unable to resolve payout coin for earning {$earning->id}");
				continue;
			}
		}

		yaamp_add_account_coin_balance($user->id, $target_coinid, $value);
		$refresh_users[$user->id] = true;

		if($target_coinid == 6)
			$total_cleared += $value;
	}

	foreach(array_keys($refresh_users) as $userid) {
		yaamp_refresh_account_summary_balance($userid);
	}

	if($total_cleared>0)
		debuglog("total cleared from mining ".bitcoinvaluetoa($total_cleared)." BTC");
}
