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
  protected $nameUrl;

  /**
   * @ORM\ManyToMany(targetEntity="MediaPackageCategory", mappedBy="package")
   */
  protected $categories;


  protected $flavors = [];

  /**
   * @return array
   */
  public function getFlavors(): array
  {
    return $this->flavors;
  }

  /**
   * @param array $flavors
   */
  public function setFlavors(array $flavors): void
  {
    $this->flavors = $flavors;
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
   * @return string
   */
  public function getNameUrl()
  {
    return $this->nameUrl;
  }

  /**
   * @param mixed $name_url
   */
  public function setNameUrl($name_url)
  {
    $this->nameUrl = $name_url;
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