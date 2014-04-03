<?php

$vars = array(
	'action' => '',
	'submit' => '',
	'id' => '',
	'type' => '',
	'message' => NULL
);

foreach ($vars as $k => $v) {
	$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

if ($vars['submit'] == _('Delete') && $vars['action'] == 'save') {
	$vars['action'] = 'delete';
}

switch ($vars['action']) {
	case 'delete':
		presencestate_item_del($vars['id']);

		$vars['id'] = '';
		$vars['action'] = '';
		break;
	case 'save':
		$vars['id'] = presencestate_item_set($vars);
		break;
}

$presencestates = presencestate_list_get();

$li[] = '<a href="config.php?display=presencestate&action=edit">' . _('New Presence State') . '</a>';
$li[] = '<hr />';

foreach ($presencestates as $presencestate) {
	$display = presencestate_display_get($presencestate['type']);
	if ($presencestate['message']) {
		$display.= ' (' . $presencestate['message'] . ')';
	}

	$li[] = '<a href="config.php?display=presencestate&action=edit&id=' . $presencestate['id'] . '">' . $display . '</a>';
}

echo '<div class="rnav">' . ul($li) . '</div>';


switch($vars['action']) {
case 'edit':
case 'save':
	$presencestate = array(
		'id' => 0,
		'type' => 'available',
		'message' => NULL
	);

	if ($vars['id']) {
		$presencestate = $presencestates[$vars['id']];
	}

	$html = '';

	$html.= heading(_('Presence Status'), 3);
	$html.= '<hr style="width:50%;margin-left:0"/>';
	$html.= form_open($_SERVER['REQUEST_URI']);
	$html.= form_hidden('action', 'save');
	$html.= form_hidden('id', $vars['id']);

	$table = new CI_Table;

	$label = fpbx_label(_('Type'));
	$table->add_row($label, form_dropdown('type', presencestate_types_get(), $presencestate['type']));

	$label = fpbx_label(_('Message'));
	$table->add_row($label, form_input('message', $presencestate['message'])); 

	$html.= $table->generate();

	$html.= br(3);
	$html.= form_submit('submit', _('Submit'));
	if ($vars['id']) {
		$html.= form_submit('submit', _('Delete'));
	}

	$html.= form_close();
	echo $html;

	break;
}
