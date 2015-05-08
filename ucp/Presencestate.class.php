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

	function getDisplay() {
		if(!$this->enabled) {
			return '';
		}
		$html = '';
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

		$html .= $this->load_view(__DIR__.'/views/settings.php',$displayvars);

		return $html;
	}

	function poll() {
		if(!empty($this->device) && $this->enabled) {
			$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
			$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
			$niceState = (!empty($t['State']) && !empty($this->types[$t['State']])) ? $this->types[$t['State']] : '';
			$menu = array();
			if(!empty($this->device)) {
				$user = $this->UCP->User->getUser();

				$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
				$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
				$menu['status'] = true;
				$menu['presence'] = $t;
				$menu['presence']['niceState'] = $niceState;
				$menu['representations'] = array(
					'available' => array('color' => 'green', 'name' => _('Available')),
					'chat' => array('color' => 'green', 'name' => _('Chat')),
					'xa' => array('color' => 'yellow', 'name' => _('Extended Away')),
					'away' => array('color' => 'yellow', 'name' => _('Away')),
					'dnd' => array('color' => 'red', 'name' => _('Do Not Disturb')),
					'unavailable' => array('color' => 'red', 'name' => _('Unavailable')),
					'not_set' => array('color' => 'grey', 'name' => _('Offline')),
				);

				$state = $this->UCP->getSetting($user['username'],$this->module,'startsessionstatus');
				$menu['startsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

				$state = $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus');
				$menu['endsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

				$menu['html'] = $this->getStatusMenu($t);
			}
			return array('status' => true, 'presence' => $t, 'niceState' => $niceState, 'states' => $this->states, 'menu' => $menu);
		} else {
			return array('status' => false);
		}
	}

	function getStatusMenu($t=null) {
		$t = empty($t) ? $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device) : $t;
		return $this->load_view(__DIR__.'/views/statusesMenu.php',array('currentState' => $t, 'states' => $this->states));
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
			case 'statuses':
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
			case 'statuses':
				//PresenceState
				//NOT_SET | UNAVAILABLE | AVAILABLE | AWAY | XA | CHAT | DND
				if(!empty($this->device)) {
					$user = $this->UCP->User->getUser();

					$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
					$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
					$return['status'] = true;
					$return['presence'] = $t;
					$return['presence']['niceState'] = $this->types[$t['State']];
					$menu['representations'] = array(
						'available' => array('color' => 'green', 'name' => _('Available')),
						'chat' => array('color' => 'green', 'name' => _('Chat')),
						'xa' => array('color' => 'yellow', 'name' => _('Extended Away')),
						'away' => array('color' => 'yellow', 'name' => _('Away')),
						'dnd' => array('color' => 'red', 'name' => _('Do Not Disturb')),
						'unavailable' => array('color' => 'red', 'name' => _('Unavailable')),
						'not_set' => array('color' => 'grey', 'name' => _('Offline')),
					);

					$state = $this->UCP->getSetting($user['username'],$this->module,'startsessionstatus');
					$return['startsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

					$state = $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus');
					$return['endsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

					$return['html'] = $this->getStatusMenu($t);
				}
				return $return;
			default:
				return false;
			break;
		}
		return $return;
	}

	public function getBadge() {
		return false;
	}

	public function getMenuItems() {
		if(!$this->enabled) {
			return array();
		}
		$menu = array();
		if(!empty($this->device)) {
			$menu = array(
				"rawname" => "presencestate",
				"name" => _("Presence"),
				"badge" => false
			);
		}
		return $menu;
	}

	public function getNavItems() {
		$out = array();
		$out[] = array(
			"rawname" => "presencestate",
			"badge" => false,
			"icon" => "fa-circle",
			"extra" => '<div class="p-container"><div class="p-msg"><span></span></div></div>',
			"menu" => array(
				"html" => '<li class="statuses">' . $this->getStatusMenu() . '</li>'
			)
		);
		return $out;
	}
}
