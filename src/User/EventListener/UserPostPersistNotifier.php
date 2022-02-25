<?php

namespace App\User\EventListener;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\Achievements\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserPostPersistNotifier
{
  protected AchievementManager $achievement_manager;
  protected VerifyEmailHelperInterface $verify_email_helper;
  protected MailerAdapter $mailer;
  protected LoggerInterface $logger;
  protected TranslatorInterface $translator;

  public function __construct(AchievementManager $achievement_manager,
                                VerifyEmailHelperInterface $verify_email_helper,
                                MailerAdapter $mailer,
                              LoggerInterface $logger,
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
    $signatureComponents = $this->verify_email_helper->generateSignature(
      'registration_confirmation_route',
      $user->getId(),
      $user->getEmail()
    );

    $this->mailer->send(
      $user->getEmail(),
      $this->translator->trans('user.verification.email', [], 'catroweb'),
      'security/registration/confirmation_email.html.twig',
      [
        'signedUrl' => $signatureComponents->getSignedUrl(),
        'user' => $user,
      ]
    );
  }
}
