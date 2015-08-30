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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $avatar;

    /**
     * @ORM\Column(type="string", length=5, nullable=false, options={"default":""})
     */
    protected $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $additional_email;

    /**
     * @ORM\OneToMany(targetEntity="Program", mappedBy="user", fetch="EXTRA_LAZY")
     */
    protected $programs;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    protected $gplus_access_token;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    protected $gplus_id_token;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    protected $gplus_refresh_token;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    protected $facebook_access_token;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    protected $oauth_password;

    /**
     * @param mixed $facebook_access_token
     */
    public function setFacebookAccessToken($facebook_access_token)
    {
        $this->facebook_access_token = $facebook_access_token;
    }

    /**
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * @param mixed $gplus_access_token
     */
    public function setGplusAccessToken($gplus_access_token)
    {
        $this->gplus_access_token = $gplus_access_token;
    }

    /**
     * @return mixed
     */
    public function getGplusAccessToken()
    {
        return $this->gplus_access_token;
    }

    /**
     * @param mixed $gplus_id_token
     */
    public function setGplusIdToken($gplus_id_token)
    {
        $this->gplus_id_token = $gplus_id_token;
    }

    /**
     * @return mixed
     */
    public function getGplusIdToken()
    {
        return $this->gplus_id_token;
    }

    /**
     * @param mixed $gplus_refresh_token
     */
    public function setGplusRefreshToken($gplus_refresh_token)
    {
        $this->gplus_refresh_token = $gplus_refresh_token;
    }

    /**
     * @return mixed
     */
    public function getGplusRefreshToken()
    {
        return $this->gplus_refresh_token;
    }

    public function __construct()
    {
        parent::__construct();
        $this->country = '';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add programs.
     *
     * @param \Catrobat\AppBundle\Entity\Program $programs
     *
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
     * Get programs.
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

  /**
   * @param mixed $additional_email
   */
  public function setAdditionalEmail($additional_email)
  {
      $this->additional_email = $additional_email;
  }

  /**
   * @return mixed
   */
  public function getAdditionalEmail()
  {
      return $this->additional_email;
  }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

        /**
         * @param mixed $oauth_password
         */
        public function setOauthPassword($oauth_password)
    {
        $this->oauth_password = $oauth_password;
    }

        /**
         * @return mixed
         */
        public function getOauthPassword()
    {
        return $this->oauth_password;
    }
}

