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

	public function getAllTypes() {
		return presencestate_types_get();
	}
	public function getAllStates() {
		return presencestate_list_get();
	}
}
