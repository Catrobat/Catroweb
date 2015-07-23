<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_package_file")
 */
class MediaPackageFile
{
  public $file;
  public $removed_id;
  public $old_extension;

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
   * @ORM\Column(type="string")
   */
  protected $extension;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected $url;

  /**
   * @ORM\ManyToOne(targetEntity="MediaPackageCategory", inversedBy="files")
   */
  protected $category;


  /**
   * @ORM\Column(type="boolean")
   */
  protected $active;

  /**
   * @return boolean
   */
  public function getActive()
  {
    return $this->active;
  }

  /**
   * @param mixed $active
   * @return MediaPackageFile
   */
  public function setActive($active)
  {
    $this->active = $active;
    return $this;
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
   * @return mixed
   */
  public function getUrl()
  {
    return $this->url;
  }

  /**
   * @param mixed $url
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }

  /**
   * @return mixed
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @param mixed $category
   */
  public function setCategory($category)
  {
    $this->category = $category;
  }

  /**
   * Set extension.
   *
   * @param string $extension
   *
   * @return MediaPackageFile
   */
  public function setExtension($extension)
  {
    $this->extension = $extension;

    return $this;
  }

  /**
   * Get extension.
   *
   * @return string
   */
  public function getExtension()
  {
    return $this->extension;
  }

}