<?php

namespace Catrobat\ApiBundle\Entity;

use Symfony\Component\Translation\Tests\String;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 */
class Project
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
  protected $name;
  
  /**
   * @ORM\Column(type="text")
   */
  protected $description;
  
  /**
   * @ORM\ManyToOne(targetEntity="User", inversedBy="projects")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
   */
  protected $user;
  

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
      return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }
}