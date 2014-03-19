<?php

namespace Catrobat\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProjectValidatorCompilerPass implements CompilerPassInterface
{
  
  /*
   * (non-PHPdoc) @see \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::process()
   */
  public function process(ContainerBuilder $container)
  {
    if (!$container->hasDefinition('catroweb.file.validator'))
    {
      return;
    }
    
    $definition = $container->getDefinition('catroweb.file.validator');
    
    $taggedServices = $container->findTaggedServiceIds('catroweb.file.validator.service');
    foreach($taggedServices as $id => $attributes)
    {
      $definition->addMethodCall('addValidator', array (
          new Reference($id) 
      ));
    }
  }

}