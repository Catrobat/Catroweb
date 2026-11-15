<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Mail;

use App\System\Mail\EmailBudgetManager;
use App\System\Mail\MailerAdapter;
use App\System\Mail\SendMail;
use App\System\Mail\SendMailHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Tests\PhpUnit\System\BackgroundJob\RecordingMessageBus;
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

    $this->assertFalse($this->sendNow($mailer, $budget, 'someone@example.org'));
  }

  public function testASentMailUsesTheBudget(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->once())->method('send');
    $budget = $this->createMock(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->method('getProvidersWithRoom')->willReturn(['main']);
    $budget->expects($this->once())->method('recordSend')->with('admin', 'main');

    $this->assertTrue($this->sendNow($mailer, $budget, 'someone@example.org'));
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

    $this->assertTrue($this->sendNow($mailer, $budget, 'someone@example.org', 'verification'));
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

    $this->assertTrue($this->sendNow($mailer, $budget, 'someone@example.org'));
    $this->assertNotNull($headers);
    $this->assertSame('Catrobat Share <share@catrobat.org>', $headers->get('From')?->getBodyAsString());
    $this->assertSame('support@catrobat.org', $headers->get('Reply-To')?->getBodyAsString());
  }

  public function testTheRequestOnlyQueuesTheMail(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->never())->method('send');
    $budget = $this->createStub(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $bus = new RecordingMessageBus();

    $this->assertTrue($this->adapter($mailer, $budget, $bus)->send('someone@example.org', 'Verify', 'mail.html.twig'));
    $this->assertCount(1, $bus->dispatched);
    $this->assertInstanceOf(SendMail::class, $bus->dispatched[0]);
  }

  public function testTheWorkerRetriesWhenNoProviderCouldSend(): void
  {
    $mailer = $this->createStub(MailerInterface::class);
    $mailer->method('send')->willThrowException(new TransportException('down'));
    $budget = $this->createStub(EmailBudgetManager::class);
    $budget->method('getProvidersWithRoom')->willReturn(['main']);
    $message = new SendMail(new Email()->to('someone@example.org')->subject('Verify')->text('x'), 'someone@example.org', 'admin');

    $this->expectException(\RuntimeException::class);
    new SendMailHandler($this->adapter($mailer, $budget, new RecordingMessageBus()))($message);
  }

  public function testSendingRightAwayDeliversWithoutTheQueue(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->once())->method('send');
    $budget = $this->createStub(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(true);
    $budget->method('getProvidersWithRoom')->willReturn(['main']);
    $bus = new RecordingMessageBus();

    $this->assertTrue($this->adapter($mailer, $budget, $bus)->sendNow('someone@example.org', 'Inactive', 'mail.html.twig', [], 'management'));
    $this->assertSame([], $bus->dispatched);
  }

  public function testSendingRightAwayRespectsTheBudget(): void
  {
    $mailer = $this->createMock(MailerInterface::class);
    $mailer->expects($this->never())->method('send');
    $budget = $this->createStub(EmailBudgetManager::class);
    $budget->method('canSend')->willReturn(false);

    $this->assertFalse($this->adapter($mailer, $budget)->sendNow('someone@example.org', 'Inactive', 'mail.html.twig', [], 'management'));
  }

  /** Sends like the request, then delivers like the worker; false if nothing was delivered. */
  private function sendNow(MailerInterface $mailer, EmailBudgetManager $budget, string $to, string $type = 'admin'): bool
  {
    $bus = new RecordingMessageBus();
    $adapter = $this->adapter($mailer, $budget, $bus);
    if (!$adapter->send($to, 'Verify', 'mail.html.twig', [], $type)) {
      return false;
    }

    $message = $bus->dispatched[0];
    $this->assertInstanceOf(SendMail::class, $message);

    return $adapter->deliver($message->email, $message->to, $message->email_type);
  }

  private function adapter(MailerInterface $mailer, EmailBudgetManager $budget, RecordingMessageBus $bus = new RecordingMessageBus()): MailerAdapter
  {
    return new MailerAdapter($mailer, new NullLogger(), $this->createStub(Environment::class), '', $budget, $bus);
  }
}
