<?php

declare(strict_types=1);

namespace App\User\Notification;

use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailDigestService
{
  private const int MAX_EMAILS_PER_RUN = 250;

  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly MailerAdapter $mailer,
    private readonly TranslatorInterface $translator,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Send digest emails for the given preference period.
   *
   * @return int number of digest emails sent
   */
  public function sendDigests(EmailNotificationPreference $preference): int
  {
    $users = $this->getUsersWithPendingNotifications($preference);
    $sent = 0;

    foreach ($users as $user) {
      if ($sent >= self::MAX_EMAILS_PER_RUN) {
        $this->logger->info(sprintf('Email digest: reached max %d emails per run, stopping.', self::MAX_EMAILS_PER_RUN));
        break;
      }

      $notifications = $this->getPendingNotificationsForUser($user);
      if ([] === $notifications) {
        continue;
      }

      $grouped = $this->groupNotifications($notifications);

      try {
        $this->sendDigestEmail($user, $grouped, $notifications);
        $this->markNotificationsAsEmailed($notifications);
        ++$sent;
      } catch (\Exception $e) {
        $this->logger->error(sprintf('Email digest: failed to send to user %s: %s', $user->getUsername() ?? 'unknown', $e->getMessage()));
      }
    }

    $this->em->flush();

    return $sent;
  }

  /**
   * @return User[]
   */
  private function getUsersWithPendingNotifications(EmailNotificationPreference $preference): array
  {
    $qb = $this->em->createQueryBuilder();

    return $qb
      ->select('DISTINCT u')
      ->from(User::class, 'u')
      ->join(CatroNotification::class, 'n', 'WITH', 'n.user = u')
      ->where('u.emailNotificationPreference = :preference')
      ->andWhere('n.emailSent = false')
      ->andWhere('u.email IS NOT NULL')
      ->andWhere('u.verified = true')
      ->setParameter('preference', $preference->value)
      ->setMaxResults(self::MAX_EMAILS_PER_RUN)
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @return CatroNotification[]
   */
  private function getPendingNotificationsForUser(User $user): array
  {
    $qb = $this->em->createQueryBuilder();

    return $qb
      ->select('n')
      ->from(CatroNotification::class, 'n')
      ->where('n.user = :user')
      ->andWhere('n.emailSent = false')
      ->setParameter('user', $user)
      ->orderBy('n.createdAt', 'DESC')
      ->setMaxResults(50)
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * Group notifications by type for the digest template.
   *
   * @param CatroNotification[] $notifications
   *
   * @return array<string, list<CatroNotification>>
   */
  public function groupNotifications(array $notifications): array
  {
    $grouped = [];

    foreach ($notifications as $notification) {
      $type = match (true) {
        $notification instanceof CommentNotification => 'comment',
        $notification instanceof LikeNotification => 'reaction',
        $notification instanceof FollowNotification => 'follower',
        $notification instanceof NewProgramNotification => 'follower',
        $notification instanceof RemixNotification => 'remix',
        $notification instanceof ModerationNotification => 'moderation',
        default => 'other',
      };

      $grouped[$type][] = $notification;
    }

    return $grouped;
  }

  /**
   * @param array<string, list<CatroNotification>> $grouped
   * @param CatroNotification[]                    $notifications
   */
  private function sendDigestEmail(User $user, array $grouped, array $notifications): void
  {
    $email = $user->getEmail();
    if (null === $email || '' === $email) {
      return;
    }

    $subject = $this->translator->trans(
      'emailDigest.subject',
      ['%count%' => count($notifications)],
      'catroweb'
    );

    $this->mailer->send(
      $email,
      $subject,
      'Email/NotificationDigest.html.twig',
      [
        'user' => $user,
        'grouped' => $grouped,
        'total_count' => count($notifications),
      ]
    );
  }

  /**
   * @param CatroNotification[] $notifications
   */
  private function markNotificationsAsEmailed(array $notifications): void
  {
    foreach ($notifications as $notification) {
      $notification->setEmailSent(true);
    }
  }
}
