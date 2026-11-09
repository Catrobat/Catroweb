<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\System\Mail\MailerAdapter;
use Doctrine\ORM\EntityManagerInterface;
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
  public function testAFailedSendDoesNotStartTheResendThrottle(): void
  {
    $user = $this->user();

    $this->assertFalse($this->verifyEmail(sent: false)->init($user)->send());
    $this->assertNull($user->getVerificationRequestedAt());
  }

  public function testAFailedSendRestoresTheEarlierRequestTime(): void
  {
    $user = $this->user();
    $earlier = new \DateTime('-3 days');
    $user->setVerificationRequestedAt($earlier);

    $this->assertFalse($this->verifyEmail(sent: false)->init($user)->send());
    $this->assertSame($earlier, $user->getVerificationRequestedAt());
  }

  public function testTheThrottleSlotIsClaimedBeforeTheMailIsSent(): void
  {
    $user = $this->user();
    $mailer = $this->createStub(MailerAdapter::class);
    $mailer->method('send')->willReturnCallback(function () use ($user): bool {
      $this->assertNotNull($user->getVerificationRequestedAt(), 'the slot must be taken before the slow send');

      return true;
    });

    $this->assertTrue($this->verifyEmail(sent: true, mailer: $mailer)->init($user)->send());
  }

  public function testASuccessfulSendStartsTheResendThrottle(): void
  {
    $user = $this->user();

    $this->assertTrue($this->verifyEmail(sent: true)->init($user)->send());
    $this->assertNotNull($user->getVerificationRequestedAt());
  }

  private function user(): User
  {
    $user = new User();
    $user->setId('9b8d1f8e-4b3c-4f7a-9e2d-1c2b3a4d5e6f');
    $user->setEmail('kid@example.org');

    return $user;
  }

  private function verifyEmail(bool $sent, ?MailerAdapter $mailer = null): VerifyEmail
  {
    $helper = $this->createStub(VerifyEmailHelperInterface::class);
    $helper->method('generateSignature')->willReturn(new VerifyEmailSignatureComponents(new \DateTimeImmutable('+1 hour'), 'https://example.org/verify', time()));
    if (null === $mailer) {
      $mailer = $this->createStub(MailerAdapter::class);
      $mailer->method('send')->willReturn($sent);
    }

    return new VerifyEmail(
      $helper,
      $mailer,
      $this->createStub(TranslatorInterface::class),
      new ParameterBag(['umbrellaTheme' => 'pocketcode']),
      $this->createStub(RouterInterface::class),
      $this->createStub(EntityManagerInterface::class),
      $this->createStub(UrlGeneratorInterface::class),
    );
  }
}
