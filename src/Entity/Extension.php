<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Ldap\Collection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="extension")
 * @ORM\Entity(repositoryClass="App\Repository\ExtensionRepository")
 */
class Extension
{

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $name;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $prefix;

  /**
   * @var Collection|Program[]
   *
   * @ORM\ManyToMany(targetEntity="\App\Entity\Program", mappedBy="extensions")
   */
  protected $programs;

  /**
   * Default constructor, initializes collections
   */
  public function __construct()
  {
    $this->programs = new ArrayCollection();
  }

  /**
   * @param Program $program
   */
  public function addProgram(Program $program)
  {
    if ($this->programs->contains($program))
    {
      return;
    }
    $this->programs->add($program);
    $program->addExtension($this);
  }

  /**
   * @param Program $program
   */
  public function removeProgram(Program $program)
  {
    if (!$this->programs->contains($program))
    {
      return;
    }
    $this->programs->removeElement($program);
    $program->removeExtension($this);
  }

  /**
   * @return Program[]|Collection
   */
  public function getPrograms()
  {
    return $this->programs;
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param mixed $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return mixed
   */
  public function getPrefix()
  {
    return $this->prefix;
  }

  /**
   * @param mixed $prefix
   */
  public function setPrefix($prefix)
  {
    $this->prefix = $prefix;
  }

  public function removeAllPrograms()
  {
    foreach ($this->programs as $program)
    {
      $this->removeProgram($program);
    }
  }

  public function __toString()
  {
    return $this->name;
  }
}
