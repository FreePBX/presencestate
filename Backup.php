<?php
namespace FreePBX\modules\__MODULENAME__;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $this->addDependency('');
    $configs = [
        'states' => $this->FreePBX->Presencestate->getAllStates(),
        'prefs' => $this->FreePBX->Presencestate->dumpPrefs(),
    ];
    $this->addConfigs($configs);
  }
}