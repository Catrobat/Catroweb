<?php
namespace Catrobat\CatrowebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="featured")
 */
class FeaturedProject
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
     * @ORM\OneToOne(targetEntity="Project",fetch="EAGER")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     **/
    private $project;

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
     * @return FeaturedProject
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
     * Set project
     *
     * @param \Catrobat\CatrowebBundle\Entity\Project $project
     * @return FeaturedProject
     */
    public function setProject(\Catrobat\CatrowebBundle\Entity\Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return \Catrobat\CatrowebBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }
}