<?php

namespace Catrobat\CoreBundle\Entity;

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
   * @param \Catrobat\CoreBundle\Entity\Program $programs          
   * @return User
   */
  public function addProgram(\Catrobat\CoreBundle\Entity\Program $programs)
  {
    $this->programs[] = $programs;
    
    return $this;
  }

  /**
   * Remove programs
   *
   * @param \Catrobat\CoreBundle\Entity\Program $programs          
   */
  public function removeProgram(\Catrobat\CoreBundle\Entity\Program $programs)
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

}