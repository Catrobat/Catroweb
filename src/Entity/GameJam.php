<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameJamRepository")
 */
class GameJam
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", length=300)
   */
  protected ?string $name = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $form_url = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $start = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $end = null;

  /**
   * @ORM\OneToMany(targetEntity="Program", mappedBy="gamejam", fetch="EXTRA_LAZY")
   */
  protected Collection $programs;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  protected ?string $hashtag = null;

  /**
   * @ORM\ManyToMany(targetEntity="Program")
   * @ORM\JoinTable(name="gamejams_sampleprograms",
   *     joinColumns={@ORM\JoinColumn(name="gamejam_id", referencedColumnName="id")},
   *     inverseJoinColumns={@ORM\JoinColumn(name="program_id", referencedColumnName="id")}
   * )
   */
  private Collection $sample_programs;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private ?string $flavor = null;

  public function __construct()
  {
    $this->programs = new ArrayCollection();
    $this->sample_programs = new ArrayCollection();
  }

  public function __toString()
  {
    return $this->getName() ?? '';
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setName(string $name): GameJam
  {
    $this->name = $name;

    return $this;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setFormUrl(string $formUrl): GameJam
  {
    $this->form_url = $formUrl;

    return $this;
  }

  public function getFormUrl(): ?string
  {
    return $this->form_url;
  }

  public function setStart(DateTime $start): GameJam
  {
    $this->start = $start;

    return $this;
  }

  public function getStart(): ?DateTime
  {
    return $this->start;
  }

  public function setEnd(DateTime $end): GameJam
  {
    $this->end = $end;

    return $this;
  }

  public function getEnd(): ?DateTime
  {
    return $this->end;
  }

  public function addProgram(Program $program): GameJam
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

  public function addSampleProgram(Program $sampleProgram): GameJam
  {
    $this->sample_programs[] = $sampleProgram;

    return $this;
  }

  public function removeSampleProgram(Program $sampleProgram): void
  {
    $this->sample_programs->removeElement($sampleProgram);
  }

  public function getSamplePrograms(): Collection
  {
    return $this->sample_programs;
  }

  public function getHashtag(): ?string
  {
    return $this->hashtag;
  }

  public function setHashtag(?string $hashtag): void
  {
    $this->hashtag = $hashtag;
  }

  public function getFlavor(): ?string
  {
    return $this->flavor;
  }

  public function setFlavor(?string $flavor): void
  {
    $this->flavor = $flavor;
  }
}
