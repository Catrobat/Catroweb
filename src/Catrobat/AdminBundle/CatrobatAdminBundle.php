<?php

namespace Catrobat\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Application;

class CatrobatAdminBundle extends Bundle
{
	public function registerCommands(Application $application)
	{
		$container = $application->getKernel()->getContainer();
	
		$application->add($container->get('catrowebadmin.command.import'));
		$application->add($container->get('catrowebadmin.command.init'));
	}
}
