<?php

echo getAdminSideBarLinks();

$coin_options = array();
foreach($coins as $coin) {
	$coin_options[] = array(
		'id' => intval($coin->id),
		'label' => $coin->algo.' / '.$coin->symbol.' - '.$coin->name,
	);
}

function renderGroupCoinOptions($coin_options, $selectedCoinId)
{
	$html = "<option value=''>Select coin</option>";
	foreach($coin_options as $option) {
		$selected = intval($option['id']) === intval($selectedCoinId) ? " selected='selected'" : '';
		$html .= "<option value='{$option['id']}'{$selected}>".CHtml::encode($option['label'])."</option>";
	}
	return $html;
}

echo "<a href='/admin/mininggroups'>Mining Groups</a>";
if (intval(arraySafeVal($editing_group, 'id', 0)) > 0)
	echo " &gt; Editing ".CHtml::encode(arraySafeVal($editing_group, 'title'));
else if (!empty($editing_group['slug']))
	echo " &gt; New group from ".CHtml::encode(arraySafeVal($editing_group, 'slug'));
echo "<br/><br/>";

if (user()->hasFlash('error'))
	echo "<div class='flash-error' style='color: darkred; margin-bottom: 10px;'>".user()->getFlash('error')."</div>";
if (user()->hasFlash('message'))
	echo "<div class='flash-success' style='color: darkgreen; margin-bottom: 10px;'>".user()->getFlash('message')."</div>";

echo "<div class='main-left-box'>";
echo "<div class='main-left-title'>Mining Group Management</div>";
echo "<div class='main-left-inner'>";
echo "<p style='color:#666;'>Configured groups are the only merge bundles shown on the public mining-groups page and API. Use inferred candidates as a starting point, then save an explicit curated definition here.</p>";

