<?php

namespace App\DB\Entity\Project;

use App\DB\Entity\Project\Remix\ProgramRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\Generator\MyUuidGenerator;
use App\Utils\TimeUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'program')]
#[ORM\Index(columns: ['rand'], name: 'rand_idx')]
#[ORM\Index(columns: ['uploaded_at'], name: 'uploaded_at_idx')]
#[ORM\Index(columns: ['views'], name: 'views_idx')]
#[ORM\Index(columns: ['downloads'], name: 'downloads_idx')]
#[ORM\Index(columns: ['name'], name: 'name_idx')]
#[ORM\Index(columns: ['user_id'], name: 'user_idx')]
#[ORM\Index(columns: ['language_version'], name: 'language_version_idx')]
#[ORM\Index(columns: ['visible'], name: 'visible_idx')]
#[ORM\Index(columns: ['private'], name: 'private_idx')]
#[ORM\Index(columns: ['debug_build'], name: 'debug_build_idx')]
#[ORM\Index(columns: ['flavor'], name: 'flavor_idx')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProgramRepository::class)]
class Program implements \Stringable
{
  final public const APK_NONE = 0;

  final public const APK_PENDING = 1;

  final public const APK_READY = 2;

  final public const INITIAL_VERSION = 1;

  #[ORM\Id]
  #[ORM\Column(name: 'id', type: 'guid')]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  protected ?string $id = null;

  #[ORM\Column(type: 'string', length: 300)]
  protected string $name;

  #[ORM\Column(type: 'text', nullable: true)]
  protected ?string $description = null;

  #[ORM\Column(type: 'text', nullable: true)]
  protected ?string $credits = null;

  #[ORM\Column(type: 'integer', options: ['default' => 1])]
  protected int $version = self::INITIAL_VERSION;

  #[ORM\Column(type: 'integer', unique: true, nullable: true)]
  protected ?int $scratch_id = null;

  /**
   * The user owning this Program. If this User gets deleted, this Program gets deleted as well.
   */
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
  #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'programs')]
  protected ?User $user = null;

  /**
   * The UserComments commenting this Program. If this Program gets deleted, these UserComments get deleted as well.
   */
  #[ORM\OneToMany(mappedBy: 'program', targetEntity: UserComment::class, cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $comments;

  /**
   * The LikeNotifications mentioning this Program. If this Program gets deleted,
   * these LikeNotifications get deleted as well.
   */
  #[ORM\OneToMany(mappedBy: 'program', targetEntity: LikeNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $like_notification_mentions;

  /**
   * The NewProgramNotification mentioning this Program as a new Program.
   * If this Program gets deleted, these NewProgramNotifications get deleted as well.
   */
  #[ORM\OneToMany(mappedBy: 'program', targetEntity: NewProgramNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $new_program_notification_mentions;

  /**
   * RemixNotifications which are triggered when this Program (child) is created as a remix of
   *  another one (parent). If this Program gets deleted, all those RemixNotifications get deleted as well.
   */
  #[ORM\OneToMany(mappedBy: 'remix_program', targetEntity: RemixNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $remix_notification_mentions_as_child;

  /**
   * RemixNotifications mentioning this Program as a parent Program of a new remix Program (child).
   * If this Program gets deleted, all RemixNotifications mentioning this program get deleted as well.
   */
  #[ORM\OneToMany(mappedBy: 'program', targetEntity: RemixNotification::class, cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $remix_notification_mentions_as_parent;

  #[ORM\JoinTable(name: 'program_tag')]
  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id')]
  #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
  #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'programs')]
  protected Collection $tags;

  #[ORM\JoinTable(name: 'program_extension')]
  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id')]
  #[ORM\InverseJoinColumn(name: 'extension_id', referencedColumnName: 'id')]
  #[ORM\ManyToMany(targetEntity: Extension::class, inversedBy: 'programs')]
  protected Collection $extensions;

  #[ORM\Column(type: 'integer')]
  protected int $views = 0;

  #[ORM\Column(type: 'integer')]
  protected int $downloads = 0;

  #[ORM\Column(type: 'datetime')]
  protected \DateTime $uploaded_at;

  #[ORM\Column(type: 'datetime')]
  protected \DateTime $last_modified_at;

  #[ORM\Column(type: 'string', options: ['default' => '0'])]
  protected string $language_version = '0';

  /**
   * New name in android: applicationVersion.
   */
  #[ORM\Column(type: 'string', options: ['default' => ''])]
  protected string $catrobat_version_name = '';

  #[ORM\Column(type: 'string', options: ['default' => ''])]
  protected string $upload_ip = '';

  #[ORM\Column(type: 'boolean', options: ['default' => true])]
  protected bool $visible = true;

  #[ORM\Column(type: 'boolean', options: ['default' => false])]
  protected bool $private = false;

  #[ORM\Column(type: 'string', options: ['default' => 'pocketcode'])]
  protected ?string $flavor = 'pocketcode';

  #[ORM\Column(type: 'string', options: ['default' => ''])]
  protected string $upload_language = '';

  #[ORM\Column(type: 'integer', options: ['default' => 0])]
  protected int $filesize = 0;

  #[ORM\Column(type: 'boolean', options: ['default' => true])]
  protected bool $remix_root = true;

  #[ORM\Column(type: 'datetime', nullable: true)]
  protected ?\DateTime $remix_migrated_at = null;

  #[ORM\OneToMany(mappedBy: 'descendant', targetEntity: ProgramRemixRelation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $catrobat_remix_ancestor_relations;

