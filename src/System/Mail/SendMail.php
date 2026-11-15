<?php

declare(strict_types=1);

namespace App\System\Mail;

use Symfony\Component\Mime\Email;

final readonly class SendMail
{
  public function __construct(public Email $email, public string $to, public string $email_type)
  {
  }
}
