<?php

declare(strict_types=1);

namespace App\System\Mail;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendMailHandler
{
  public function __construct(private MailerAdapter $mailer)
  {
  }

  public function __invoke(SendMail $message): void
  {
    if (!$this->mailer->deliver($message->email, $message->to, $message->email_type)) {
      // Every provider failed: Messenger retries later, then keeps the mail in the failed queue (admin page).
      throw new \RuntimeException('No mail provider could send the email to '.$message->to);
    }
  }
}
