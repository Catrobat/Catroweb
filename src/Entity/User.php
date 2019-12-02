<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\GuidType;
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
   * @ORM\Column(name="id", type="guid")
   * @ORM\GeneratedValue(strategy="UUID")
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
   * @ORM\OneToMany(targetEntity="Program", mappedBy="user", fetch="EXTRA_LAZY", cascade={"remove"})
   */
  protected $programs;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="following")
   */
  protected $followers;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\User", inversedBy="followers")
   */
  protected $following;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramLike",
   *     mappedBy="user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var Collection|ProgramLike[]
   */
  protected $likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserLikeSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var Collection|UserLikeSimilarityRelation[]
   */
  protected $relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserLikeSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var Collection|UserLikeSimilarityRelation[]
   */
  protected $reverse_relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserRemixSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var Collection|UserRemixSimilarityRelation[]
   */
  protected $relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserRemixSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var Collection|UserRemixSimilarityRelation[]
   */
  protected $reverse_relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected $gplus_access_token;

  /**
   * @ORM\Column(type="string", length=5000, nullable=true)
   */
  protected $gplus_id_token;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected $gplus_refresh_token;

  /**
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $limited = false;

  /**
   * User constructor.
   */
  public function __construct()
  {
    parent::__construct();
    $this->programs = new ArrayCollection();
    $this->followers = new ArrayCollection();
    $this->following = new ArrayCollection();
    $this->country = '';
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
   * @return GuidType
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Add programs.
   *
   * @param Program $programs
   *
   * @return User
   */
  public function addProgram(Program $programs)
  {
    $this->programs[] = $programs;

    return $this;
  }

  /**
   * Remove programs
   *
   * @param Program $programs
   */
  public function removeProgram(Program $programs)
  {
    $this->programs->removeElement($programs);
  }

  /**
   * Get programs.
   *
   * @return Collection
   */
  public function getPrograms()
  {
    return $this->programs;
  }

  /**
   * @return mixed
   */
  public function getUploadToken()
  {
    return $this->upload_token;
  }

  /**
   * @param $upload_token
   */
  public function setUploadToken($upload_token)
  {
    $this->upload_token = $upload_token;
  }

  /**
   * @return string
   */
  public function getCountry()
  {
    return $this->country;
  }

  /**
   * @param $country
   *
   * @return $this
   */
  public function setCountry($country)
  {
    $this->country = $country;

    return $this;
  }

  /**
   * @param $id
   */
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

  /**
   * @return mixed
   */
  public function getAvatar()
  {
    return $this->avatar;
  }

  /**
   * @param $avatar
   *
   * @return $this
   */
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
  public function getDn(): string
  {
    return $this->dn !== null ? $this->dn : '';
  }

  /**
   * @return bool
   */
  public function isLimited()
  {
    return $this->limited;
  }

  /**
   * @param $limited
   */
  public function setLimited($limited)
  {
    $this->limited = $limited;
  }

  /**
   * @return ProgramLike[]|Collection
   */
  public function getLikes()
  {
    return ($this->likes != null) ? $this->likes : new ArrayCollection();
  }

  /**
   * @param ProgramLike[]|Collection $likes
   */
  public function setLikes($likes)
  {
    $this->likes = $likes;
  }

  /**
   * @return mixed
   */
  public function getFollowers()
  {
    return $this->followers;
  }

  /**
   * @param $follower
   */
  public function addFollower($follower)
  {
    $this->followers->add($follower);
  }

  /**
   * @param $follower
   */
  public function removeFollower($follower)
  {
    $this->followers->removeElement($follower);
  }

  /**
   * @param $user
   *
   * @return bool
   */
  public function hasFollower($user)
  {
    return $this->followers->contains($user);
  }

  /**
   * @return mixed
   */
  public function getFollowing()
  {
    return $this->following;
  }

  /**
   * @param $follower
   */
  public function addFollowing($follower)
  {
    $this->following->add($follower);
  }

  /**
   * @param $follower
   */
  public function removeFollowing($follower)
  {
    $this->following->removeElement($follower);
  }

  /**
   * @param $user
   *
   * @return bool
   */
  public function isFollowing($user)
  {
    return $this->following->contains($user);
  }
}
