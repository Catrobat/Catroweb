<?php

namespace App\User\EventListener;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\Achievements\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserPostPersistNotifier
{
  public function __construct(protected AchievementManager $achievement_manager, protected VerifyEmailHelperInterface $verify_email_helper, protected MailerAdapter $mailer, protected LoggerInterface $logger, protected TranslatorInterface $translator)
  {
  }

  public function postPersist(User $user, LifecycleEventArgs $event): void
  {
    $this->addVerifiedDeveloperAchievement($user);
    $this->sendVerifyEmail($user); 
  }

  /**
   * @throws \Exception
   */
  protected function addVerifiedDeveloperAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementVerifiedDeveloper($user);
  }

  protected function sendVerifyEmail(User $user): void
  {
    $signatureComponents = $this->verify_email_helper->generateSignature(
      'registration_confirmation_route',
      $user->getId(),
      $user->getEmail()
    );

    $expirationTime = $signatureComponents->getExpiresAt();

    $this->mailer->send(
      $user->getEmail(),
      $this->translator->trans('user.verification.email', [], 'catroweb'),
      'security/registration/confirmation_email.html.twig',
      [
        'expire' => $expirationTime->format('H:i'), // Y-m-d H:i:s for a full date
        'signedUrl' => $signatureComponents->getSignedUrl(),
        'user' => $user,
      ]
    );
  }
}
