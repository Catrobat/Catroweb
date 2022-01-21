<?php

namespace App\EventListener;

use App\Entity\User;
use App\Manager\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserPostUpdateNotifier
{
  protected AchievementManager $achievement_manager;
  protected VerifyEmailHelperInterface $verify_email_helper;
  protected MailerInterface $mailer;

  public function __construct(AchievementManager $achievement_manager, VerifyEmailHelperInterface $verify_email_helper, MailerInterface $mailer)
  {
    $this->achievement_manager = $achievement_manager;
    $this->verify_email_helper = $verify_email_helper;
    $this->mailer = $mailer;
  }

  /**
   * @throws TransportExceptionInterface
   */
  public function postUpdate(User $user, LifecycleEventArgs $event): void
  {
    $this->addPerfectProfileAchievement($user);
    $this->addBronzeUserAchievement($user);
    $this->sendVerifyEmail($user);
  }

  /**
   * @throws Exception
   */
  protected function addPerfectProfileAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementPerfectProfile($user);
  }

  /**
   * @throws Exception
   */
  protected function addBronzeUserAchievement(User $user): void
  {
    $this->achievement_manager->unlockAchievementBronzeUser($user);
  }

  /**
   * @throws TransportExceptionInterface
   */
  protected function sendVerifyEmail(User $user): void
  {
    $signatureComponents = $this->verify_email_helper->generateSignature(
      'registration_confirmation_route',
      $user->getId(),
      $user->getEmail()
    );

    $email = new TemplatedEmail();
    $email->from(new Address('noreply@catrob.at', 'Catrobat Mail Bot'));
    $email->to($user->getEmail());
    $email->htmlTemplate('security/registration/confirmation_email.html.twig');
    $email->context([
      'signedUrl' => $signatureComponents->getSignedUrl(),
      'user' => $user,
    ]);

    $this->mailer->send($email);
  }
}
