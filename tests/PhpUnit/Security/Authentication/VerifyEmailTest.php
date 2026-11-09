<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\System\Mail\MailerAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @internal
 */
#[CoversClass(VerifyEmail::class)]
final class VerifyEmailTest extends TestCase
{
  /** @var list<string> DQL of the conditional UPDATEs, in order */
  private array $updates = [];

  public function testAFailedSendKeepsTheEarlierRequestTime(): void
  {
    $user = $this->user();
    $earlier = new \DateTime('-3 days');
    $user->setVerificationRequestedAt($earlier);

    $this->assertFalse($this->verifyEmail($this->mailer(false))->init($user)->send());
    $this->assertSame($earlier, $user->getVerificationRequestedAt());
  }

  public function testASuccessfulSendStartsTheResendThrottle(): void
  {
    $user = $this->user();

    $this->assertTrue($this->verifyEmail($this->mailer(true))->init($user)->send());
    $this->assertNotNull($user->getVerificationRequestedAt());
  }

  public function testAResendClaimsTheFreeSlotAndSends(): void
  {
    $user = $this->user();

    $this->assertTrue($this->verifyEmail($this->mailer(true), rows_updated: [1])->init($user)->sendUnlessRecentlySent());
    $this->assertNotNull($user->getVerificationRequestedAt());
    $this->assertCount(1, $this->updates);
    $this->assertStringContainsString(':cutoff', $this->updates[0]);
  }

  public function testAResendDoesNotSendWhileAnotherRequestHoldsTheSlot(): void
  {
    $user = $this->user();
    $mailer = $this->createMock(MailerAdapter::class);
    $mailer->expects($this->never())->method('send');

    $this->assertNull($this->verifyEmail($mailer, rows_updated: [0])->init($user)->sendUnlessRecentlySent(), 'null = already sent recently');
    $this->assertNull($user->getVerificationRequestedAt());
  }

  public function testAFailedResendHandsTheSlotBackOnlyIfItIsStillOurs(): void
  {
    $user = $this->user();
    $earlier = new \DateTime('-3 days');
    $user->setVerificationRequestedAt($earlier);

    $this->assertFalse($this->verifyEmail($this->mailer(false), rows_updated: [1, 1])->init($user)->sendUnlessRecentlySent());
    $this->assertSame($earlier, $user->getVerificationRequestedAt());
    $this->assertCount(2, $this->updates);
    $this->assertStringContainsString('= :claimed', $this->updates[1]);
  }

  public function testAnExceptionWhileSendingHandsTheSlotBack(): void
  {
    $user = $this->user();
    $mailer = $this->createStub(MailerAdapter::class);
    $mailer->method('send')->willThrowException(new \RuntimeException('template broke'));

    try {
      $this->verifyEmail($mailer, rows_updated: [1, 1])->init($user)->sendUnlessRecentlySent();
      $this->fail('the exception must propagate');
    } catch (\RuntimeException) {
    }

    $this->assertNull($user->getVerificationRequestedAt());
    $this->assertCount(2, $this->updates);
    $this->assertStringContainsString('= :claimed', $this->updates[1]);
  }

  private function user(): User
  {
    $user = new User();
    $user->setId('9b8d1f8e-4b3c-4f7a-9e2d-1c2b3a4d5e6f');
    $user->setEmail('kid@example.org');

    return $user;
  }

  private function mailer(bool $sent): MailerAdapter
  {
    $mailer = $this->createStub(MailerAdapter::class);
    $mailer->method('send')->willReturn($sent);

    return $mailer;
  }

  /**
   * @param list<int> $rows_updated what each conditional UPDATE reports, in order
   */
  private function verifyEmail(MailerAdapter $mailer, array $rows_updated = []): VerifyEmail
  {
    $helper = $this->createStub(VerifyEmailHelperInterface::class);
    $helper->method('generateSignature')->willReturn(new VerifyEmailSignatureComponents(new \DateTimeImmutable('+1 hour'), 'https://example.org/verify', time()));

    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('createQuery')->willReturnCallback(function (string $dql) use (&$rows_updated): Query {
      $this->updates[] = $dql;
      $query = $this->createStub(Query::class);
      $query->method('setParameter')->willReturnSelf();
      $query->method('execute')->willReturn(array_shift($rows_updated) ?? 0);

      return $query;
    });

    return new VerifyEmail(
      $helper,
      $mailer,
      $this->createStub(TranslatorInterface::class),
      new ParameterBag(['umbrellaTheme' => 'pocketcode']),
      $this->createStub(RouterInterface::class),
      $entity_manager,
      $this->createStub(UrlGeneratorInterface::class),
    );
  }
}
