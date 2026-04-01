<?php

declare(strict_types=1);

namespace App\Security\ParentalConsent;

use App\DB\Entity\User\ConsentLog;
use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class ParentalConsentService
{
  public function __construct(
    private readonly VerifyEmailHelperInterface $verify_email_helper,
    private readonly MailerAdapter $mailer,
    private readonly TranslatorInterface $translator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly EntityManagerInterface $entity_manager,
    private readonly UrlGeneratorInterface $url_generator,
    private readonly UserManager $user_manager,
  ) {
  }

  public function sendConsentRequest(User $user): void
  {
    $parent_email = $user->getParentEmail();
    if (null === $parent_email || '' === $parent_email) {
      return;
    }

    $theme = $this->parameter_bag->get('umbrellaTheme');
    $signature = $this->verify_email_helper->generateSignature(
      'parental_consent_confirm',
      (string) $user->getId(),
      $parent_email,
      ['theme' => $theme, 'id' => (string) $user->getId()]
    );

    $this->mailer->send(
      $parent_email,
      $this->translator->trans('consent.email.subject', [], 'catroweb'),
      'Email/ParentalConsentEmail.html.twig',
      [
        'signedUrl' => $signature->getSignedUrl(),
        'username' => $user->getUsername(),
        'parentPortalUrl' => $this->url_generator->generate('parent_portal', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'parentInfoUrl' => $this->url_generator->generate('parent_info', [], UrlGeneratorInterface::ABSOLUTE_URL),
      ]
    );

    $this->logAction($user, 'consent_requested', $parent_email);
  }

  public function sendManagementLink(string $parent_email): void
  {
    $users = $this->entity_manager->createQueryBuilder()
      ->select('u')
      ->from(User::class, 'u')
      ->where('u.parent_email = :email')
      ->setParameter('email', $parent_email)
      ->getQuery()
      ->getResult()
    ;

    if ([] === $users) {
      return;
    }

    $theme = $this->parameter_bag->get('umbrellaTheme');
    $signature = $this->verify_email_helper->generateSignature(
      'parent_management',
      'parent',
      $parent_email,
      ['theme' => $theme, 'email' => $parent_email]
    );

    $usernames = array_map(static fn (User $u) => $u->getUsername(), $users);

    $this->mailer->send(
      $parent_email,
      $this->translator->trans('parent.management.email.subject', [], 'catroweb'),
      'Email/ParentManagementEmail.html.twig',
      [
        'signedUrl' => $signature->getSignedUrl(),
        'childCount' => count($users),
        'usernames' => $usernames,
      ]
    );
  }

  public function getChildrenByParentEmail(string $parent_email): array
  {
    return $this->entity_manager->createQueryBuilder()
      ->select('u')
      ->from(User::class, 'u')
      ->where('u.parent_email = :email')
      ->setParameter('email', $parent_email)
      ->getQuery()
      ->getResult()
    ;
  }

  public function confirmConsent(User $user, ?string $ip_address = null): void
  {
    $user->setConsentStatus('granted');
    $this->entity_manager->flush();
    $this->logAction($user, 'consent_granted', $user->getParentEmail() ?? '', $ip_address);
  }

  public function revokeConsent(User $user, ?string $ip_address = null): void
  {
    $user->setConsentStatus('revoked');
    $this->entity_manager->flush();
    $this->logAction($user, 'consent_revoked', $user->getParentEmail() ?? '', $ip_address);
  }

  public function deleteChildAccount(User $user, ?string $ip_address = null): void
  {
    $this->logAction($user, 'account_deleted', $user->getParentEmail() ?? '', $ip_address);
    $this->user_manager->delete($user);
  }

  public function logAction(User $user, string $action, string $parent_email, ?string $ip_address = null): void
  {
    $log = new ConsentLog($user, $action, $parent_email, $ip_address);
    $this->entity_manager->persist($log);
    $this->entity_manager->flush();
  }
}
