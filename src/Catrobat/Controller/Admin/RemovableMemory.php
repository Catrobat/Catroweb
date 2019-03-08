<?php

namespace App\Catrobat\Controller\Admin;


/**
 * Class RemovableMemory
 * @package App\Catrobat\Controller\Admin
 */
class RemovableMemory
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
  public $size;

  /**
   * @var
   */
  public $size_raw;

  /**
   * @var
   */
  public $command_link;

  /**
   * @var
   */
  public $command_name;

  /**
   * @var
   */
  public $download_link;

  /**
   * @var
   */
  public $execute_link;

  /**
   * @var
   */
  public $archive_command_link;

  /**
   * @var
   */
  public $archive_command_name;


  /**
   * RemovableMemory constructor.
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
   * @param mixed $size
   */
  public function setSize($size)
  {
    $this->size = $size;
  }

  /**
   * @param mixed $size
   */
  public function setSizeRaw($size)
  {
    $this->size_raw = $size;
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
   * @param mixed $download_link
   */
  public function setDownloadLink($download_link)
  {
    $this->download_link = $download_link;
  }

  /**
   * @return mixed
   */
  public function getArchiveCommandLink()
  {
    return $this->archive_command_link;
  }

  /**
   * @param mixed $archive_command_link
   */
  public function setArchiveCommandLink($archive_command_link)
  {
    $this->archive_command_link = $archive_command_link;
  }

  /**
   * @return mixed
   */
  public function getArchiveCommandName()
  {
    return $this->archive_command_name;
  }

  /**
   * @param mixed $archive_command_name
   */
  public function setArchiveCommandName($archive_command_name)
  {
    $this->archive_command_name = $archive_command_name;
  }

  /**
   * @param mixed $execute_link
   */
  public function setExecuteLink($execute_link)
  {
    $this->execute_link = $execute_link;
  }
}