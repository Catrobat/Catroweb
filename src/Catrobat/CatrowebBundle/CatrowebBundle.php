<?php

namespace Catrobat\CatrowebBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Catrobat\CatrowebBundle\DependencyInjection\ProjectValidatorCompilerPass;

class CatrowebBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
    $container->addCompilerPass(new ProjectValidatorCompilerPass());
  }
}
