<?php

namespace App\EventListener;

use App\Entity\User;
use App\Manager\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserPostPersistNotifier
{
  protected AchievementManager $achievement_manager;
  protected VerifyEmailHelperInterface $verify_email_helper;
  protected MailerInterface $mailer;
  protected LoggerInterface $logger;
  protected TranslatorInterface $translator;

  public function __construct(AchievementManager $achievement_manager,
                                VerifyEmailHelperInterface $verify_email_helper,
                                MailerInterface $mailer, LoggerInterface $logger,
                                TranslatorInterface $translator)
  {
    $this->translator = $translator;
    $this->achievement_manager = $achievement_manager;
    $this->verify_email_helper = $verify_email_helper;
    $this->mailer = $mailer;
    $this->logger = $logger;
  }

  public function postPersist(User $user, LifecycleEventArgs $event): void
  {
    $this->addVerifiedDeveloperAchievement($user);
    $this->sendVerifyEmail($user);
  }

  /**
   * @throws Exception
   */
  protected function addVerifiedDeveloperAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementVerifiedDeveloper($user);
  }

  protected function sendVerifyEmail(User $user): void
  {
    try {
      $signatureComponents = $this->verify_email_helper->generateSignature(
                'registration_confirmation_route',
                $user->getId(),
                $user->getEmail()
            );

      $email = (new TemplatedEmail())
        ->from(new Address('no-reply@catrob.at', 'Catrobat Mail Bot'))
        ->to($user->getEmail())
        ->subject($this->translator->trans('user.verification.email', [], 'catroweb'))
        ->htmlTemplate('security/registration/confirmation_email.html.twig')
        ->context([
          'signedUrl' => $signatureComponents->getSignedUrl(),
          'user' => $user,
        ])
      ;

      $this->mailer->send($email);
    } catch (TransportExceptionInterface $e) {
      $this->logger->critical("Can't send verification email to \"".$user->getEmail().'" '.$e->getMessage());
    }
  }
}
