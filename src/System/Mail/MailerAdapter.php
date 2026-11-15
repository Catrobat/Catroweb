<?php

declare(strict_types=1);

namespace App\System\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\HtmlToTextConverter\DefaultHtmlToTextConverter;
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
    protected EmailBudgetManager $budgetManager,
    protected MessageBusInterface $messageBus,
  ) {
  }

  public function send(string $to, string $subject, string $template, array $context = [], string $emailType = 'admin'): bool
  {
    // Accounts made for imported Scratch authors have a reserved, undeliverable "<name>@localhost" address.
    if (str_ends_with(strtolower($to), '@localhost')) {
      return false;
    }

    if (!$this->budgetManager->canSend($emailType)) {
      $this->logger->warning(sprintf('Email budget exhausted for type "%s". Email to %s with subject "%s" was not sent.', $emailType, $to, $subject));

      return false;
    }

    // Rendered here, where the request's locale and host are known; the mail worker only delivers it.
    try {
      $email = $this->buildEmail($to, $subject, $template, $context);
    } catch (\Throwable $exception) {
      $this->logger->error('Can\'t render mail template: '.$template.': '.$exception->getMessage(), ['exception' => $exception]);

      return false; // never an empty mail
    }

    $this->messageBus->dispatch(new SendMail($email, $to, $emailType));

    return true;
  }

  /**
   * On the mail worker. Free mail tiers have small daily limits, so several providers are chained: the first one
   * with room left sends, and one that fails (down, or over a limit we did not know about) hands over to the next.
   * ponytail: a provider that times out after accepting the mail causes a duplicate from the next one; a
   * duplicate (same link) beats a lost verification mail. Fall through only on connect/auth errors if it hurts.
   */
  public function deliver(Message $email, string $to, string $emailType): bool
  {
    // $email = $this->signEmail($email); // Signing is currently disabled due to breveo
    foreach ($this->budgetManager->getProvidersWithRoom() as $provider) {
      $email->getHeaders()->remove('X-Transport');
      $email->getHeaders()->addTextHeader('X-Transport', $provider);
      if ($this->sendEmail($email, $to, $provider)) {
        $this->budgetManager->recordSend($emailType, $provider);

        return true;
      }
    }

    $this->logger->error(sprintf('No mail provider could send the email to %s with subject "%s".', $to, $email->getHeaders()->getHeaderBody('Subject')));

    return false;
  }

  protected function buildEmail(string $to, string $subject, string $template, array $context): Email
  {
    $html = $this->templateWrapper->render($template, $context);

    // A plain Email, already rendered: a TemplatedEmail would be rendered again on the worker, from a context that
    // may hold entities.
    return new Email()
      ->from(new Address('share@catrobat.org', 'Catrobat Share'))
      ->replyTo('support@catrobat.org')
      ->to($to)
      ->subject($subject)
      ->html($html)
      ->text(new DefaultHtmlToTextConverter()->convert($html, 'utf-8'))
    ;
  }

  protected function signEmail(Message $email): Message
  {
    try {
      return new DkimSigner('file://'.$this->dkim_private_key_path, 'share.catrobat.org', 'sf')->sign($email);
    } catch (InvalidArgumentException $invalidArgumentException) {
      if ('prod' === $_ENV['APP_ENV']) {
        $this->logger->error(sprintf('Private dkim key is missing (%s): ', $this->dkim_private_key_path).$invalidArgumentException->getMessage(), ['exception' => $invalidArgumentException]);
      }

      return $email;
    }
  }

  protected function sendEmail(Message $email, string $to, string $provider): bool
  {
    try {
      $this->mailer->send($email);

      return true;
    } catch (TransportExceptionInterface $transportException) {
      $this->logger->warning(sprintf('Mail provider "%s" can\'t send email to %s; Reason ', $provider, $to).$transportException->getMessage());

      return false;
    }
  }
}
