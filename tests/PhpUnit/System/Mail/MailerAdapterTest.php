<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Mail;

use App\System\Mail\EmailBudgetManager;
use App\System\Mail\MailerAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;
use Twig\Environment;

/**
 * @internal
 */
#[CoversClass(MailerAdapter::class)]
final class MailerAdapterTest extends TestCase
{
  public function testAnImportedScratchAuthorsPlaceholderAddressIsNotMailed(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->never())->method('send');
    $budget = $this->createMock(EmailBudgetManager::class);
    $budget->expects($this->never())->method('recordSend');

    $this->assertFalse($this->adapter($mailer, $budget)->send('CoderGuy09@localhost', 'Verify', 'mail.html.twig'));
  }

  public function testAFailedSendDoesNotUseTheBudget(): void
  {
    $mailer = $this->createStub(MailerInterface::class);
    $mailer->method('send')->willThrowException(new TransportException('rejected'));
    $budget = $this->createMock(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->method('getProvidersWithRoom')->willReturn(['main', 'mailjet']);
    $budget->expects($this->never())->method('recordSend');

    $this->assertFalse($this->adapter($mailer, $budget)->send('someone@example.org', 'Verify', 'mail.html.twig'));
  }

  public function testASentMailUsesTheBudget(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->once())->method('send');
    $budget = $this->createMock(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->method('getProvidersWithRoom')->willReturn(['main']);
    $budget->expects($this->once())->method('recordSend')->with('admin', 'main');

    $this->assertTrue($this->adapter($mailer, $budget)->send('someone@example.org', 'Verify', 'mail.html.twig'));
  }

  public function testAFailingProviderHandsTheMailToTheNextOne(): void
  {
    $transports = [];
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->exactly(2))->method('send')->willReturnCallback(
      static function (Message $email) use (&$transports): void {
        $transports[] = $email->getHeaders()->getHeaderBody('X-Transport');
        if ('main' === end($transports)) {
          throw new TransportException('daily limit reached');
        }
      }
    );
    $budget = $this->createMock(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->method('getProvidersWithRoom')->willReturn(['main', 'mailjet', 'ses']);
    $budget->expects($this->once())->method('recordSend')->with('verification', 'mailjet');

    $this->assertTrue($this->adapter($mailer, $budget)->send('someone@example.org', 'Verify', 'mail.html.twig', [], 'verification'));
    $this->assertSame(['main', 'mailjet'], $transports);
  }

  public function testMailIsSentFromShareWithRepliesToSupport(): void
  {
    $headers = null;
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->once())->method('send')->willReturnCallback(
      static function (Message $email) use (&$headers): void {
        $headers = $email->getHeaders();
      }
    );
    $budget = $this->createStub(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->method('getProvidersWithRoom')->willReturn(['main']);

    $this->assertTrue($this->adapter($mailer, $budget)->send('someone@example.org', 'Verify', 'mail.html.twig'));
    $this->assertNotNull($headers);
    $this->assertSame('Catrobat Share <share@catrobat.org>', $headers->get('From')?->getBodyAsString());
    $this->assertSame('support@catrobat.org', $headers->get('Reply-To')?->getBodyAsString());
  }

  private function adapter(MailerInterface $mailer, EmailBudgetManager $budget): MailerAdapter
  {
    return new MailerAdapter($mailer, new NullLogger(), $this->createStub(Environment::class), '', $budget);
  }
}
