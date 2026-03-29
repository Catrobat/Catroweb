<?php

declare(strict_types=1);

namespace Tests\PhpUnit\User\Notification;

use App\User\Notification\EmailNotificationPreference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EmailNotificationPreference::class)]
class EmailNotificationPreferenceTest extends TestCase
{
  public function testAllCasesExist(): void
  {
    $this->assertSame('immediate', EmailNotificationPreference::IMMEDIATE->value);
    $this->assertSame('daily', EmailNotificationPreference::DAILY->value);
    $this->assertSame('weekly', EmailNotificationPreference::WEEKLY->value);
    $this->assertSame('none', EmailNotificationPreference::NONE->value);
  }

  public function testTryFromValidValues(): void
  {
    $this->assertSame(EmailNotificationPreference::IMMEDIATE, EmailNotificationPreference::tryFrom('immediate'));
    $this->assertSame(EmailNotificationPreference::DAILY, EmailNotificationPreference::tryFrom('daily'));
    $this->assertSame(EmailNotificationPreference::WEEKLY, EmailNotificationPreference::tryFrom('weekly'));
    $this->assertSame(EmailNotificationPreference::NONE, EmailNotificationPreference::tryFrom('none'));
  }

  public function testTryFromInvalidValueReturnsNull(): void
  {
    $this->assertNull(EmailNotificationPreference::tryFrom('invalid'));
    $this->assertNull(EmailNotificationPreference::tryFrom(''));
  }
}
