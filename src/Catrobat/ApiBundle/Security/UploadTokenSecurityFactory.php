<?php

namespace Catrobat\ApiBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Reference;

class UploadTokenSecurityFactory implements SecurityFactoryInterface
{

  public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
  {
    $providerId = 'security.authentication.provider.uploadtoken.' . $id;
    $container->setDefinition($providerId, new DefinitionDecorator('uploadtoken.security.authentication.provider'))->replaceArgument(0, new Reference($userProvider));
    
    $listenerId = 'security.authentication.listener.uploadtoken.' . $id;
    $listener = $container->setDefinition($listenerId, new DefinitionDecorator('uploadtoken.security.authentication.listener'))->replaceArgument(2, $id);
    
    return array (
        $providerId,
        $listenerId,
        $defaultEntryPoint 
    );
  }

  public function getPosition()
  {
    return 'pre_auth';
  }

  public function getKey()
  {
    return 'uploadtoken';
  }

  public function addConfiguration(NodeDefinition $node)
  {
  }

}