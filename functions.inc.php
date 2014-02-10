<?php

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

function presencestate_item_put($vars) {
	global $db;

	$sql = 'REPLACE INTO presencestate_list (id, type, message) VALUES (?, ?, ?)';
	$ret = $db->query($sql, array($vars['id'], $vars['type'], $vars['message']));

	if (DB::isError($ret)) {
		die_freepbx("Could not save presence state.\n");
	}

	if (empty($vars['id'])) {
		$vars['id'] = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
	}

	return $vars['id'];
}

function presencestate_item_del($id) {
	global $db;

	$sql = 'DELETE FROM presencestate_list WHERE `id` = ?';
	$ret = $db->query($sql, array($id));

	if (DB::isError($ret)) {
		die_freepbx("Could not delete presence state.\n");
	}
}

?>
