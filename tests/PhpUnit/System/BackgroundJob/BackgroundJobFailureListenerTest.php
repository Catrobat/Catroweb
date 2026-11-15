<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\BackgroundJob;

use App\Project\Scratch\ConvertScratchProject;
use App\System\BackgroundJob\BackgroundJobFailureListener;
use App\System\Mail\SendMail;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Mime\Email;

/**
 * @internal
 */
#[CoversClass(BackgroundJobFailureListener::class)]
final class BackgroundJobFailureListenerTest extends TestCase
{
  public function testAJobWhoseRetriesAreUsedUpIsFailedWithPlainSql(): void
  {
    $connection = $this->createMock(Connection::class);
    $connection->expects($this->once())->method('executeStatement')->with(
      $this->stringContains('UPDATE background_job SET state = :failed'),
      $this->callback(static fn (array $params): bool => 'job-1' === $params['id'] && 'failed' === $params['failed']),
    );

    new BackgroundJobFailureListener($connection)($this->event(new ConvertScratchProject('job-1', 1, null), will_retry: false));
  }

  public function testAJobThatWillBeRetriedKeepsWaiting(): void
  {
    $connection = $this->createMock(Connection::class);
    $connection->expects($this->never())->method('executeStatement');

    new BackgroundJobFailureListener($connection)($this->event(new ConvertScratchProject('job-1', 1, null), will_retry: true));
  }

  public function testMessagesWithoutAJobAreIgnored(): void
  {
    $connection = $this->createMock(Connection::class);
    $connection->expects($this->never())->method('executeStatement');

    new BackgroundJobFailureListener($connection)($this->event(new SendMail(new Email(), 'a@example.org', 'admin'), will_retry: false));
  }

  private function event(object $message, bool $will_retry): WorkerMessageFailedEvent
  {
    $event = new WorkerMessageFailedEvent(new Envelope($message), 'scratch', new \RuntimeException('crash'));
    if ($will_retry) {
      $event->setForRetry();
    }

    return $event;
  }
}
