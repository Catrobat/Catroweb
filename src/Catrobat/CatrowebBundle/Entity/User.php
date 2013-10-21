<?php

namespace Catrobat\CatrowebBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
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
   * @ORM\Column(type="string", length=300)
   */
  protected $token;
  
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
   * @param \Catrobat\CatrowebBundle\Entity\Project $projects          
   * @return User
   */
  public function addProject(\Catrobat\CatrowebBundle\Entity\Project $projects)
  {
    $this->projects[] = $projects;
    
    return $this;
  }

  /**
   * Remove projects
   *
   * @param \Catrobat\CatrowebBundle\Entity\Project $projects          
   */
  public function removeProject(\Catrobat\CatrowebBundle\Entity\Project $projects)
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

  public function getToken()
  {
    return $this->token;
  }

  public function setToken($token)
  {
    $this->token = $token;
  }

}