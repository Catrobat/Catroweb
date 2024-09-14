<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordEmail
{
  private User $user;
  private string $locale;

  public function __construct(
    private readonly ResetPasswordHelperInterface $reset_password_helper,
    protected MailerAdapter $mailer,
    protected TranslatorInterface $translator)
  {
  }

  public function init(User $user, string $locale): self
  {
    $this->locale = $locale;
    $this->user = $user;

    return $this;
  }

  public function getTemplate(): string
  {
    return 'Security/ResetPassword/ResetPasswordEmail.html.twig';
  }

  /**
   * @throws ResetPasswordExceptionInterface
   */
  public function getContext(): array
  {
    return [
      'resetToken' => $this->reset_password_helper->generateResetToken($this->user),
    ];
  }

  public function getFakeContext(): array
  {
    return [
      'resetToken' => $this->reset_password_helper->generateFakeResetToken(),
    ];
  }

  /**
   * @throws ResetPasswordExceptionInterface
   */
  public function send(): void
  {
    $this->mailer->send(
      $this->user->getEmail(),
      $this->translator->trans('passwordRecovery.subject', [], 'catroweb', $this->locale),
      $this->getTemplate(),
      $this->getContext()
    );
  }
}
