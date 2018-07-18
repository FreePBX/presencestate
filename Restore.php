<?php
namespace FreePBX\modules\Presencestate;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
      $configs = reset($this->getConfigs());
      foreach($configs['states'] as $state){
          $this->FreePBX->Presencestate->setItem($state);
      }
      $this->FreePBX->Presencestate->loadPrefs($configs['prefs']);
  }
}
