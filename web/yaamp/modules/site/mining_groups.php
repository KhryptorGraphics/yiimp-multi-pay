<?php

$algo = trim(getparam('algo'));
$address = trim(getparam('address'));
$user = !empty($address) ? getuserparam($address) : null;
$groups = yaamp_get_mining_groups($algo, $user);

$algos = array();
foreach ($groups as $group) {
	$algoName = arraySafeVal($group, 'algo');
	if (!empty($algoName))
		$algos[$algoName] = $algoName;
}
ksort($algos);

$grouped = array();
foreach ($groups as $group) {
	$algoName = arraySafeVal($group, 'algo', 'unknown');
	if (!isset($grouped[$algoName]))
		$grouped[$algoName] = array();
	$grouped[$algoName][] = $group;
}
ksort($grouped);
?>

<div class="main-left-box">
<div class="main-left-title">Mining Groups</div>
<div class="main-left-inner">

<p>Use this page to see operator-curated mining groups that are ready for public use. Dedicated groups have their own port. Merge groups appear here only after they have been explicitly configured, and they require a payout address for every required member coin.</p>

<form method="get" action="/site/mining_groups" style="margin-bottom: 12px;">
	<label><b>Algo</b></label>
	<select name="algo" style="margin-left: 6px; margin-right: 12px;">
		<option value="">All algos</option>
		<?php foreach ($algos as $algoName): ?>
			<option value="<?= CHtml::encode($algoName) ?>"<?= $algoName === $algo ? ' selected="selected"' : '' ?>><?= CHtml::encode($algoName) ?></option>
		<?php endforeach; ?>
	</select>

	<label><b>Wallet address</b></label>
	<input type="text" name="address" size="42" value="<?= CHtml::encode($address) ?>" placeholder="Optional wallet for readiness checks" style="margin-left: 6px; margin-right: 12px;" />
	<button type="submit">Refresh</button>
	<?php if (!empty($algo) || !empty($address)): ?>
		<a href="/site/mining_groups" style="margin-left: 10px;">Clear</a>
	<?php endif; ?>
</form>

<?php if (!empty($address) && !$user): ?>
	<div style="padding: 8px 10px; margin-bottom: 10px; border: 1px solid #e2c0c0; background: #fff4f4; color: #933;">
		This wallet is not known to the pool yet. Readiness is shown with placeholders until the account is created by a miner login.
	</div>
<?php endif; ?>

<?php if (empty($grouped)): ?>
	<p><i>No mining groups are defined for the current filter.</i></p>
<?php endif; ?>

<?php foreach ($grouped as $algoName => $algoGroups): ?>
	<h3 style="margin-top: 18px; margin-bottom: 8px;"><?= CHtml::encode(strtoupper($algoName)) ?></h3>
	<?php foreach ($algoGroups as $group): ?>
		<?php
		$state = arraySafeVal($group, 'user_state', array());
		$primaryCoin = arraySafeVal($group, 'primary_coin');
		$ready = (bool) arraySafeVal($state, 'ready');
		$available = (bool) arraySafeVal($group, 'available');
		$statusColor = $ready ? '#216b2f' : ($available ? '#8a6d1f' : '#933');
		$statusText = $ready ? 'Ready' : ($available ? 'Missing addresses' : 'Unavailable');
		?>
		<div style="border: 1px solid #d9e3ec; background: #fbfdff; padding: 12px; margin-bottom: 14px;">
			<div style="display: flex; justify-content: space-between; gap: 12px; align-items: baseline; flex-wrap: wrap;">
				<div>
					<b style="font-size: 1.05em;"><?= CHtml::encode(arraySafeVal($group, 'title')) ?></b>
					<span style="margin-left: 8px; color: #4b5f78;"><?= CHtml::encode(arraySafeVal($group, 'mode_label')) ?></span>
					<span style="margin-left: 8px; color: #7b8896; font-size: .9em;"><?= CHtml::encode(arraySafeVal($group, 'source_label')) ?></span>
				</div>
				<div style="color: <?= $statusColor ?>; font-weight: bold;"><?= CHtml::encode($statusText) ?></div>
			</div>

			<?php if (!empty($group['description'])): ?>
				<div style="margin-top: 6px; color: #57697c;"><?= CHtml::encode($group['description']) ?></div>
			<?php endif; ?>

			<div style="margin-top: 10px;">
				<div><b>Stratum:</b> <span style="font-family: monospace;"><?= CHtml::encode(arraySafeVal($group, 'stratum')) ?></span></div>
				<?php if ($primaryCoin): ?>
					<div><b>Primary coin:</b> <?= CHtml::encode($primaryCoin->name) ?> (<?= CHtml::encode($primaryCoin->symbol) ?>)</div>
				<?php endif; ?>
				<?php if (!empty($address)): ?>
					<div><b>Configured:</b> <?php $configuredText = implode(', ', arraySafeVal($state, 'configured_symbols', array())); ?><?= !empty($configuredText) ? CHtml::encode($configuredText) : '<i>none</i>' ?></div>
					<div><b>Missing:</b> <?php $missingText = implode(', ', arraySafeVal($state, 'missing_symbols', array())); ?><?= !empty($missingText) ? CHtml::encode($missingText) : '<i>none</i>' ?></div>
				<?php endif; ?>
			</div>

			<table class="dataGrid2" style="margin-top: 10px;">
				<thead>
					<tr>
						<th></th>
						<th>Coin</th>
						<th>Role</th>
						<th>Required</th>
						<?php if (!empty($address)): ?><th>Configured address</th><?php endif; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($group['coins'] as $entry): ?>
					<?php
					$coin = $entry['coin'];
					$coinAddress = ($user) ? yaamp_get_account_address($user, $coin->id) : null;
					?>
					<tr class="ssrow">
						<td width="18"><img width="16" src="<?= CHtml::encode($coin->image) ?>" /></td>
						<td><?= CHtml::encode($coin->name) ?> (<?= CHtml::encode($coin->symbol) ?>)</td>
						<td><?= CHtml::encode(strtoupper(arraySafeVal($entry, 'role', 'member'))) ?></td>
						<td><?= intval(arraySafeVal($entry, 'required', 1)) ? 'yes' : 'optional' ?></td>
						<?php if (!empty($address)): ?>
							<td style="font-family: monospace; font-size: .85em;"><?= !empty($coinAddress) ? CHtml::encode($coinAddress) : '<span style="color:#933;">missing</span>' ?></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<div style="margin-top: 10px; padding: 8px 10px; background: #ffffee; border: 1px solid #ebe3ab;">
				<div><b>Username</b>: <span style="font-family: monospace;"><?= CHtml::encode(arraySafeVal($group['setup'], 'username')) ?></span></div>
				<div><b>Password</b>: <span style="font-family: monospace;"><?= CHtml::encode(arraySafeVal($group['setup'], 'password')) ?></span></div>
			</div>
		</div>
	<?php endforeach; ?>
<?php endforeach; ?>

</div>
</div>
