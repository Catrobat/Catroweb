<?php

declare(strict_types=1);

namespace App;

use App\System\Log\IgnoreLoginRequiredAccessDenied;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
  use MicroKernelTrait;

  #[\Override]
  public function process(ContainerBuilder $container): void
  {
    // The Bugsnag bundle has no config option for custom callbacks.
    $container->getDefinition('bugsnag')
      ->addMethodCall('registerCallback', [new Reference(IgnoreLoginRequiredAccessDenied::class)])
    ;
  }
}
