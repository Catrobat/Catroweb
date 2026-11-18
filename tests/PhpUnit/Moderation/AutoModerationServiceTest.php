<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\Moderation\AgeSignalDetector;
use App\Moderation\AutoModerationService;
use App\Moderation\ContentVisibilityManager;
use App\Security\ContentSafety\ContentSafetyScanner;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(AutoModerationService::class)]
#[Group('unit')]
class AutoModerationServiceTest extends TestCase
{
  /** @var list<object> */
  private array $persisted = [];

  public function testAgeSignalHidesAndQueuesForReview(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->method('isContentHidden')->willReturn(false);
    $visibility->expects($this->once())->method('hideContent')->with(ContentType::Studio, 's1');

    $this->assertTrue($this->service($visibility)->flagAgeSignal(ContentType::Studio, 's1', 'My studio', '+18 projects'));

    $report = array_values(array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentReport))[0];
    $this->assertNull($report->getReporter());
    $this->assertSame('inappropriate_content', $report->getCategory());
    $this->assertStringContainsString('+18', (string) $report->getNote());
    $this->assertSame([['type' => 'text', 'name' => '+18']], $report->getFlaggedAssets(), 'positional texts: no field');
    $this->assertCount(1, array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentModerationAction));
  }

  public function testTheOwnerIsToldWhichFieldOrImageHidTheContent(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('getContentOwnerId')->willReturn('owner');

    $this->service($visibility)->flagAgeSignal(ContentType::Project, 'p1', name: 'Space game', description: 'only 18+');
    $report = array_values(array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentReport))[0];
    $this->assertSame([['type' => 'text', 'name' => '18+', 'field' => 'description']], $report->getFlaggedAssets());
    $this->assertSame(['category' => 'improper_rating', 'fields' => ['description']], $this->ownerReason());

    $this->persisted = [];
    $images = [['type' => 'image', 'name' => '0123456789abcdef0123456789abcdef_Mom.png', 'score' => 0.999]];
    $this->service($visibility)->flagForReview(ContentType::Project, 'p2', ReportCategory::SexualContent, 'Automatic: image', 0.999, $images);
    $this->assertSame(['category' => 'sexual_content', 'images' => ['Mom']], $this->ownerReason());
  }

  public function testACommunityHideGivesTheCategoryWithTheMostTrust(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $visibility->method('getContentOwnerId')->willReturn('owner');
    $repository = $this->createStub(EntityRepository::class);
    $repository->method('findBy')->willReturn([
      new ContentReport()->setCategory('spam')->setReporter(new User())->setReporterTrustScore(2.5),
      new ContentReport()->setCategory('sexual_content')->setReporter(new User())->setReporterTrustScore(1.0),
      new ContentReport()->setCategory('sexual_content')->setReporter(new User())->setReporterTrustScore(1.0),
      new ContentReport()->setCategory('sexual_content')->setReporterTrustScore(5.0), // an automatic flag doesn't vote
    ]);

    $this->service($visibility, $repository)->autoHideContent(ContentType::Project, 'p1', 3.0);

    $this->assertSame(['category' => 'spam'], $this->ownerReason());
  }

  public function testAProfileIsQueuedButNeverHidden(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->expects($this->never())->method('hideContent');

    $user = new User();
    $user->setId('u1');
    $user->setUsername('Builder');
    $user->setAbout('18+ only');
    $user->setCurrentlyWorkingOn('a racing game');

    $this->assertTrue($this->service($visibility)->flagUserProfile($user));

    $this->assertFalse($user->getProfileHidden());
    $this->assertNull($user->getAbout());
    $this->assertSame('a racing game', $user->getCurrentlyWorkingOn());
    $report = array_values(array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentReport))[0];
    $this->assertSame('inappropriate_profile', $report->getCategory());
    $this->assertSame([], array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentModerationAction));
  }

  public function testAUsernameIsKeptAndQueued(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->expects($this->never())->method('hideContent');

    $user = new User();
    $user->setId('u2');
    $user->setUsername('nsfw_artist');

    $this->assertTrue($this->service($visibility)->flagUserProfile($user));
    $this->assertSame('nsfw_artist', $user->getUsername());
    $this->assertCount(1, array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentReport));
  }

  public function testAReviewOnlyFlagQueuesTheContentAndLeavesItVisible(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->method('isContentHidden')->willReturn(false);
    $visibility->method('getContentOwnerId')->willReturn('owner');
    $visibility->expects($this->never())->method('hideContent');

    $images = [['type' => 'image', 'name' => 'look.png', 'score' => 0.99]];
    $this->service($visibility)->flagForReview(ContentType::Project, 'p1', ReportCategory::SexualContent, 'Automatic: image', 0.99, $images, false);

    $report = array_values(array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentReport))[0];
    $this->assertSame($images, $report->getFlaggedAssets());
    $this->assertSame([], array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentModerationAction), 'no auto_hidden entry');
    $this->assertSame(['admin' => AutoModerationService::ACTION_REVIEW_REQUESTED], $this->notifications(), 'admins only, the owner is not told');
  }

  public function testAHidingFlagNotifiesTheOwnerAndAdmins(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->method('isContentHidden')->willReturn(false);
    $visibility->method('getContentOwnerId')->willReturn('owner');
    $visibility->expects($this->once())->method('hideContent');

    $this->service($visibility)->flagForReview(ContentType::Project, 'p1', ReportCategory::SexualContent, 'Automatic: image', 0.999);

    $this->assertSame(['owner' => ContentModerationAction::ACTION_AUTO_HIDDEN, 'admin' => ContentModerationAction::ACTION_AUTO_HIDDEN], $this->notifications());
  }

  public function testAReviewOnlyFlagIsNotQueuedWhileOneIsPending(): void
  {
    $visibility = $this->createStub(ContentVisibilityManager::class);
    $repository = $this->createStub(EntityRepository::class);
    $repository->method('findOneBy')->willReturn(new ContentReport());

    $this->service($visibility, $repository)->flagForReview(ContentType::Project, 'p1', ReportCategory::SexualContent, 'Automatic: image', hide: false);
    $this->assertSame([], $this->persisted);
  }

  public function testHarmlessTextChangesNothing(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->expects($this->never())->method('hideContent');

    $this->assertFalse($this->service($visibility)->flagAgeSignal(ContentType::Project, 'p1', 'Space game', 'needs version 1.18+'));
    $this->assertSame([], $this->persisted);
  }

  public function testAlreadyHiddenContentIsNotQueuedTwice(): void
  {
    $visibility = $this->createMock(ContentVisibilityManager::class);
    $visibility->method('isContentHidden')->willReturn(true);
    $visibility->expects($this->never())->method('hideContent');

    $this->service($visibility)->flagAgeSignal(ContentType::Project, 'p1', 'nsfw');
    $this->assertSame([], $this->persisted);
  }

  /**
   * @return array<string, mixed>|null
   */
  private function ownerReason(): ?array
  {
    foreach ($this->persisted as $o) {
      if ($o instanceof ModerationNotification && 'owner' === $o->getUser()->getUsername()) {
        return $o->getModerationReason();
      }
    }

    $this->fail('the owner got no notification');
  }

  /**
   * @return array<string, string|null> username => moderation action of the persisted notifications
   */
  private function notifications(): array
  {
    $notifications = [];
    foreach ($this->persisted as $o) {
      if ($o instanceof ModerationNotification) {
        $notifications[(string) $o->getUser()->getUsername()] = $o->getModerationAction();
      }
    }

    return $notifications;
  }

  private function service(ContentVisibilityManager $visibility, ?EntityRepository $repository = null): AutoModerationService
  {
    $admin = new User();
    $admin->setUsername('admin');
    $owner = new User();
    $owner->setUsername('owner');

    $query = $this->createStub(Query::class);
    $query->method('getResult')->willReturn([$admin]);
    $query_builder = $this->createStub(QueryBuilder::class);
    $query_builder->method('select')->willReturnSelf();
    $query_builder->method('from')->willReturnSelf();
    $query_builder->method('where')->willReturnSelf();
    $query_builder->method('setParameter')->willReturnSelf();
    $query_builder->method('getQuery')->willReturn($query);

    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('createQueryBuilder')->willReturn($query_builder);
    $entity_manager->method('find')->willReturn($owner);
    $entity_manager->method('getRepository')->willReturn($repository ?? $this->createStub(EntityRepository::class));
    $entity_manager->method('persist')->willReturnCallback(function (object $o): void {
      $this->persisted[] = $o;
    });

    return new AutoModerationService(
      $visibility,
      $entity_manager,
      new AgeSignalDetector(),
      $this->createStub(ContentSafetyScanner::class),
      $this->createStub(MessageBusInterface::class),
    );
  }
}
