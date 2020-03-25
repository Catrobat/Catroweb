<?php

namespace App\Catrobat\Controller\Admin;

class AdminCommand
{
  public string $name;

  public string $description;

  public string $command_link;

  public string $progress_link;

  public string $command_name;

  public function __construct(string $name = '', string $description = '')
  {
    $this->name = $name;
    $this->description = $description;
  }

  public function setCommandLink(string $command): void
  {
    $this->command_link = $command;
  }

  public function setCommandName(string $command): void
  {
    $this->command_name = $command;
  }

  public function getProgressLink(): string
  {
    return $this->progress_link;
  }

  public function setProgressLink(string $progress_link): void
  {
    $this->progress_link = $progress_link;
  }
}
