<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function presencestate_types_get() {
	$types = array(
		'available' => _('Available'),
		'chat' => _('Chat'),
		'away' => _('Away'),
		'dnd' => _('DND'),
		'xa' => _('Extended Away'),
		'unavailable' => _('Unavailable')
	);

	return $types;
}

function presencestate_display_get($state) {
	$display = presencestate_types_get();

	return isset($display[$state]) ? $display[$state] : NULL;
}

function presencestate_prefs_get($extension) {
	global $db;

	$sql = 'SELECT item_id, pref FROM presencestate_prefs WHERE extension = ?';
	$ret = $db->getAll($sql, array($extension), DB_FETCHMODE_ASSOC);

	if (DB::isError($ret)) {
		die_freepbx("Could not get presence state preferences.\n");
	}

	$presencestates = presencestate_list_get();

	foreach ($presencestates as $presencestate) {
		if ($presencestate['type'] == "dnd") {
			/* Default to DND. */
			$prefs[$presencestate['id']] = "dnd";
		} else {
			$prefs[$presencestate['id']] = '';
		}
	}

	foreach ($ret as $row) {
		$prefs[$row['item_id']] = $row['pref'];
	}

	return $prefs;
}

function presencestate_prefs_set($extension, $vars) {
	global $db;

	$sql = 'REPLACE INTO presencestate_prefs (extension, item_id, pref) VALUES (?, ?, ?)';
	$ret = $db->query($sql, array($extension, $vars['id'], $vars['pref']));

	if (DB::isError($ret)) {
		die_freepbx("Could not save presence state preference.\n");
	}
}

function presencestate_list_get() {
	global $db;

	$sql = 'SELECT * FROM presencestate_list';
	$ret = $db->getAll($sql, DB_FETCHMODE_ASSOC);

	if (DB::isError($ret)) {
		die_freepbx("Could not get list of presence states.\n");
	}

	foreach ($ret as $row) {
		$presencestates[$row['id']] = $row;
	}

	asort($presencestates);
	return $presencestates;
}

function presencestate_item_set($vars) {
	global $db;

	$sql = 'REPLACE INTO presencestate_list (id, type, message) VALUES (?, ?, ?)';
	$ret = $db->query($sql, array($vars['id'], $vars['type'], $vars['message']));

	if (DB::isError($ret)) {
		die_freepbx("Could not save presence state.\n");
	}

	if (empty($vars['id'])) {
		$vars['id'] = $db->insert_id();
	}

	return $vars['id'];
}

function presencestate_item_del($id) {
	global $db;

	$sql = 'DELETE FROM presencestate_prefs WHERE `item_id` = ?';
	$ret = $db->query($sql, array($id));

	if (DB::isError($ret)) {
		die_freepbx("Could not delete presence state.\n");
	}

	$sql = 'DELETE FROM presencestate_list WHERE `id` = ?';
	$ret = $db->query($sql, array($id));

	if (DB::isError($ret)) {
		die_freepbx("Could not delete presence state.\n");
	}
}

?>
