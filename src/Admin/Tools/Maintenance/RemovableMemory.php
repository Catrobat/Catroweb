<?php

namespace App\Admin\Tools\Maintenance;

class RemovableMemory
{
  public ?string $size = null;

  public ?int $size_raw = null;

  public ?string $command_link = null;

  public ?string $command_name = null;

  public ?string $download_link = null;

  public ?string $execute_link = null;

  public ?string $archive_command_link = null;

  public ?string $archive_command_name = null;

  public function __construct(public string $name = '', public string $description = '')
  {
  }

  public function setSize(string $size): void
  {
    $this->size = $size;
  }

  public function setSizeRaw(int $size): void
  {
    $this->size_raw = $size;
  }

  public function setCommandLink(string $command): void
  {
    $this->command_link = $command;
  }

  public function setCommandName(string $command): void
  {
    $this->command_name = $command;
  }

  public function setDownloadLink(string $download_link): void
  {
    $this->download_link = $download_link;
  }

  public function getArchiveCommandLink(): ?string
  {
    return $this->archive_command_link;
  }

  public function setArchiveCommandLink(string $archive_command_link): void
  {
    $this->archive_command_link = $archive_command_link;
  }

  public function getArchiveCommandName(): ?string
  {
    return $this->archive_command_name;
  }

  public function setArchiveCommandName(string $archive_command_name): void
  {
    $this->archive_command_name = $archive_command_name;
  }

  public function setExecuteLink(string $execute_link): void
  {
    $this->execute_link = $execute_link;
  }
}
