<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class VerifyEmail
{
  public const string RESEND_INTERVAL = '1 day';

  private VerifyEmailSignatureComponents $signature_components;
  private User $user;
  private string $delete_url;

  public function __construct(
    protected VerifyEmailHelperInterface $verify_email_helper,
    protected MailerAdapter $mailer,
    protected TranslatorInterface $translator,
    protected ParameterBagInterface $parameter_bag,
    protected RouterInterface $router,
    protected EntityManagerInterface $entityManager,
    private readonly UrlGeneratorInterface $url_generator)
  {
  }

  public function init(User $user): self
  {
    $this->user = $user;
    $theme = $this->parameter_bag->get('umbrellaTheme');
    $this->delete_url = $this->url_generator->generate('profile', ['theme' => $theme]);
    $this->signature_components = $this->verify_email_helper->generateSignature(
      'registration_confirmation_route',
      $user->getId(),
      $user->getEmail(),
      ['theme' => $theme]
    );

    return $this;
  }

  public function getTemplate(): string
  {
    return 'Security/Registration/VerifyAccountEmail.html.twig';
  }

  public function getContext(): array
  {
    return [
      'signedUrl' => $this->signature_components->getSignedUrl(),
      'deleteUrl' => $this->delete_url,
      'user' => $this->user,
      'expire' => new \DateTime($this->signature_components->getExpiresAt()->format('Y-m-d H:i:s'))
        ->setTimezone(new \DateTimeZone('Europe/Vienna'))
        ->format('H:i'),
    ];
  }

  /**
   * Sends unconditionally (registration, email change, admin) and returns whether the mail went out. The resend
   * throttle only starts on success, so a failed send doesn't lock the user out for a day.
   */
  public function send(): bool
  {
    $sent = $this->deliver();
    if ($sent) {
      $this->user->setVerificationRequestedAt(TimeUtils::getDateTime());
      $this->entityManager->persist($this->user);
      $this->entityManager->flush();
    }

    return $sent;
  }

  /**
   * User-initiated resend: at most one mail per RESEND_INTERVAL, also under parallel requests. The slot is claimed
   * with one conditional UPDATE before the (slow) send and handed back only if it is still ours when the send fails.
   */
  public function sendUnlessRecentlySent(): bool
  {
    $now = TimeUtils::getDateTime();
    $previous = $this->user->getVerificationRequestedAt();
    $claimed = $this->updateRequestedAt($now, 'u.verification_requested_at IS NULL OR u.verification_requested_at <= :cutoff', ['cutoff' => new \DateTime('-'.self::RESEND_INTERVAL)]);
    if (!$claimed) {
      return false;
    }

    $this->user->setVerificationRequestedAt($now);
    $sent = $this->deliver();
    if (!$sent) {
      $this->updateRequestedAt($previous, 'u.verification_requested_at = :claimed', ['claimed' => $now]);
      $this->user->setVerificationRequestedAt($previous);
    }

    return $sent;
  }

  private function deliver(): bool
  {
    return $this->mailer->send(
      $this->user->getEmail(),
      $this->translator->trans('user.verification.email', [], 'catroweb'),
      $this->getTemplate(),
      $this->getContext(),
      'verification'
    );
  }

  /**
   * @param array<string, \DateTimeInterface> $parameters
   */
  private function updateRequestedAt(?\DateTimeInterface $value, string $condition, array $parameters): bool
  {
    $query = $this->entityManager->createQuery(
      'UPDATE '.User::class.' u SET u.verification_requested_at = :value WHERE u.id = :id AND ('.$condition.')'
    )
      ->setParameter('value', $value)
      ->setParameter('id', $this->user->getId())
    ;
    foreach ($parameters as $name => $parameter) {
      $query->setParameter($name, $parameter);
    }

    return 1 === $query->execute();
  }
}
