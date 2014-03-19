<?php

namespace Catrobat\CatrowebBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CatrowebBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
  }
}
