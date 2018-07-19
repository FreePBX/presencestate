<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
namespace UCP\Modules;
use \UCP\Modules as Modules;

class Presencestate extends Modules{
	protected $module = 'Presencestate';
	private $device = null;
	private $states = null;
	private $types = null;
	private $enabled = true;

	function __construct($Modules) {
		$this->Modules = $Modules;
		$this->device = $this->Modules->getDefaultDevice();
		$this->states = $this->UCP->FreePBX->Presencestate->getAllStates();
		$this->types = $this->UCP->FreePBX->Presencestate->getAllTypes();

		$user = $this->UCP->User->getUser();
		$this->enabled = $this->UCP->getCombinedSettingByID($user['id'],$this->module,'enabled');

		$this->UCP->Modgettext->push_textdomain("presencestate");
		foreach($this->states as &$state) {
			$state['nice'] = _($this->types[$state['type']]);
			switch($state['type']) {
				case 'available':
				case 'chat':
					$state['color'] = 'green';
				break;
				case 'xa':
				case 'away':
					$state['color'] = 'yellow';
				break;
				case 'dnd':
				case 'unavailable':
					$state['color'] = 'red';
				break;
				case 'not_set':
				default:
					$state['color'] = 'grey';
				break;
			}
		}
		$this->UCP->Modgettext->pop_textdomain();

		uasort($this->states, array($this,'sort'));
	}

	private function sort($a,$b) {
		$t = array_keys($this->types);
		return array_search($a['type'],$t) > array_search($b['type'],$t);
	}

	function logout() {
		if(!empty($this->device) && $this->enabled) {
			$user = $this->UCP->User->getUser();
			$state = $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus');
			if(!empty($state) && !empty($this->states[$state])) {
				$type = $this->states[$state]['type'];
				$message = !empty($this->states[$state]['message']) ? $this->states[$state]['message'] : '';
				$this->UCP->FreePBX->astman->set_global($this->UCP->FreePBX->Config->get_conf_setting('AST_FUNC_PRESENCE_STATE') . '(CustomPresence:' . $this->device . ')', '"'.$type . ',,' . $message.'"');
			}
		}
	}

	function login() {

	}

	function poll() {
		if(!empty($this->device) && $this->enabled) {
			$menu = array();
			if(!empty($this->device)) {
				$user = $this->UCP->User->getUser();

				$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
				$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';

				$menu['status'] = true;
				$menu['presence'] = $t;

				$state = $this->UCP->getSetting($user['username'],$this->module,'startsessionstatus');
				$menu['startsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

				$state = $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus');
				$menu['endsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;
			}
			return array('status' => true, 'presence' => $t, 'states' => $this->states, 'menu' => $menu);
		} else {
			return array('status' => false);
		}
	}

	/**
	* Determine what commands are allowed
	*
	* Used by Ajax Class to determine what commands are allowed by this class
	*
	* @param string $command The command something is trying to perform
	* @param string $settings The Settings being passed through $_POST or $_PUT
	* @return bool True if pass
	*/
	function ajaxRequest($command, $settings) {
		switch($command) {
			case 'set':
			case 'savesettings':
				return true;
			default:
				return false;
			break;
		}
	}

	/**
	* The Handler for all ajax events releated to this class
	*
	* Used by Ajax Class to process commands
	*
	* @return mixed Output if success, otherwise false will generate a 500 error serverside
	*/
	function ajaxHandler() {
		$return = array("status" => false, "message" => "");
		if(!$this->enabled) {
			return $return;
		}
		switch($_REQUEST['command']) {
			case 'savesettings':
				$user = $this->UCP->User->getUser();
				$this->UCP->setSetting($user['username'],$this->module,'startsessionstatus',$_POST['startsessionstatus']);
				$this->UCP->setSetting($user['username'],$this->module,'endsessionstatus',$_POST['endsessionstatus']);
				$this->UCP->FreePBX->Presencestate->presencestatePrefsSetMultiple($this->device,$_POST['events']);

				$startsessionstatus = !empty($_POST['startsessionstatus']) && !empty($this->states[$_POST['startsessionstatus']]) ? $this->states[$_POST['startsessionstatus']] : null;
				$endsessionstatus = !empty($_POST['endsessionstatus']) && !empty($this->states[$_POST['endsessionstatus']]) ? $this->states[$_POST['endsessionstatus']] : null;

				return array("status" => true, "message" => "ok", "startsessionstatus" => $startsessionstatus, "endsessionstatus" => $endsessionstatus);
			case 'set':
				$state = !empty($_POST['state']) ? $_POST['state'] : null;
				if(!empty($this->device) && !empty($state)) {
					$type = $this->states[$state]['type'];
					$message = !empty($this->states[$state]['message']) ? $this->states[$state]['message'] : '';
					$this->UCP->FreePBX->astman->set_global($this->UCP->FreePBX->Config->get_conf_setting('AST_FUNC_PRESENCE_STATE') . '(CustomPresence:' . $this->device . ')', '"'.$type . ',,' . $message.'"');
					return array("status" => true, "State" => $type, "Message" => $message, "poller" => $this->poll());
				}
				break;
			default:
				return false;
		}
		return $return;
	}

	public function getStaticSettings() {
		$user = $this->UCP->User->getUser();
		return array(
			'startSessionStatus' => $this->UCP->getSetting($user['username'],$this->module,'startsessionstatus'),
			'endSessionStatus' => $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus')
		);
	}

	public function getWidgetList() {
		$widgetList = $this->getSimpleWidgetList();

		return $widgetList;
	}

	public function getSimpleWidgetList() {
		if(!$this->enabled || empty($this->device)) {
			return array();
		}

		$widgets = array(
			"rawname" => "presencestate",
			"display" => _("Presence"),
			"icon" => "fa fa-user",
			"list" => array(
				"presencestate" => array(
					"display" => _("Presence"),
					"hasSettings" => true,
					"defaultsize" => array("height" => 2, "width" => 1),
					"minsize" => array("height" => 2, "width" => 1)
				)
			)
		);

		return $widgets;
	}

	public function getWidgetDisplay($id) {
		$display = array();

		$display['title'] = _('Presence');

		$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
		$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
		$display['html'] = $this->load_view(__DIR__.'/views/widget.php', array('currentState' => $t, 'states' => $this->states));

		return $display;
	}

	public function getWidgetSettingsDisplay($id) {
		if(!$this->enabled) {
			return '';
		}
		$displayvars = array();
		// fm | dnd | null
		$displayvars['states'] = $this->UCP->FreePBX->Presencestate->presencestatePrefsGet($this->device);
		foreach($displayvars['states'] as $id => &$pref) {
			$tmp = $pref;
			$pref = $this->states[$id];
			$pref['pref'] = $tmp;
			$pref['niceMessage'] = !empty($pref['message']) ? ' - '.$pref['message'] : '';
		}

		usort($displayvars['states'], array($this,'sort'));

		$displayvars['actions'] = array(
			"" => _("Do Nothing"),
			"dnd" => _('Do Not Disturb'),
			"fm" => _('Findme/Follow Me'),
		);
		$user = $this->UCP->User->getUser();
		$displayvars['startsessionstatus'] = $this->UCP->getSetting($user['username'],$this->module,'startsessionstatus');
		$displayvars['endsessionstatus'] = $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus');

		$display = array();
		$display['title'] = _('Presence Settings');
		$display['html'] = $this->load_view(__DIR__.'/views/settings.php',$displayvars);

		return $display;
	}

	public function getSimpleWidgetSettingsDisplay($id) {
		return $this->getWidgetSettingsDisplay($id);
	}
}
