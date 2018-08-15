<?php
namespace Catrobat\AppBundle\Services;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Exception\Exception;
use Catrobat\AppBundle\Entity\ClickStatistic;
use Catrobat\AppBundle\Entity\HomepageClickStatistic;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Catrobat\AppBundle\RecommenderSystem\RecommendedPageId;
use Geocoder\Exception\CollectionIsEmpty;
use Symfony\Bridge\Monolog\Logger;
use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;

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

    public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id,
                                                    $rec_by_program_id, $locale, $is_user_specific_recommendation = false)
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

        $results = $this->geocoder->geocode($ip);

        $result = $results->first();

        $latitude = $result->getCoordinates()->getLatitude();
        $longitude = $result->getCoordinates()->getLongitude();
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
        $program_download_statistic->setCountryCode($country_code);
        $program_download_statistic->setCountryName($country_name);
        $program_download_statistic->setLocale($locale);

        if ($rec_by_page_id != null && RecommendedPageId::isValidRecommendedPageId($rec_by_page_id)) {
            // all recommendations (except tag-recommendations -> see below)
            $program_download_statistic->setRecommendedByPageId($rec_by_page_id);
            $rec_by_program = ($rec_by_program_id != null) ? $this->programmanager->find($rec_by_program_id) : null;
            $program_download_statistic->setUserSpecificRecommendation($is_user_specific_recommendation);

            if ($rec_by_program != null) {
                $program_download_statistic->setRecommendedByProgram($rec_by_program);
            }
        } else if ($rec_tag_by_program_id != null) {
            // tag-recommendations
            $rec_program = $this->programmanager->find($rec_tag_by_program_id);
            if ($rec_program != null) {
                $program_download_statistic->setRecommendedFromProgramViaTag($rec_program);
            }
        }

        try {
            $this->entity_manager->persist($program_download_statistic);
            $program->addProgramDownloads($program_download_statistic);
            $this->entity_manager->persist($program);
            $this->entity_manager->flush();
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            return false;
        }

        return true;
    }

    public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name,
                                          $referrer, $locale = null, $is_recommended_program_a_scratch_program = false,
                                          $is_user_specific_recommendation = false)
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

        $results = $this->geocoder->geocode($ip);

        $result = $results->first();

        $country_code = $result->getCountry()->getCode();
        $country_name = $result->getCountry()->getName();

        $this->logger->addDebug('Received geocoded data - , country code: ' . $country_code . ', country name: ' . $country_name);

        if (in_array($type, ['programs', 'rec_homepage', 'rec_remix_graph', 'rec_remix_notification', 'rec_specific_programs', 'show_remix_graph'])) {
            $click_statistics = new ClickStatistic();
            $click_statistics->setType($type);
            $click_statistics->setUserAgent($user_agent);
            $click_statistics->setUser($user);
            $click_statistics->setReferrer($referrer);
            $click_statistics->setClickedAt(new \DateTime());
            $click_statistics->setIp($ip);
            $click_statistics->setCountryCode($country_code);
            $click_statistics->setCountryName($country_name);
            $click_statistics->setLocale($locale);
            $click_statistics->setUserSpecificRecommendation($is_user_specific_recommendation);

            if ($rec_from_id > 0) {
                $recommended_from = $this->programmanager->find($rec_from_id);
                $click_statistics->setRecommendedFromProgram($recommended_from);
            }

            if ($is_recommended_program_a_scratch_program) {
                $click_statistics->setScratchProgramId($rec_program_id);
            } else {
                $click_statistics->setProgram($this->programmanager->find($rec_program_id));
            }

            $this->entity_manager->persist($click_statistics);
            $this->entity_manager->flush();

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
            $click_statistics->setCountryCode($country_code);
            $click_statistics->setCountryName($country_name);
            $click_statistics->setLocale($locale);

            $this->entity_manager->persist($click_statistics);
            $this->entity_manager->flush();


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
            $click_statistics->setCountryCode($country_code);
            $click_statistics->setCountryName($country_name);
            $click_statistics->setLocale($locale);

            $this->entity_manager->persist($click_statistics);
            $this->entity_manager->flush();
        }

        return true;
    }

    public function createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale)
    {
        $ip = $this->getOriginalClientIp($request);
        $user_agent = $this->getUserAgent($request);
        $session_user = $this->getSessionUser();

        if ($session_user === 'anon.') {
            $user = null;
        } else {
            $user = $session_user;
        }

        $this->logger->addDebug('create click stats for program id: ' . $program_id . ', ip: ' . $ip .
            ', user agent: ' . $user_agent . ', referrer: ' . $referrer);
        if ($user !== null) {
            $this->logger->addDebug('user: ' . $user->getUsername());
        } else {
            $this->logger->addDebug('user: anon.');
        }

        $homepage_click_statistics = new HomepageClickStatistic();
        $homepage_click_statistics->setType($type);
        $homepage_click_statistics->setUserAgent($user_agent);
        $homepage_click_statistics->setUser($user);
        $homepage_click_statistics->setReferrer($referrer);
        $homepage_click_statistics->setClickedAt(new \DateTime());
        $homepage_click_statistics->setIp($ip);
        $homepage_click_statistics->setLocale($locale);
        $program = $this->programmanager->find($program_id);
        $homepage_click_statistics->setProgram($program);
        $this->entity_manager->persist($homepage_click_statistics);
        $this->entity_manager->flush();
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
}
