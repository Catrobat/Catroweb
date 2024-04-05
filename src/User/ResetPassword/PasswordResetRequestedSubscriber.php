<?php

declare(strict_types=1);

namespace App\User\ResetPassword;

use App\Api\Services\Base\TranslatorAwareTrait;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class PasswordResetRequestedSubscriber implements EventSubscriberInterface
{
  use TranslatorAwareTrait;

  public function __construct(
    protected UserManager $user_manager,
    protected MailerAdapter $mailer,
    protected LoggerInterface $logger,
    protected ResetPasswordHelperInterface $reset_password_helper,
    TranslatorInterface $translator)
  {
    $this->initTranslator($translator);
  }

  public function onPasswordResetRequested(PasswordResetRequestedEvent $event): void
  {
    $this->sendPasswordResetEmail($event->getEmail(), $event->getLocale());
  }

  public static function getSubscribedEvents(): array
  {
    return [
      PasswordResetRequestedEvent::class => 'onPasswordResetRequested',
    ];
  }

  protected function sendPasswordResetEmail(string $email, string $locale): void
  {
    $user = $this->user_manager->findUserByEmail($email);
    if (!$user) {
      return; // Do not reveal whether a user account was found or not. Nothing to do here
    }
    try {
      $this->mailer->send(
        $email,
        $this->__('passwordRecovery.subject', [], $locale),
        'security/reset_password/email.html.twig',
        ['resetToken' => $this->reset_password_helper->generateResetToken($user)]
      );
    } catch (ResetPasswordExceptionInterface $e) {
      $this->logger->info("Can't create reset token for {$email}; Reason ".$e);
    }
  }
}
