<?php

declare(strict_types=1);

namespace App\System\Testing\Playwright;

use App\DB\Entity\System\Statistic;
use App\Storage\FileHelper;
use App\System\Testing\Behat\ContextTrait;
use App\System\Testing\DataFixtures\DataBaseUtils;
use Symfony\Component\HttpKernel\KernelInterface;

final class WebGeneralFixtureSeeder
{
  use ContextTrait {
    ContextTrait::__construct as private initContextTrait;
  }

  public function __construct(KernelInterface $kernel)
  {
    $this->initContextTrait($kernel);
  }

  /**
   * Recreate the test database and generated fixtures once before the suite.
   *
   * @throws \Exception
   */
  public function prepareEnvironment(): void
  {
    DataBaseUtils::recreateTestEnvironment();
    $this->clearFilesystemState();
  }

  /**
   * Reset browser-facing state and seed a named data set for Playwright.
   *
   * @throws \Exception
   */
  public function seed(string $dataset): void
  {
    $this->resetScenarioState();

    match ($dataset) {
      'minimal' => $this->seedMinimal(),
      'homepage' => $this->seedHomepage(),
      'language-switcher' => $this->seedLanguageSwitcher(),
      'statistics-footer' => $this->seedStatisticsFooter(),
      default => throw new \InvalidArgumentException(sprintf('Unknown Playwright web-general dataset "%s".', $dataset)),
    };
  }

  /**
   * @throws \Exception
   */
  private function resetScenarioState(): void
  {
    DataBaseUtils::databaseRollback();
    $this->clearFilesystemState();
  }

  /**
   * Mirror the Behat cleanup hooks so browser tests start from the same storage state.
   *
   * @throws \Exception
   */
  private function clearFilesystemState(): void
  {
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.file.extract.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.file.storage.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.screenshot.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.thumbnail.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.featuredimage.dir'));
    FileHelper::emptyDirectory($this->getSymfonyParameterAsString('catrobat.apk.dir'));
  }

  /**
   * @throws \Exception
   */
  private function seedMinimal(): void
  {
    $this->insertUser([
      'id' => '9001',
      'name' => 'PlaywrightCatrobat',
      'password' => '123456',
    ], false);

    $this->getManager()->flush();

    $this->insertProject([
      'id' => '9002',
      'name' => 'project 1',
      'owned by' => 'PlaywrightCatrobat',
    ], false);

    $this->getManager()->flush();
  }

  /**
   * @throws \Exception
   */
  private function seedHomepage(): void
  {
    $this->seedUsers([
      ['id' => '9101', 'name' => 'PlaywrightCatrobat'],
      ['id' => '9102', 'name' => 'PlaywrightUser1'],
      ['id' => '9103', 'name' => 'PlaywrightCatrobat2'],
    ]);

    $this->seedExtensions([
      ['id' => '9201', 'internal_title' => 'embroidery'],
    ]);

    $this->seedFlavors([
      ['id' => '9301', 'name' => 'pocketcode'],
      ['id' => '9303', 'name' => 'embroidery'],
    ]);

    $this->seedProjects([
      ['id' => '9401', 'name' => 'project 1', 'owned by' => 'PlaywrightCatrobat', 'extensions' => 'embroidery', 'flavor' => 'pocketcode'],
      ['id' => '9402', 'name' => 'project 2', 'owned by' => 'PlaywrightCatrobat', 'extensions' => 'embroidery', 'flavor' => 'embroidery'],
      ['id' => '9403', 'name' => 'project 3', 'owned by' => 'PlaywrightUser1', 'flavor' => 'pocketcode'],
      ['id' => '9404', 'name' => 'project 4', 'owned by' => 'PlaywrightUser1', 'flavor' => 'embroidery'],
      ['id' => '9405', 'name' => 'project 5', 'owned by' => 'PlaywrightUser1', 'flavor' => 'pocketcode'],
      ['id' => '9406', 'name' => 'project 6', 'owned by' => 'PlaywrightCatrobat2', 'flavor' => 'pocketcode'],
      ['id' => '9407', 'name' => 'project 7', 'owned by' => 'PlaywrightCatrobat2', 'flavor' => 'pocketcode'],
    ]);

    $this->seedFeaturedProjects([
      ['name' => 'project 1', 'active' => '0', 'priority' => '1'],
      ['name' => 'project 2', 'active' => '1', 'priority' => '3'],
      ['name' => 'project 3', 'active' => '1', 'priority' => '2'],
      ['name' => '', 'url' => 'http://www.google.at/', 'active' => '1', 'priority' => '5'],
      ['name' => '', 'url' => 'http://www.orf.at/', 'active' => '0', 'priority' => '4'],
    ]);

    $this->seedExampleProjects([
      ['name' => 'project 4', 'active' => '0', 'priority' => '1'],
      ['name' => 'project 5', 'active' => '1', 'priority' => '3'],
      ['name' => 'project 6', 'active' => '1', 'priority' => '2'],
    ]);

    $this->seedScratchRemixRelations([
      ['scratch_parent_id' => '70058680', 'catrobat_child_id' => '9406'],
      ['scratch_parent_id' => '70058680', 'catrobat_child_id' => '9407'],
    ]);
  }

