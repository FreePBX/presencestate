<?php
/**
 * This is the User Control Panel Object.
 *
 * Copyright (C) 2013 Schmooze Com, INC
 * Copyright (C) 2013 Andrew Nagy <andrew.nagy@schmoozecom.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   FreePBX UCP BMO
 * @author   Andrew Nagy <andrew.nagy@schmoozecom.com>
 * @license   AGPL v3
 */
namespace UCP\Modules;
use \UCP\Modules as Modules;

class Presencestate extends Modules{
	protected $module = 'Presencestate';
	private $device = null;
	private $states = null;
	private $types = null;

	function __construct($Modules) {
		$this->Modules = $Modules;
		$this->device = $this->Modules->getDefaultDevice();
		$this->states = $this->UCP->FreePBX->Presencestate->getAllStates();
		$this->types = $this->UCP->FreePBX->Presencestate->getAllTypes();
		foreach($this->states as &$state) {
			$state['nice'] = $this->types[$state['type']];
		}
		uasort($this->states, array($this,'sort'));
	}

	private function sort($a,$b) {
		$t = array_keys($this->types);
		return array_search($a['type'],$t) > array_search($b['type'],$t);
	}

	function logout() {
		if(!empty($this->device)) {
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

		$html .= $this->loadCSS();
		$html .= $this->loadLESS();
		$html .= $this->loadScripts();
		$html .= $this->load_view(__DIR__.'/views/settings.php',$displayvars);

		return $html;
	}

	function poll() {
		if(!empty($this->device)) {
			$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$this->device);
			$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
			return array('status' => true, 'presence' => $t, 'niceState' => $this->types[$t['State']], 'states' => $this->states);
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
					return array("status" => true, "State" => $type, "Message" => $message);
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
					$return['states'] = array_values($this->states);

					$state = $this->UCP->getSetting($user['username'],$this->module,'startsessionstatus');
					$return['startsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;

					$state = $this->UCP->getSetting($user['username'],$this->module,'endsessionstatus');
					$return['endsessionstatus'] = !empty($state) && !empty($this->states[$state]) ? $this->states[$state] : null;
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
		$menu = array();
		if(!empty($this->device)) {
			$menu = array(
				"rawname" => "presencestate",
				"name" => "Presence",
				"badge" => false
			);
		}
		return $menu;
	}
}
