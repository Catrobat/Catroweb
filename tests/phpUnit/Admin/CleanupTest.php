<?php


namespace phpUnit\Admin;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CleanupTest extends KernelTestCase
{

  /**
   * @test
   */
  public function cleanLogs()
  {
    // setup app
    $kernel = static::createKernel();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clean:logs');

    /* alternative to $application->find() without using command name. */
//    $kernel->boot();
//    $command = new CleanLogsCommand();
//    $command->setContainer($kernel->getContainer());

    $log_dir = $kernel->getContainer()->getParameter('catrobat.logs.dir');

    // fill directory
    $test_log_dir = $log_dir . DIRECTORY_SEPARATOR . 'test';
    @mkdir($test_log_dir);
    for ($i = 0; $i < 10; $i++)
    {
      tempnam($test_log_dir, '');
    }

    for ($i = 0; $i < 4; $i++)
    {
      tempnam($log_dir, '');
    }

    // run command
    $commandTester = new CommandTester($command);
    $return = $commandTester->execute([]);
    $this->assertEquals(0, $return);

    // check if directory is empty
    $this->assertEmpty(array_diff(scandir($log_dir), ['.', '..', '.gitignore']),
      'Not all files in log directory got deleted.');


  }

}