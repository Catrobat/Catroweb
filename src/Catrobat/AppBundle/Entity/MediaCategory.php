<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\MediaCategory")
 * @ORM\Table(name="media_category")
 */
class MediaCategory
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="text", nullable=false)
   */
  protected $name;

  /**
   * @ORM\ManyToOne(targetEntity="MediaPackage", inversedBy="categories")
   **/
  protected $package;

  /**
   * @ORM\OneToMany(targetEntity="MediaFile", mappedBy="category")
   */
  protected $files;

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
  public function getPackage()
  {
    return $this->package;
  }

  /**
   * @param mixed $package
   */
  public function setPackage($package)
  {
    $this->package = $package;
  }

  /**
   * @return mixed
   */
  public function getFiles()
  {
    return $this->files;
  }

  /**
   * @param mixed $files
   */
  public function setFiles($files)
  {
    $this->files = $files;
  }

}