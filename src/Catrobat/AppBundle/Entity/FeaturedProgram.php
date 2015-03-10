<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\FeaturedRepository")
 * @ORM\EntityListeners({"Catrobat\AppBundle\Listeners\Entity\FeaturedProgramImageListener"})
 * @ORM\Table(name="featured")
 */
class FeaturedProgram
{
    public $file;
    public $removed_id;
    public $old_image_type;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $imagetype;
    
    /**
     * @ORM\ManyToOne(targetEntity="Program", fetch="EAGER")
     **/
    private $program;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;
    
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
     * Set image
     *
     * @param string $image
     * @return FeaturedProgram
     */
    public function setImageType($image)
    {
        $this->imagetype = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImageType()
    {
        return $this->imagetype;
    }

    /**
     * Set program
     *
     * @param \Catrobat\AppBundle\Entity\Program $program
     * @return FeaturedProgram
     */
    public function setProgram(\Catrobat\AppBundle\Entity\Program $program = null)
    {
        $this->program = $program;
    
        return $this;
    }

    /**
     * Get program
     *
     * @return \Catrobat\AppBundle\Entity\Program 
     */
    public function getProgram()
    {
        return $this->program;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
 
 
}