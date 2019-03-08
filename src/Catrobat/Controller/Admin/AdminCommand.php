<?php

namespace App\Catrobat\Controller\Admin;


/**
 * Class AdminCommand
 * @package App\Catrobat\Controller\Admin
 */
class AdminCommand
{

  /**
   * @var String
   */
  public $name;

  /**
   * @var String
   */
  public $description;

  /**
   * @var
   */
  public $command_link;

  /**
   * @var
   */
  public $progress_link;

  /**
   * @var
   */
  public $command_name;


  /**
   * AdminCommand constructor.
   *
   * @param String $name
   * @param String $description
   */
  public function __construct(String $name = "", String $description = "")
  {
    $this->name = $name;
    $this->description = $description;
  }

  /**
   * @param mixed $command
   */
  public function setCommandLink($command)
  {
    $this->command_link = $command;
  }

  /**
   * @param mixed $command
   */
  public function setCommandName($command)
  {
    $this->command_name = $command;
  }

  /**
   * @return mixed
   */
  public function getProgressLink()
  {
    return $this->progress_link;
  }

  /**
   * @param mixed $progress_link
   */
  public function setProgressLink($progress_link)
  {
    $this->progress_link = $progress_link;
  }
}