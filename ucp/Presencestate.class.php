<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
namespace UCP\Modules;
use \UCP\Modules as Modules;
#[\AllowDynamicProperties]
class Presencestate extends Modules{
	protected $module = 'Presencestate';
	private $device = null;
	private $states = null;
	private $types = null;
	private $enabled = true;
	private $user = null;
	private $userId = false;
	private $userName = '';

	function __construct($Modules) {
		$this->Modules = $Modules;
		$this->device = $this->Modules->getDefaultDevice();
		$this->states = $this->UCP->FreePBX->Presencestate->getAllStates();
		$this->types = $this->UCP->FreePBX->Presencestate->getAllTypes();

		$this->user = $this->UCP->User->getUser();
		$this->userId = $this->user ? $this->user["id"] : false;
		$this->userName = $this->user ? $this->user["username"] : '';
		$this->enabled = $this->UCP->getCombinedSettingByID($this->userId,$this->module,'enabled');

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

	private function sort($a, $b) {
		$t = array_keys($this->types);
		$aIndex = array_search($a['type'], $t);
		$bIndex = array_search($b['type'], $t);
	
		if ($aIndex === $bIndex) {
			return 0; // $a and $b are equal
		} elseif ($aIndex < $bIndex) {
			return -1; // $a comes before $b
		} else {
			return 1; // $a comes after $b
		}
	}

	function logout() {
		if(!empty($this->device) && $this->enabled) {
			$state = $this->UCP->getSetting($this->userName,$this->module,'endsessionstatus');
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
				$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
				$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';

				$menu['status'] = true;
				$menu['presence'] = $t;

				$state = $this->UCP->getSetting($this->userName,$this->module,'startsessionstatus');
				$menu['startsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

				$state = $this->UCP->getSetting($this->userName,$this->module,'endsessionstatus');
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
				$this->UCP->setSetting($this->userName,$this->module,'startsessionstatus',$_POST['startsessionstatus']);
				$this->UCP->setSetting($this->userName,$this->module,'endsessionstatus',$_POST['endsessionstatus']);
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
		return array(
			'startSessionStatus' => $this->UCP->getSetting($this->userName,$this->module,'startsessionstatus'),
			'endSessionStatus' => $this->UCP->getSetting($this->userName,$this->module,'endsessionstatus')
		);
	}

	public function getWidgetList() {
		$widgetList = $this->getSimpleWidgetList();

		return $widgetList;
	}

	public function getSimpleWidgetList() {
		$responseData = array(
			"rawname" => "presencestate",
			"display" => _("Presence"),
			"icon" => "fa fa-user",
			"list" => []
		);
		$errors = $this->validate();
		if ($errors['hasError']) {
			return array_merge($responseData, $errors);
		}

		$widgets['presencestate'] = [
			"display" => _("Presence"),
			"hasSettings" => true,
			"defaultsize" => array("height" => 2, "width" => 1),
			"minsize" => array("height" => 2, "width" => 1)
		];

		$responseData['list'] = $widgets;
		return $responseData;
	}

	/**
	 * validate against rules
	 */
	private function validate() {
		$data = array(
			'hasError' => false,
			'errorMessages' => []
		);

		if (!$this->enabled) {
			$data['hasError'] = true;
			$data['errorMessages'][] = _('Presence State is not enabled for this user.');
		}
		if (empty($this->device)) {
			$data['hasError'] = true;
			$data['errorMessages'][] = _("This user doesn't have a default extension.");
		}

		return $data;
	}

	public function getWidgetDisplay($id) {
		$errors = $this->validate();
		if ($errors['hasError']) {
			return $errors;
		}

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
		$displayvars['startsessionstatus'] = $this->UCP->getSetting($this->userName,$this->module,'startsessionstatus');
		$displayvars['endsessionstatus'] = $this->UCP->getSetting($this->userName,$this->module,'endsessionstatus');

		$display = array();
		$display['title'] = _('Presence Settings');
		$display['html'] = $this->load_view(__DIR__.'/views/settings.php',$displayvars);

		return $display;
	}

	public function getSimpleWidgetSettingsDisplay($id) {
		return $this->getWidgetSettingsDisplay($id);
	}
}
