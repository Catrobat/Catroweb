<?php

namespace App\DB\Entity\Project;

use App\DB\Entity\Project\Remix\ProjectRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProjectRemixRelation;
use App\DB\Entity\Project\Scratch\ScratchProjectRemixRelation;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProjectNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\Generator\MyUuidGenerator;
use App\Utils\TimeUtils;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks
 *
 * @ORM\Table(
 *     name="project",
 *     indexes={
 *
 *         @ORM\Index(name="rand_idx", columns={"rand"}),
 *         @ORM\Index(name="uploaded_at_idx", columns={"uploaded_at"}),
 *         @ORM\Index(name="views_idx", columns={"views"}),
 *         @ORM\Index(name="downloads_idx", columns={"downloads"}),
 *         @ORM\Index(name="name_idx", columns={"name"}),
 *         @ORM\Index(name="user_idx", columns={"user_id"}),
 *         @ORM\Index(name="language_version_idx", columns={"language_version"}),
 *         @ORM\Index(name="visible_idx", columns={"visible"}),
 *         @ORM\Index(name="private_idx", columns={"private"}),
 *         @ORM\Index(name="debug_build_idx", columns={"debug_build"}),
 *         @ORM\Index(name="flavor_idx", columns={"flavor"}),
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project implements \Stringable
{
  final public const APK_NONE = 0;

  final public const APK_PENDING = 1;

  final public const APK_READY = 2;

  final public const INITIAL_VERSION = 1;

  /**
   * @ORM\Id
   *
   * @ORM\Column(name="id", type="guid")
   *
   * @ORM\GeneratedValue(strategy="CUSTOM")
   *
   * @ORM\CustomIdGenerator(class=MyUuidGenerator::class)
   */
  protected ?string $id = null;

  /**
   * @ORM\Column(type="string", length=300)
   */
  protected string $name;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected ?string $description = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected ?string $credits = null;

  /**
   * @ORM\Column(type="integer", options={"default": 1})
   */
  protected int $version = self::INITIAL_VERSION;

  /**
   * @ORM\Column(type="integer", nullable=true, unique=true)
   */
  protected ?int $scratch_id = null;

  /**
   * The user owning this Project. If this User gets deleted, this Project gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity=User::class,
   *     inversedBy="projects"
   * )
   *
   * @ORM\JoinColumn(
   *     name="user_id",
   *     referencedColumnName="id",
   *     nullable=false
   * )
   */
  protected ?User $user = null;

  /**
   * The UserComments commenting this Project. If this Project gets deleted, these UserComments get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity=UserComment::class,
   *     mappedBy="project",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $comments;

  /**
   * The LikeNotifications mentioning this Project. If this Project gets deleted,
   * these LikeNotifications get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity=LikeNotification::class,
   *     mappedBy="project",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $like_notification_mentions;

  /**
   * The NewProjectNotification mentioning this Project as a new Project.
   * If this Project gets deleted, these NewProjectNotifications get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity=NewProjectNotification::class,
   *     mappedBy="project",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $new_project_notification_mentions;

  /**
   * RemixNotifications which are triggered when this Project (child) is created as a remix of
   *  another one (parent). If this Project gets deleted, all those RemixNotifications get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity=RemixNotification::class,
   *     mappedBy="remix_project",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $remix_notification_mentions_as_child;

  /**
   * RemixNotifications mentioning this Project as a parent Project of a new remix Project (child).
   * If this Project gets deleted, all RemixNotifications mentioning this project get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity=RemixNotification::class,
   *     mappedBy="project",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $remix_notification_mentions_as_parent;

  /**
   * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="projects")
   *
   * @ORM\JoinTable(
   *     name="project_tag",
   *     joinColumns={
   *
   *         @ORM\JoinColumn(name="project_id", referencedColumnName="id")
   *     },
   *     inverseJoinColumns={
   *         @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
   *     }
   * )
   */
  protected Collection $tags;

  /**
   * @ORM\ManyToMany(targetEntity=Extension::class, inversedBy="projects")
   *
   * @ORM\JoinTable(
   *     name="project_extension",
   *     joinColumns={
   *
   *         @ORM\JoinColumn(name="project_id", referencedColumnName="id")
   *     },
   *     inverseJoinColumns={
   *         @ORM\JoinColumn(name="extension_id", referencedColumnName="id")
   *     }
   * )
   */
  protected Collection $extensions;

  /**
   * @ORM\Column(type="integer")
   */
  protected int $views = 0;

  /**
   * @ORM\Column(type="integer")
   */
  protected int $downloads = 0;

  /**
   * @ORM\Column(type="datetime")
   */
  protected \DateTime $uploaded_at;

  /**
   * @ORM\Column(type="datetime")
   */
  protected \DateTime $last_modified_at;

  /**
   * @ORM\Column(type="string", options={"default": "0"})
   */
  protected string $language_version = '0';

  /**
   * New name in android: applicationVersion.
   *
   * @ORM\Column(type="string", options={"default": ""})
   */
  protected string $catrobat_version_name = '';

  /**
   * @ORM\Column(type="string", options={"default": ""})
   */
  protected string $upload_ip = '';

  /**
   * @ORM\Column(type="boolean", options={"default": true})
   */
  protected bool $visible = true;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $private = false;

  /**
   * @ORM\Column(type="string", options={"default": "pocketcode"})
   */
  protected ?string $flavor = 'pocketcode';

  /**
   * @ORM\Column(type="string", options={"default": ""})
   */
  protected string $upload_language = '';

  /**
   * @ORM\Column(type="integer", options={"default": 0})
   */
  protected int $filesize = 0;

  /**
   * @ORM\Column(type="boolean", options={"default": true})
   */
  protected bool $remix_root = true;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected ?\DateTime $remix_migrated_at = null;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ProjectRemixRelation::class,
   *     mappedBy="descendant",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_ancestor_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ProjectRemixBackwardRelation::class,
   *     mappedBy="child",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_backward_parent_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ProjectRemixRelation::class,
   *     mappedBy="ancestor",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_descendant_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ProjectRemixBackwardRelation::class,
   *     mappedBy="parent",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_backward_child_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ScratchProjectRemixRelation::class,
   *     mappedBy="catrobat_child",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $scratch_remix_parent_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity=ProjectLike::class,
   *     mappedBy="project",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $likes;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $approved = false;

  /**
   * @ORM\ManyToOne(targetEntity=User::class)
   *
   * @ORM\JoinColumn(name="approved_by_user", referencedColumnName="id", nullable=true)
   */
  protected ?User $approved_by_user = null;

  /**
   * @ORM\Column(type="smallint", options={"default": 0})
   */
  protected int $apk_status = 0;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected ?\DateTime $apk_request_time = null;

  /**
   * @ORM\Column(type="integer", options={"default": 0})
   */
  protected int $apk_downloads = 0;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $debug_build = false;

  /**
   * @ORM\OneToMany(targetEntity=ProjectInappropriateReport::class, mappedBy="project", fetch="EXTRA_LAZY")
   */
  protected Collection $reports;

  /**
   * @ORM\Column(type="integer", options={"default": 0})
   */
  protected int $rand = 0;

  /**
   * @ORM\Column(type="float", options={"default": 0.0})
   */
  protected float $popularity = 0.0;

  /**
   * @ORM\OneToMany(targetEntity=ProjectCustomTranslation::class, mappedBy="project", cascade={"remove"})
   */
  private Collection $custom_translations;

  /**
   * No ORM entry.
   */
  private bool $should_invalidate_translation_cache = false;

  public function __construct()
  {
    $this->comments = new ArrayCollection();
    $this->like_notification_mentions = new ArrayCollection();
    $this->new_project_notification_mentions = new ArrayCollection();
    $this->remix_notification_mentions_as_child = new ArrayCollection();
    $this->remix_notification_mentions_as_parent = new ArrayCollection();
    $this->tags = new ArrayCollection();
    $this->extensions = new ArrayCollection();
    $this->catrobat_remix_ancestor_relations = new ArrayCollection();
    $this->catrobat_remix_backward_parent_relations = new ArrayCollection();
    $this->catrobat_remix_descendant_relations = new ArrayCollection();
    $this->catrobat_remix_backward_child_relations = new ArrayCollection();
    $this->scratch_remix_parent_relations = new ArrayCollection();
    $this->likes = new ArrayCollection();
    $this->reports = new ArrayCollection();
    $this->custom_translations = new ArrayCollection();
  }

  public function __toString(): string
  {
    return $this->name.' (#'.$this->id.')';
  }

  public function setApprovedByUser(?User $approved_by_user): void
  {
    $this->approved_by_user = $approved_by_user;
  }

  public function getApprovedByUser(): ?User
  {
    return $this->approved_by_user;
  }

  /**
   * @ORM\PreUpdate
   *
   * @throws \Exception
   */
  public function updateLastModifiedTimestamp(): void
  {
    $this->setLastModifiedAt(TimeUtils::getDateTime());
  }

  /**
   * @ORM\PrePersist
   *
   * @throws \Exception
   */
  public function updateTimestamps(): void
  {
    $this->updateLastModifiedTimestamp();
    if (null == $this->getUploadedAt()) {
      $this->setUploadedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function setInitialVersion(): void
  {
    $this->version = self::INITIAL_VERSION;
  }

  public function isInitialVersion(): bool
  {
    return self::INITIAL_VERSION === $this->version;
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function setName(string $name): Project
  {
    $this->name = $name;
    $this->should_invalidate_translation_cache = true;

    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setDescription(?string $description): Project
  {
    $this->description = $description;
    $this->should_invalidate_translation_cache = true;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setCredits(?string $credits): Project
  {
    $this->credits = $credits;
    $this->should_invalidate_translation_cache = true;

    return $this;
  }

  public function getCredits(): ?string
  {
    return $this->credits;
  }

  public function setViews(int $views): Project
  {
    $this->views = $views;

    return $this;
  }

  public function getViews(): int
  {
    return $this->views;
  }

  public function setVersion(int $version): Project
  {
    $this->version = $version;

    return $this;
  }

  public function incrementVersion(): Project
  {
    ++$this->version;

    return $this;
  }

  public function getVersion(): int
  {
    return $this->version;
  }

  public function setDownloads(int $downloads): Project
  {
    $this->downloads = $downloads;

    return $this;
  }

  public function getDownloads(): int
  {
    return $this->downloads;
  }

  public function setUploadedAt(\DateTime $uploadedAt): Project
  {
    $this->uploaded_at = $uploadedAt;

    return $this;
  }

  public function getUploadedAt(): \DateTime
  {
    return $this->uploaded_at;
  }

  public function setLastModifiedAt(\DateTime $lastModifiedAt): Project
  {
    $this->last_modified_at = $lastModifiedAt;

    return $this;
  }

  public function getLastModifiedAt(): \DateTime
  {
    return $this->last_modified_at;
  }

  public function setRemixMigratedAt(?\DateTime $remix_migrated_at): Project
  {
    $this->remix_migrated_at = $remix_migrated_at;

    return $this;
  }

  public function getRemixMigratedAt(): ?\DateTime
  {
    return $this->remix_migrated_at;
  }

  /**
   * Sets the user owning this Project.
   */
  public function setUser(User $user = null): Project
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Returns the user owning this Project.
   */
  public function getUser(): ?User
  {
    return $this->user;
  }

  public function getUsernameString(): string
  {
    return $this->user->getUserIdentifier();
  }

  public function getComments(): Collection
  {
    return $this->comments;
  }

  public function setComments(Collection $comments): void
  {
    $this->comments = $comments;
  }

  public function setLanguageVersion(string $languageVersion): Project
  {
    $this->language_version = $languageVersion;

    return $this;
  }

  public function getLanguageVersion(): string
  {
    return $this->language_version;
  }

  public function setCatrobatVersionName(string $catrobat_version_name): Project
  {
    $this->catrobat_version_name = $catrobat_version_name;

    return $this;
  }

  public function getCatrobatVersionName(): string
  {
    return $this->catrobat_version_name;
  }

  public function setUploadIp(string $uploadIp): Project
  {
    $this->upload_ip = $uploadIp;

    return $this;
  }

  public function getUploadIp(): string
  {
    return $this->upload_ip;
  }

  public function setVisible(bool $visible): Project
  {
    $this->visible = $visible;

    return $this;
  }

  public function setPrivate(bool $private): Project
  {
    $this->private = $private;

    return $this;
  }

  public function getPrivate(): bool
  {
    return $this->private;
  }

  public function getVisible(): bool
  {
    return $this->visible;
  }

  public function setUploadLanguage(string $uploadLanguage): Project
  {
    $this->upload_language = $uploadLanguage;

    return $this;
  }

  public function getUploadLanguage(): string
  {
    return $this->upload_language;
  }

  public function setFilesize(int $filesize): Project
  {
    $this->filesize = $filesize;

    return $this;
  }

  public function getFilesize(): int
  {
    return $this->filesize;
  }

  public function setApproved(bool $approved): void
  {
    $this->approved = $approved;
  }

  public function getApproved(): bool
  {
    return $this->approved;
  }

  public function isVisible(): bool
  {
    return $this->visible;
  }

  public function setId(string $id): void
  {
    $this->id = $id;
  }

  public function getFlavor(): ?string
  {
    return $this->flavor;
  }

  public function setFlavor(?string $flavor): void
  {
    $this->flavor = $flavor;
  }

  public function getApkStatus(): int
  {
    return $this->apk_status;
  }

  public function setApkStatus(int $apk_status): Project
  {
    $this->apk_status = $apk_status;

    return $this;
  }

  public function setApkRequestTime(?\DateTime $apkRequestTime): Project
  {
    $this->apk_request_time = $apkRequestTime;

    return $this;
  }

  public function getApkRequestTime(): ?\DateTime
  {
    return $this->apk_request_time;
  }

  public function setApkDownloads(int $apkDownloads): Project
  {
    $this->apk_downloads = $apkDownloads;

    return $this;
  }

  public function getApkDownloads(): int
  {
    return $this->apk_downloads;
  }

  public function addTag(Tag $tag): void
  {
    if ($this->tags->contains($tag)) {
      return;
    }
    $this->tags->add($tag);
  }

  public function removeTag(Tag $tag): void
  {
    $this->tags->removeElement($tag);
  }

  public function addExtension(Extension $extension): void
  {
    if ($this->extensions->contains($extension)) {
      return;
    }
    $this->extensions->add($extension);
  }

  public function removeExtension(Extension $extension): void
  {
    $this->extensions->removeElement($extension);
  }

  public function removeAllExtensions(): void
  {
    foreach ($this->extensions as $extension) {
      $this->removeExtension($extension);
    }
  }

  public function setRemixRoot(bool $is_remix_root): void
  {
    $this->remix_root = $is_remix_root;
  }

  public function isRemixRoot(): bool
  {
    return $this->remix_root;
  }

  public function getCatrobatRemixAncestorRelations(): Collection
  {
    return $this->catrobat_remix_ancestor_relations;
  }

  public function getCatrobatRemixBackwardParentRelations(): Collection
  {
    return $this->catrobat_remix_backward_parent_relations;
  }

  public function getCatrobatRemixDescendantRelations(): Collection
  {
    return $this->catrobat_remix_descendant_relations;
  }

  public function getCatrobatRemixDescendantIds(): array
  {
    $relations = $this->getCatrobatRemixDescendantRelations()->getValues();

    return array_unique(array_map(fn (ProjectRemixRelation $ra) => $ra->getDescendantId(), $relations));
  }

  public function getScratchRemixParentRelations(): Collection
  {
    return $this->scratch_remix_parent_relations;
  }

  public function getLikes(): Collection
  {
    return $this->likes;
  }

  public function setLikes(Collection $likes): void
  {
    $this->likes = $likes;
  }

  public function getTags(): Collection
  {
    return $this->tags;
  }

  public function getExtensions(): Collection
  {
    return $this->extensions;
  }

  public function getExtensionsString(): string
  {
    $extensions = [];
    foreach ($this->extensions as $project_extension) {
      /* @var Extension $project_extension */
      $extensions[] = $project_extension->getInternalTitle();
    }

    return implode(', ', $extensions);
  }

  public function getTagsString(): string
  {
    $tags = [];
    foreach ($this->tags as $project_tag) {
      /* @var Tag $project_tag */
      $tags[] = $project_tag->getInternalTitle();
    }

    return implode(', ', $tags);
  }

  public function isDebugBuild(): bool
  {
    return $this->debug_build;
  }

  public function setDebugBuild(bool $debug_build): void
  {
    $this->debug_build = $debug_build;
  }

  /**
   * Returns the LikeNotifications mentioning this Project.
   */
  public function getLikeNotificationMentions(): Collection
  {
    return $this->like_notification_mentions;
  }

  /**
   * Sets the LikeNotifications mentioning this Project.
   */
  public function setLikeNotificationMentions(Collection $like_notification_mentions): void
  {
    $this->like_notification_mentions = $like_notification_mentions;
  }

  /**
   * Returns the NewProjectNotification mentioning this Project as a new Project.
   */
  public function getNewProjectNotificationMentions(): Collection
  {
    return $this->new_project_notification_mentions;
  }

  /**
   * Sets the NewProjectNotifications mentioning this Project as a new Project.
   */
  public function setNewProjectNotificationMentions(Collection $new_project_notification_mentions): void
  {
    $this->new_project_notification_mentions = $new_project_notification_mentions;
  }

  public function getReports(): Collection
  {
    return $this->reports;
  }

  public function getReportsCount(): int
  {
    return $this->getReports()->count();
  }

  /**
   * Returns the RemixNotifications which are triggered when this Project (child) is created as a remix of
   * another one (parent).
   */
  public function getRemixNotificationMentionsAsChild(): Collection
  {
    return $this->remix_notification_mentions_as_child;
  }

  /**
   * Sets theRemixNotifications which are triggered when this Project (child) is created as a remix of
   * another one (parent).
   */
  public function setRemixNotificationMentionsAsChild(Collection $remix_notification_mentions_as_child): void
  {
    $this->remix_notification_mentions_as_child = $remix_notification_mentions_as_child;
  }

  /**
   * Returns the RemixNotifications mentioning this Project as a parent Project of a new remix Project (child).
   */
  public function getRemixNotificationMentionsAsParent(): Collection
  {
    return $this->remix_notification_mentions_as_parent;
  }

  /**
   * Sets the RemixNotifications mentioning this Project as a parent Project of a new remix Project (child).
   */
  public function setRemixNotificationMentionsAsParent(Collection $remix_notification_mentions_as_parent): void
  {
    $this->remix_notification_mentions_as_parent = $remix_notification_mentions_as_parent;
  }

  public function isExample(): bool
  {
    return false;
  }

  public function getImageType(): string
  {
    return '';
  }

  public function getProject(): ?Project
  {
    return $this;
  }

  public function setScratchId(?int $scratch_id): void
  {
    $this->scratch_id = $scratch_id;
  }

  public function getScratchId(): ?int
  {
    return $this->scratch_id;
  }

  public function isScratchProject(): bool
  {
    return null !== $this->scratch_id;
  }

  public function getCustomTranslations(): Collection
  {
    return $this->custom_translations;
  }

  public function getRand(): int
  {
    return $this->rand;
  }

  public function setRand(int $rand): Project
  {
    $this->rand = $rand;

    return $this;
  }

  public function shouldInvalidateTranslationCache(): bool
  {
    return $this->should_invalidate_translation_cache;
  }

  public function getPopularity(): float
  {
    return $this->popularity;
  }

  public function setPopularity(float $popularity): Project
  {
    $this->popularity = $popularity;

    return $this;
  }
}
