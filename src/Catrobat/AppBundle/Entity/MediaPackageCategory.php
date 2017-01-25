<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_package_category")
 */
class MediaPackageCategory
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
   * @ORM\ManyToMany(targetEntity="MediaPackage", inversedBy="categories")
   **/
  protected $package;

  /**
   * @ORM\OneToMany(targetEntity="MediaPackageFile", mappedBy="category")
   */
  protected $files;

  /**
   * @ORM\Column(type="integer")
   */
  protected $priority = 0;

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


  public function __toString()
  {
    if(count($this->package)) {
      $string = $this->name." (";
      $count = count($this->package);

      for($it = 0; $it < $count; $it++) {
        $string .= $this->package[$it];

        if($it < ($count-1)) {
            $string .= ", ";
        }
      }
      $string .= ")";

      return $string;
    }
    else
      return $this->name;
  }

  /**
   * @return mixed
   */
  public function getPriority()
  {
    return $this->priority;
  }

  /**
   * @param mixed $priority
   */
  public function setPriority($priority)
  {
    $this->priority = $priority;
  }

}