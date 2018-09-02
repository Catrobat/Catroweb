<?php

namespace Catrobat\AppBundle\Services;

class ProgramDevicePermissionReader
{
  public function getPermissions($filepath)
  {
    @$permissions = file('zip://' . $filepath . "#permissions.txt", FILE_IGNORE_NEW_LINES);
    if ($permissions === false)
    {
      return [];
    }

    return $permissions;
  }
}
