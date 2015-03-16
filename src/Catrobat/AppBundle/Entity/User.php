<?php

namespace Catrobat\AppBundle\Entity;

use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;
  
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected $upload_token;

  /**
  * @ORM\Column(type="boolean", nullable=true, options={"default":false})
  */
  protected $upload_notification;

  /**
   * @ORM\OneToMany(targetEntity="Program", mappedBy="user")
   */
  protected $programs;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $additional_email;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $country;

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Get id
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Add programs
   *
   * @param \Catrobat\AppBundle\Entity\Program $programs          
   * @return User
   */
  public function addProgram(\Catrobat\AppBundle\Entity\Program $programs)
  {
    $this->programs[] = $programs;
    
    return $this;
  }

  /**
   * Remove programs
   *
   * @param \Catrobat\AppBundle\Entity\Program $programs          
   */
  public function removeProgram(\Catrobat\AppBundle\Entity\Program $programs)
  {
    $this->programs->removeElement($programs);
  }

  /**
   * Get programs
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getPrograms()
  {
    return $this->programs;
  }

  public function getUploadToken()
  {
    return $this->upload_token;
  }

  public function setUploadToken($upload_token)
  {
    $this->upload_token = $upload_token;
  }

  /**
   * @param mixed $upload_notification
   */
  public function setUploadNotification($upload_notification)
  {
      $this->upload_notification = $upload_notification;
  }

  /**
   * @return mixed
   */
  public function getUploadNotification()
  {
      return $this->upload_notification;
  }

  /**
   * @param mixed $additional_email
   */
  public function setAdditionalEmail($additional_email)
  {
    $this->additional_email = $additional_email;
  }

  /**
   * @return mixed
   */
  public function getAdditionalEmail()
  {
    return $this->additional_email;
  }

  /**
   * @param mixed $country
   */
  public function setCountry($country)
  {
    $this->country = $country;
  }

  /**
   * @return mixed
   */
  public function getCountry()
  {
    return $this->country;
  }

}