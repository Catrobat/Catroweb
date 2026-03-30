<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\DB\Entity\Project\Program;
use App\System\Testing\Behat\ContextTrait;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

class SanitizerContext implements Context
{
  use ContextTrait;

  /**
   * @BeforeSuite
   */
  public static function enableSanitizer(): void
  {
    $_ENV['TEXT_SANITIZER_ENABLED'] = 'true';
    $_SERVER['TEXT_SANITIZER_ENABLED'] = 'true';
    putenv('TEXT_SANITIZER_ENABLED=true');
  }

  #[Then('the project should have name :name')]
  public function theProjectShouldHaveName(string $name): void
  {
    $project = $this->getLastUploadedProject();
    Assert::assertSame($name, $project->getName());
  }

  #[Then('the project should have description :description')]
  public function theProjectShouldHaveDescription(string $description): void
  {
    $project = $this->getLastUploadedProject();
    Assert::assertSame($description, $project->getDescription());
  }

  private function getLastUploadedProject(): Program
  {
    $projects = $this->getManager()->getRepository(Program::class)->findBy(
      [],
      ['uploaded_at' => 'DESC'],
      1
    );
    Assert::assertNotEmpty($projects, 'No project found in database');

    return $projects[0];
  }
}
