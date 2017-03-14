<?php

namespace DivineOmega\ThisIsHowIRole\DatabaseDrivers;

use DivineOmega\ThisIsHowIRole\Interfaces\DatabaseDriverInterface;
use DivineOmega\ThisIsHowIRole\DatabaseDrivers\BaseDatabaseDriver;
use DivineOmega\ThisIsHowIRole\DatabaseDrivers\Eloquent\Role;
use DivineOmega\ThisIsHowIRole\Utils;

class EloquentDatabaseDriver extends BaseDatabaseDriver implements DatabaseDriverInterface
{
  private function cacheKey($className, $foreignId)
  {
    return 'TIHIR_ELOQUENT_'.$className.'_'.$foreignId;
  }

  protected function getRoles($className, $foreignId)
  {
    if (Utils::testModeActive()) {
      return '';
    }

    $cacheKey = $this->cacheKey($className, $foreignId);

    if (!($roles = $this->cache->get($cacheKey)))
    {

      $role = Role::where('class_name', $className)->where('foreign_id', $foreignId)->limit(1)->first();

      if (!$role) {
        $role = new Role;
        $role->roles = '';
        $role->class_name = $className;
        $role->foreign_id = $foreignId;
      }

      $roles = $role->roles;

      $this->cache->set($cacheKey, $roles);

    }

    return $roles;
  }

  protected function setRoles($className, $foreignId, $roles)
  {
    if (Utils::testModeActive()) {
      return;
    }

    $cacheKey = $this->cacheKey($className, $foreignId);

    $this->cache->delete($cacheKey);

    $role = Role::where('class_name', $className)->where('foreign_id', $foreignId)->limit(1)->first();

    $role->roles = $roles;
    $role->save();
  }

}
