<?php

declare(strict_types=1);

namespace App\Security;

class PasswordGenerator
{
  /**
   * @throws \Exception
   */
  public static function generateRandomPassword(int $length = 32): string
  {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
            '0123456789-=~!@#$%&*()_+,.<>?;:[]{}|';

    $password = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; ++$i) {
      $password .= $chars[random_int(0, $max)];
    }

    return $password;
  }
}
