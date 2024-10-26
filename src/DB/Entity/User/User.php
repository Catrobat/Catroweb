<?php

declare(strict_types=1);

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
use App\Utils\CanonicalFieldsUpdater;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'fos_user')]
#[ORM\Index(name: 'upload_token_idx', columns: ['upload_token'])]
#[ORM\Index(name: 'confirmation_token_isx', columns: ['confirmation_token'])]
#[ORM\Index(name: 'username_canonical_idx', columns: ['username_canonical'])]
#[ORM\Index(name: 'email_canonical_idx', columns: ['email_canonical'])]
#[ORM\Index(name: 'scratch_user_id_idx', columns: ['scratch_user_id'])]
#[ORM\Index(name: 'google_id_idx', columns: ['google_id'])]
#[ORM\Index(name: 'facebook_id_idx', columns: ['google_id'])]
#[ORM\Index(name: 'apple_id_idx', columns: ['google_id'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
  public const string ROLE_DEFAULT = 'ROLE_USER';
  public const string ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

  public static string $SCRATCH_PREFIX = 'Scratch:';

  #[ORM\Id]
  #[ORM\Column(name: 'id', type: Types::GUID)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  protected string $id;

  /**
   * @deprecated API v1
   */
  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $upload_token = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  protected ?string $avatar = null;

  /**
   * Programs owned by this user.
   * When this user is deleted, all the programs owned by him should be deleted too.
   *
   * @var Collection<int, Program>
   */
  #[ORM\OneToMany(targetEntity: Program::class, mappedBy: 'user', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $programs;

  /**
   * Requests to change the password issued by this user.
   * When this user is deleted, all the reset-password requests issued by him should be deleted too.
   *
   * @var Collection<int, ResetPasswordRequest>
   */
  #[ORM\OneToMany(targetEntity: ResetPasswordRequest::class, mappedBy: 'user', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $reset_password_requests;

  /**
   * Notifications which are available for this user (shown upon login).
   * When this user is deleted, all notifications for him should also be deleted.
   *
   * @var Collection<int, CatroNotification>
   */
  #[ORM\OneToMany(targetEntity: CatroNotification::class, mappedBy: 'user', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $notifications;

  /**
   * Comments written by this user.
   * When this user is deleted, all the comments he wrote should be deleted too.
   *
   * @var Collection<int, UserComment>
   */
  #[ORM\OneToMany(targetEntity: UserComment::class, mappedBy: 'user', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $comments;

  /**
   * FollowNotifications mentioning this user as a follower.
   * When this user will be deleted, all FollowNotifications mentioning
   * him as a follower, should also be deleted.
   *
   * @var Collection<int, FollowNotification>
   */
  #[ORM\OneToMany(targetEntity: FollowNotification::class, mappedBy: 'follower', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $follow_notification_mentions;

  /**
   * LikeNotifications mentioning this user as giving a like to another user.
   * When this user will be deleted, all LikeNotifications mentioning
   * him as a user giving a like to another user, should also be deleted.
   *
   * @var Collection<int, LikeNotification>
   */
  #[ORM\OneToMany(targetEntity: LikeNotification::class, mappedBy: 'like_from', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $like_notification_mentions;

  /**
   * @var Collection<int, User>
   */
  #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'following')]
  protected Collection $followers;

  /**
   * @var Collection<int, User>
   */
  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'followers')]
  protected Collection $following;

  /**
   * @var Collection<int, ProgramLike>
   */
  #[ORM\OneToMany(targetEntity: ProgramLike::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $likes;

  /**
   * @var Collection<int, UserLikeSimilarityRelation>
   */
  #[ORM\OneToMany(targetEntity: UserLikeSimilarityRelation::class, mappedBy: 'first_user', cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $relations_of_similar_users_based_on_likes;

  /**
   * @var Collection<int, UserLikeSimilarityRelation>
   */
  #[ORM\OneToMany(targetEntity: UserLikeSimilarityRelation::class, mappedBy: 'second_user', cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $reverse_relations_of_similar_users_based_on_likes;

  /**
   * @var Collection<int, UserRemixSimilarityRelation>
   */
  #[ORM\OneToMany(targetEntity: UserRemixSimilarityRelation::class, mappedBy: 'first_user', cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $relations_of_similar_users_based_on_remixes;

  /**
   * @var Collection<int, UserRemixSimilarityRelation>
   */
  #[ORM\OneToMany(targetEntity: UserRemixSimilarityRelation::class, mappedBy: 'second_user', cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $reverse_relations_of_similar_users_based_on_remixes;

  /**
   * @deprecated
   */
  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $gplus_access_token = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $google_id = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $facebook_id = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $google_access_token = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $facebook_access_token = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $apple_id = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $apple_access_token = null;

  /**
   * @deprecated
   */
  #[ORM\Column(type: Types::STRING, length: 5000, nullable: true)]
  protected ?string $gplus_id_token = null;

  /**
   * @deprecated
   */
  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  protected ?string $gplus_refresh_token = null;

  #[ORM\Column(type: Types::INTEGER, unique: true, nullable: true)]
  protected ?int $scratch_user_id = null;

  #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
  protected bool $oauth_password_created = false;

  #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
  protected bool $oauth_user = false;

  #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
  protected bool $verified = false;

  /**
   * @var Collection<int, ProgramInappropriateReport>
   */
  #[ORM\OneToMany(targetEntity: ProgramInappropriateReport::class, mappedBy: 'reporting_user', fetch: 'EXTRA_LAZY')]
  protected Collection $reports_triggered_by_this_user;

  /**
   * @var Collection<int, ProgramInappropriateReport>
   */
  #[ORM\OneToMany(targetEntity: ProgramInappropriateReport::class, mappedBy: 'reported_user', fetch: 'EXTRA_LAZY')]
  protected Collection $reports_of_this_user;

  #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
  protected ?string $about = null;

  #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
  protected ?string $currently_working_on = null;

  #[ORM\Column(type: Types::INTEGER, nullable: true)]
  protected ?int $ranking_score = null;

  #[ORM\Column(type: 'string', length: 180, nullable: false)]
  protected ?string $username = null;

  #[ORM\Column(name: 'username_canonical', type: 'string', length: 180, unique: true, nullable: false)]
  protected ?string $usernameCanonical = null;

  #[ORM\Column(type: 'string', length: 180, nullable: false)]
  protected ?string $email = null;

  #[ORM\Column(name: 'email_canonical', type: 'string', length: 180, unique: true, nullable: false)]
  protected ?string $emailCanonical = null;

  #[ORM\Column(type: 'boolean')]
  protected bool $enabled = false;

  #[ORM\Column(type: 'string', nullable: true)]
  protected ?string $salt = null;

  #[ORM\Column(type: 'string', nullable: false)]
  protected ?string $password = null;

  protected ?string $plainPassword = null;

  #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
  protected ?\DateTimeInterface $lastLogin = null;

  #[ORM\Column(name: 'verification_requested_at', type: 'datetime', nullable: true)]
  protected ?\DateTimeInterface $verification_requested_at = null;

  #[ORM\Column(name: 'confirmation_token', type: 'string', length: 180, unique: true, nullable: true)]
  protected ?string $confirmationToken = null;

  #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
  protected ?\DateTimeInterface $password_requested_at = null;

  #[ORM\Column(name: 'created_at', type: 'datetime')]
  protected \DateTimeInterface $createdAt;

  #[ORM\Column(name: 'updated_at', type: 'datetime')]
  protected \DateTimeInterface $updatedAt;

  #[ORM\Column(name: 'roles', type: 'array')]
  protected array $roles = [];

  public function __construct()
  {
    $this->reset_password_requests = new ArrayCollection();
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
    return preg_replace('/^'.self::$SCRATCH_PREFIX.'/', '', (string) $this->getUsername());
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
    return $this->verified;
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
    return $this->currently_working_on;
  }

  public function setCurrentlyWorkingOn(?string $currently_working_on): void
  {
    $this->currently_working_on = $currently_working_on;
  }

  public function getRankingScore(): ?int
  {
    return $this->ranking_score;
  }

  public function setRankingScore(?int $ranking_score): void
  {
    $this->ranking_score = $ranking_score;
  }

  #[ORM\PrePersist]
  public function prePersist(): void
  {
    $this->createdAt = new \DateTime();
    $this->updatedAt = new \DateTime();
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void
  {
    $this->updatedAt = new \DateTime();
  }

  public function __toString(): string
  {
    return $this->getUserIdentifier();
  }

  public function __serialize(): array
  {
    return [
      $this->password,
      $this->salt,
      $this->usernameCanonical,
      $this->username,
      $this->enabled,
      $this->id,
      $this->email,
      $this->emailCanonical,
    ];
  }

  public function __unserialize(array $data): void
  {
    [
      $this->password,
      $this->salt,
      $this->usernameCanonical,
      $this->username,
      $this->enabled,
      $this->id,
      $this->email,
      $this->emailCanonical,
    ] = $data;
  }

  public function addRole(string $role): void
  {
    $role = strtoupper($role);

    if (self::ROLE_DEFAULT === $role) {
      return;
    }

    if (!\in_array($role, $this->roles, true)) {
      $this->roles[] = $role;
    }
  }

  public function eraseCredentials(): void
  {
    $this->plainPassword = null;
  }

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function getUserIdentifier(): string
  {
    return $this->getUsername() ?? '-';
  }

  public function getUsernameCanonical(): ?string
  {
    return $this->usernameCanonical;
  }

  public function getSalt(): ?string
  {
    return $this->salt;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function getEmailCanonical(): ?string
  {
    return $this->emailCanonical;
  }

  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function getPlainPassword(): ?string
  {
    return $this->plainPassword;
  }

  public function getLastLogin(): ?\DateTimeInterface
  {
    return $this->lastLogin;
  }

  public function getConfirmationToken(): ?string
  {
    return $this->confirmationToken;
  }

  public function getRoles(): array
  {
    $roles = $this->roles;

    // we need to make sure to have at least one role
    $roles[] = self::ROLE_DEFAULT;

    return array_values(array_unique($roles));
  }

  public function hasRole(string $role): bool
  {
    return \in_array(strtoupper($role), $this->getRoles(), true);
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }

  public function isSuperAdmin(): bool
  {
    return $this->hasRole(self::ROLE_SUPER_ADMIN);
  }

  public function removeRole(string $role): void
  {
    if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
      unset($this->roles[$key]);
      $this->roles = array_values($this->roles);
    }
  }

  public function setUsername(?string $username): void
  {
    $this->username = $username;
    $canonicalFieldsUpdater = new CanonicalFieldsUpdater();
    $canonicalFieldsUpdater->updateCanonicalFields($this);
  }

  public function setUsernameCanonical(?string $usernameCanonical): void
  {
    $this->usernameCanonical = $usernameCanonical;
  }

  public function setSalt(?string $salt): void
  {
    $this->salt = $salt;
  }

  public function setEmail(?string $email): void
  {
    $this->email = $email;
    $canonicalFieldsUpdater = new CanonicalFieldsUpdater();
    $canonicalFieldsUpdater->updateCanonicalFields($this);
  }

  public function setEmailCanonical(?string $emailCanonical): void
  {
    $this->emailCanonical = $emailCanonical;
  }

  public function setEnabled(bool $enabled): void
  {
    $this->enabled = $enabled;
  }

  public function setPassword(?string $password): void
  {
    $this->password = $password;
  }

  public function setSuperAdmin(bool $boolean): void
  {
    if (true === $boolean) {
      $this->addRole(self::ROLE_SUPER_ADMIN);
    } else {
      $this->removeRole(self::ROLE_SUPER_ADMIN);
    }
  }

  public function setPlainPassword(?string $password): void
  {
    $this->plainPassword = $password;

    // Do not remove this, it will trigger preUpdate doctrine event
    // when you only change the password, since plainPassword
    // is not persisted on the entity, doctrine does not watch for it.
    $this->updatedAt = new \DateTime();
  }

  public function setLastLogin(?\DateTimeInterface $time = null): void
  {
    $this->lastLogin = $time;
  }

  public function setConfirmationToken(?string $confirmationToken): void
  {
    $this->confirmationToken = $confirmationToken;
  }

  public function setPasswordRequestedAt(?\DateTimeInterface $date = null): void
  {
    $this->password_requested_at = $date;
  }

  public function getPasswordRequestedAt(): ?\DateTimeInterface
  {
    return $this->password_requested_at;
  }

  public function isPasswordRequestNonExpired(int $ttl): bool
  {
    $passwordRequestedAt = $this->getPasswordRequestedAt();

    return null !== $passwordRequestedAt && $passwordRequestedAt->getTimestamp() + $ttl > time();
  }

  public function setRoles(array $roles): void
  {
    $this->roles = [];

    foreach ($roles as $role) {
      $this->addRole($role);
    }
  }

  public function isEqualTo(UserInterface $user): bool
  {
    if (!$user instanceof self) {
      return false;
    }

    if ($this->password !== $user->getPassword()) {
      return false;
    }

    if ($this->salt !== $user->getSalt()) {
      return false;
    }

    if ($this->username !== $user->getUsername()) {
      return false;
    }

    return true;
  }

  public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
  {
    $this->createdAt = $createdAt;
  }

  public function getCreatedAt(): ?\DateTimeInterface
  {
    return $this->createdAt;
  }

  public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
  {
    $this->updatedAt = $updatedAt;
  }

  public function getUpdatedAt(): ?\DateTimeInterface
  {
    return $this->updatedAt;
  }

  public function getRealRoles(): array
  {
    return $this->roles;
  }

  public function setRealRoles(array $roles): void
  {
    $this->setRoles($roles);
  }

  public function getVerificationRequestedAt(): ?\DateTimeInterface
  {
    return $this->verification_requested_at;
  }

  public function setVerificationRequestedAt(?\DateTimeInterface $verification_requested_at): User
  {
    $this->verification_requested_at = $verification_requested_at;

    return $this;
  }
}
