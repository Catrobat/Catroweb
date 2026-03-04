<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentAppealRepository;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\AppealState;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportState;
use App\Moderation\AppealException;
use App\Moderation\AppealProcessor;
use App\Moderation\ContentVisibilityManager;
use App\Moderation\TrustScoreCalculator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AppealProcessor::class)]
final class AppealProcessorTest extends TestCase
{
  private function buildProcessor(
    ?ContentAppealRepository $appeal_repository = null,
    ?ContentReportRepository $report_repository = null,
    ?ContentVisibilityManager $visibility_manager = null,
    ?EntityManagerInterface $entity_manager = null,
    ?TrustScoreCalculator $trust_calculator = null,
  ): AppealProcessor {
    return new AppealProcessor(
      $appeal_repository ?? $this->createStub(ContentAppealRepository::class),
      $report_repository ?? $this->createStub(ContentReportRepository::class),
      $visibility_manager ?? $this->createStub(ContentVisibilityManager::class),
      $entity_manager ?? $this->createStub(EntityManagerInterface::class),
      $trust_calculator ?? $this->createStub(TrustScoreCalculator::class),
    );
  }

  private function createUserStub(string $id = 'appellant-id'): User
  {
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn($id);

    return $user;
  }

  private function createValidVisibility(string $owner_id = 'appellant-id'): ContentVisibilityManager
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('isContentHidden')->willReturn(true);
    $visibility->method('getContentOwnerId')->willReturn($owner_id);

    return $visibility;
  }

  #[Group('unit')]
  public function testEmptyReasonThrowsException(): void
  {
    $processor = $this->buildProcessor();

    $this->expectException(AppealException::class);
    $this->expectExceptionCode(AppealException::CODE_REASON_REQUIRED);

    $processor->processAppeal(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      '',
    );
  }

  #[Group('unit')]
  public function testWhitespaceOnlyReasonThrowsException(): void
  {
    $processor = $this->buildProcessor();

    $this->expectException(AppealException::class);
    $this->expectExceptionCode(AppealException::CODE_REASON_REQUIRED);

    $processor->processAppeal(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      '   ',
    );
  }

  #[Group('unit')]
  public function testContentNotFoundThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(false);

    $processor = $this->buildProcessor(visibility_manager: $visibility);

    $this->expectException(AppealException::class);
    $this->expectExceptionCode(AppealException::CODE_NOT_FOUND);

    $processor->processAppeal(
      $this->createUserStub(),
      ContentType::Project,
      'nonexistent-id',
      'This is my content',
    );
  }

  #[Group('unit')]
  public function testContentNotHiddenThrowsException(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('contentExists')->willReturn(true);
    $visibility->method('isContentHidden')->willReturn(false);

    $processor = $this->buildProcessor(visibility_manager: $visibility);

    $this->expectException(AppealException::class);
    $this->expectExceptionCode(AppealException::CODE_NOT_HIDDEN);

    $processor->processAppeal(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'This is my content',
    );
  }

  #[Group('unit')]
  public function testNonOwnerThrowsException(): void
  {
    $visibility = $this->createValidVisibility('other-owner');

    $processor = $this->buildProcessor(visibility_manager: $visibility);

    $this->expectException(AppealException::class);
    $this->expectExceptionCode(AppealException::CODE_NOT_OWNER);

    $processor->processAppeal(
      $this->createUserStub('not-the-owner'),
      ContentType::Project,
      'project-id',
      'This is my content',
    );
  }

  #[Group('unit')]
  public function testPendingAppealExistsThrowsException(): void
  {
    $visibility = $this->createValidVisibility();

    $appeal_repo = $this->createStub(ContentAppealRepository::class);
    $appeal_repo->method('hasExistingAppeal')->willReturn(true);

    $processor = $this->buildProcessor(
      appeal_repository: $appeal_repo,
      visibility_manager: $visibility,
    );

    $this->expectException(AppealException::class);
    $this->expectExceptionCode(AppealException::CODE_ALREADY_PENDING);

    $processor->processAppeal(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'This is my content',
    );
  }

  #[Group('unit')]
  public function testSuccessfulAppealCreation(): void
  {
    $visibility = $this->createValidVisibility();

    $appeal_repo = $this->createStub(ContentAppealRepository::class);
    $appeal_repo->method('hasExistingAppeal')->willReturn(false);

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      appeal_repository: $appeal_repo,
      visibility_manager: $visibility,
      entity_manager: $em,
    );

    $result = $processor->processAppeal(
      $this->createUserStub(),
      ContentType::Project,
      'project-id',
      'This content was incorrectly flagged',
    );

    $this->assertSame('project', $result->getContentType());
    $this->assertSame('project-id', $result->getContentId());
    $this->assertSame('This content was incorrectly flagged', $result->getReason());
    $this->assertSame(AppealState::Pending->value, $result->getState());
  }

  #[Group('unit')]
  public function testApproveAppealShowsContent(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->expects($this->once())
      ->method('showContent')
      ->with(ContentType::Project, 'project-id')
    ;

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('findReportsForContent')->willReturn([]);

    $trust = $this->createStub(TrustScoreCalculator::class);
    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      visibility_manager: $visibility,
      entity_manager: $em,
      trust_calculator: $trust,
    );

    $appeal = new ContentAppeal();
    $appeal->setContentType('project');
    $appeal->setContentId('project-id');
    $appeal->setAppellant($this->createUserStub());

    $processor->approveAppeal($appeal, $this->createUserStub('admin-id'));

    $this->assertSame(AppealState::Approved->value, $appeal->getState());
  }

  #[Group('unit')]
  public function testRejectAppealKeepsContentHidden(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->expects($this->never())->method('showContent');
    $visibility->method('getContentName')->willReturn('Test Project');

    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      visibility_manager: $visibility,
      entity_manager: $em,
    );

    $appeal = new ContentAppeal();
    $appeal->setContentType('project');
    $appeal->setContentId('project-id');
    $appeal->setAppellant($this->createUserStub());

    $processor->rejectAppeal($appeal, $this->createUserStub('admin-id'), 'Violation confirmed');

    $this->assertSame(AppealState::Rejected->value, $appeal->getState());
    $this->assertSame('Violation confirmed', $appeal->getResolutionNote());
  }

  #[Group('unit')]
  public function testApproveAppealRejectsNewReports(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);

    $reporter = $this->createUserStub('reporter-id');
    $new_report = new ContentReport();
    $new_report->setReporter($reporter);
    $new_report->setState(ReportState::New->value);

    $accepted_report = new ContentReport();
    $accepted_report->setReporter($reporter);
    $accepted_report->setState(ReportState::Accepted->value);

    $report_repo = $this->createStub(ContentReportRepository::class);
    $report_repo->method('findReportsForContent')->willReturn([$new_report, $accepted_report]);

    $trust = $this->createStub(TrustScoreCalculator::class);
    $em = $this->createStub(EntityManagerInterface::class);

    $processor = $this->buildProcessor(
      report_repository: $report_repo,
      visibility_manager: $visibility,
      entity_manager: $em,
      trust_calculator: $trust,
    );

    $appeal = new ContentAppeal();
    $appeal->setContentType('project');
    $appeal->setContentId('project-id');
    $appeal->setAppellant($this->createUserStub());

    $processor->approveAppeal($appeal, $this->createUserStub('admin-id'));

    // New reports should be rejected, accepted reports left unchanged
    $this->assertSame(ReportState::Rejected->value, $new_report->getState());
    $this->assertSame(ReportState::Accepted->value, $accepted_report->getState());
  }
}
