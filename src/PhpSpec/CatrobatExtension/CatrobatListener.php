<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
use Symfony\Component\Filesystem\Filesystem;

class CatrobatListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{

  public function beforeSuite(SuiteEvent $event)
  {
    $fixtures_dir = __DIR__."/../../Catrobat/TestBundle/DataFixtures/GeneratedFixtures/";
    $fixtures_dir = realpath($fixtures_dir)."/";
    define("__SPEC_FIXTURE_DIR__",$fixtures_dir);
    echo("Using data fixtures in " . __SPEC_FIXTURE_DIR__ . "\n");
  }
  
  public static function getSubscribedEvents()
  {
    return array (
        'beforeSuite' => array (
            'beforeSuite',
            - 10
        )
    );
  }

}