<?php

namespace Tests\phpUnit\Commands;

use App\Catrobat\Services\TestEnv\DataFixtures\ProjectDataFixtures;
use App\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * Class ClearExtractedProjectsTest.
 *
 * @internal
 * @coversNothing
 */
class ClearExtractedProjectsTest extends KernelTestCase
{
  private ProjectDataFixtures $project_data_fixtures;

  private CommandTester $command_tester;

  private string $extract_dir;

  protected function setUp(): void
  {
    $kernel = static::createKernel();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clear:extracted');
    $this->command_tester = new CommandTester($command);

    $this->project_data_fixtures = $kernel->getContainer()->get(ProjectDataFixtures::class);

    $this->extract_dir = $kernel->getContainer()->getParameter('catrobat.file.extract.dir');

    // create dir if not exists
    if (!file_exists($this->extract_dir))
    {
      mkdir($this->extract_dir);
    }

    $this->clearExtractDir();
  }

  /**
   * @test
   */
  public function clearExtractedProjectsShouldRemoveAllUnusedData(): void
  {
    $this->generateUnusedData();

    $return = $this->command_tester->execute([]);
    $this->assertEquals(0, $return);

    // check if directory is empty
    $this->assertEmpty(array_diff(scandir($this->extract_dir), ['.', '..', '.gitignore']),
      'Not all files in log directory got deleted.');
  }

  /**
   * @test
   */
  public function clearExtractedProjectsShouldNotRemoveUnusedData(): void
  {
    $this->generateUnusedData();

    $this->project_data_fixtures->insertProject(['directory_hash' => 'realHash'], true);
    mkdir($this->extract_dir.DIRECTORY_SEPARATOR.'realHash');

    $return = $this->command_tester->execute([]);
    $this->assertEquals(0, $return);

    // check that directory contains still all the used data
    $this->assertCount(1, array_diff(scandir($this->extract_dir), ['.', '..', '.gitignore']),
      'All files in log directory got deleted.');

    RefreshTestEnvHook::databaseRollback();
  }

  /**
   * @test
   */
  public function clearExtractedProjectsShouldRemoveAllDataIfSpecified(): void
  {
    $this->generateUnusedData();

    $this->project_data_fixtures->insertProject(['directory_hash' => 'realHash2'], true);
    mkdir($this->extract_dir.DIRECTORY_SEPARATOR.'realHash2');

    $return = $this->command_tester->execute(['--remove-all' => true]);
    $this->assertEquals(0, $return);

    // check if directory is empty
    $this->assertEmpty(array_diff(scandir($this->extract_dir), ['.', '..', '.gitignore']),
      'Not all files in log directory got deleted.');

    RefreshTestEnvHook::databaseRollback();
  }

  private function clearExtractDir(): void
  {
    $files = array_diff(scandir($this->extract_dir), ['.', '..', '.gitignore']);
    foreach ($files as $dir)
    {
      if (is_dir($this->extract_dir.$dir))
      {
        Utils::removeDirectory($this->extract_dir.$dir);
      }
    }
  }

  private function generateUnusedData(): void
  {
    for ($i = 0; $i < 15; ++$i)
    {
      $dir_name = $this->extract_dir.DIRECTORY_SEPARATOR.'fakeHash'.$i;
      mkdir($dir_name);
      mkdir($dir_name.DIRECTORY_SEPARATOR.'recursive_dir');
      touch($dir_name.DIRECTORY_SEPARATOR.'code.xml');
    }
  }
}
