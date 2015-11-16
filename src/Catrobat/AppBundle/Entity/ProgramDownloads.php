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
     * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="program")
     * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
     */
    protected $program;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $downloaded_at;

    /**
     * @ORM\Column(type="text", options={"default":""})
     */
    protected $ip;

    /**
     * @ORM\Column(type="text", options={"default":""})
     */
    protected $latitude;

    /**
     * @ORM\Column(type="text", options={"default":""})
     */
    protected $longitude;

    /**
     * @ORM\Column(type="text", options={"default":""})
     */
    protected $country_code;

    /**
     * @ORM\Column(type="text", options={"default":""})
     */
    protected $country_name;

    /**
     * @ORM\Column(type="string", options={"default":""})
     */
    protected $street;

    /**
     * @ORM\Column(type="string", options={"default":""})
     */
    protected $postal_code;

    /**
     * @ORM\Column(type="string", options={"default":""})
     */
    protected $locality = 0;

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
    public function getLatitude()
    {
        return $this->latitude;
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
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
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
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @return mixed
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @param mixed $locality
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;
    }
}
