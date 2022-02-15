<?php

namespace App\System\Mail;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Message;
use Twig\Environment;

class MailerAdapter
{
  protected MailerInterface $mailer;
  protected LoggerInterface $logger;
  protected Environment $templateWrapper;
  protected string $dkim_private_key_path;

  public function __construct(
    MailerInterface $mailer,
    LoggerInterface $logger,
    Environment $templateWrapper,
    string $dkim_private_key_path // bind in services!
  ) {
    $this->mailer = $mailer;
    $this->logger = $logger;
    $this->templateWrapper = $templateWrapper;
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
    $html = '';
    try {
      $this->templateWrapper->render($template, $context);
    } catch (Exception $e) {
      $this->logger->error("Can't render mail template: {$template}".$e->getMessage());
    }

    return (new TemplatedEmail())
      ->from(new Address('share@catrob.at'))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($context)
      ->html($html)
    ;
  }

  protected function signEmail(Message $email): Message
  {
    try {
      return (new DkimSigner('file://'.$this->dkim_private_key_path, 'catrob.at', 'sf'))->sign($email);
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
