<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program")
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\ProgramRepository")
 */
class Program
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
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User", inversedBy="programs")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
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
   * @ORM\Column(type="string", options={"default":0})
   */
  protected $language_version = 0;
  
  /**
   * @ORM\Column(type="string", options={"default":""})
   */
  protected $catrobat_version_name;
  
  /**
   * @ORM\Column(type="integer", options={"default":0})
   */
  protected $catrobat_version;
  
  /**
   * @ORM\Column(type="string", options={"default":""})
   */
  protected $upload_ip;
  
  /**
   * @ORM\Column(type="boolean", options={"default":true})
   */
  protected $visible;
  
  /**
   * @ORM\Column(type="string", options={"default":""})
   */
  protected $upload_language;
  
  /**
   * @ORM\Column(type="integer", options={"default":0})
   */
  protected $filesize;
  
  /**
   * @ORM\Column(type="integer", options={"default":0})
   */
  protected $remix_count;
  
  /**
   * @ORM\ManyToOne(targetEntity="Program")
   * @ORM\JoinColumn(name="remix_id", referencedColumnName="id")
   */
  protected $remix_of;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $approved;


    /**
     * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_by_user", referencedColumnName="id", nullable=true)
     */
    protected $approved_by_user;

    /**
     * @param mixed $approved_by_user
     */
    public function setApprovedByUser($approved_by_user)
    {
        $this->approved_by_user = $approved_by_user;
    }

    /**
     * @return mixed
     */
    public function getApprovedByUser()
    {
        return $this->approved_by_user;
    }
  
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
   * @return Program
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
     * @param \Catrobat\AppBundle\Entity\User $user
     * @return Program
     */
    public function setUser(\Catrobat\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Catrobat\AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    
    public function __toString()
    {
      return $this->name;
    }

    /**
     * Set language_version
     *
     * @param string $languageVersion
     * @return Program
     */
    public function setLanguageVersion($languageVersion)
    {
        $this->language_version = $languageVersion;
    
        return $this;
    }

    /**
     * Get language_version
     *
     * @return string 
     */
    public function getLanguageVersion()
    {
        return $this->language_version;
    }

    /**
     * Set catrobat_version_name
     *
     * @param string $catrobatVersionName
     * @return Program
     */
    public function setCatrobatVersionName($catrobatVersionName)
    {
        $this->catrobat_version_name = $catrobatVersionName;
    
        return $this;
    }

    /**
     * Get catrobat_version_name
     *
     * @return string 
     */
    public function getCatrobatVersionName()
    {
        return $this->catrobat_version_name;
    }

    /**
     * Set catrobat_version
     *
     * @param integer $catrobatVersion
     * @return Program
     */
    public function setCatrobatVersion($catrobatVersion)
    {
        $this->catrobat_version = $catrobatVersion;
    
        return $this;
    }

    /**
     * Get catrobat_version
     *
     * @return integer 
     */
    public function getCatrobatVersion()
    {
        return $this->catrobat_version;
    }

    /**
     * Set upload_ip
     *
     * @param string $uploadIp
     * @return Program
     */
    public function setUploadIp($uploadIp)
    {
        $this->upload_ip = $uploadIp;
    
        return $this;
    }

    /**
     * Get upload_ip
     *
     * @return string 
     */
    public function getUploadIp()
    {
        return $this->upload_ip;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return Program
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    
        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set upload_language
     *
     * @param string $uploadLanguage
     * @return Program
     */
    public function setUploadLanguage($uploadLanguage)
    {
        $this->upload_language = $uploadLanguage;
    
        return $this;
    }

    /**
     * Get upload_language
     *
     * @return string 
     */
    public function getUploadLanguage()
    {
        return $this->upload_language;
    }

    /**
     * Set filesize
     *
     * @param integer $filesize
     * @return Program
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
    
        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer 
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * Set remix_count
     *
     * @param integer $remixCount
     * @return Program
     */
    public function setRemixCount($remixCount)
    {
        $this->remix_count = $remixCount;
    
        return $this;
    }

    /**
     * Get remix_count
     *
     * @return integer 
     */
    public function getRemixCount()
    {
        return $this->remix_count;
    }

    /**
     * Set remix_of
     *
     * @param \Catrobat\AppBundle\Entity\Program $remixOf
     * @return Program
     */
    public function setRemixOf(\Catrobat\AppBundle\Entity\Program $remixOf = null)
    {
        $this->remix_of = $remixOf;
    
        return $this;
    }

    /**
     * Get remix_of
     *
     * @return \Catrobat\AppBundle\Entity\Program 
     */
    public function getRemixOf()
    {
        return $this->remix_of;
    }

    /**
     * Set if program is approved
     *
     * @param boolean
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    /**
     * Get if program is approved
     *
     * @return boolean
     */
    public function getApproved()
    {
        return $this->approved;
    }
    
    public function isVisible()
    {
      return $this->visible;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
}