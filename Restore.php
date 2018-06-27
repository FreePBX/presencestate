<?php
namespace FreePBX\modules\__MODULENAME__;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
      $configs = $this->getConfigs();
      foreach($configs['statess'] as $state){
          $this->FreePBX->Presencestate->setItem($state);
      }
      $this->FreePBX->Presencestate->loadPrefs($configs['prefs']);
  }
}