<?php

namespace App\DB\Entity\User;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\RecommenderSystem\UserLikeSimilarityRelation;
use App\DB\Entity\User\RecommenderSystem\UserRemixSimilarityRelation;
use App\DB\EntityRepository\User\UserRepository;
use App\DB\Generator\MyUuidGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @ORM\Table(
 *     name="fos_user",
 *     indexes={
 *
 *         @ORM\Index(name="upload_token_idx", columns={"upload_token"}),
 *         @ORM\Index(name="confirmation_token_isx", columns={"confirmation_token"}),
 *         @ORM\Index(name="username_canonical_idx", columns={"username_canonical"}),
 *         @ORM\Index(name="email_canonical_idx", columns={"email_canonical"}),
 *         @ORM\Index(name="scratch_user_id_idx", columns={"scratch_user_id"}),
 *         @ORM\Index(name="google_id_idx", columns={"google_id"}),
 *         @ORM\Index(name="facebook_id_idx", columns={"google_id"}),
 *         @ORM\Index(name="apple_id_idx", columns={"google_id"})
 *     }
 * )
 */
class User extends BaseUser
{
  public static string $SCRATCH_PREFIX = 'Scratch:';
  /**
   * @ORM\Id
   *
   * @ORM\Column(name="id", type="guid")
   *
   * @ORM\GeneratedValue(strategy="CUSTOM")
   *
   * @ORM\CustomIdGenerator(class=MyUuidGenerator::class)
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
   * Programs owned by this user.
   * When this user is deleted, all the programs owned by him should be deleted too.
   *
   * @ORM\OneToMany(
   *     targetEntity=Program::class,
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $programs;

  /**
   * Requests to change the password issued by this user.
   * When this user is deleted, all the reset-password requests issued by him should be deleted too.
   *
   * @ORM\OneToMany(
   *     targetEntity=ResetPasswordRequest::class,
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $reset_password_requests;

  /**
   * Notifications which are available for this user (shown upon login).
   * When this user is deleted, all notifications for him should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity=CatroNotification::class,
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
   *     targetEntity=UserComment::class,
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
   *     targetEntity=FollowNotification::class,
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
   *     targetEntity=LikeNotification::class,
   *     mappedBy="like_from",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $like_notification_mentions;

  /**
   * @ORM\ManyToMany(targetEntity=User::class, mappedBy="following")
   */
  protected Collection $followers;

