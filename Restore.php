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
    public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir)
    {
        $tables = array_flip($tables + $unknownTables);
        if (!isset($tables['presencestate_list'])) {
            return $this;
        }
        $cb = $this->FreePBX->Presencestate;
        $cb->setDatabase($pdo);
        $configs = [
            'states' => $this->FreePBX->Presencestate->getAllStates(),
            'prefs' => $this->FreePBX->Presencestate->dumpPrefs(),
        ];
        $cb->resetDatabase();
        foreach ($configs['states'] as $state) {
           $cb->setItem($state);
        }
        $cb->loadPrefs($configs['prefs']);

        return $this;
    }
}
