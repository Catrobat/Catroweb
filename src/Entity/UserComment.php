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
   * @ORM\Column(type="integer")
   */
  protected $programId;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="programs", referencedColumnName="id", nullable=true)
   */
  private $program;

  /**
   * @ORM\Column(type="integer")
   */
  protected $userId;

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
   * @return mixed
   */
  public function getProgramId()
  {
    return $this->programId;
  }

  /**
   * @param mixed $programId
   */
  public function setProgramId($programId)
  {
    $this->programId = $programId;
  }

  /**
   * @return mixed
   */
  public function getUserId()
  {
    return $this->userId;
  }

  /**
   * @param mixed $userId
   */
  public function setUserId($userId)
  {
    $this->userId = $userId;
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
}