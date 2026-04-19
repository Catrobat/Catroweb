<?php

declare(strict_types=1);

namespace App\System\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Twig\Environment;

class MailerAdapter
{
  public function __construct(
    protected MailerInterface $mailer,
    protected LoggerInterface $logger,
    protected Environment $templateWrapper,
    protected EmailBudgetManager $budgetManager,
  ) {
  }

  public function send(string $to, string $subject, string $template, array $context = [], string $emailType = 'admin'): bool
  {
    if (!$this->budgetManager->canSend($emailType)) {
      $this->logger->warning(sprintf('Email budget exhausted for type "%s". Email to %s with subject "%s" was not sent.', $emailType, $to, $subject));

      return false;
    }

    $email = $this->buildEmail($to, $subject, $template, $context);
    $this->sendEmail($email, $to);
    $this->budgetManager->recordSend($emailType);

    return true;
  }

  protected function buildEmail(string $to, string $subject, string $template, array $context): Message
  {
    $html = '';
    try {
      $html = $this->templateWrapper->render($template, $context);
    } catch (\Exception $exception) {
      $this->logger->error('Can\'t render mail template: '.$template.$exception->getMessage());
    }

    return new TemplatedEmail()
      ->from(new Address('support@catrob.at'))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($context)
      ->html($html)
    ;
  }

  protected function sendEmail(Message $email, string $to): void
  {
    try {
      $this->mailer->send($email);
    } catch (TransportExceptionInterface $transportException) {
      $this->logger->error(sprintf('Can\'t send email to %s; Reason ', $to).$transportException->getMessage());
    }
  }
}
