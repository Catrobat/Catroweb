<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user", indexes={@Index(columns={"username"}, flags={"fulltext"})})
 */
class User extends BaseUser implements LdapUserInterface
{
  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="guid")
   * @ORM\GeneratedValue(strategy="CUSTOM")
   * @ORM\CustomIdGenerator(class="App\Utils\MyUuidGenerator")
   *
   * @var string
   */
  protected $id;

  /**
   * @deprecated API v1
   *
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $upload_token = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected ?string $avatar = null;

  /**
   * @ORM\Column(type="string", length=5, nullable=false, options={"default": ""})
   */
  protected string $country = '';

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $additional_email = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $dn = null;

  /**
   * Programs owned by this user.
   * When this user is deleted, all the programs owned by him should be deleted too.
   *
   * @ORM\OneToMany(
   *     targetEntity="Program",
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $programs;

  /**
   * Notifications which are available for this user (shown upon login).
   * When this user is deleted, all notifications for him should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity="CatroNotification",
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $notifications;

  /**
   * Comments written by this user.
   * When this user is deleted, all the comments he wrote should be deleted too.
   *
   * @ORM\OneToMany(
   *     targetEntity="UserComment",
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $comments;

  /**
   * FollowNotifications mentioning this user as a follower.
   * When this user will be deleted, all FollowNotifications mentioning
   * him as a follower, should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity="App\Entity\FollowNotification",
   *     mappedBy="follower",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $follow_notification_mentions;

  /**
   * LikeNotifications mentioning this user as giving a like to another user.
   * When this user will be deleted, all LikeNotifications mentioning
   * him as a user giving a like to another user, should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity="App\Entity\LikeNotification",
   *     mappedBy="like_from",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $like_notification_mentions;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="following")
   */
  protected Collection $followers;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\User", inversedBy="followers")
   */
  protected Collection $following;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramLike",
   *     mappedBy="user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserLikeSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserLikeSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $reverse_relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserRemixSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserRemixSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $reverse_relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $gplus_access_token = null;

  /**
   * @ORM\Column(type="string", length=5000, nullable=true)
   */
  protected ?string $gplus_id_token = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $gplus_refresh_token = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $limited = false;

  /**
   * @ORM\OneToMany(targetEntity="App\Entity\ProgramInappropriateReport", mappedBy="reportingUser", fetch="EXTRA_LAZY")
   */
  protected Collection $program_inappropriate_reports;

  public function __construct()
  {
    parent::__construct();
    $this->programs = new ArrayCollection();
    $this->notifications = new ArrayCollection();
    $this->comments = new ArrayCollection();
    $this->follow_notification_mentions = new ArrayCollection();
    $this->like_notification_mentions = new ArrayCollection();
    $this->followers = new ArrayCollection();
    $this->following = new ArrayCollection();
    $this->likes = new ArrayCollection();
    $this->relations_of_similar_users_based_on_likes = new ArrayCollection();
    $this->reverse_relations_of_similar_users_based_on_likes = new ArrayCollection();
    $this->relations_of_similar_users_based_on_remixes = new ArrayCollection();
    $this->reverse_relations_of_similar_users_based_on_remixes = new ArrayCollection();
    $this->program_inappropriate_reports = new ArrayCollection();
  }

  public function setGplusAccessToken(?string $gplus_access_token): void
  {
    $this->gplus_access_token = $gplus_access_token;
  }

  public function getGplusAccessToken(): ?string
  {
    return $this->gplus_access_token;
  }

  public function setGplusIdToken(?string $gplus_id_token): void
  {
    $this->gplus_id_token = $gplus_id_token;
  }

  public function getGplusIdToken(): ?string
  {
    return $this->gplus_id_token;
  }

  public function setGplusRefreshToken(?string $gplus_refresh_token): void
  {
    $this->gplus_refresh_token = $gplus_refresh_token;
  }

  public function getGplusRefreshToken(): ?string
  {
    return $this->gplus_refresh_token;
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function addProgram(Program $program): User
  {
    $this->programs[] = $program;

    return $this;
  }

  public function removeProgram(Program $program): void
  {
    $this->programs->removeElement($program);
  }

  public function getPrograms(): Collection
  {
    return $this->programs;
  }

  public function getUploadToken(): ?string
  {
    return $this->upload_token;
  }

  public function setUploadToken(?string $upload_token): void
  {
    $this->upload_token = $upload_token;
  }

  public function getCountry(): string
  {
    return $this->country;
  }

  public function setCountry(string $country): User
  {
    $this->country = $country;

    return $this;
  }

  public function setId(string $id): void
  {
    $this->id = $id;
  }

  public function setAdditionalEmail(?string $additional_email): void
  {
    $this->additional_email = $additional_email;
  }

  public function getAdditionalEmail(): ?string
  {
    return $this->additional_email;
  }

  public function getAvatar(): ?string
  {
    return $this->avatar;
  }

  public function setAvatar(?string $avatar): User
  {
    $this->avatar = $avatar;

    return $this;
  }

  /**
   * Set Ldap Distinguished Name.
   */
  public function setDn(string $dn): void
  {
    $this->dn = strtolower($dn);
  }

  /**
   * Get Ldap Distinguished Name.
   */
  public function getDn(): ?string
  {
    return null !== $this->dn ? $this->dn : '';
  }

  public function isLimited(): bool
  {
    return $this->limited;
  }

  public function setLimited(bool $limited): void
  {
    $this->limited = $limited;
  }

  public function getLikes(): Collection
  {
    return $this->likes;
  }

  public function setLikes(Collection $likes): void
  {
    $this->likes = $likes;
  }

  public function getFollowers(): Collection
  {
    return $this->followers;
  }

  public function addFollower(User $follower): void
  {
    $this->followers->add($follower);
  }

  public function removeFollower(User $follower): void
  {
    $this->followers->removeElement($follower);
  }

  public function hasFollower(User $user): bool
  {
    return $this->followers->contains($user);
  }

  public function getFollowing(): Collection
  {
    return $this->following;
  }

  public function addFollowing(User $follower): void
  {
    $this->following->add($follower);
  }

  public function removeFollowing(User $follower): void
  {
    $this->following->removeElement($follower);
  }

  public function isFollowing(User $user): bool
  {
    return $this->following->contains($user);
  }

  /**
   * Returns the FollowNotifications mentioning this user as a follower.
   */
  public function getFollowNotificationMentions(): Collection
  {
    return $this->follow_notification_mentions;
  }

  /**
   * Sets the FollowNotifications mentioning this user as a follower.
   */
  public function setFollowNotificationMentions(Collection $follow_notification_mentions): void
  {
    $this->follow_notification_mentions = $follow_notification_mentions;
  }

  public function getProgramInappropriateReports(): Collection
  {
    return $this->program_inappropriate_reports;
  }

  public function getProgramInappropriateReportsCount(): int
  {
    $programs_collection = $this->getPrograms();
    $programs = $programs_collection->getValues();
    $count = 0;
    foreach ($programs as $program)
    {
      $count += $program->getReportsCount();
    }

    return $count;
  }

  public function getComments(): Collection
  {
    return $this->comments;
  }

  public function getReportedCommentsCount(): int
  {
    /** @var ArrayCollection $comments_collection */
    $comments_collection = $this->getComments();
    $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('isReported', 1));

    return $comments_collection->matching($criteria)->count();
  }
}
