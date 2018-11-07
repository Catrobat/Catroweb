<?php

namespace Catrobat\AppBundle\Controller\Admin;


class AdminCommand
{
  public $name;
  public $description;
  public $command_link;
  public $progress_link;
  public $command_name;

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