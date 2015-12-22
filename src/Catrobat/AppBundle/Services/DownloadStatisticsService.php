<?php
namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Symfony\Bridge\Monolog\Logger;
use Catrobat\AppBundle\Entity\ProgramManager;

class DownloadStatisticsService
{
    private $programmanager;
    private $entity_manager;
    private $geocoder;
    private $logger;

    public function __construct(ProgramManager $programmanager, $entity_manager, $geocoder, Logger $logger)
    {
        $this->programmanager = $programmanager;
        $this->entity_manager = $entity_manager;
        $this->geocoder = $geocoder;
        $this->logger = $logger;
    }

    public function createProgramDownloadStatistics($program_id, $ip, $user_agent, $user, $referrer)
    {
        $this->logger->addDebug('create download stats for program id: ' . $program_id . ', ip: ' . $ip .
            ', user agent: ' . $user_agent . ', referrer: ' . $referrer);
        if ($user !== null) {
            $this->logger->addDebug('user: ' . $user->getUsername());
        } else {
            $this->logger->addDebug('user: anon.');
        }

        $results = $this->geocoder
            ->using('geo_plugin')
            ->geocode($ip);

        $result = $results->first();

        $latitude = $result->getLatitude();
        $longitude = $result->getLongitude();
        $country_code = $result->getCountry()->getCode();
        $country_name = $result->getCountry()->getName();

        $this->logger->addDebug('Received geocoded data - latitude: ' . $latitude . ', longitude: ' . $longitude .
            ', country code: ' . $country_code . ', country name: ' . $country_name);

        $program = $this->programmanager->find($program_id);

        $program_download_statistic = new ProgramDownloads();
        $program_download_statistic->setProgram($program);
        $program_download_statistic->setUserAgent($user_agent);
        $program_download_statistic->setUser($user);
        $program_download_statistic->setReferrer($referrer);
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

        $this->logger->addDebug('Received Google Maps data - street: ' . $street . ', postal code: ' . $postal_code .
            ', locality: ' . $locality);

        $program_download_statistic->setStreet($street);
        $program_download_statistic->setPostalCode($postal_code);
        $program_download_statistic->setLocality($locality);

        $this->entity_manager->persist($program_download_statistic);
        $this->entity_manager->flush();
    }
}
