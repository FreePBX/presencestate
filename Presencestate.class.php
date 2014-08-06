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
		if(isset($_REQUEST['presencestate|enable'])) {
			if($_REQUEST['presencestate|enable'] == 'yes') {
				$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','enabled',true);
			} else {
				$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','enabled',false);
			}
		} else {
			$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','enabled',true);
		}
		$this->FreePBX->Ucp->setSetting($user['username'],'Presencestate','saved',true);
	}

	public function getUCPAdminDisplay($user) {
		$html = array();
		$saved = $this->FreePBX->Ucp->getSetting($user['username'],'Presencestate','saved');
		$enabled = ($saved) ? $this->FreePBX->Ucp->getSetting($user['username'],'Presencestate','enabled') : true;
		$html[0]['description'] = '<a href="#" class="info">'._("Enable Presence").':<span>'._("Whether to allow this user to be able to change their presence state/status in UCP").'</span></a>';
		$html[0]['content'] = load_view(dirname(__FILE__)."/views/ucp_config.php",array("enabled" => $enabled));
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

	public function getAllTypes() {
		return presencestate_types_get();
	}
	public function getAllStates() {
		return presencestate_list_get();
	}
}
