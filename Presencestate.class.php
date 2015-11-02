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
				'action' => !empty($_REQUEST['action'])?$_REQUEST['action']:'',
				'submit' => '',
				'id' => !empty($_REQUEST['id'])?$_REQUEST['id']:'',
				'type' => !empty($_REQUEST['type'])?$_REQUEST['type']:'',
				'message' => !empty($_REQUEST['message'])?$_REQUEST['message']:NULL
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

	public function ucpDelGroup($id,$display,$data) {
	}

	public function ucpAddGroup($id, $display, $data) {
		$this->ucpUpdateGroup($id,$display,$data);
	}

	public function ucpUpdateGroup($id,$display,$data) {
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'group') {
			if(isset($_POST['presencestate_enable']) && $_POST['presencestate_enable'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByGID($id,'Presencestate','enabled',true);
			} else {
				$this->FreePBX->Ucp->setSettingByGID($id,'Presencestate','enabled',false);
			}
		}
	}

	/**
	* Hook functionality from userman when a user is deleted
	* @param {int} $id      The userman user id
	* @param {string} $display The display page name where this was executed
	* @param {array} $data    Array of data to be able to use
	*/
	public function ucpDelUser($id, $display, $ucpStatus, $data) {

	}

	/**
	* Hook functionality from userman when a user is added
	* @param {int} $id      The userman user id
	* @param {string} $display The display page name where this was executed
	* @param {array} $data    Array of data to be able to use
	*/
	public function ucpAddUser($id, $display, $ucpStatus, $data) {
		$this->ucpUpdateUser($id, $display, $ucpStatus, $data);
	}

	/**
	* Hook functionality from userman when a user is updated
	* @param {int} $id      The userman user id
	* @param {string} $display The display page name where this was executed
	* @param {array} $data    Array of data to be able to use
	*/
	public function ucpUpdateUser($id, $display, $ucpStatus, $data) {
		if($display == 'userman' && isset($_POST['type']) && $_POST['type'] == 'user') {
			if(isset($_POST['presencestate_enable']) && $_POST['presencestate_enable'] == 'yes') {
				$this->FreePBX->Ucp->setSettingByID($id,'Presencestate','enabled',true);
			} elseif(isset($_POST['presencestate_enable']) && $_POST['presencestate_enable'] == 'no') {
				$this->FreePBX->Ucp->setSettingByID($id,'Presencestate','enabled',false);
			} elseif(isset($_POST['presencestate_enable']) && $_POST['presencestate_enable'] == 'inherit') {
				$this->FreePBX->Ucp->setSettingByID($id,'Presencestate','enabled',null);
			}
		}
	}

	public function ucpConfigPage($mode, $user, $action) {
		if(empty($user)) {
			$enabled = ($mode == 'group') ? true : null;
		} else {
			if($mode == 'group') {
				$enabled = $this->FreePBX->Ucp->getSettingByGID($user['id'],'Presencestate','enabled');
				$enabled = !($enabled) ? false : true;
			} else {
				$enabled = $this->FreePBX->Ucp->getSettingByID($user['id'],'Presencestate','enabled');
			}
		}

		$html = array();
		$html[0] = array(
			"title" => _("Presence State"),
			"rawname" => "presencestate",
			"content" => load_view(dirname(__FILE__)."/views/ucp_config.php",array("mode" => $mode, "enabled" => $enabled))
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
					$buttons = array();
				}
			break;
		}
		return $buttons;
	}
	public function getRightNav($request) {
	  if(isset($request['view']) && $request['view'] == 'form'){
	    return load_view(__DIR__."/views/bootnav.php",array());
	  }
	}
}
