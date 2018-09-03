<?php
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 01.03.16
 * Time: 17:32
 */

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="TeacherTemplateRepository")
 * @ORM\Table(name="teacher_template")
 */
class TeacherTemplate
{
  /**
   *
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\Column(type="text")
   */
  protected $fileSystemLocation;

  /**
   * @ORM\Column(type="text")
   */
  protected $friendlyName;

  /**
   * @ORM\Column(type="integer")
   */
  protected $priority;

  /**
   * @return mixed
   */
  public function getFileSystemLocation()
  {
    return $this->fileSystemLocation;
  }

  /**
   * @param mixed $fileSystemLocation
   */
  public function setFileSystemLocation($fileSystemLocation)
  {
    $this->fileSystemLocation = $fileSystemLocation;
  }

  /**
   * @return mixed
   */
  public function getFriendlyName()
  {
    return $this->friendlyName;
  }

  /**
   * @param mixed $friendlyName
   */
  public function setFriendlyName($friendlyName)
  {
    $this->friendlyName = $friendlyName;
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


}