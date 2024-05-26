<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Statements;

class BroadcastScriptStatement extends Statement
{
  final public const string BEGIN_STRING = 'when receive message ';

  private mixed $message;

  public function __construct(mixed $statementFactory, mixed $xmlTree, mixed $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      '');
  }

  #[\Override]
  public function execute(): string
  {
    $children = $this->executeChildren();
    $code = parent::addSpaces().self::BEGIN_STRING;
    if (null != $this->message) {
      $code .= $this->message->execute();
    }

    return $code.('<br/>'.$children);
  }

  #[\Override]
  public function executeChildren(): string
  {
    $code = '';
    foreach ($this->statements as $value) {
      if ($value instanceof ReceivedMessageStatement) {
        $this->message = $value;
      } else {
        $code .= $value->execute();
      }
    }

    return $code;
  }

  public function getMessage(): mixed
  {
    if (null == $this->message) {
      $this->message = $this->xmlTree->receivedMessage;
    }

    return $this->message;
  }
}
