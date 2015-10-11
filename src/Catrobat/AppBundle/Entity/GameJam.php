<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GameJam
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
     * @ORM\Column(type="string", length=300)
     */
    protected $form_url;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $start;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $end;

    /**
     * @ORM\OneToMany(targetEntity="Program", mappedBy="gamejam", fetch="EXTRA_LAZY")
     */
    protected $programs;
    
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
     *
     * @return GameJam
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
     * Set formUrl
     *
     * @param string $formUrl
     *
     * @return GameJam
     */
    public function setFormUrl($formUrl)
    {
        $this->form_url = $formUrl;
        
        return $this;
    }

    /**
     * Get formUrl
     *
     * @return string
     */
    public function getFormUrl()
    {
        return $this->form_url;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     *
     * @return GameJam
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;
        
        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     *
     * @return GameJam
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;
        
        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->programs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add program
     *
     * @param \Catrobat\AppBundle\Entity\Program $program
     *
     * @return GameJam
     */
    public function addProgram(\Catrobat\AppBundle\Entity\Program $program)
    {
        $this->programs[] = $program;

        return $this;
    }

    /**
     * Remove program
     *
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    public function removeProgram(\Catrobat\AppBundle\Entity\Program $program)
    {
        $this->programs->removeElement($program);
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
}
