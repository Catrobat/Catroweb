<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_package")
 */
class MediaPackage
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
   * @ORM\Column(type="text", nullable=false)
   */
  protected $name_url;

  /**
   * @ORM\ManyToMany(targetEntity="MediaPackageCategory", mappedBy="package")
   */
  protected $categories;

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
  public function getNameUrl()
  {
    return $this->name_url;
  }

  /**
   * @param mixed $name_url
   */
  public function setNameUrl($name_url)
  {
    $this->name_url = $name_url;
  }

  /**
   * @return mixed
   */
  public function getCategories()
  {
    return $this->categories;
  }

  /**
   * @param mixed $categories
   */
  public function setCategories($categories)
  {
    $this->categories = $categories;
  }

  public function __toString()
  {
    return (string)$this->name;
  }
}