<?php

declare(strict_types=1);

namespace App\User\ResetPassword;

use App\Api\Services\Base\TranslatorAwareTrait;
use App\Security\Authentication\ResetPasswordEmail;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[AsEventListener(event: PasswordResetRequestedEvent::class, method: 'sendPasswordResetEmail')]
class PasswordResetRequestedEventListener
{
  use TranslatorAwareTrait;

  public function __construct(
    protected UserManager $user_manager,
    protected LoggerInterface $logger,
    protected ResetPasswordEmail $reset_password_email)
  {
  }

  public function sendPasswordResetEmail(PasswordResetRequestedEvent $event): void
  {
    $user = $this->user_manager->findUserByEmail($event->getEmail());
    if (!$user instanceof UserInterface) {
      return; // Do not reveal whether a user account was found or not. Nothing to do here
    }

    try {
      $this->reset_password_email
        ->init($user, $event->getLocale())
        ->send()
      ;
    } catch (ResetPasswordExceptionInterface $resetPasswordException) {
      $this->logger->info(sprintf('Can\'t create reset token for %s; Reason ', $event->getEmail()).$resetPasswordException);
    }
  }
}