  #[ORM\OneToMany(mappedBy: 'child', targetEntity: ProgramRemixBackwardRelation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $catrobat_remix_backward_parent_relations;

  #[ORM\OneToMany(mappedBy: 'ancestor', targetEntity: ProgramRemixRelation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $catrobat_remix_descendant_relations;

  #[ORM\OneToMany(mappedBy: 'parent', targetEntity: ProgramRemixBackwardRelation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $catrobat_remix_backward_child_relations;

  #[ORM\OneToMany(mappedBy: 'catrobat_child', targetEntity: ScratchProgramRemixRelation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $scratch_remix_parent_relations;

  #[ORM\OneToMany(mappedBy: 'program', targetEntity: ProgramLike::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
  protected Collection $likes;

  #[ORM\Column(type: 'boolean', options: ['default' => false])]
  protected bool $approved = false;

  #[ORM\JoinColumn(name: 'approved_by_user', referencedColumnName: 'id', nullable: true)]
  #[ORM\ManyToOne(targetEntity: User::class)]
  protected ?User $approved_by_user = null;

  #[ORM\Column(type: 'smallint', options: ['default' => 0])]
  protected int $apk_status = 0;

  #[ORM\Column(type: 'datetime', nullable: true)]
  protected ?\DateTime $apk_request_time = null;

  #[ORM\Column(type: 'integer', options: ['default' => 0])]
  protected int $apk_downloads = 0;

  #[ORM\Column(type: 'boolean', options: ['default' => false])]
  protected bool $debug_build = false;

  #[ORM\OneToMany(mappedBy: 'program', targetEntity: ProgramInappropriateReport::class, fetch: 'EXTRA_LAZY')]
  protected Collection $reports;

  #[ORM\Column(type: 'integer', options: ['default' => 0])]
  protected int $rand = 0;

  #[ORM\Column(type: 'float', options: ['default' => '0.0'])]
  protected float $popularity = 0.0;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectCustomTranslation::class, cascade: ['remove'])]
  private Collection $custom_translations;

  #[ORM\Column(type: 'integer', options: ['default' => 0])]
  protected int $not_for_kids = 0;

  /**
   * No ORM entry.
   */
  private bool $should_invalidate_translation_cache = false;

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
   * @throws \Exception
   */
  #[ORM\PreUpdate]
  public function updateLastModifiedTimestamp(): void
  {
    $this->setLastModifiedAt(TimeUtils::getDateTime());
  }

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    $this->updateLastModifiedTimestamp();
    if (null == $this->getUploadedAt()) {
      $this->setUploadedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }
  }

  #[ORM\PrePersist]
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
    $this->should_invalidate_translation_cache = true;

    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setDescription(?string $description): Program
  {
    $this->description = $description;
    $this->should_invalidate_translation_cache = true;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setCredits(?string $credits): Program
  {
    $this->credits = $credits;
    $this->should_invalidate_translation_cache = true;

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

  public function setUploadedAt(\DateTime $uploadedAt): Program
  {
    $this->uploaded_at = $uploadedAt;

    return $this;
  }

  public function getUploadedAt(): \DateTime
  {
    return $this->uploaded_at;
  }

  public function setLastModifiedAt(\DateTime $lastModifiedAt): Program
  {
    $this->last_modified_at = $lastModifiedAt;

    return $this;
  }

  public function getLastModifiedAt(): \DateTime
  {
    return $this->last_modified_at;
  }

  public function setRemixMigratedAt(?\DateTime $remix_migrated_at): Program
  {
    $this->remix_migrated_at = $remix_migrated_at;

    return $this;
  }

  public function getRemixMigratedAt(): ?\DateTime
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

  public function setLanguageVersion(string $languageVersion): Program
  {
    $this->language_version = $languageVersion;

    return $this;
  }

  public function getLanguageVersion(): string
  {
    return $this->language_version;
  }

  public function setCatrobatVersionName(string $catrobat_version_name): Program
  {
    $this->catrobat_version_name = $catrobat_version_name;

    return $this;
  }

  public function getCatrobatVersionName(): string
  {
    return $this->catrobat_version_name;
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

  public function getApkStatus(): int
  {
    return $this->apk_status;
  }

  public function setApkStatus(int $apk_status): Program
  {
    $this->apk_status = $apk_status;

    return $this;
  }

  public function setApkRequestTime(?\DateTime $apkRequestTime): Program
  {
    $this->apk_request_time = $apkRequestTime;

    return $this;
  }

  public function getApkRequestTime(): ?\DateTime
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

    return array_unique(array_map(fn (ProgramRemixRelation $ra) => $ra->getDescendantId(), $relations));
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
    foreach ($this->extensions as $program_extension) {
      /* @var Extension $program_extension */
      $extensions[] = $program_extension->getInternalTitle();
    }

    return implode(', ', $extensions);
  }

  public function getTagsString(): string
  {
    $tags = [];
    foreach ($this->tags as $program_tag) {
      /* @var Tag $program_tag */
      $tags[] = $program_tag->getInternalTitle();
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

  public function getCustomTranslations(): Collection
  {
    return $this->custom_translations;
  }

  public function getRand(): int
  {
    return $this->rand;
  }

  public function setRand(int $rand): Program
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

  public function setPopularity(float $popularity): Program
  {
    $this->popularity = $popularity;

    return $this;
  }

  public function getNotForKids(): int
  {
    return $this->not_for_kids;
  }

  public function setNotForKids(int $not_for_kids): Program
  {
    $this->not_for_kids = $not_for_kids;

    return $this;
  }
}
