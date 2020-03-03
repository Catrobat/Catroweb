<?php

namespace App\Catrobat\Requests;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class AddProgramRequest.
 */
class AddProgramRequest
{
  /**
   * @var User
   */
  private $user;
  /**
   * @var File
   */
  private $programfile;
  /**
   * @var string
   */
  private $ip;
  /**
   * @var null
   */
  private $gamejam;
  /**
   * @var null
   */
  private $language;
  /**
   * @var string
   */
  private $flavor;

  /**
   * AddProgramRequest constructor.
   *
   * @param string $ip
   * @param null   $gamejam
   * @param null   $language
   * @param string $flavor
   */
  public function __construct(User $user, File $programfile, $ip = '127.0.0.1', $gamejam = null, $language = null, $flavor = 'pocketcode')
  {
    $this->user = $user;
    $this->programfile = $programfile;
    $this->ip = $ip;
    $this->gamejam = $gamejam;
    $this->language = $language;
    $this->flavor = $flavor;
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  public function setUser(User $user)
  {
    $this->user = $user;
  }

  /**
   * @return File
   */
  public function getProgramfile()
  {
    return $this->programfile;
  }

  public function setProgramfile(File $programfile)
  {
    $this->programfile = $programfile;
  }

  /**
   * @return string
   */
  public function getIp()
  {
    return $this->ip;
  }

  public function getGamejam()
  {
    return $this->gamejam;
  }

  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * @param $language
   */
  public function setLanguage($language)
  {
    $this->language = $language;
  }

  /**
   * @return string
   */
  public function getFlavor()
  {
    return $this->flavor;
  }
}
