<?php


namespace Catrobat\Sonata\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user_user")
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
     * @ORM\OneToMany(targetEntity="\Catrobat\CoreBundle\Entity\Project", mappedBy="user")
     */
    protected $projects;

    public function __construct()
    {
        parent::__construct();
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
     * Add projects
     *
     * @param \Catrobat\CoreBundle\Entity\Project $projects
     * @return User
     */
    public function addProject(\Catrobat\CoreBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Catrobat\CoreBundle\Entity\Project $projects
     */
    public function removeProject(\Catrobat\CoreBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }


    /**
     * get token
     *
     * @return String
     */

    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param String
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

}