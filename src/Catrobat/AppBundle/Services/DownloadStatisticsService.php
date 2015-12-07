<?php
namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Symfony\Component\DependencyInjection\Container;
use Catrobat\AppBundle\Entity\ProgramManager;

class DownloadStatisticsService
{
    private $programmanager;
    private $entity_manager;
    private $geocoder;

    public function __construct(ProgramManager $programmanager, $entity_manager, $geocoder)
    {
        $this->programmanager = $programmanager;
        $this->entity_manager = $entity_manager;
        $this->geocoder = $geocoder;
    }

    public function createProgramDownloadStatistics($program_id, $ip, $user_agent)
    {
        $results = $this->geocoder
            ->using('host_ip')
            ->geocode($ip);

        $result = $results->first();

        $latitude = $result->getLatitude();
        $longitude = $result->getLongitude();
        $country_code = $result->getCountry()->getCode();
        $country_name = $result->getCountry()->getName();

        $program = $this->programmanager->find($program_id);

        $program_download_statistic = new ProgramDownloads();
        $program_download_statistic->setProgram($program);
        $program_download_statistic->setUserAgent($user_agent);
        $program_download_statistic->setDownloadedAt(new \DateTime());
        $program_download_statistic->setIp($ip);
        $program_download_statistic->setLatitude($latitude);
        $program_download_statistic->setLongitude($longitude);
        $program_download_statistic->setCountryCode($country_code);
        $program_download_statistic->setCountryName($country_name);

        $this->entity_manager->persist($program_download_statistic);
        $program->addProgramDownloads($program_download_statistic);
        $this->entity_manager->persist($program);
        $this->entity_manager->flush();

        $this->addGoogleMapsGeocodeData($latitude, $longitude, $program_download_statistic);
        return true;
    }

    private function addGoogleMapsGeocodeData($latitude, $longitude, $program_download_statistic) {
        $results_google = $this->geocoder
            ->using('google_maps')
            ->reverse($latitude, $longitude);

        $result = $results_google->first();

        $street = $result->getStreetName() . ' ' . $result->getStreetNumber();
        $postal_code = $result->getPostalCode();
        $locality = $result->getLocality();

        $program_download_statistic->setStreet($street);
        $program_download_statistic->setPostalCode($postal_code);
        $program_download_statistic->setLocality($locality);

        $this->entity_manager->persist($program_download_statistic);
        $this->entity_manager->flush();
    }
}
