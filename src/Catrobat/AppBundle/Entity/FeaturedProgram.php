<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="featured")
 */
class FeaturedProgram
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $image;
    
    /**
     * @ORM\OneToOne(targetEntity="Program",fetch="EAGER")
     * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
     **/
    private $program;

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
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
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
}