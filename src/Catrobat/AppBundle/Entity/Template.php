<?php

namespace Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Listeners\View\TemplateListSerializer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="template")
 */
class Template
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
     * @ORM\Column(type="boolean")
     */
    protected $active = true;

     protected $thumbnail;

     protected $landscape_program_file;

     protected $portrait_program_file;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getThumbnail()
    {
      return $this->thumbnail;
    }

    /**
     * @param mixed $thumbnail
     */
    public function setThumbnail($thumbnail)
    {
      $this->thumbnail = $thumbnail;
    }

    /**
     * @return mixed
     */
    public function getLandscapeProgramFile()
    {
      return $this->landscape_program_file;
    }

    /**
     * @param mixed $landscape_program_file
     */
    public function setLandscapeProgramFile($landscape_program_file)
    {
      $this->landscape_program_file = $landscape_program_file;
    }

    /**
     * @return mixed
     */
    public function getPortraitProgramFile()
    {
      return $this->portrait_program_file;
    }

    /**
     * @param mixed $portrait_program_file
     */
    public function setPortraitProgramFile($portrait_program_file)
    {
      $this->portrait_program_file = $portrait_program_file;
    }
}
