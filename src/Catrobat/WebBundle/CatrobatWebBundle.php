<?php

namespace Catrobat\WebBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CatrobatWebBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
  }
}
