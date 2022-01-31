<?php

namespace App\EventSubscriber;

use App\Api\Services\Base\TranslatorAwareTrait;
use App\Event\PasswordResetRequestedEvent;
use App\Manager\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class PasswordResetRequestedSubscriber implements EventSubscriberInterface
{
  use TranslatorAwareTrait;
  protected MailerInterface $mailer;
  protected UserManager $user_manager;
  protected LoggerInterface $logger;
  protected ResetPasswordHelperInterface $reset_password_helper;

  public function __construct(
    UserManager $user_manager,
    MailerInterface $mailer,
    LoggerInterface $logger,
    ResetPasswordHelperInterface $reset_password_helper,
    TranslatorInterface $translator)
  {
    $this->initTranslator($translator);
    $this->user_manager = $user_manager;
    $this->mailer = $mailer;
    $this->logger = $logger;
    $this->reset_password_helper = $reset_password_helper;
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
      $email = (new TemplatedEmail())
        ->from(new Address('no-reply@catrob.at', 'Catrobat Mail Bot'))
        ->to($user->getEmail())
        ->subject($this->__('passwordRecovery.subject', [], $locale))
        ->htmlTemplate('security/reset_password/email.html.twig')
        ->context([
          'resetToken' => $this->reset_password_helper->generateResetToken($user),
        ])
      ;
      $this->mailer->send($email);
    } catch (TransportExceptionInterface $e) {
      $this->logger->error("Can't send email to {$email}; Reason ".$e->getMessage());
    } catch (ResetPasswordExceptionInterface $e) {
      $this->logger->error("Can't create reset token for {$email}; Reason ".$e->getMessage());
    }
  }
}
