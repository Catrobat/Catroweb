<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="scratch_program")
 * @ORM\Entity(repositoryClass="App\Repository\ScratchProgramRepository")
 */
class ScratchProgram
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false)
   */
  protected $id;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected $name;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected $description;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected $username;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $last_modified_at;

  /**
   * ScratchProgram constructor.
   *
   * @param $id
   *
   * @throws \Exception
   */
  public function __construct($id)
  {
    $this->id = $id;
    $this->updateLastModifiedTimestamp();
  }

  /**
   * @param string $name
   *
   * @return ScratchProgram
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * @param string $description
   *
   * @return ScratchProgram
   */
  public function setDescription($description)
  {
    $this->description = $description;

    return $this;
  }

  /**
   * @param string $username
   *
   * @return ScratchProgram
   */
  public function setUsername($username)
  {
    $this->username = $username;

    return $this;
  }

  /**
   * @ORM\PreUpdate
   *
   * @throws \Exception
   */
  public function updateLastModifiedTimestamp()
  {
    $this->setLastModifiedAt(new \DateTime());
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string|null
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string|null
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @return string|null
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Set last_modified_at.
   *
   * @param \DateTime $last_modified_at
   *
   * @return ScratchProgram
   */
  public function setLastModifiedAt(\DateTime $last_modified_at)
  {
    $this->last_modified_at = $last_modified_at;

    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getLastModifiedAt()
  {
    return $this->last_modified_at;
  }

}
