<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserCommentRepository")
 * @ORM\Table(name="user_comment")
 */
class UserComment
{
  /**
   *
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="programId", referencedColumnName="id", nullable=true)
   */
  private $program;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="comments", fetch="EXTRA_LAZY")
   * @ORM\JoinColumn(name="userId", referencedColumnName="id", nullable=true)
   */
  protected $user;

  /**
   * @ORM\Column(type="date")
   */
  protected $uploadDate;

  /**
   * @ORM\Column(type="text")
   */
  protected $text;

  /**
   * @ORM\Column(type="string")
   */
  protected $username;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isReported;

  /**
   * @return mixed
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @param mixed $program
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param User|mixed $user
   */
  public function setUser($user)
  {
    $this->user = $user;
  }

  /**
   * @return mixed
   */
  public function getUploadDate()
  {
    return $this->uploadDate;
  }

  /**
   * @param mixed $uploadDate
   */
  public function setUploadDate($uploadDate)
  {
    $this->uploadDate = $uploadDate;
  }

  /**
   * @return mixed
   */
  public function getText()
  {
    return $this->text;
  }

  /**
   * @param mixed $text
   */
  public function setText($text)
  {
    $this->text = $text;
  }

  /**
   * @return mixed
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @param mixed $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  /**
   * @return mixed
   */
  public function getIsReported()
  {
    return $this->isReported;
  }

  /**
   * @param mixed $isReported
   */
  public function setIsReported($isReported)
  {
    $this->isReported = $isReported;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->text;
  }
}