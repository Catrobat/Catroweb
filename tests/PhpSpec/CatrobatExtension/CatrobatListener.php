<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\Event\SuiteEvent;
use Symfony\Component\Filesystem\Filesystem;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\Finder\Finder;

/**
 * Class CatrobatListener
 * @package PhpSpec\CatrobatExtension
 */
class CatrobatListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
  /**
   * @var
   */
  protected $io;
  /**
   * @var string
   */
  protected $cache_dir;

  /**
   * CatrobatListener constructor.
   */
  public function __construct()
  {
    $this->cache_dir = __DIR__ . '/../../testdata/Cache';
    define('__SPEC_CACHE_DIR__', $this->cache_dir);
  }

  /**
   * @param SuiteEvent $event
   */
  public function beforeSuite(SuiteEvent $event)
  {
    $fixtures_dir = __DIR__ . '/../../testdata/DataFixtures';
    $fixtures_dir = realpath($fixtures_dir) . '/';
    define('__SPEC_FIXTURES_DIR__', $fixtures_dir);
    define('__SPEC_GENERATED_FIXTURES_DIR__', $fixtures_dir . '/GeneratedFixtures/');
    if ($this->io)
    {
      $this->io->writeln('<info>Using data fixtures in ' . __SPEC_FIXTURES_DIR__ . '</info>');
      $this->io->writeln('<info>Using generated data fixtures in ' . __SPEC_GENERATED_FIXTURES_DIR__ . '</info>');
      $this->io->writeln('<info>Clearing Cache ' . __SPEC_CACHE_DIR__ . ' after every example</info>');
    }
  }

  /**
   * @param ExampleEvent $event
   */
  public function beforeExample(ExampleEvent $event)
  {
    $this->emptyDirectory($this->cache_dir);
  }

  /**
   * @param ConsoleIO $io
   */
  public function setIO(ConsoleIO $io)
  {
    $this->io = $io;
  }

  /**
   * @param $directory
   */
  private function emptyDirectory($directory)
  {
    $filesystem = new Filesystem();

    $finder = new Finder();
    $finder->in($directory)->depth(0);
    foreach ($finder as $file)
    {
      $filesystem->remove($file);
    }
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents()
  {
    return [
      'beforeSuite'   => [
        'beforeSuite',
        -10,
      ],
      'beforeExample' => [
        'beforeExample',
        -10,
      ],
    ];
  }
}
