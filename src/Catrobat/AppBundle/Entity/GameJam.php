<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\GameJamRepository")
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
     * @ORM\Column(type="string", length=300, nullable=true)
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
     * @ORM\ManyToMany(targetEntity="Program")
     * @ORM\JoinTable(name="gamejams_sampleprograms",
     *      joinColumns={@ORM\JoinColumn(name="gamejam_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="program_id", referencedColumnName="id")}
     *      )
     **/
    private $sample_programs;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $hashtag;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $flavor;

    public function __construct()
    {
        $this->programs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sample_programs = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Add sampleProgram
     *
     * @param \Catrobat\AppBundle\Entity\Program $sampleProgram
     *
     * @return GameJam
     */
    public function addSampleProgram(\Catrobat\AppBundle\Entity\Program $sampleProgram)
    {
        $this->sample_programs[] = $sampleProgram;

        return $this;
    }

    /**
     * Remove sampleProgram
     *
     * @param \Catrobat\AppBundle\Entity\Program $sampleProgram
     */
    public function removeSampleProgram(\Catrobat\AppBundle\Entity\Program $sampleProgram)
    {
        $this->sample_programs->removeElement($sampleProgram);
    }

    /**
     * Get samplePrograms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSamplePrograms()
    {
        return $this->sample_programs;
    }
    
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getHashtag()
    {
        return $this->hashtag;
    }

    /**
     * @param mixed $hashtag
     */
    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
    }

    /**
     * @return mixed
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param mixed $flavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;
    }


}
