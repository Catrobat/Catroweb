<?php

namespace App\Catrobat\CatrobatCode\Statements;


/**
 * Class BroadcastScriptStatement
 * @package App\Catrobat\CatrobatCode\Statements
 */
class BroadcastScriptStatement extends Statement
{
  const BEGIN_STRING = "when receive message ";

  /**
   * @var
   */
  private $message;

  /**
   * BroadcastScriptStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      "");
  }

  /**
   * @return string
   */
  public function execute()
  {
    $children = $this->executeChildren();
    $code = parent::addSpaces() . self::BEGIN_STRING;
    if ($this->message != null)
    {
      $code .= $this->message->execute();
    }
    $code .= "<br/>" . $children;

    return $code;
  }

  /**
   * @return string
   */
  public function executeChildren()
  {
    $code = '';
    foreach ($this->statements as $value)
    {
      if ($value instanceof ReceivedMessageStatement)
      {
        $this->message = $value;
      }
      else
      {
        $code .= $value->execute();
      }
    }

    return $code;
  }

  /**
   * @return mixed
   */
  public function getMessage()
  {
    if ($this->message == null)
    {
      $this->message = $this->xmlTree->receivedMessage;
    }

    return $this->message;
  }
}
