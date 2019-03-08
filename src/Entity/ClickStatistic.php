<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="click_statistics")
 */
class ClickStatistic
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="text", options={"default":""}, nullable=false)
   */
  protected $type;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Tag", inversedBy="tags")
   * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=true)
   */
  protected $tag;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Extension", inversedBy="extension")
   * @ORM\JoinColumn(name="extension_id", referencedColumnName="id", nullable=true)
   */
  protected $extension;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   * @var Program
   */
  protected $program;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected $scratch_program_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="rec_from_program_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected $recommended_from_program;

  /**
   * @ORM\Column(type="boolean", options={"default":false}, nullable=true)
   */
  protected $user_specific_recommendation = false;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $clicked_at;

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
   * @ORM\ManyToOne(targetEntity="\App\Entity\User")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected $user;

  /**
   * @ORM\Column(type="string", options={"default":""}, nullable=true)
   */
  protected $referrer;

  /**
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @param Program $program
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }

  /**
   * @return int
   */
  public function getScratchProgramId()
  {
    return $this->scratch_program_id;
  }

  /**
   * @param int $scratch_program_id
   */
  public function setScratchProgramId($scratch_program_id)
  {
    $this->scratch_program_id = $scratch_program_id;
  }

  /**
   * @return Program
   */
  public function getRecommendedFromProgram()
  {
    return $this->recommended_from_program;
  }

  /**
   * @param Program $recommended_from_program
   */
  public function setRecommendedFromProgram($recommended_from_program)
  {
    $this->recommended_from_program = $recommended_from_program;
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
   * @param mixed $latitude
   */
  public function setLatitude($latitude)
  {
    $this->latitude = $latitude;
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
   * @param mixed $referrer
   */
  public function setReferrer($referrer)
  {
    $this->referrer = $referrer;
  }

  /**
   * @return mixed
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @param mixed $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * @return mixed
   */
  public function getTag()
  {
    return $this->tag;
  }

  /**
   * @param mixed $tag
   */
  public function setTag($tag)
  {
    $this->tag = $tag;
  }

  /**
   * @return mixed
   */
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * @param mixed $extension
   */
  public function setExtension($extension)
  {
    $this->extension = $extension;
  }

  /**
   * @return mixed
   */
  public function getClickedAt()
  {
    return $this->clicked_at;
  }

  /**
   * @param mixed $clicked_at
   */
  public function setClickedAt($clicked_at)
  {
    $this->clicked_at = $clicked_at;
  }
}
