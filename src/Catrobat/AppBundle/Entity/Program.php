<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\EntityListeners({"Catrobat\AppBundle\Listeners\Entity\ProgramListener"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program")
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\ProgramRepository")
 */
class Program
{
  /**
   *
   */
  const APK_NONE = 0;

  /**
   *
   */
  const APK_PENDING = 1;

  /**
   *
   */
  const APK_READY = 2;

  /**
   *
   */
  const INITIAL_VERSION = 1;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="string", length=300)
   */
  protected $name;

  /**
   * @ORM\Column(type="text")
   */
  protected $description;

  /**
   * @ORM\Column(type="integer", options={"default" = 1})
   */
  protected $version = self::INITIAL_VERSION;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User", inversedBy="programs")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
   */
  protected $user;

  /**
   * @var \Doctrine\Common\Collections\Collection|Tag[]
   *
   * @ORM\ManyToMany(targetEntity="\Catrobat\AppBundle\Entity\Tag", inversedBy="programs")
   * @ORM\JoinTable(
   *  name="program_tag",
   *  joinColumns={
   *      @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true)
   *  },
   *  inverseJoinColumns={
   *      @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=true)
   *  }
   * )
   */
  protected $tags;

  /**
   * @var \Doctrine\Common\Collections\Collection|Extension[]
   *
   * @ORM\ManyToMany(targetEntity="\Catrobat\AppBundle\Entity\Extension", inversedBy="programs")
   * @ORM\JoinTable(
   *  name="program_extension",
   *  joinColumns={
   *      @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true)
   *  },
   *  inverseJoinColumns={
   *      @ORM\JoinColumn(name="extension_id", referencedColumnName="id", nullable=true)
   *  }
   * )
   */
  protected $extensions;

  /**
   * @ORM\Column(type="integer")
   */
  protected $views = 0;

  /**
   * @ORM\Column(type="integer")
   */
  protected $downloads = 0;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $directory_hash;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $uploaded_at;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $last_modified_at;

  /**
   * @ORM\Column(type="string", options={"default":0})
   */
  protected $language_version = 0;

  /**
   * @ORM\Column(type="string", options={"default":""})
   */
  protected $catrobat_version_name;

  /**
   * @ORM\Column(type="integer", options={"default":0})
   */
  protected $catrobat_version;

  /**
   * @ORM\Column(type="string", options={"default":""})
   */
  protected $upload_ip;

  /**
   * @ORM\Column(type="boolean", options={"default":true})
   */
  protected $visible;

  /**
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $private = false;

  /**
   * @ORM\Column(type="string", options={"default":"pocketcode"})
   */
  protected $flavor = 'pocketcode';

  /**
   * @ORM\Column(type="string", options={"default":""})
   */
  protected $upload_language;

  /**
   * @ORM\Column(type="integer", options={"default":0})
   */
  protected $filesize;

  /**
   * @ORM\Column(type="boolean", options={"default":true})
   */
  protected $remix_root;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $remix_migrated_at;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ProgramRemixRelation",
   *     mappedBy="descendant",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ProgramRemixRelation[]
   */
  protected $catrobat_remix_ancestor_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ProgramRemixBackwardRelation",
   *     mappedBy="child",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ProgramRemixBackwardRelation[]
   */
  protected $catrobat_remix_backward_parent_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ProgramRemixRelation",
   *     mappedBy="ancestor",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ProgramRemixRelation[]
   */
  protected $catrobat_remix_descendant_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ProgramRemixBackwardRelation",
   *     mappedBy="parent",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ProgramRemixBackwardRelation[]
   */
  protected $catrobat_remix_backward_child_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ScratchProgramRemixRelation",
   *     mappedBy="catrobat_child",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ScratchProgramRemixRelation[]
   */
  protected $scratch_remix_parent_relations;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\Catrobat\AppBundle\Entity\ProgramLike",
   *     mappedBy="program",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   * @var \Doctrine\Common\Collections\Collection|ProgramLike[]
   */
  protected $likes;

  /**
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $approved;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="approved_by_user", referencedColumnName="id", nullable=true)
   */
  protected $approved_by_user;

  /**
   * @ORM\ManyToOne(targetEntity="StarterCategory", inversedBy="programs")
   * @ORM\JoinColumn(nullable=true)
   */
  protected $category;

  /**
   * @ORM\Column(type="smallint", options={"default":0})
   */
  protected $apk_status = 0;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $apk_request_time;

  /**
   * @ORM\Column(type="integer", options={"default":0})
   */
  protected $apk_downloads = 0;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\GameJam", inversedBy="programs")
   * @ORM\JoinColumn(nullable=true)
   */
  protected $gamejam;

  /**
   * @ORM\Column(type="boolean", options={"default":false})
   */
  protected $gamejam_submission_accepted = false;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $gamejam_submission_date;

  /**
   * @ORM\OneToMany(targetEntity="ProgramDownloads", mappedBy="program", cascade={"remove"})
   */
  protected $program_downloads;

  /**
   * Program constructor.
   */
  public function __construct()
  {
    $this->program_downloads = new ArrayCollection();
    $this->tags = new ArrayCollection();
    $this->extensions = new ArrayCollection();
    $this->catrobat_remix_ancestor_relations = new ArrayCollection();
    $this->catrobat_remix_backward_parent_relations = new ArrayCollection();
    $this->catrobat_remix_descendant_relations = new ArrayCollection();
    $this->remix_migrated_at = null;
    $this->likes = new ArrayCollection();
  }

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $fb_post_id = '';

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $fb_post_url = '';

  /**
   * @return mixed
   */
  public function getFbPostId()
  {
    return $this->fb_post_id;
  }

  /**
   * @param mixed $fb_post_id
   */
  public function setFbPostId($fb_post_id)
  {
    $this->fb_post_id = $fb_post_id;
  }

  /**
   * @param mixed $approved_by_user
   */
  public function setApprovedByUser($approved_by_user)
  {
    $this->approved_by_user = $approved_by_user;
  }

  /**
   * @return mixed
   */
  public function getApprovedByUser()
  {
    return $this->approved_by_user;
  }

  /**
   * @ORM\PreUpdate
   *
   * @throws \Exception
   */
  public function updateLastModifiedTimestamp()
  {
    $this->setLastModifiedAt(new \DateTime());
  }

  /**
   * @ORM\PrePersist
   *
   * @throws \Exception
   */
  public function updateTimestamps()
  {
    $this->updateLastModifiedTimestamp();
    if ($this->getUploadedAt() == null)
    {
      $this->setUploadedAt(new \DateTime());
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function setInitialVersion()
  {
    $this->version = self::INITIAL_VERSION;
  }

  /**
   * @return bool
   */
  public function isInitialVersion()
  {
    return $this->version == self::INITIAL_VERSION;
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
   * Set name.
   *
   * @param string $name
   *
   * @return Program
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name.
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set description.
   *
   * @param string $description
   *
   * @return Program
   */
  public function setDescription($description)
  {
    $this->description = $description;

    return $this;
  }

  /**
   * Get description.
   *
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set views.
   *
   * @param int $views
   *
   * @return Program
   */
  public function setViews($views)
  {
    $this->views = $views;

    return $this;
  }

  /**
   * Get views.
   *
   * @return int
   */
  public function getViews()
  {
    return $this->views;
  }

  /**
   * Set version.
   *
   * @param int $version
   *
   * @return Program
   */
  public function setVersion($version)
  {
    $this->version = $version;

    return $this;
  }

  /**
   * Increment version.
   *
   * @return Program
   */
  public function incrementVersion()
  {
    $this->version += 1;

    return $this;
  }

  /**
   * Get Version.
   *
   * @return int
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Set downloads.
   *
   * @param int $downloads
   *
   * @return Program
   */
  public function setDownloads($downloads)
  {
    $this->downloads = $downloads;

    return $this;
  }

  /**
   * Get downloads.
   *
   * @return int
   */
  public function getDownloads()
  {
    return $this->downloads;
  }

  /**
   * Set uploaded_at.
   *
   * @param \DateTime $uploadedAt
   *
   * @return Program
   */
  public function setUploadedAt($uploadedAt)
  {
    $this->uploaded_at = $uploadedAt;

    return $this;
  }

  /**
   * Get uploaded_at.
   *
   * @return \DateTime
   */
  public function getUploadedAt()
  {
    return $this->uploaded_at;
  }

  /**
   * Set last_modified_at.
   *
   * @param \DateTime $lastModifiedAt
   *
   * @return Program
   */
  public function setLastModifiedAt($lastModifiedAt)
  {
    $this->last_modified_at = $lastModifiedAt;

    return $this;
  }

  /**
   * Get last_modified_at.
   *
   * @return \DateTime
   */
  public function getLastModifiedAt()
  {
    return $this->last_modified_at;
  }

  /**
   * @param \DateTime $remix_migrated_at
   *
   * @return Program
   */
  public function setRemixMigratedAt($remix_migrated_at)
  {
    $this->remix_migrated_at = $remix_migrated_at;

    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getRemixMigratedAt()
  {
    return $this->remix_migrated_at;
  }

  /**
   * Set user.
   *
   * @param User $user
   *
   * @return Program
   */
  public function setUser(User $user = null)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user.
   *
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->name . " (#" . $this->id . ")";
  }

  /**
   * Set language_version.
   *
   * @param string $languageVersion
   *
   * @return Program
   */
  public function setLanguageVersion($languageVersion)
  {
    $this->language_version = $languageVersion;

    return $this;
  }

  /**
   * Get language_version.
   *
   * @return string
   */
  public function getLanguageVersion()
  {
    return $this->language_version;
  }

  /**
   * Set catrobat_version_name.
   *
   * @param string $catrobatVersionName
   *
   * @return Program
   */
  public function setCatrobatVersionName($catrobatVersionName)
  {
    $this->catrobat_version_name = $catrobatVersionName;

    return $this;
  }

  /**
   * Get catrobat_version_name.
   *
   * @return string
   */
  public function getCatrobatVersionName()
  {
    return $this->catrobat_version_name;
  }

  /**
   * Set catrobat_version.
   *
   * @param int $catrobatVersion
   *
   * @return Program
   */
  public function setCatrobatVersion($catrobatVersion)
  {
    $this->catrobat_version = $catrobatVersion;

    return $this;
  }

  /**
   * Get catrobat_version.
   *
   * @return int
   */
  public function getCatrobatVersion()
  {
    return $this->catrobat_version;
  }

  /**
   * Set upload_ip.
   *
   * @param string $uploadIp
   *
   * @return Program
   */
  public function setUploadIp($uploadIp)
  {
    $this->upload_ip = $uploadIp;

    return $this;
  }

  /**
   * Get upload_ip.
   *
   * @return string
   */
  public function getUploadIp()
  {
    return $this->upload_ip;
  }

  /**
   * Set visible.
   *
   * @param bool $visible
   *
   * @return Program
   */
  public function setVisible($visible)
  {
    $this->visible = $visible;

    return $this;
  }

  /**
   * Set private.
   *
   * @param bool $private
   *
   * @return Program
   */
  public function setPrivate($private)
  {
    $this->private = $private;

    return $this;
  }

  /**
   * Get private.
   *
   * @return bool
   */
  public function getPrivate()
  {
    return $this->private;
  }

  /**
   * Get visible.
   *
   * @return bool
   */
  public function getVisible()
  {
    return $this->visible;
  }

  /**
   * Set upload_language.
   *
   * @param string $uploadLanguage
   *
   * @return Program
   */
  public function setUploadLanguage($uploadLanguage)
  {
    $this->upload_language = $uploadLanguage;

    return $this;
  }

  /**
   * Get upload_language.
   *
   * @return string
   */
  public function getUploadLanguage()
  {
    return $this->upload_language;
  }

  /**
   * Set filesize.
   *
   * @param int $filesize
   *
   * @return Program
   */
  public function setFilesize($filesize)
  {
    $this->filesize = $filesize;

    return $this;
  }

  /**
   * Get filesize.
   *
   * @return int
   */
  public function getFilesize()
  {
    return $this->filesize;
  }

  /**
   * Set if program is approved.
   *
   * @param
   *            boolean
   */
  public function setApproved($approved)
  {
    $this->approved = $approved;
  }

  /**
   * Get if program is approved.
   *
   * @return bool
   */
  public function getApproved()
  {
    return $this->approved;
  }

  /**
   * @return mixed
   */
  public function isVisible()
  {
    return $this->visible;
  }

  /**
   * @param $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getFlavor()
  {
    return $this->flavor;
  }

  /**
   * @param mixed $flavor
   */
  public function setFlavor($flavor)
  {
    $this->flavor = $flavor;
  }

  /**
   * @return mixed
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @param mixed $category
   */
  public function setCategory($category)
  {
    $this->category = $category;
  }

  /**
   * @param mixed $directory_hash
   */
  public function setExtractedDirectoryHash($directory_hash)
  {
    $this->directory_hash = $directory_hash;
  }

  /**
   * @return mixed
   */
  public function getExtractedDirectoryHash()
  {
    return $this->directory_hash;
  }

  /**
   * @return int
   */
  public function getApkStatus()
  {
    return $this->apk_status;
  }

  /**
   * @param $apk_status
   *
   * @return $this
   */
  public function setApkStatus($apk_status)
  {
    $this->apk_status = $apk_status;

    return $this;
  }

  /**
   * Set directory_hash.
   *
   * @param string $directoryHash
   *
   * @return Program
   */
  public function setDirectoryHash($directoryHash)
  {
    $this->directory_hash = $directoryHash;

    return $this;
  }

  /**
   * Get directory_hash.
   *
   * @return string
   */
  public function getDirectoryHash()
  {
    return $this->directory_hash;
  }

  /**
   * Set apk_request_time.
   *
   * @param \DateTime $apkRequestTime
   *
   * @return Program
   */
  public function setApkRequestTime($apkRequestTime)
  {
    $this->apk_request_time = $apkRequestTime;

    return $this;
  }

  /**
   * Get apk_request_time.
   *
   * @return \DateTime
   */
  public function getApkRequestTime()
  {
    return $this->apk_request_time;
  }

  /**
   * Set apk_downloads.
   *
   * @param int $apkDownloads
   *
   * @return Program
   */
  public function setApkDownloads($apkDownloads)
  {
    $this->apk_downloads = $apkDownloads;

    return $this;
  }

  /**
   * Get apk_downloads.
   *
   * @return int
   */
  public function getApkDownloads()
  {
    return $this->apk_downloads;
  }

  /**
   * Set gamejam
   *
   * @param GameJam $gamejam
   *
   * @return Program
   */
  public function setGamejam(GameJam $gamejam = null)
  {
    $this->gamejam = $gamejam;

    return $this;
  }

  /**
   * Get gamejam
   *
   * @return GameJam
   */
  public function getGamejam()
  {
    return $this->gamejam;
  }

  /**
   * Set accepted
   *
   * @param boolean $accepted
   *
   * @return Program
   */
  public function setAcceptedForGameJam($accepted)
  {
    $this->gamejam_submission_accepted = $accepted;

    return $this;
  }

  /**
   * @param $accepted
   */
  public function setGamejamSubmissionAccepted($accepted)
  {
    $this->gamejam_submission_accepted = $accepted;
  }

  /**
   * @return bool
   */
  public function getGamejamSubmissionAccepted()
  {
    return $this->gamejam_submission_accepted;
  }

  /**
   * Get accepted
   *
   * @return boolean
   */
  public function isAcceptedForGameJam()
  {
    return $this->gamejam_submission_accepted;
  }

  /**
   * @param $date
   */
  public function setGameJamSubmissionDate($date)
  {
    $this->gamejam_submission_date = $date;
  }

  /**
   * @return mixed
   */
  public function getGameJamSubmissionDate()
  {
    return $this->gamejam_submission_date;
  }

  /**
   * @return bool
   */
  public function getGamejam_submission_accepted()
  {
    return $this->gamejam_submission_accepted;
  }

  /**
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getProgramDownloads()
  {
    return $this->program_downloads;
  }

  /**
   * @param ProgramDownloads $program_download
   *
   * @return ProgramDownloads[]|\Doctrine\Common\Collections\Collection
   */
  public function addProgramDownloads(ProgramDownloads $program_download)
  {
    $this->program_downloads[] = $program_download;

    return $this->program_downloads;
  }

  /**
   * @return mixed
   */
  public function getFbPostUrl()
  {
    return $this->fb_post_url;
  }

  /**
   * @param mixed $fb_post_url
   */
  public function setFbPostUrl($fb_post_url)
  {
    $this->fb_post_url = $fb_post_url;
  }

  /**
   * @param Tag $tag
   */
  public function addTag(Tag $tag)
  {
    if ($this->tags->contains($tag))
    {
      return;
    }
    $this->tags->add($tag);
    $tag->addProgram($this);
  }

  /**
   * @param Tag $tag
   */
  public function removeTag(Tag $tag)
  {
    if (!$this->tags->contains($tag))
    {
      return;
    }
    $this->tags->removeElement($tag);
    $tag->removeProgram($this);
  }

  /**
   * @param Extension $extension
   */
  public function addExtension(Extension $extension)
  {
    if ($this->extensions->contains($extension))
    {
      return;
    }
    $this->extensions->add($extension);
    $extension->addProgram($this);
  }

  /**
   * @param Extension $extension
   */
  public function removeExtension(Extension $extension)
  {
    if (!$this->extensions->contains($extension))
    {
      return;
    }
    $this->extensions->removeElement($extension);
    $extension->removeProgram($this);
  }

  /**
   *
   */
  public function removeAllExtensions()
  {
    foreach ($this->extensions as $extension)
    {
      $this->removeExtension($extension);
    }
  }

  /**
   * Set as remix root.
   *
   * @param bool $is_remix_root
   *
   * @return Program
   */
  public function setRemixRoot($is_remix_root)
  {
    $this->remix_root = $is_remix_root;

    return $this;
  }

  /**
   * Get remix root.
   *
   * @return bool
   */
  public function isRemixRoot()
  {
    return $this->remix_root;
  }

  /**
   * @return ProgramRemixRelation[]|\Doctrine\Common\Collections\Collection
   */
  public function getCatrobatRemixAncestorRelations()
  {
    return ($this->catrobat_remix_ancestor_relations != null)
      ? $this->catrobat_remix_ancestor_relations
      : new ArrayCollection();
  }

  /**
   * @return ProgramRemixBackwardRelation[]|\Doctrine\Common\Collections\Collection
   */
  public function getCatrobatRemixBackwardParentRelations()
  {
    return ($this->catrobat_remix_backward_parent_relations != null)
      ? $this->catrobat_remix_backward_parent_relations
      : new ArrayCollection();
  }

  /**
   * @return ProgramRemixRelation[]|\Doctrine\Common\Collections\Collection
   */
  public function getCatrobatRemixDescendantRelations()
  {
    return ($this->catrobat_remix_descendant_relations != null)
      ? $this->catrobat_remix_descendant_relations
      : new ArrayCollection();
  }

  /**
   * @return array
   */
  public function getCatrobatRemixDescendantIds()
  {
    /**
     * @var $ra ProgramRemixRelation
     */

    $relations = $this->getCatrobatRemixDescendantRelations()->getValues();

    return array_unique(array_map(function ($ra) {
      return $ra->getDescendantId();
    }, $relations));
  }

  /**
   * @return ScratchProgramRemixRelation[]|\Doctrine\Common\Collections\Collection
   */
  public function getScratchRemixParentRelations()
  {
    return ($this->scratch_remix_parent_relations != null)
      ? $this->scratch_remix_parent_relations
      : new ArrayCollection();
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
   * @return Tag[]|\Doctrine\Common\Collections\Collection
   */
  public function getTags()
  {
    return $this->tags;
  }

  /**
   * @return Extension[]|\Doctrine\Common\Collections\Collection
   */
  public function getExtensions()
  {
    return $this->extensions;
  }
}
