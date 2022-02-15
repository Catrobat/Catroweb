<?php

namespace App\System\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Message;

class MailerAdapter
{
  protected MailerInterface $mailer;
  protected LoggerInterface $logger;
  protected string $dkim_private_key_path;

  public function __construct(
    MailerInterface $mailer,
    LoggerInterface $logger,
    string $dkim_private_key_path // bind in services!
  ) {
    $this->mailer = $mailer;
    $this->logger = $logger;
    $this->dkim_private_key_path = $dkim_private_key_path;
  }

  public function send(string $to, string $subject, string $template, array $context = []): void
  {
    $email = $this->buildEmail($to, $subject, $template, $context);
    $signedEmail = $this->signEmail($email);
    $this->sendEmail($signedEmail, $to);
  }

  protected function buildEmail(string $to, string $subject, string $template, array $context): Message
  {
    return (new TemplatedEmail())
      ->from(new Address('share@catrob.at'))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($context)
    ;
  }

  protected function signEmail(Message $email): Message
  {
    try {
      return (new DkimSigner($this->dkim_private_key_path, 'catrob.at', 'sf'))->sign($email);
    } catch (InvalidArgumentException $e) {
      if ('prod' === $_ENV['APP_ENV']) {
        $this->logger->error("Private dkim key is missing ({$this->dkim_private_key_path}): ".$e->getMessage());
      }

      return $email;
    }
  }

  protected function sendEmail(Message $email, string $to): void
  {
    try {
      $this->mailer->send($email);
    } catch (TransportExceptionInterface $e) {
      $this->logger->error("Can't send email to {$to}; Reason ".$e->getMessage());
    }
  }
}
