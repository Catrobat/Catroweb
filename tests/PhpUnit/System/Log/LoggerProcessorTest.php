<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Log;

use App\System\Log\LoggerProcessor;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @covers \App\System\Log\LoggerProcessor
 *
 * @internal
 */
class LoggerProcessorTest extends KernelTestCase
{
  public function testAddsRequestIdFromHeader(): void
  {
    $extra = $this->process(['HTTP_X_REQUEST_ID' => 'abc-123'])->extra;

    $this->assertSame('abc-123', $extra['request_id']);
    $this->assertSame('anonymous', $extra['session_user']);
  }

  public function testOmitsMissingOrMalformedRequestId(): void
  {
    $this->assertArrayNotHasKey('request_id', $this->process([])->extra);
    $this->assertArrayNotHasKey('request_id', $this->process(['HTTP_X_REQUEST_ID' => 'a b|c'])->extra);
    $this->assertArrayNotHasKey('request_id', $this->process(['HTTP_X_REQUEST_ID' => "abc\n"])->extra);
  }

  public function testNoRequestLeavesRecordUntouched(): void
  {
    $processor = new LoggerProcessor(new RequestStack(), new TokenStorage());

    $this->assertSame([], $processor($this->record())->extra);
  }

  public function testFormatterRendersRequestIdAndStackTrace(): void
  {
    /** @var FormatterInterface $formatter */
    $formatter = static::getContainer()->get('monolog.formatter.catrobat_custom_formatter');

    $line = $formatter->format($this->process(['HTTP_X_REQUEST_ID' => 'abc-123'], new \RuntimeException('boom')));
    $this->assertStringContainsString('rid=abc-123', $line);
    $this->assertStringContainsString('[stacktrace]', $line);
    $this->assertStringContainsString('#0 ', $line);
    $this->assertStringNotContainsString(dirname(__DIR__, 4).'/', $line);

    $line = $formatter->format($this->record());
    $this->assertStringContainsString('rid= ', $line);
    $this->assertStringNotContainsString('%extra.', $line);
  }

  private function process(array $server, ?\Throwable $e = null): LogRecord
  {
    $stack = new RequestStack();
    $stack->push(Request::create('/', server: $server));

    return new LoggerProcessor($stack, new TokenStorage())($this->record($e));
  }

  private function record(?\Throwable $e = null): LogRecord
  {
    return new LogRecord(new \DateTimeImmutable(), 'app', Level::Error, 'failed', null === $e ? [] : ['exception' => $e]);
  }
}
