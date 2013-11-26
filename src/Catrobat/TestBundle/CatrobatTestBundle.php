<?php

namespace Catrobat\TestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Application;

class CatrobatTestBundle extends Bundle
{
  public function registerCommands(Application $application)
  {
    $container = $application->getKernel()->getContainer();
  
    $application->add($container->get('catrobat.test.command.generatetestdata'));
  }
}
