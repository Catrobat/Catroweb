<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\BackgroundJob;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/** Keeps what code queues, so a test can assert it or hand it to the handler like a worker would. */
final class RecordingMessageBus implements MessageBusInterface
{
  /** @var list<object> */
  public array $dispatched = [];

  #[\Override]
  public function dispatch(object $message, array $stamps = []): Envelope
  {
    $this->dispatched[] = $message;

    return new Envelope($message, $stamps);
  }
}
