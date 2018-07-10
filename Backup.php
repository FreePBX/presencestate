<?php
namespace FreePBX\modules\Presencestate;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $configs = [
        'states' => $this->FreePBX->Presencestate->getAllStates(),
        'prefs' => $this->FreePBX->Presencestate->dumpPrefs(),
    ];
    $this->addConfigs($configs);
  }
}
