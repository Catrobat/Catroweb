<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\NolbExampleRepository")
 * @ORM\Table(name="nolb_example_program")
 */
class NolbExampleProgram
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\OneToOne(targetEntity="Program", fetch="EAGER")
   **/
  private $program;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $active;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $is_for_female = false;

  /**
   * @ORM\Column(type="integer")
   */
  protected $downloads_from_female = 0;

  /**
   * @ORM\Column(type="integer")
   */
  protected $downloads_from_male = 0;

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
  public function getActive()
  {
    return $this->active;
  }

  /**
   * @param mixed $active
   */
  public function setActive($active)
  {
    $this->active = $active;
  }

  /**
   * @return mixed
   */
  public function getIsForFemale()
  {
    return $this->is_for_female;
  }

  /**
   * @param mixed $is_for_female
   */
  public function setIsForFemale($is_for_female)
  {
    $this->is_for_female = $is_for_female;
  }

  /**
   * @return mixed
   */
  public function getIsForMale()
  {
    return !$this->is_for_female;
  }

  /**
   * @param mixed $is_for_male
   */
  public function setIsForMale($is_for_male)
  {
    $this->is_for_female = !$is_for_male;
  }

  /**
   * @return mixed
   */
  public function getDownloadsFromMale()
  {
    return $this->downloads_from_male;
  }

  /**
   * @param mixed $downloads_from_male
   */
  public function setDownloadsFromMale($downloads_from_male)
  {
    $this->downloads_from_male = $downloads_from_male;
  }

  public function increaseMaleDownloads()
  {
    $this->downloads_from_male = $this->downloads_from_male + 1;
  }

  /**
   * @return mixed
   */
  public function getDownloadsFromFemale()
  {
    return $this->downloads_from_female;
  }

  /**
   * @param mixed $downloads_from_female
   */
  public function setDownloadsFromFemale($downloads_from_female)
  {
    $this->downloads_from_female = $downloads_from_female;
  }

  public function increaseFemaleDownloads()
  {
    $this->downloads_from_female = $this->downloads_from_female + 1;
  }


}
