<?php

namespace Catrobat\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Catrobat\ApiBundle\Security\UploadTokenSecurityFactory;

class CatrobatApiBundle extends Bundle
{

  public function build(ContainerBuilder $container)
  {
    parent::build($container);
    
    $extension = $container->getExtension('security');
    $extension->addSecurityListenerFactory(new UploadTokenSecurityFactory());
  }

}
