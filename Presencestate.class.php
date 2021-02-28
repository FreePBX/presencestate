<?php
// vim: set ai ts=4 sw=4 ft=php:
class Presencestate implements BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null)
			throw new Exception("Not given a FreePBX Object");

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->FreePBX->Modules->loadFunctionsInc("presencestate");
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
		/* Check for first install */
		$q = $this->db->query('SELECT count(*) as count FROM presencestate_list;');
		$res = $q->fetch(\PDO::FETCH_ASSOC);
		if ($res['count'] == 0) {
			/* Add default presence states */
			$sql = 'INSERT INTO presencestate_list (`type`) VALUES
			 ("available"),
			 ("chat"),
			 ("away"),
			 ("dnd"),
			 ("xa"),
			 ("unavailable")
			;';
			$this->db->query($sql);
		}
	}
	public function uninstall() {

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
		$this->FreePBX->Modules->loadFunctionsInc('presencestate');
		return presencestate_prefs_get($extension);
	}

	public function presencestatePrefsSetMultiple($extension, $array) {
		$this->FreePBX->Modules->loadFunctionsInc('presencestate');
		foreach($array as $id => $val) {
			$this->presencestatePrefsSet($extension, array('id'=>$id,'pref'=>$val));
		}
	}

	public function presencestatePrefsSet($extension, $vars) {
		$this->FreePBX->Modules->loadFunctionsInc('presencestate');
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
		$this->FreePBX->Modules->loadFunctionsInc('presencestate');
		return presencestate_types_get();
	}
	public function getAllStates() {
        $sql = 'SELECT * FROM presencestate_list';
        $ret = $this->db->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
	$presencestates = [];
        foreach ($ret as $row) {
            $presencestates[$row['id']] = $row;
        }
        asort($presencestates);
        return $presencestates;
	}
	public function setDatabase($pdo){
		$this->db = $pdo;
		return $this;
	}
	
	public function resetDatabase(){
		$this->db = $this->FreePBX->Database;
		return $this;
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

	/**
	 * Get All Device States
	 * @method getAllDevicesStates
	 * @return array
	 */
	public function getAllDevicesStates() {
		$devices = $this->FreePBX->astman->PresenceStateList();
		$states = $this->getAllStates();
		$final = array();
		foreach($devices as $t) {
			$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
			$result = array(
				"id" => null,
				"type" => $t['Status'],
				"message" => $t['Message']
			);
			foreach($states as $state) {
				if($t['Message'] == $state['message'] && $state['type'] == $t['Status']) {
					$result['id'] = $state['id'];
				}
			}
			$parts = explode(":",$t['Presentity']);
			$final[$parts[1]] = $result;
		}
		return $final;
	}

	/**
	 * Get Presence State by Device
	 * @method getStateByDevice
	 * @param  integer           $device The device ID
	 * @return array                   The device state
	 */
	public function getStateByDevice($device) {
		$dev = $this->FreePBX->Core->getDevice($device);
		if(empty($dev)) {
			throw new \Exception("Device does not exist!");
		}
		$t = $this->FreePBX->astman->PresenceState('CustomPresence:'.$device);
		$t['Message'] = ($t['Message'] != 'Presence State') ? $t['Message'] : '';
		$states = $this->getAllStates();
		$result = array(
			"id" => null,
			"type" => $t['State'],
			"message" => $t['Message']
		);
		foreach($states as $state) {
			if($t['Message'] == $state['message'] && $state['type'] == $t['State']) {
				$result['id'] = $state['id'];
			}
		}
		return $result;
	}

	/**
	 * Set Presence State by Device
	 * @method setStateByDevice
	 * @param  integer           $device  The device ID
	 * @param  string           $state   The presence state state
	 * @param  string           $message The message to override
	 */
	public function setStateByDevice($device, $state, $message = null) {
		$dev = $this->FreePBX->Core->getDevice($device);
		if(empty($dev)) {
			throw new \Exception("Device does not exist!");
		}
		$states = $this->getAllStates();
		if(!empty($state) && !empty($states[$state])) {
			$type = $states[$state]['type'];
			$msg = !empty($states[$state]['message']) ? $states[$state]['message'] : '';
			$msg = !empty($message) ? $message : $msg;
			$this->FreePBX->astman->set_global($this->FreePBX->Config->get_conf_setting('AST_FUNC_PRESENCE_STATE') . '(CustomPresence:' . $device . ')', '"'.$type . ',,' . $msg.'"');
		} else {
			throw new \Exception("Invalid state of '".$state."'");
		}
	}

	public function getRightNav($request) {
	  if(isset($request['view']) && $request['view'] == 'form'){
	    return load_view(__DIR__."/views/bootnav.php",array());
	  }
    }
    public function dumpPrefs(){
        return $this->db->query('SELECT * FROM presencestate_prefs')
            ->fetchAll(PDO::FETCH_ASSOC);
    }
    public function loadPrefs($prefs){
        $stmt = $this->db->prepare('REPLACE INTO presencestate_prefs (extension, item_id, pref) VALUES (:extension, :item_id, :pref)');
        foreach ($prefs as $item) {
            if(count($item) !== 3){
                continue;
            }
            $stmt->execute([
                ':extension' => $item['extension'],
                ':item_id' => $item['item_id'],
                ':pref' => $item['pref'],
            ]);
        }
        return $this;
    }

    public function setItem($vars){
        $sql = 'REPLACE INTO presencestate_list (id, type, message) VALUES (?, ?, ?)';
        $this->db->prepare($sql)
            ->execute([$vars['id'], $vars['type'], $vars['message']]);
        return $vars['id'];

    }
}
