<?php

namespace Catrobat\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Catrobat\CoreBundle\DependencyInjection\ProjectValidatorCompilerPass;

class CatrobatCoreBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
    $container->addCompilerPass(new ProjectValidatorCompilerPass());
  }
}
