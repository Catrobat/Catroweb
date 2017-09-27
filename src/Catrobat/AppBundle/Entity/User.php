<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements LdapUserInterface
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
   * @ORM\Column(type="string", nullable=true)
   */
  protected $dn;

  /**
   * @ORM\OneToMany(targetEntity="Program", mappedBy="user", fetch="EXTRA_LAZY")
   */
  protected $programs;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ProgramLike",
   *     mappedBy="user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ProgramLike[]
   */
  protected $likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\UserLikeSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|UserLikeSimilarityRelation[]
   */
  protected $relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\UserLikeSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|UserLikeSimilarityRelation[]
   */
  protected $reverse_relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\UserRemixSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|UserRemixSimilarityRelation[]
   */
  protected $relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\UserRemixSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|UserRemixSimilarityRelation[]
   */
  protected $reverse_relations_of_similar_users_based_on_remixes;

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
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $limited = false;

  /**
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $nolb_user = false;

  /**
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $warned = false;

  /**
   * @ORM\Column(type="smallint", options={"default":0})
   */
  protected $times_banned = 0;

  /**
   * @ORM\Column(type="datetime", nullable=true, options={"default":null})
   */
  protected $banned_until = null;

  public function __construct()
  {
    parent::__construct();
    $this->programs = new \Doctrine\Common\Collections\ArrayCollection();
    $this->country = '';
  }

  /**
   *
   * @param mixed $facebook_access_token
   */
  public function setFacebookAccessToken($facebook_access_token)
  {
    $this->facebook_access_token = $facebook_access_token;
  }

  /**
   *
   * @return mixed
   */
  public function getFacebookAccessToken()
  {
    return $this->facebook_access_token;
  }

  /**
   *
   * @param mixed $gplus_access_token
   */
  public function setGplusAccessToken($gplus_access_token)
  {
    $this->gplus_access_token = $gplus_access_token;
  }

  /**
   *
   * @return mixed
   */
  public function getGplusAccessToken()
  {
    return $this->gplus_access_token;
  }

  /**
   *
   * @param mixed $gplus_id_token
   */
  public function setGplusIdToken($gplus_id_token)
  {
    $this->gplus_id_token = $gplus_id_token;
  }

  /**
   *
   * @return mixed
   */
  public function getGplusIdToken()
  {
    return $this->gplus_id_token;
  }

  /**
   *
   * @param mixed $gplus_refresh_token
   */
  public function setGplusRefreshToken($gplus_refresh_token)
  {
    $this->gplus_refresh_token = $gplus_refresh_token;
  }

  /**
   *
   * @return mixed
   */
  public function getGplusRefreshToken()
  {
    return $this->gplus_refresh_token;
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
   *
   * @param mixed $additional_email
   */
  public function setAdditionalEmail($additional_email)
  {
    $this->additional_email = $additional_email;
  }

  /**
   *
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
   * Set Ldap Distinguished Name.
   *
   * @param string $dn
   *            Distinguished Name
   */
  public function setDn($dn)
  {
    $this->dn = strtolower($dn);
  }

  /**
   * Get Ldap Distinguished Name.
   *
   * @return string Distinguished Name
   */
  public function getDn()
  {
    return $this->dn;
  }

  public function isLimited()
  {
    return $this->limited;
  }

  public function setLimited($limited)
  {
    $this->limited = $limited;
  }

  /**
   * @return mixed
   */
  public function getNolbUser()
  {
    return $this->nolb_user;
  }

  /**
   * @param mixed $nolb_user
   */
  public function setNolbUser($nolb_user)
  {
    $this->nolb_user = $nolb_user;
  }

  /**
   * @return ProgramLike[]|\Doctrine\Common\Collections\Collection
   */
  public function getLikes()
  {
    return ($this->likes != null) ? $this->likes : new ArrayCollection();
  }

  /**
   * @param ProgramLike[]|\Doctrine\Common\Collections\Collection $likes
   */
  public function setLikes($likes)
  {
    $this->likes = $likes;
  }

  /**
   * @return mixed
   */
  public function getBannedUntil()
  {
    return $this->banned_until;
  }

  /**
   * @param mixed $banned_until
   */
  public function setBannedUntil($banned_until)
  {
    $this->banned_until = $banned_until;
  }

  /**
   * @return mixed
   */
  public function getTimesBanned()
  {
    return $this->times_banned;
  }

  /**
   * @param mixed $times_banned
   */
  public function setTimesBanned($times_banned)
  {
    $this->times_banned = $times_banned;
  }

  public function getLocked()
  {
    return $this->locked;
  }

  /**
   * Bans the user
   * First time: 24h
   * Second time: 7d
   * Third time: permanent
   * @return String $admin_message contains the text for the flash message
   */
  public function ban()
  {
    $this->times_banned++;
    $ban_duration = 0;
    switch ($this->times_banned)
    {
      case 1:
        $time_to_ban = '+1 day';
        $ban_duration = 1;
        break;
      case 2:
        $time_to_ban = '+7 days';
        $ban_duration = 7;
        break;
      case 3:
        $time_to_ban = '+90 years';
        $ban_duration = 99;
        break;
      default:
        // should never happen
        $time_to_ban = '+7 days';
        $ban_duration = 100;
        break;
    }
    $banned_until = new \DateTime($time_to_ban);
    $this->banned_until = $banned_until;
    $this->setLocked(true);

    return $ban_duration;
  }

  public function isWarned()
  {
    return $this->warned;
  }

  /**
   * @param mixed $warned
   */
  public function setWarned($warned)
  {
    $this->warned = $warned;
  }
}
