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
   * @ORM\OneToMany(targetEntity="Project", mappedBy="user")
   */
  protected $projects;

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
   * Add projects
   *
   * @param \Catrobat\CoreBundle\Entity\Project $projects          
   * @return User
   */
  public function addProject(\Catrobat\CoreBundle\Entity\Project $projects)
  {
    $this->projects[] = $projects;
    
    return $this;
  }

  /**
   * Remove projects
   *
   * @param \Catrobat\CoreBundle\Entity\Project $projects          
   */
  public function removeProject(\Catrobat\CoreBundle\Entity\Project $projects)
  {
    $this->projects->removeElement($projects);
  }

  /**
   * Get projects
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getProjects()
  {
    return $this->projects;
  }

  public function getUploadToken()
  {
    return $this->upload_token;
  }

  public function setUploadToken($upload_token)
  {
    $this->upload_token = $upload_token;
  }

}