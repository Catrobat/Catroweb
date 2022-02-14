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

  public function __construct(MailerInterface $mailer, LoggerInterface $logger)
  {
    $this->mailer = $mailer;
    $this->logger = $logger;
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
      $signer = new DkimSigner('.dkim/private.key', 'catrob.at', 'sf');

      return $signer->sign($email);
    } catch (InvalidArgumentException $e) {
      if ('prod' === $_ENV['APP_ENV']) {
        $this->logger->error('Private dkim key is missing: '.$e->getMessage());
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
