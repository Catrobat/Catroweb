<?php

namespace App;

use Exception;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
  use MicroKernelTrait;

  /**
   * @var string
   */
  const CONFIG_EXTS = '.{php,xml,yaml,yml}';

  public function getCacheDir(): string
  {
    return $this->getProjectDir().'/var/cache/'.$this->environment;
  }

  public function getLogDir(): string
  {
    return $this->getProjectDir().'/var/log';
  }

  public function registerBundles()
  {
    $contents = require $this->getProjectDir().'/config/bundles.php';
    foreach ($contents as $class => $envs)
    {
      if ($envs[$this->environment] ?? $envs['all'] ?? false)
      {
        yield new $class();
      }
    }
  }

  /**
   * @throws Exception
   */
  protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
  {
    $c->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
    $c->setParameter('container.dumper.inline_class_loader', true);

    $confDir = $this->getProjectDir().'/config';
    $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
    $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
    $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
    $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
  }

  /**
   * @throws Exception
   */
  protected function configureRoutes(RouteCollectionBuilder $routes): void
  {
    $confDir = $this->getProjectDir().'/config';
    $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
    $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
    $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
  }
}
