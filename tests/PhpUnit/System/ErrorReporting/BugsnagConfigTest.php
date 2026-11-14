<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\ErrorReporting;

use Bugsnag\Client;
use Bugsnag\Report;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class BugsnagConfigTest extends KernelTestCase
{
  public function testSessionDataIsRedacted(): void
  {
    $report = Report::fromPHPThrowable($this->client()->getConfig(), new \RuntimeException('boom'));
    $report->setMetaData(['session' => ['_security_main' => 'O:...$2y$13$hash...']]);

    $this->assertSame('[FILTERED]', $report->toArray()['metaData']['session']);
  }

  private function client(): Client
  {
    $client = self::getContainer()->get('bugsnag');
    $this->assertInstanceOf(Client::class, $client);

    return $client;
  }
}
