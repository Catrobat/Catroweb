<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
use Symfony\Component\Filesystem\Filesystem;
use PhpSpec\Console\IO;

class CatrobatListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
  protected $io;
  
  public function beforeSuite(SuiteEvent $event)
  {
    $fixtures_dir = __DIR__."/../../Catrobat/TestBundle/DataFixtures";
    $fixtures_dir = realpath($fixtures_dir)."/";
    define("__SPEC_FIXTURES_DIR__",$fixtures_dir);
    define("__SPEC_GENERATED_FIXTURES_DIR__",$fixtures_dir."GeneratedFixtures/");
    if ($this->io)
    {
      $this->io->writeln("<info>Using data fixtures in " . __SPEC_FIXTURES_DIR__ . "</info>");
      $this->io->writeln("<info>Using generated data fixtures in " . __SPEC_GENERATED_FIXTURES_DIR__ . "</info>");
    }
  }
  
  public function setIO(IO $io)
  {
    $this->io = $io;
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