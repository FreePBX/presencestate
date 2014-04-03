<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db;

$sql[] = 'CREATE TABLE IF NOT EXISTS `presencestate_list` (
 `id` int(11) NOT NULL auto_increment,
 `type` varchar(25),
 `message` varchar(80) default NULL,
 PRIMARY KEY (`id`)
);';

$sql[] = 'CREATE TABLE IF NOT EXISTS `presencestate_prefs` (
 `extension` varchar(20) NOT NULL,
 `item_id` int(11) NOT NULL,
 `pref` varchar(25),
 PRIMARY KEY (`extension`, `item_id`)
);';

/* Check for first install */
$q = $db->query('SELECT * FROM presencestate_list;');
if (DB::isError($q)) {
	/* Add default presence states */
	$sql[] = 'INSERT INTO presencestate_list (`type`) VALUES
	 ("available"),
	 ("chat"),
	 ("away"),
	 ("dnd"),
	 ("xa"),
	 ("unavailable")
	;';
}

foreach ($sql as $statement){
	$check = $db->query($statement);
	if (DB::IsError($check)){
		die_freepbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

?>
