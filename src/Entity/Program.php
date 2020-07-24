<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program")
 * @ORM\Entity(repositoryClass="App\Repository\ProgramRepository")
 */
class Program
{
  const APK_NONE = 0;

  const APK_PENDING = 1;

  const APK_READY = 2;

  const INITIAL_VERSION = 1;

  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="guid")
   * @ORM\GeneratedValue(strategy="CUSTOM")
   * @ORM\CustomIdGenerator(class="App\Utils\MyUuidGenerator")
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
   * The user owning this Program. If this User gets deleted, this Program gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\User",
   *     inversedBy="programs"
   * )
   * @ORM\JoinColumn(
   *     name="user_id",
   *     referencedColumnName="id",
   *     nullable=false
   * )
   */
  protected ?User $user = null;

  /**
   * The UserComments commenting this Program. If this Program gets deleted, these UserComments get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity="UserComment",
   *     mappedBy="program",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $comments;

  /**
   * The LikeNotifications mentioning this Program. If this Program gets deleted,
   * these LikeNotifications get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity="App\Entity\LikeNotification",
   *     mappedBy="program",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $like_notification_mentions;

  /**
   * The NewProgramNotification mentioning this Program as a new Program.
   * If this Program gets deleted, these NewProgramNotifications get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity="App\Entity\NewProgramNotification",
   *     mappedBy="program",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $new_program_notification_mentions;

  /**
   * RemixNotifications which are triggered when this Program (child) is created as a remix of
   *  another one (parent). If this Program gets deleted, all those RemixNotifications get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity="RemixNotification",
   *     mappedBy="remix_program",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $remix_notification_mentions_as_child;

  /**
   * RemixNotifications mentioning this Program as a parent Program of a new remix Program (child).
   * If this Program gets deleted, all RemixNotifications mentioning this program get deleted as well.
   *
   * @ORM\OneToMany(
   *     targetEntity="RemixNotification",
   *     mappedBy="program",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $remix_notification_mentions_as_parent;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\Tag", inversedBy="programs")
   * @ORM\JoinTable(
   *     name="program_tag",
   *     joinColumns={
   *         @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   *     },
   *     inverseJoinColumns={
   *         @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
   *     }
   * )
   */
  protected Collection $tags;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\Extension", inversedBy="programs")
   * @ORM\JoinTable(
   *     name="program_extension",
   *     joinColumns={
   *         @ORM\JoinColumn(name="program_id", referencedColumnName="id")
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
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $directory_hash = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected DateTime $uploaded_at;

  /**
   * @ORM\Column(type="datetime")
   */
  protected DateTime $last_modified_at;

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
   * @ORM\Column(type="integer", options={"default": 0})
   */
  protected int $catrobat_version = 0;

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
  protected ?DateTime $remix_migrated_at = null;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramRemixRelation",
   *     mappedBy="descendant",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_ancestor_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramRemixBackwardRelation",
   *     mappedBy="child",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_backward_parent_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramRemixRelation",
   *     mappedBy="ancestor",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_descendant_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramRemixBackwardRelation",
   *     mappedBy="parent",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $catrobat_remix_backward_child_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ScratchProgramRemixRelation",
   *     mappedBy="catrobat_child",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $scratch_remix_parent_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramLike",
   *     mappedBy="program",
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
   * @ORM\ManyToOne(targetEntity="\App\Entity\User")
   * @ORM\JoinColumn(name="approved_by_user", referencedColumnName="id", nullable=true)
   */
  protected ?User $approved_by_user;

  /**
   * @ORM\ManyToOne(targetEntity="StarterCategory", inversedBy="programs", cascade={"persist"})
   * @ORM\JoinColumn(nullable=true)
   */
  protected ?StarterCategory $category = null;

  /**
   * @ORM\Column(type="smallint", options={"default": 0})
   */
  protected int $apk_status = 0;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected ?DateTime $apk_request_time = null;

  /**
   * @ORM\Column(type="integer", options={"default": 0})
   */
  protected int $apk_downloads = 0;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\GameJam", inversedBy="programs")
   * @ORM\JoinColumn(nullable=true)
   */
  protected ?GameJam $gamejam = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $gamejam_submission_accepted = false;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected ?DateTime $gamejam_submission_date = null;

  /**
   * @ORM\OneToMany(targetEntity="ProgramDownloads", mappedBy="program", cascade={"remove"})
   */
  protected Collection $program_downloads;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $debug_build = false;

  /**
   * @ORM\OneToMany(targetEntity="App\Entity\ProgramInappropriateReport", mappedBy="program", fetch="EXTRA_LAZY")
   */
  protected Collection $reports;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  private bool $snapshots_enabled = false;

  /**
   * Program constructor.
   */
  public function __construct()
  {
    $this->comments = new ArrayCollection();
    $this->like_notification_mentions = new ArrayCollection();
    $this->new_program_notification_mentions = new ArrayCollection();
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
    $this->program_downloads = new ArrayCollection();
    $this->reports = new ArrayCollection();
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
   * @throws Exception
   */
  public function updateLastModifiedTimestamp(): void
  {
    $this->setLastModifiedAt(TimeUtils::getDateTime());
  }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function updateTimestamps(): void
  {
    $this->updateLastModifiedTimestamp();
    if (null == $this->getUploadedAt())
    {
      $this->setUploadedAt(new DateTime('now', new DateTimeZone('UTC')));
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

  public function setName(string $name): Program
  {
    $this->name = $name;

    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setDescription(?string $description): Program
  {
    $this->description = $description;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setCredits(?string $credits): Program
  {
    $this->credits = $credits;

    return $this;
  }

  public function getCredits(): ?string
  {
    return $this->credits;
  }

  public function setViews(int $views): Program
  {
    $this->views = $views;

    return $this;
  }

  public function getViews(): int
  {
    return $this->views;
  }

  public function setVersion(int $version): Program
  {
    $this->version = $version;

    return $this;
  }

  public function incrementVersion(): Program
  {
    ++$this->version;

    return $this;
  }

  public function getVersion(): int
  {
    return $this->version;
  }

  public function setDownloads(int $downloads): Program
  {
    $this->downloads = $downloads;

    return $this;
  }

  public function getDownloads(): int
  {
    return $this->downloads;
  }

  public function setUploadedAt(DateTime $uploadedAt): Program
  {
    $this->uploaded_at = $uploadedAt;

    return $this;
  }

  public function getUploadedAt(): DateTime
  {
    return $this->uploaded_at;
  }

  public function setLastModifiedAt(DateTime $lastModifiedAt): Program
  {
    $this->last_modified_at = $lastModifiedAt;

    return $this;
  }

  public function getLastModifiedAt(): DateTime
  {
    return $this->last_modified_at;
  }

  public function setRemixMigratedAt(?DateTime $remix_migrated_at): Program
  {
    $this->remix_migrated_at = $remix_migrated_at;

    return $this;
  }

  public function getRemixMigratedAt(): ?DateTime
  {
    return $this->remix_migrated_at;
  }

  /**
   * Sets the user owning this Program.
   */
  public function setUser(?User $user = null): Program
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Returns the user owning this Program.
   */
  public function getUser(): ?User
  {
    return $this->user;
  }

  public function getComments(): Collection
  {
    return $this->comments;
  }

  public function setComments(Collection $comments): void
  {
    $this->comments = $comments;
  }

  public function setLanguageVersion(string $languageVersion): Program
  {
    $this->language_version = $languageVersion;

    return $this;
  }

  public function getLanguageVersion(): string
  {
    return $this->language_version;
  }

  public function setCatrobatVersionName(string $catrobatVersionName): Program
  {
    $this->catrobat_version_name = $catrobatVersionName;

    return $this;
  }

  public function getCatrobatVersionName(): string
  {
    return $this->catrobat_version_name;
  }

  public function setCatrobatVersion(int $catrobatVersion): Program
  {
    $this->catrobat_version = $catrobatVersion;

    return $this;
  }

  public function getCatrobatVersion(): int
  {
    return $this->catrobat_version;
  }

  public function setUploadIp(string $uploadIp): Program
  {
    $this->upload_ip = $uploadIp;

    return $this;
  }

  public function getUploadIp(): string
  {
    return $this->upload_ip;
  }

  public function setVisible(bool $visible): Program
  {
    $this->visible = $visible;

    return $this;
  }

  public function setPrivate(bool $private): Program
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

  public function setUploadLanguage(string $uploadLanguage): Program
  {
    $this->upload_language = $uploadLanguage;

    return $this;
  }

  public function getUploadLanguage(): string
  {
    return $this->upload_language;
  }

  public function setFilesize(int $filesize): Program
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

  public function getCategory(): ?StarterCategory
  {
    return $this->category;
  }

  public function setCategory(?StarterCategory $category): void
  {
    $this->category = $category;
  }

  public function setExtractedDirectoryHash(?string $directory_hash): void
  {
    $this->directory_hash = $directory_hash;
  }

  public function getExtractedDirectoryHash(): ?string
  {
    return $this->directory_hash;
  }

  public function getApkStatus(): int
  {
    return $this->apk_status;
  }

  public function setApkStatus(int $apk_status): Program
  {
    $this->apk_status = $apk_status;

    return $this;
  }

  public function setApkRequestTime(?DateTime $apkRequestTime): Program
  {
    $this->apk_request_time = $apkRequestTime;

    return $this;
  }

  public function getApkRequestTime(): ?DateTime
  {
    return $this->apk_request_time;
  }

  public function setApkDownloads(int $apkDownloads): Program
  {
    $this->apk_downloads = $apkDownloads;

    return $this;
  }

  public function getApkDownloads(): int
  {
    return $this->apk_downloads;
  }

  public function setGamejam(?GameJam $gamejam = null): Program
  {
    $this->gamejam = $gamejam;

    return $this;
  }

  public function getGamejam(): ?GameJam
  {
    return $this->gamejam;
  }

  public function setAcceptedForGameJam(bool $accepted): Program
  {
    $this->gamejam_submission_accepted = $accepted;

    return $this;
  }

  public function setGamejamSubmissionAccepted(bool $accepted): void
  {
    $this->gamejam_submission_accepted = $accepted;
  }

  public function getGamejamSubmissionAccepted(): bool
  {
    return $this->gamejam_submission_accepted;
  }

  public function isAcceptedForGameJam(): bool
  {
    return $this->gamejam_submission_accepted;
  }

  public function setGameJamSubmissionDate(?DateTime $date): void
  {
    $this->gamejam_submission_date = $date;
  }

  public function getGameJamSubmissionDate(): ?DateTime
  {
    return $this->gamejam_submission_date;
  }

  public function getGamejam_submission_accepted(): bool
  {
    return $this->gamejam_submission_accepted;
  }

  public function getProgramDownloads(): Collection
  {
    return $this->program_downloads;
  }

  public function addProgramDownloads(ProgramDownloads $program_download): Collection
  {
    $this->program_downloads[] = $program_download;

    return $this->program_downloads;
  }

  public function addTag(Tag $tag): void
  {
    if ($this->tags->contains($tag))
    {
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
    if ($this->extensions->contains($extension))
    {
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
    foreach ($this->extensions as $extension)
    {
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

    return array_unique(array_map(function (ProgramRemixRelation $ra)
    {
      return $ra->getDescendantId();
    }, $relations));
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
    foreach ($this->extensions as $program_extension)
    {
      /* @var Extension $program_extension */
      $extensions[] = $program_extension->getName();
    }

    return implode(', ', $extensions);
  }

  public function getTagsString(): string
  {
    $tags = [];
    foreach ($this->tags as $program_tag)
    {
      /* @var Tag $program_tag */
      $tags[] = $program_tag->getEn();
      $tags[] = $program_tag->getDe();
      $tags[] = $program_tag->getIt();
      $tags[] = $program_tag->getFr();
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
   * Returns the LikeNotifications mentioning this Program.
   */
  public function getLikeNotificationMentions(): Collection
  {
    return $this->like_notification_mentions;
  }

  /**
   * Sets the LikeNotifications mentioning this Program.
   */
  public function setLikeNotificationMentions(Collection $like_notification_mentions): void
  {
    $this->like_notification_mentions = $like_notification_mentions;
  }

  /**
   * Returns the NewProgramNotification mentioning this Program as a new Program.
   */
  public function getNewProgramNotificationMentions(): Collection
  {
    return $this->new_program_notification_mentions;
  }

  /**
   * Sets the NewProgramNotifications mentioning this Program as a new Program.
   */
  public function setNewProgramNotificationMentions(Collection $new_program_notification_mentions): void
  {
    $this->new_program_notification_mentions = $new_program_notification_mentions;
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
   * Returns the RemixNotifications which are triggered when this Program (child) is created as a remix of
   * another one (parent).
   */
  public function getRemixNotificationMentionsAsChild(): Collection
  {
    return $this->remix_notification_mentions_as_child;
  }

  /**
   * Sets theRemixNotifications which are triggered when this Program (child) is created as a remix of
   * another one (parent).
   */
  public function setRemixNotificationMentionsAsChild(Collection $remix_notification_mentions_as_child): void
  {
    $this->remix_notification_mentions_as_child = $remix_notification_mentions_as_child;
  }

  /**
   * Returns the RemixNotifications mentioning this Program as a parent Program of a new remix Program (child).
   */
  public function getRemixNotificationMentionsAsParent(): Collection
  {
    return $this->remix_notification_mentions_as_parent;
  }

  /**
   * Sets the RemixNotifications mentioning this Program as a parent Program of a new remix Program (child).
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

  public function getProgram(): ?Program
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

  public function isScratchProgram(): bool
  {
    return null !== $this->scratch_id;
  }

  public function setSnapshotsEnabled(bool $snapshots_enabled): void
  {
    $this->snapshots_enabled = $snapshots_enabled;
  }

  public function isSnapshotsEnabled(): bool
  {
    return $this->snapshots_enabled;
  }
}
