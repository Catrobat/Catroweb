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
    $budget->expects($this->never())->method('recordSend');

    $this->assertFalse($this->adapter($mailer, $budget)->send('someone@example.org', 'Verify', 'mail.html.twig'));
  }

  public function testASentMailUsesTheBudget(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->once())->method('send');
    $budget = $this->createMock(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->expects($this->once())->method('recordSend')->with('admin');

    $this->assertTrue($this->adapter($mailer, $budget)->send('someone@example.org', 'Verify', 'mail.html.twig'));
  }

  private function adapter(MailerInterface $mailer, EmailBudgetManager $budget): MailerAdapter
  {
    return new MailerAdapter($mailer, new NullLogger(), $this->createStub(Environment::class), '', $budget);
  }
}
