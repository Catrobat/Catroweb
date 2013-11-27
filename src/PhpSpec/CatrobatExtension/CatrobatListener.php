<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
use Symfony\Component\Filesystem\Filesystem;

class CatrobatListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{

  public function beforeSuite(SuiteEvent $event)
  {
    $fixtures_dir = __DIR__."/../../Catrobat/TestBundle/DataFixtures";
    $fixtures_dir = realpath($fixtures_dir)."/";
    define("__SPEC_FIXTURES_DIR__",$fixtures_dir);
    define("__SPEC_GENERATED_FIXTURES_DIR__",$fixtures_dir."/GeneratedFixtures/");
    echo("<info>Using data fixtures in " . __SPEC_FIXTURES_DIR__ . "</info>\n");
    echo("<info>Using generated data fixtures in " . __SPEC_GENERATED_FIXTURES_DIR__ . "</info>\n");
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