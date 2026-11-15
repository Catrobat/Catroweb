<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Enum\ContentType;
use App\Moderation\AgeSignalDetector;
use App\Moderation\AutoModerationService;
use App\Moderation\ContentVisibilityManager;
use App\Security\ContentSafety\ContentSafetyScanner;
use Doctrine\ORM\EntityManagerInterface;
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
    $this->assertCount(1, array_filter($this->persisted, static fn (object $o): bool => $o instanceof ContentModerationAction));
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

  private function service(ContentVisibilityManager $visibility): AutoModerationService
  {
    $query = $this->createStub(Query::class);
    $query->method('getResult')->willReturn([]);
    $query_builder = $this->createStub(QueryBuilder::class);
    $query_builder->method('select')->willReturnSelf();
    $query_builder->method('from')->willReturnSelf();
    $query_builder->method('where')->willReturnSelf();
    $query_builder->method('setParameter')->willReturnSelf();
    $query_builder->method('getQuery')->willReturn($query);

    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('createQueryBuilder')->willReturn($query_builder);
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
