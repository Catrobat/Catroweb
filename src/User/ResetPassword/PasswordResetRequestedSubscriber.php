<?php

declare(strict_types=1);

namespace App\User\ResetPassword;

use App\Api\Services\Base\TranslatorAwareTrait;
use App\Security\Authentication\ResetPasswordEmail;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class PasswordResetRequestedSubscriber implements EventSubscriberInterface
{
  use TranslatorAwareTrait;

  public function __construct(
    protected UserManager $user_manager,
    protected LoggerInterface $logger,
    protected ResetPasswordEmail $reset_password_email)
  {
  }

  public function onPasswordResetRequested(PasswordResetRequestedEvent $event): void
  {
    $this->sendPasswordResetEmail($event->getEmail(), $event->getLocale());
  }

  #[\Override]
  public static function getSubscribedEvents(): array
  {
    return [
      PasswordResetRequestedEvent::class => 'onPasswordResetRequested',
    ];
  }

  protected function sendPasswordResetEmail(string $email, string $locale): void
  {
    $user = $this->user_manager->findUserByEmail($email);
    if (!$user instanceof UserInterface) {
      return; // Do not reveal whether a user account was found or not. Nothing to do here
    }

    try {
      $this->reset_password_email
        ->init($user, $locale)
        ->send()
      ;
    } catch (ResetPasswordExceptionInterface $resetPasswordException) {
      $this->logger->info(sprintf('Can\'t create reset token for %s; Reason ', $email).$resetPasswordException);
    }
  }
}
