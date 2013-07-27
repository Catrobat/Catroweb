<?php

namespace Catrobat\CatrowebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="Catrobat\CatrowebBundle\Entity\ProjectRepository")
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
   * @ORM\Column(type="integer")
   */
  protected $views = 0;
  
  /**
   * @ORM\Column(type="integer")
   */
  protected $downloads = 0;
  
  /**
   * @ORM\Column(type="string")
   */
  protected $filename;
  
  /**
   * @ORM\Column(type="string")
   */
  protected $thumbnail;
  
  /**
   * @ORM\Column(type="string")
   */
  protected $screenshot;
  
  /**
   * @ORM\Column(type="datetime")
   */
  protected $uploaded_at;
  
  /**
   * @ORM\Column(type="datetime")
   */
  protected $last_modified_at;
  
  /**
   * @ORM\PreUpdate
   */
  public function updateLastModifiedTimestamp()
  {
    $this->setLastModifiedAt(new \DateTime());
  }
  
  /**
   * @ORM\PrePersist
   */
  public function updateTimestamps()
  {
    $this->updateLastModifiedTimestamp();
    if ($this->getUploadedAt() == null)
    {
      $this->setUploadedAt(new \DateTime());
    }
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
   * Set description
   *
   * @param string $description          
   * @return Project
   */
  public function setDescription($description)
  {
    $this->description = $description;
    
    return $this;
  }
  
  /**
   * Get description
   *
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  
  /**
   * Set views
   *
   * @param integer $views          
   * @return Project
   */
  public function setViews($views)
  {
    $this->views = $views;
    
    return $this;
  }
  
  /**
   * Get views
   *
   * @return integer
   */
  public function getViews()
  {
    return $this->views;
  }
  
  /**
   * Set downloads
   *
   * @param integer $downloads          
   * @return Project
   */
  public function setDownloads($downloads)
  {
    $this->downloads = $downloads;
    
    return $this;
  }
  
  /**
   * Get downloads
   *
   * @return integer
   */
  public function getDownloads()
  {
    return $this->downloads;
  }
  
  /**
   * Set filename
   *
   * @param string $filename          
   * @return Project
   */
  public function setFilename($filename)
  {
    $this->filename = $filename;
    
    return $this;
  }
  
  /**
   * Get filename
   *
   * @return string
   */
  public function getFilename()
  {
    return $this->filename;
  }
  
  /**
   * Set thumbnail
   *
   * @param string $thumbnail          
   * @return Project
   */
  public function setThumbnail($thumbnail)
  {
    $this->thumbnail = $thumbnail;
    
    return $this;
  }
  
  /**
   * Get thumbnail
   *
   * @return string
   */
  public function getThumbnail()
  {
    return $this->thumbnail;
  }
  
  /**
   * Set screenshot
   *
   * @param string $screenshot          
   * @return Project
   */
  public function setScreenshot($screenshot)
  {
    $this->screenshot = $screenshot;
    
    return $this;
  }
  
  /**
   * Get screenshot
   *
   * @return string
   */
  public function getScreenshot()
  {
    return $this->screenshot;
  }
  
  /**
   * Set uploaded_at
   *
   * @param \DateTime $uploadedAt          
   * @return Project
   */
  public function setUploadedAt($uploadedAt)
  {
    $this->uploaded_at = $uploadedAt;
    
    return $this;
  }
  
  /**
   * Get uploaded_at
   *
   * @return \DateTime
   */
  public function getUploadedAt()
  {
    return $this->uploaded_at;
  }
  
  /**
   * Set last_modified_at
   *
   * @param \DateTime $lastModifiedAt          
   * @return Project
   */
  public function setLastModifiedAt($lastModifiedAt)
  {
    $this->last_modified_at = $lastModifiedAt;
    
    return $this;
  }
  
  /**
   * Get last_modified_at
   *
   * @return \DateTime
   */
  public function getLastModifiedAt()
  {
    return $this->last_modified_at;
  }

    /**
     * Set user
     *
     * @param \Catrobat\CatrowebBundle\Entity\User $user
     * @return Project
     */
    public function setUser(\Catrobat\CatrowebBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Catrobat\CatrowebBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    
    public function __toString()
    {
      return $this->name;
    }
}