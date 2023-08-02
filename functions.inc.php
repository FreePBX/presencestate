<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function presencestate_types_get() {
	$types = ['available' => _('Available'), 'chat' => _('Chat'), 'away' => _('Away'), 'dnd' => _('DND'), 'xa' => _('Extended Away'), 'unavailable' => _('Unavailable')];

	return $types;
}

function presencestate_display_get($state) {
	$display = presencestate_types_get();

	return $display[$state] ?? NULL;
}

function presencestate_prefs_get($extension) {
	$prefs = [];
 global $db;

	$sql = 'SELECT item_id, pref FROM presencestate_prefs WHERE extension = ?';
	$ret = $db->getAll($sql, [$extension], DB_FETCHMODE_ASSOC);

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
	$ret = $db->query($sql, [$extension, $vars['id'], $vars['pref']]);

	if (DB::isError($ret)) {
		die_freepbx("Could not save presence state preference.\n");
	}
}

function presencestate_list_get() {
    FreePBX::Modules()->deprecatedFunction();
    return FreePBX::Presencestate()->getAllStates();
}

function presencestate_item_set($vars) {
    FreePBX::Modules()->deprecatedFunction();
    return FreePBX::Presencestate()->setItem($vars);
}

function presencestate_item_del($id) {
	global $db;

	$sql = 'DELETE FROM presencestate_prefs WHERE `item_id` = ?';
	$ret = $db->query($sql, [$id]);

	if (DB::isError($ret)) {
		die_freepbx("Could not delete presence state.\n");
	}

	$sql = 'DELETE FROM presencestate_list WHERE `id` = ?';
	$ret = $db->query($sql, [$id]);

	if (DB::isError($ret)) {
		die_freepbx("Could not delete presence state.\n");
	}
}
