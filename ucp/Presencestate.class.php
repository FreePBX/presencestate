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

	function __construct($Modules) {
		$this->Modules = $Modules;
	}

	function getDisplay() {
        return 'h';
	}

    function poll() {
		$device = $this->Modules->getDefaultDevice();
		if(!empty($device)) {
			$states = $this->UCP->FreePBX->Presencestate->getAllStates();
			$types = $this->UCP->FreePBX->Presencestate->getAllTypes();
			foreach($states as &$state) {
				$state['nice'] = $types[$state['type']];
			}
			$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$device);
			$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
        	return array('status' => true, 'presence' => $t, 'niceState' => $types[$t['State']], 'states' => $states);
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
			case 'set':
				$device = $this->Modules->getDefaultDevice();
				$state = !empty($_POST['state']) ? $_POST['state'] : null;
				if(!empty($device) && !empty($state)) {
					$states = $this->UCP->FreePBX->Presencestate->getAllStates();
					$type = $states[$state]['type'];
					$message = !empty($states[$state]['message']) ? $states[$state]['message'] : '';
					$this->UCP->FreePBX->astman->set_global($this->UCP->FreePBX->Config->get_conf_setting('AST_FUNC_PRESENCE_STATE') . '(CustomPresence:' . $device . ')', '"'.$type . ',,' . $message.'"');
					return array("status" => true, "State" => $type, "Message" => $message);
				}
			break;
			case 'statuses':
				//PresenceState
				//NOT_SET | UNAVAILABLE | AVAILABLE | AWAY | XA | CHAT | DND
				$device = $this->Modules->getDefaultDevice();
				if(!empty($device)) {
					$states = $this->UCP->FreePBX->Presencestate->getAllStates();
					$types = $this->UCP->FreePBX->Presencestate->getAllTypes();
					foreach($states as &$state) {
						$state['nice'] = $types[$state['type']];
					}
					$t = $this->UCP->FreePBX->astman->PresenceState('CustomPresence:'.$device);
					$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
					$return['status'] = true;
					$return['presence'] = $t;
					$return['presence']['niceState'] = $types[$t['State']];
					$return['states'] = $states;
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
		$menu = array(
			"rawname" => "presencestate",
			"name" => "Presence",
			"badge" => false
		);
		return $menu;
	}

	private function _checkExtension($extension) {
		$user = $this->UCP->User->getUser();
		$extensions = $this->UCP->getSetting($user['username'],$this->module,'assigned');
		return in_array($extension,$extensions);
	}
}
