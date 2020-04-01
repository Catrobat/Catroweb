<?php

namespace App\Catrobat\Services;

class ProgramDevicePermissionReader
{
  /**
   * @return array|bool
   */
  public function getPermissions(string $filepath)
  {
    @$permissions = file('zip://'.$filepath.'#permissions.txt', FILE_IGNORE_NEW_LINES);
    if (!$permissions)
    {
      return [];
    }

    return $permissions;
  }
}
