<?php
namespace Catrobat\AppBundle\Services;

use Behat\Mink\Exception\Exception;
use Catrobat\AppBundle\Entity\ClickStatistic;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Geocoder\Exception\CollectionIsEmpty;
use Symfony\Bridge\Monolog\Logger;
use Catrobat\AppBundle\Entity\ProgramManager;

class StatisticsService
{
    private $programmanager;
    private $entity_manager;
    private $geocoder;
    private $logger;
    private $security_token_storage;

    public function __construct(ProgramManager $programmanager, $entity_manager, $geocoder, Logger $logger, $security_token_storage)
    {
        $this->programmanager = $programmanager;
        $this->entity_manager = $entity_manager;
        $this->geocoder = $geocoder;
        $this->logger = $logger;
        $this->security_token_storage = $security_token_storage;
    }

    public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_id)
    {

        $ip = $this->getOriginalClientIp($request);
        $user_agent = $this->getUserAgent($request);
        $session_user = $this->getSessionUser();

        if ($session_user === 'anon.') {
            $user = null;
        } else {
            $user = $session_user;
        }

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

        if ($rec_id != null) {
            $rec_program = $this->programmanager->find($rec_id);
            if ($rec_program != null) {
                $program_download_statistic->setRecommendedFromProgram($rec_program);
            }
        }

        $this->entity_manager->persist($program_download_statistic);
        $program->addProgramDownloads($program_download_statistic);
        $this->entity_manager->persist($program);
        $this->entity_manager->flush();

        $this->addGoogleMapsGeocodeData($latitude, $longitude, $program_download_statistic);
        return true;
    }

    public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name, $referrer)
    {

        $ip = $this->getOriginalClientIp($request);
        $user_agent = $this->getUserAgent($request);
        $session_user = $this->getSessionUser();

        if ($session_user === 'anon.') {
            $user = null;
        } else {
            $user = $session_user;
        }

        $this->logger->addDebug('create download stats for program id: ' . $rec_from_id . ', ip: ' . $ip .
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

        if($type == "programs") {

            $recommended_from = $this->programmanager->find($rec_from_id);
            $recommended_program = $this->programmanager->find($rec_program_id);

            $click_statistics = new ClickStatistic();
            $click_statistics->setType($type);
            $click_statistics->setRecommendedFromProgram($recommended_from);
            $click_statistics->setProgram($recommended_program);
            $click_statistics->setUserAgent($user_agent);
            $click_statistics->setUser($user);
            $click_statistics->setReferrer($referrer);
            $click_statistics->setClickedAt(new \DateTime());
            $click_statistics->setIp($ip);
            $click_statistics->setLatitude($latitude);
            $click_statistics->setLongitude($longitude);
            $click_statistics->setCountryCode($country_code);
            $click_statistics->setCountryName($country_name);

            $this->entity_manager->persist($click_statistics);
            $this->entity_manager->flush();

            $this->addGoogleMapsGeocodeData($latitude, $longitude, $click_statistics);

        } else if ($type == "tags") {

            $tag_repo = $this->entity_manager->getRepository("\Catrobat\AppBundle\Entity\Tag");
            $tag = $tag_repo->find($tag_id);

            $click_statistics = new ClickStatistic();
            $click_statistics->setType($type);
            $click_statistics->setTag($tag);
            $click_statistics->setUserAgent($user_agent);
            $click_statistics->setUser($user);
            $click_statistics->setReferrer($referrer);
            $click_statistics->setClickedAt(new \DateTime());
            $click_statistics->setIp($ip);
            $click_statistics->setLatitude($latitude);
            $click_statistics->setLongitude($longitude);
            $click_statistics->setCountryCode($country_code);
            $click_statistics->setCountryName($country_name);

            $this->entity_manager->persist($click_statistics);
            $this->entity_manager->flush();

            $this->addGoogleMapsGeocodeData($latitude, $longitude, $click_statistics);

        } else if ($type == "extensions") {

            $extensions_repo = $this->entity_manager->getRepository("\Catrobat\AppBundle\Entity\Extension");

            $extension = $extensions_repo->getExtensionByName($extension_name);

            if ($extension == null){
                return;
            }

            $click_statistics = new ClickStatistic();
            $click_statistics->setType($type);
            $click_statistics->setExtension($extension[0]);
            $click_statistics->setUserAgent($user_agent);
            $click_statistics->setUser($user);
            $click_statistics->setReferrer($referrer);
            $click_statistics->setClickedAt(new \DateTime());
            $click_statistics->setIp($ip);
            $click_statistics->setLatitude($latitude);
            $click_statistics->setLongitude($longitude);
            $click_statistics->setCountryCode($country_code);
            $click_statistics->setCountryName($country_name);

            $this->entity_manager->persist($click_statistics);
            $this->entity_manager->flush();

            $this->addGoogleMapsGeocodeData($latitude, $longitude, $click_statistics);
        }

        return true;
    }

    private function getUserAgent($request)
    {
        return $request->headers->get('User-Agent');
    }

    private function getSessionUser()
    {
        return $this->security_token_storage->getToken()->getUser();
    }

    private function getOriginalClientIp($request)
    {
        $ip = $request->getClientIp();
        if (strpos($ip,',') !== false) {
            $ip = substr($ip,0,strpos($ip,','));
        }
        return $ip;
    }

    private function addGoogleMapsGeocodeData($latitude, $longitude, $some_statistic) {

        $results_google = $this->geocoder
            ->using('google_maps')
            ->reverse($latitude, $longitude);

        try {
            $result = $results_google->first();

        } catch (CollectionIsEmpty $collectionIsEmpty) {
            return;
        }

        $street = $result->getStreetName() . ' ' . $result->getStreetNumber();
        $postal_code = $result->getPostalCode();
        $locality = $result->getLocality();

        $this->logger->addDebug('Received Google Maps data - street: ' . $street . ', postal code: ' . $postal_code .
            ', locality: ' . $locality);

        $some_statistic->setStreet($street);
        $some_statistic->setPostalCode($postal_code);
        $some_statistic->setLocality($locality);

        $this->entity_manager->persist($some_statistic);
        $this->entity_manager->flush();

    }
}
