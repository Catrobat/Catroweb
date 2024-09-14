<?php

declare(strict_types=1);

namespace App\System\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Message;
use Twig\Environment;

class MailerAdapter
{
  public function __construct(
    protected MailerInterface $mailer,
    protected LoggerInterface $logger,
    protected Environment $templateWrapper,
    #[Autowire('%dkim.private.key%')]
    protected string $dkim_private_key_path,
  ) {
  }

  public function send(string $to, string $subject, string $template, array $context = []): void
  {
    $email = $this->buildEmail($to, $subject, $template, $context);
    // $email = $this->signEmail($email); // Signing is currently disabled due to breveo
    $this->sendEmail($email, $to);
  }

  protected function buildEmail(string $to, string $subject, string $template, array $context): Message
  {
    $html = '';
    try {
      $html = $this->templateWrapper->render($template, $context);
    } catch (\Exception $exception) {
      $this->logger->error('Can\'t render mail template: '.$template.$exception->getMessage());
    }

    return (new TemplatedEmail())
      ->from(new Address('support@catrob.at'))
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
      return (new DkimSigner('file://'.$this->dkim_private_key_path, 'share.catrob.at', 'sf'))->sign($email);
    } catch (InvalidArgumentException $invalidArgumentException) {
      if ('prod' === $_ENV['APP_ENV']) {
        $this->logger->error(sprintf('Private dkim key is missing (%s): ', $this->dkim_private_key_path).$invalidArgumentException->getMessage());
      }

      return $email;
    }
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
