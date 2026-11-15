<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\BackgroundJob;

use App\DB\Entity\Project\Project;
use App\DB\Entity\System\BackgroundJob;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\ConvertScratchProject;
use App\Project\Scratch\ConvertScratchProjectHandler;
use App\Project\Scratch\ScratchManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConvertScratchProjectHandler::class)]
final class ConvertScratchProjectHandlerTest extends TestCase
{
  public function testAConvertedProjectFinishesTheJob(): void
  {
    $project = new Project();
    $project->setId('converted');
    $job = $this->handle(static fn (): Project => $project);

    $this->assertSame(BackgroundJob::DONE, $job->getState());
    $this->assertSame('converted', $job->getResult());
  }

  public function testAProjectThatCannotBeConvertedFailsTheJobWithTheMessage(): void
  {
    $job = $this->handle(static fn (): never => throw new ScratchConversionException('The Scratch project is too large to convert.'));

    $this->assertSame(BackgroundJob::FAILED, $job->getState());
    $this->assertSame(422, $job->getErrorStatus());
    $this->assertSame('The Scratch project is too large to convert.', $job->getErrorMessage());
  }

  public function testAnUnknownProjectFailsWith404(): void
  {
    $job = $this->handle(static fn (): never => throw new ScratchConversionException('gone', ScratchConversionException::NOT_FOUND));

    $this->assertSame(404, $job->getErrorStatus());
  }

  public function testScratchBeingDownIsRetried(): void
  {
    $this->expectException(\RuntimeException::class);
    $this->handle(static fn (): never => throw new ScratchConversionException('down', ScratchConversionException::UPSTREAM));
  }

  private function handle(\Closure $import): BackgroundJob
  {
    $job = new BackgroundJob(ScratchManager::JOB_TYPE, '1');
    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('find')->willReturn($job);
    $scratch_manager = $this->createStub(ScratchManager::class);
    $scratch_manager->method('importProject')->willReturnCallback($import);

    new ConvertScratchProjectHandler($scratch_manager, $entity_manager)(new ConvertScratchProject($job->getId(), 1, null));

    return $job;
  }
}
