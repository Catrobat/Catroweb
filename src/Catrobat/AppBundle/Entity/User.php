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
     * @ORM\Column(type="string", length=5, nullable=false, options={"default":""})
     */
    protected $country;

    /**
     * @ORM\OneToMany(targetEntity="Program", mappedBy="user")
     */
    protected $programs;

    public function __construct()
    {
        parent::__construct();
        $this->country = "";
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
     *
     * @param mixed $upload_notification            
     */
    public function setUploadNotification($upload_notification)
    {
        $this->upload_notification = $upload_notification;
    }

    /**
     *
     * @return mixed
     */
    public function getUploadNotification()
    {
        return $this->upload_notification;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }
    
    public function setId($id)
    {
        $this->id = $id; 
    }
}