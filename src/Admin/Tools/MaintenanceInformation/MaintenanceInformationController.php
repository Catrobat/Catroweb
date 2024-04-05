<?php

declare(strict_types=1);

namespace App\Admin\Tools\MaintenanceInformation;

use Sonata\AdminBundle\Controller\CRUDController;

/**
 * @phpstan-extends CRUDController<object>
 */
class MaintenanceInformationController extends CRUDController
{
  public function __construct()
  {
  }
}
