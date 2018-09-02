<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="program_downloads")
 */
class ProgramDownloads
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="program_downloads")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=false)
   * @var Program
   */
  protected $program;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected $recommended_by_page_id;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="recommended_by_program_id", referencedColumnName="id", nullable=true)
   */
  protected $recommended_by_program;

  /**
   * @ORM\Column(type="boolean", options={"default":false}, nullable=true)
   */
  protected $user_specific_recommendation = false;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="rec_from_program_id", referencedColumnName="id", nullable=true)
   */
  protected $recommended_from_program_via_tag;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $downloaded_at;

  /**
   * @ORM\Column(type="text", options={"default":""})
   */
  protected $ip;

  /**
   * @ORM\Column(type="text", options={"default":""}, nullable=true)
   */
  protected $country_code;

  /**
   * @ORM\Column(type="text", options={"default":""}, nullable=true)
   */
  protected $country_name;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $locale;

  /**
   * @ORM\Column(type="string", options={"default":""}, nullable=true)
   */
  protected $user_agent;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected $user;

  /**
   * @ORM\Column(type="string", options={"default":""}, nullable=true)
   */
  protected $referrer;

  /**
   * @return mixed
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @param mixed $program
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }

  /**
   * @return mixed
   */
  public function getRecommendedFromProgramViaTag()
  {
    return $this->recommended_from_program_via_tag;
  }

  /**
   * @param mixed $recommended_from_program_via_tag
   */
  public function setRecommendedFromProgramViaTag($recommended_from_program_via_tag)
  {
    $this->recommended_from_program_via_tag = $recommended_from_program_via_tag;
  }

  /**
   * @return mixed
   */
  public function getDownloadedAt()
  {
    return $this->downloaded_at;
  }

  /**
   * @param mixed $downloaded_at
   */
  public function setDownloadedAt($downloaded_at)
  {
    $this->downloaded_at = $downloaded_at;
  }

  /**
   * @return mixed
   */
  public function getIp()
  {
    return $this->ip;
  }

  /**
   * @param mixed $ip
   */
  public function setIp($ip)
  {
    $this->ip = $ip;
  }

  /**
   * @return mixed
   */
  public function getCountryCode()
  {
    return $this->country_code;
  }

  /**
   * @param mixed $country_code
   */
  public function setCountryCode($country_code)
  {
    $this->country_code = $country_code;
  }

  /**
   * @return mixed
   */
  public function getCountryName()
  {
    return $this->country_name;
  }

  /**
   * @param mixed $country_name
   */
  public function setCountryName($country_name)
  {
    $this->country_name = $country_name;
  }

  /**
   * @return mixed
   */
  public function getLocale()
  {
    return $this->locale;
  }

  /**
   * @param mixed $locale
   */
  public function setLocale($locale)
  {
    $this->locale = $locale;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getUserAgent()
  {
    return $this->user_agent;
  }

  /**
   * @param mixed $user_agent
   */
  public function setUserAgent($user_agent)
  {
    $this->user_agent = $user_agent;
  }

  /**
   * @return mixed
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param mixed $user
   */
  public function setUser($user)
  {
    $this->user = $user;
  }

  /**
   * @return mixed
   */
  public function getReferrer()
  {
    return $this->referrer;
  }

  /**
   * @return int
   */
  public function getRecommendedByPageId()
  {
    return $this->recommended_by_page_id;
  }

  /**
   * @param int $recommended_by_page_id
   */
  public function setRecommendedByPageId($recommended_by_page_id)
  {
    $this->recommended_by_page_id = $recommended_by_page_id;
  }

  /**
   * @return Program
   */
  public function getRecommendedByProgram()
  {
    return $this->recommended_by_program;
  }

  /**
   * @param Program $recommended_by_program
   */
  public function setRecommendedByProgram($recommended_by_program)
  {
    $this->recommended_by_program = $recommended_by_program;
  }

  /**
   * @return bool
   */
  public function getUserSpecificRecommendation()
  {
    return $this->user_specific_recommendation;
  }

  /**
   * @param bool $is_user_specific_recommendation
   */
  public function setUserSpecificRecommendation($is_user_specific_recommendation)
  {
    $this->user_specific_recommendation = $is_user_specific_recommendation;
  }

  /**
   * @param mixed $referrer
   */
  public function setReferrer($referrer)
  {
    $this->referrer = $referrer;
  }
}
