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
      'expire' => (new \DateTime($this->signature_components->getExpiresAt()->format('Y-m-d H:i:s')))
        ->setTimezone(new \DateTimeZone('Europe/Vienna'))
        ->format('H:i'),
    ];
  }

  public function send(): void
  {
    $this->user->setVerificationRequestedAt(TimeUtils::getDateTime());
    $this->entityManager->persist($this->user);
    $this->entityManager->flush();
    $this->mailer->send(
      $this->user->getEmail(),
      $this->translator->trans('user.verification.email', [], 'catroweb'),
      $this->getTemplate(),
      $this->getContext()
    );
  }
}