  /**
   * @ORM\ManyToMany(targetEntity=User::class, inversedBy="followers")
   */
  protected Collection $following;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ProgramLike::class,
   *     mappedBy="user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity=UserLikeSimilarityRelation::class,
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity=UserLikeSimilarityRelation::class,
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $reverse_relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity=UserRemixSimilarityRelation::class,
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\OneToMany(
   *     targetEntity=UserRemixSimilarityRelation::class,
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $reverse_relations_of_similar_users_based_on_remixes;

  /**
   * @deprecated
   *
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $gplus_access_token = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $google_id = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $facebook_id = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $google_access_token = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $facebook_access_token = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $apple_id = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $apple_access_token = null;
  /**
   * @deprecated
   *
   * @ORM\Column(type="string", length=5000, nullable=true)
   */
  protected ?string $gplus_id_token = null;

  /**
   * @deprecated
   *
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $gplus_refresh_token = null;

  /**
   * @ORM\Column(type="integer", nullable=true, unique=true)
   */
  protected ?int $scratch_user_id = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $oauth_password_created = false;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $oauth_user = false;

  /**
   * @ORM\Column(type="boolean", options={"default": true})
   */
  protected bool $verified = true;

  /**
   * @ORM\OneToMany(targetEntity=ProgramInappropriateReport::class, mappedBy="reporting_user", fetch="EXTRA_LAZY")
   */
  protected Collection $reports_triggered_by_this_user;

  /**
   * @ORM\OneToMany(targetEntity=ProgramInappropriateReport::class, mappedBy="reported_user", fetch="EXTRA_LAZY")
   */
  protected Collection $reports_of_this_user;

  /**
   * @ORM\Column(type="text", length=65535, nullable=true)
   */
  protected ?string $about = null;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  protected ?string $currentlyWorkingOn = null;

  /**
   * @ORM\Column(type="integer", nullable=true, unique=true)
   */
  protected ?int $ranking_score = null;

  public function __construct()
  {
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
    $this->reports_triggered_by_this_user = new ArrayCollection();
    $this->reports_of_this_user = new ArrayCollection();
  }

  public function getAppleId(): ?string
  {
    return $this->apple_id;
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

  public function setId(string $id): void
  {
    $this->id = $id;
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

  public function getReportsTriggeredByThisUser(): Collection
  {
    return $this->reports_triggered_by_this_user;
  }

  public function getReportsOfThisUser(): Collection
  {
    return $this->reports_of_this_user;
  }

  public function getReportsOfThisUserCount(): int
  {
    return count($this->getReportsOfThisUser());
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

  public function setGoogleId(?string $google_id): void
  {
    $this->google_id = $google_id;
  }

  public function getGoogleId(): ?string
  {
    return $this->google_id;
  }

  public function setGoogleAccessToken(?string $google_access_token): void
  {
    $this->google_access_token = $google_access_token;
  }

  public function getGoogleAccessToken(): ?string
  {
    return $this->google_access_token;
  }

  public function changeCreatedAt(\DateTime $createdAt): void
  {
    $this->createdAt = $createdAt;
  }

  public function getScratchUserId(): ?int
  {
    return $this->scratch_user_id;
  }

  public function isScratchUser(): bool
  {
    return null !== $this->scratch_user_id;
  }

  public function setScratchUsername(string $username): void
  {
    $this->setUsername(self::$SCRATCH_PREFIX.$username);
  }

  public function getScratchUsername(): string
  {
    return preg_replace('/^'.self::$SCRATCH_PREFIX.'/', '', $this->getUsername());
  }

  public function setScratchUserId(?int $scratch_user_id): void
  {
    $this->scratch_user_id = $scratch_user_id;
  }

  public function isOauthPasswordCreated(): bool
  {
    return $this->oauth_password_created;
  }

  public function setOauthPasswordCreated(bool $oauth_password_created): void
  {
    $this->oauth_password_created = $oauth_password_created;
  }

  public function isOauthUser(): bool
  {
    return $this->oauth_user;
  }

  public function setOauthUser(bool $oauth_user): void
  {
    $this->oauth_user = $oauth_user;
  }

  public function getFacebookId(): ?string
  {
    return $this->facebook_id;
  }

  public function setFacebookId(?string $facebook_id): void
  {
    $this->facebook_id = $facebook_id;
  }

  public function getFacebookAccessToken(): ?string
  {
    return $this->facebook_access_token;
  }

  public function setFacebookAccessToken(?string $facebook_access_token): void
  {
    $this->facebook_access_token = $facebook_access_token;
  }

  public function setAppleId(?string $apple_id): void
  {
    $this->apple_id = $apple_id;
  }

  public function getAppleAccessToken(): ?string
  {
    return $this->apple_access_token;
  }

  public function setAppleAccessToken(?string $apple_access_token): void
  {
    $this->apple_access_token = $apple_access_token;
  }

  public function setVerified(bool $verified): self
  {
    $this->verified = $verified;

    return $this;
  }

  public function isVerified(): bool
  {
    // All user are automatically verified in non production environments
    $app_env = $_ENV['APP_ENV'];

    return 'prod' !== $app_env || $this->verified;
  }

  public function getAbout(): ?string
  {
    return $this->about;
  }

  public function setAbout(?string $about): void
  {
    $this->about = $about;
  }

  public function getCurrentlyWorkingOn(): ?string
  {
    return $this->currentlyWorkingOn;
  }

  public function setCurrentlyWorkingOn(?string $currentlyWorkingOn): void
  {
    $this->currentlyWorkingOn = $currentlyWorkingOn;
  }

  public function getRankingScore(): ?int
  {
    return $this->ranking_score;
  }

  public function setRankingScore(?int $ranking_score): void
  {
    $this->ranking_score = $ranking_score;
  }
}
