<?php
// vim: set ai ts=4 sw=4 ft=php:
include_once(__DIR__.'/functions.inc.php');
class Presencestate implements BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null)
			throw new Exception("Not given a FreePBX Object");

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}

	public function doConfigPageInit($page) {
		if($page == 'presencestate'){
			$vars = array(
				'action' => $_REQUEST['action']?$_REQUEST['action']:'',
				'submit' => '',
				'id' => $_REQUEST['id']?$_REQUEST['id']:'',
				'type' => $_REQUEST['type']?$_REQUEST['type']:'',
				'message' => $_REQUEST['message']?$_REQUEST['message']:NULL
			);
			switch ($vars['action']) {
				case 'delete':
					presencestate_item_del($vars['id']);
					break;
				case 'save':
					$_REQUEST['id'] = presencestate_item_set($vars);
					break;
			}
		}
	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}
	public function genConfig() {

	}

	public function processUCPAdminDisplay($user) {
		if(isset($_REQUEST['presencestate_enable'])) {
			if($_REQUEST['presencestate_enable'] == 'yes') {
				$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','enabled',true);
			} else {
				$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','enabled',false);
			}
		} else {
			$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','enabled',true);
		}
	}

	public function getUCPAdminDisplay($user) {
		$html = array();
		$enabled = $this->FreePBX->Ucp->getSetting($user['username'],'Presencestate','enabled', true);
		$enabled = is_null($enabled) ? true : $enabled;
		$html[0] = array(
			"title" => _("Presence State"),
			"rawname" => "presencestate",
			"content" => load_view(dirname(__FILE__)."/views/ucp_config.php",array("penabled" => $enabled))
		);
		return $html;
	}

	public function presencestatePrefsGet($extension) {
		return presencestate_prefs_get($extension);
	}

	public function presencestatePrefsSetMultiple($extension, $array) {
		foreach($array as $id => $val) {
			$this->presencestatePrefsSet($extension, array('id'=>$id,'pref'=>$val));
		}
	}

	public function presencestatePrefsSet($extension, $vars) {
		presencestate_prefs_set($extension, $vars);
	}
	public function presencestateItemGet($id){
		$sql = 'SELECT * FROM presencestate_list WHERE id = :id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchObject();
	}
	public function getAllTypes() {
		return presencestate_types_get();
	}
	public function getAllStates() {
		return presencestate_list_get();
	}
	public function ajaxRequest($req, &$setting) {
		if ($req == "getJSON") {
			return true;
		}else{
			return false;
		}
	}
	public function ajaxHandler() {
		if($_REQUEST['command'] == 'getJSON'){
			switch ($_REQUEST['jdata']) {
				case 'grid':
					$list = presencestate_list_get();
					$types = presencestate_types_get();
					$ret = array();
					foreach ($list as $item) {
						$ret[] = array('id' => $item['id'], 'message' => $item['message'], 'type' => $types[$item['type']]);
					}

					return $ret;
				break;

				default:
					print json_encode(_("Invalid Request"));
				break;
			}
		}
	}
	public function getActionBar($request) {
		$buttons = array();
		switch($request['display']) {
			//this is usually your module's rawname
			case 'presencestate':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				//We hide the delete button if we are not editing an item. "id" should be whatever your unique element is.
				if (empty($request['id'])) {
					unset($buttons['delete']);
				}
				//If we are not in the form view lets 86 the buttons
				if (empty($request['view'])){
					unset($buttons);
				}
			break;
		}
		return $buttons;
	}
}
