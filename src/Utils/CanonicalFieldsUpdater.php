<?php

declare(strict_types=1);

namespace App\Utils;

use App\DB\Entity\User\User;

class CanonicalFieldsUpdater
{
  public function updateCanonicalFields(User $user): void
  {
    $user->setUsernameCanonical($this->canonicalizeUsername($user->getUserIdentifier()));
    $user->setEmailCanonical($this->canonicalizeEmail($user->getEmail()));
  }

  public function canonicalizeEmail(?string $email): ?string
  {
    return $this->canonicalize($email);
  }

  public function canonicalizeUsername(?string $username): ?string
  {
    return $this->canonicalize($username);
  }

  public function canonicalize(?string $string): ?string
  {
    if (null === $string) {
      return null;
    }

    $detectedOrder = mb_detect_order();
    assert(is_array($detectedOrder));

    $encoding = mb_detect_encoding($string, $detectedOrder, true);

    return false !== $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER
            );
  }
}