  /**
   * @throws \Exception
   */
  private function seedLanguageSwitcher(): void
  {
    $this->seedUsers([
      ['id' => '9501', 'name' => 'PlaywrightCatrobat'],
      ['id' => '9502', 'name' => 'PlaywrightOtherUser'],
    ]);

    $this->seedProjects([
      ['id' => '9601', 'name' => 'Minions', 'owned by' => 'PlaywrightCatrobat'],
      ['id' => '9602', 'name' => 'Galaxy', 'owned by' => 'PlaywrightOtherUser'],
      ['id' => '9603', 'name' => 'Alone', 'owned by' => 'PlaywrightCatrobat'],
    ]);

    $this->seedProjectReactions([
      ['username' => 'PlaywrightCatrobat', 'project_id' => '9601', 'type' => 1, 'created at' => '01.01.2017 12:00'],
      ['username' => 'PlaywrightCatrobat', 'project_id' => '9602', 'type' => 2, 'created at' => '01.01.2017 12:00'],
      ['username' => 'PlaywrightOtherUser', 'project_id' => '9601', 'type' => 4, 'created at' => '01.01.2017 12:00'],
    ]);

    $this->seedFeaturedProjects([
      ['name' => 'Minions', 'active' => '1', 'flavor' => 'pocketcode', 'priority' => '1', 'ios_only' => 'no'],
    ]);

    $this->seedForwardRemixRelations([
      ['ancestor_id' => '9601', 'descendant_id' => '9601', 'depth' => '0'],
      ['ancestor_id' => '9601', 'descendant_id' => '9602', 'depth' => '1'],
      ['ancestor_id' => '9602', 'descendant_id' => '9602', 'depth' => '0'],
      ['ancestor_id' => '9603', 'descendant_id' => '9603', 'depth' => '0'],
    ]);
  }

  private function seedStatisticsFooter(): void
  {
    $statistic = $this->getStatisticsRepository()?->findOneBy(['id' => 1]);
    if (!$statistic instanceof Statistic) {
      $statistic = new Statistic();
    }

    $statistic->setUsers('10');
    $statistic->setProjects('17');

    $this->getManager()->persist($statistic);
    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $users
   */
  private function seedUsers(array $users): void
  {
    foreach ($users as $user) {
      $this->insertUser($user, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $projects
   *
   * @throws \Exception
   */
  private function seedProjects(array $projects): void
  {
    foreach ($projects as $project) {
      $this->insertProject($project, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $featuredProjects
   */
  private function seedFeaturedProjects(array $featuredProjects): void
  {
    foreach ($featuredProjects as $featuredProject) {
      $this->insertFeaturedProject($featuredProject, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $exampleProjects
   */
  private function seedExampleProjects(array $exampleProjects): void
  {
    foreach ($exampleProjects as $exampleProject) {
      $this->insertExampleProject($exampleProject, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $flavors
   */
  private function seedFlavors(array $flavors): void
  {
    foreach ($flavors as $flavor) {
      $this->insertFlavor($flavor, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $extensions
   */
  private function seedExtensions(array $extensions): void
  {
    foreach ($extensions as $extension) {
      $this->insertExtension($extension, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $scratchRemixRelations
   */
  private function seedScratchRemixRelations(array $scratchRemixRelations): void
  {
    foreach ($scratchRemixRelations as $scratchRemixRelation) {
      $this->insertScratchRemixRelation($scratchRemixRelation, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $forwardRemixRelations
   */
  private function seedForwardRemixRelations(array $forwardRemixRelations): void
  {
    foreach ($forwardRemixRelations as $forwardRemixRelation) {
      $this->insertForwardRemixRelation($forwardRemixRelation, false);
    }

    $this->getManager()->flush();
  }

  /**
   * @param list<array<string, string>> $projectReactions
   */
  private function seedProjectReactions(array $projectReactions): void
  {
    foreach ($projectReactions as $projectReaction) {
      $config = [
        'username' => $projectReaction['username'] ?? $projectReaction['user'],
        'project_id' => $projectReaction['project_id'] ?? $projectReaction['project'],
        'type' => (int) $projectReaction['type'],
        'created at' => $projectReaction['created at'] ?? $projectReaction['created_at'],
      ];

      $this->insertProjectLike($config, false);
    }

    $this->getManager()->flush();
  }
}
