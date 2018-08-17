<?php

namespace PhpSpec\CatrobatExtension;

use PhpSpec\Event\SuiteEvent;
use Symfony\Component\Filesystem\Filesystem;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\Finder\Finder;

class CatrobatListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    protected $io;
    protected $cache_dir;

    public function __construct()
    {
        $this->cache_dir = realpath(__DIR__.'/../../../testdata/Cache').'/';
        define('__SPEC_CACHE_DIR__', $this->cache_dir);
    }

    public function beforeSuite(SuiteEvent $event)
    {
        $fixtures_dir = __DIR__.'/../../../testdata/DataFixtures';
        $fixtures_dir = realpath($fixtures_dir).'/';
        define('__SPEC_FIXTURES_DIR__', $fixtures_dir);
        define('__SPEC_GENERATED_FIXTURES_DIR__', $fixtures_dir.'GeneratedFixtures/');
        if ($this->io) {
            $this->io->writeln('<info>Using data fixtures in '.__SPEC_FIXTURES_DIR__.'</info>');
            $this->io->writeln('<info>Using generated data fixtures in '.__SPEC_GENERATED_FIXTURES_DIR__.'</info>');
            $this->io->writeln('<info>Clearing Cache '.__SPEC_CACHE_DIR__.' after every example</info>');
        }
    }

    public function beforeExample(ExampleEvent $event)
    {
        $this->emptyDirectory($this->cache_dir);
    }

    public function setIO(ConsoleIO $io)
    {
        $this->io = $io;
    }

    private function emptyDirectory($directory)
    {
        $filesystem = new Filesystem();

        $finder = new Finder();
        $finder->in($directory)->depth(0);
        foreach ($finder as $file) {
            $filesystem->remove($file);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
        'beforeSuite' => array(
            'beforeSuite',
            -10,
        ),
        'beforeExample' => array(
            'beforeExample',
            -10,
        ),
    );
    }
}
