<?php

namespace Catrobat\AppBundle\Services;

/**
 * Class ProgramDevicePermissionReader
 * @package Catrobat\AppBundle\Services
 */
class ProgramDevicePermissionReader
{
  /**
   * @param $filepath
   *
   * @return array|bool
   */
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