echo "<h3>Configured Groups</h3>";
if (empty($configured_groups)) {
	echo "<p><i>No configured mining groups yet.</i></p>";
} else {
	echo "<table class='dataGrid'>";
	echo "<thead><tr><th>Title</th><th>Algo</th><th>Mode</th><th>Stratum</th><th>Coins</th><th>Status</th><th>Actions</th></tr></thead><tbody>";
	foreach($configured_groups as $group) {
		$coin_labels = array();
		foreach(arraySafeVal($group, 'coins', array()) as $entry) {
			$coin = arraySafeVal($entry, 'coin');
			if ($coin)
				$coin_labels[] = $coin->symbol;
		}
		$status = arraySafeVal($group, 'available') ? 'ready' : 'unavailable';
		echo "<tr>";
		echo "<td><b>".CHtml::encode(arraySafeVal($group, 'title'))."</b><br/><span style='font-size:.85em;color:#666;'>".CHtml::encode(arraySafeVal($group, 'slug'))."</span></td>";
		echo "<td>".CHtml::encode(arraySafeVal($group, 'algo'))."</td>";
		echo "<td>".CHtml::encode(arraySafeVal($group, 'mode_label'))."</td>";
		echo "<td><span style='font-family:monospace;'>".CHtml::encode(arraySafeVal($group, 'stratum'))."</span></td>";
		echo "<td>".CHtml::encode(implode(', ', $coin_labels))."</td>";
		echo "<td>".CHtml::encode($status)."</td>";
		echo "<td>";
		echo "<a href='/admin/mininggroups?id=".intval(arraySafeVal($group, 'id'))."'>Edit</a>";
		echo " &nbsp; ";
		echo "<form method='post' action='/admin/mininggroups' style='display:inline;' onsubmit=\"return confirm('Delete this mining group?');\">";
		echo "<input type='hidden' name='delete_group_id' value='".intval(arraySafeVal($group, 'id'))."'/>";
		echo "<button type='submit'>Delete</button>";
		echo "</form>";
		echo "</td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
}

echo "<h3 style='margin-top:18px;'>Inferred Merge Candidates</h3>";
if (empty($candidate_groups)) {
	echo "<p><i>No unpublished merge candidates are currently inferred from coin metadata.</i></p>";
} else {
	echo "<table class='dataGrid'>";
	echo "<thead><tr><th>Candidate</th><th>Algo</th><th>Coins</th><th>Notes</th><th></th></tr></thead><tbody>";
	foreach($candidate_groups as $group) {
		$coin_labels = array();
		foreach(arraySafeVal($group, 'coins', array()) as $entry) {
			$coin = arraySafeVal($entry, 'coin');
			if ($coin)
				$coin_labels[] = $coin->symbol;
		}
		echo "<tr>";
		echo "<td><b>".CHtml::encode(arraySafeVal($group, 'title'))."</b><br/><span style='font-size:.85em;color:#666;'>".CHtml::encode(arraySafeVal($group, 'slug'))."</span></td>";
		echo "<td>".CHtml::encode(arraySafeVal($group, 'algo'))."</td>";
		echo "<td>".CHtml::encode(implode(', ', $coin_labels))."</td>";
		echo "<td>".CHtml::encode(arraySafeVal($group, 'description'))."</td>";
		echo "<td><a href='/admin/mininggroups?prefill=".urlencode(arraySafeVal($group, 'slug'))."'>Create curated group</a></td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
}

echo "<h3 style='margin-top:18px;'>".(intval(arraySafeVal($editing_group, 'id', 0)) > 0 ? 'Edit Group' : 'Create Group')."</h3>";
echo "<form method='post' action='/admin/mininggroups".(intval(arraySafeVal($editing_group, 'id', 0)) > 0 ? '?id='.intval(arraySafeVal($editing_group, 'id')) : '')."'>";
echo "<input type='hidden' name='mining_group[id]' value='".intval(arraySafeVal($editing_group, 'id', 0))."'/>";

echo "<table class='dataGrid' style='width:100%;'>";
echo "<tr><td width='160'><b>Slug</b></td><td><input type='text' name='mining_group[slug]' value='".CHtml::encode(arraySafeVal($editing_group, 'slug'))."' style='width:100%; font-family:monospace;' maxlength='128'/></td></tr>";
echo "<tr><td><b>Title</b></td><td><input type='text' name='mining_group[title]' value='".CHtml::encode(arraySafeVal($editing_group, 'title'))."' style='width:100%;' maxlength='255'/></td></tr>";
echo "<tr><td><b>Algo</b></td><td><input type='text' name='mining_group[algo]' value='".CHtml::encode(arraySafeVal($editing_group, 'algo'))."' style='width:180px;' maxlength='32'/></td></tr>";
echo "<tr><td><b>Mode</b></td><td><select name='mining_group[mode]'>";
foreach(array('merge' => 'Simultaneous Merge-Mining', 'dedicated' => 'Dedicated', 'switch' => 'Profit Switch') as $value => $label) {
	$selected = arraySafeVal($editing_group, 'mode', 'dedicated') === $value ? " selected='selected'" : '';
	echo "<option value='{$value}'{$selected}>".CHtml::encode($label)."</option>";
}
echo "</select></td></tr>";
echo "<tr><td><b>Description</b></td><td><textarea name='mining_group[description]' rows='3' style='width:100%;'>".CHtml::encode(arraySafeVal($editing_group, 'description'))."</textarea></td></tr>";
echo "<tr><td><b>Hostname</b></td><td><input type='text' name='mining_group[hostname]' value='".CHtml::encode(arraySafeVal($editing_group, 'hostname'))."' style='width:100%;' maxlength='255'/><br/><span style='font-size:.85em;color:#666;'>Leave blank to use the default stratum hostname.</span></td></tr>";
echo "<tr><td><b>Port</b></td><td><input type='text' name='mining_group[port]' value='".CHtml::encode((string) arraySafeVal($editing_group, 'port'))."' style='width:120px;'/></td></tr>";
echo "<tr><td><b>Primary Coin</b></td><td><select name='mining_group[primary_coinid]'>".renderGroupCoinOptions($coin_options, arraySafeVal($editing_group, 'primary_coinid'))."</select></td></tr>";
echo "<tr><td><b>Sort Order</b></td><td><input type='text' name='mining_group[sortorder]' value='".CHtml::encode((string) arraySafeVal($editing_group, 'sortorder', 0))."' style='width:120px;'/></td></tr>";
echo "<tr><td><b>Active</b></td><td><label><input type='checkbox' name='mining_group[active]' value='1'".(intval(arraySafeVal($editing_group, 'active', 1)) ? " checked='checked'" : '')."/> Expose this group publicly</label></td></tr>";
echo "</table>";

echo "<h4 style='margin-top:16px;'>Group Coins</h4>";
echo "<table class='dataGrid'>";
echo "<thead><tr><th>Coin</th><th>Role</th><th>Required</th><th>Position</th></tr></thead><tbody id='group-coin-rows'>";
$row_index = 0;
foreach((array) arraySafeVal($editing_group, 'coins', array()) as $entry) {
	$selected_coinid = intval(arraySafeVal($entry, 'coinid', 0));
	$selected_role = arraySafeVal($entry, 'role', 'member');
	$required = intval(arraySafeVal($entry, 'required', 1));
	$position = intval(arraySafeVal($entry, 'position', $row_index));
	echo "<tr>";
	echo "<td><select name='group_coin[{$row_index}][coinid]'>".renderGroupCoinOptions($coin_options, $selected_coinid)."</select></td>";
	echo "<td><select name='group_coin[{$row_index}][role]'>";
	foreach(array('primary' => 'primary', 'aux' => 'aux', 'member' => 'member') as $value => $label) {
		$selected = $selected_role === $value ? " selected='selected'" : '';
		echo "<option value='{$value}'{$selected}>{$label}</option>";
	}
	echo "</select></td>";
	echo "<td><label><input type='checkbox' name='group_coin[{$row_index}][required]' value='1'".($required ? " checked='checked'" : '')."/> required</label></td>";
	echo "<td><input type='text' name='group_coin[{$row_index}][position]' value='{$position}' style='width:70px;'/></td>";
	echo "</tr>";
	$row_index++;
}
if ($row_index === 0) {
	echo "<tr>";
	echo "<td><select name='group_coin[0][coinid]'>".renderGroupCoinOptions($coin_options, 0)."</select></td>";
	echo "<td><select name='group_coin[0][role]'><option value='primary'>primary</option><option value='aux'>aux</option><option value='member'>member</option></select></td>";
	echo "<td><label><input type='checkbox' name='group_coin[0][required]' value='1' checked='checked'/> required</label></td>";
	echo "<td><input type='text' name='group_coin[0][position]' value='0' style='width:70px;'/></td>";
	echo "</tr>";
	$row_index = 1;
}
echo "</tbody></table>";
echo "<br/><button type='button' onclick='addGroupCoinRow()'>Add Coin Row</button> ";
echo "<input type='submit' value='Save Mining Group'/>";
echo "</form>";

echo "<div style='display:none'><table><tbody><tr id='group-coin-row-template'>";
echo "<td><select data-name='coinid'>".renderGroupCoinOptions($coin_options, 0)."</select></td>";
echo "<td><select data-name='role'><option value='primary'>primary</option><option value='aux'>aux</option><option value='member'>member</option></select></td>";
echo "<td><label><input type='checkbox' data-name='required' value='1' checked='checked'/> required</label></td>";
echo "<td><input type='text' data-name='position' value='0' style='width:70px;'/></td>";
echo "</tr></tbody></table></div>";

echo "<script>
var groupCoinRowIndex = {$row_index};
function addGroupCoinRow() {
	var template = document.getElementById('group-coin-row-template');
	var clone = template.cloneNode(true);
	clone.removeAttribute('id');
	var controls = clone.querySelectorAll('[data-name]');
	for (var i = 0; i < controls.length; i++) {
		var field = controls[i].getAttribute('data-name');
		var name = 'group_coin[' + groupCoinRowIndex + '][' + field + ']';
		controls[i].setAttribute('name', name);
		if (field === 'position')
			controls[i].value = groupCoinRowIndex;
	}
	groupCoinRowIndex += 1;
	document.getElementById('group-coin-rows').appendChild(clone);
}
</script>";

echo "</div></div>";
