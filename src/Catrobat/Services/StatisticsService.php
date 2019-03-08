<?php

namespace App\Catrobat\Services;

use App\Entity\ClickStatistic;
use App\Entity\HomepageClickStatistic;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Catrobat\RecommenderSystem\RecommendedPageId;
use Doctrine\ORM\EntityManager;
use Geocoder\Geocoder;
use Symfony\Bridge\Monolog\Logger;
use App\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;


/**
 * Class StatisticsService
 * @package App\Catrobat\Services
 */
class StatisticsService
{
  /**
   * @var ProgramManager
   */
  private $programmanager;
  /**
   * @var EntityManager
   */
  private $entity_manager;
  /**
   * @var Geocoder
   */
  private $geocoder;
  /**
   * @var Logger
   */
  private $logger;
  /**
   * @var TokenStorage
   */
  private $security_token_storage;

  /**
   * StatisticsService constructor.
   *
   * @param ProgramManager $programmanager
   * @param                $entity_manager
   * @param                $geocoder
   * @param Logger         $logger
   * @param                $security_token_storage
   */
  public function __construct(ProgramManager $programmanager, $entity_manager, $geocoder,
                              Logger $logger, $security_token_storage)
  {
    $this->programmanager = $programmanager;
    $this->entity_manager = $entity_manager;
    $this->geocoder = $geocoder;
    $this->logger = $logger;
    $this->security_token_storage = $security_token_storage;
  }

  /**
   * @param      $request
   * @param      $program_id
   * @param      $referrer
   * @param      $rec_tag_by_program_id
   * @param      $rec_by_page_id
   * @param      $rec_by_program_id
   * @param      $locale
   * @param bool $is_user_specific_recommendation
   *
   * @return bool
   * @throws \Geocoder\Exception\Exception
   * @throws \Exception
   */
  public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id,
                                                  $rec_by_program_id, $locale, $is_user_specific_recommendation = false)
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    if ($session_user === 'anon.')
    {
      $user = null;
    }
    else
    {
      $user = $session_user;
    }

    $this->logger->addDebug('create download stats for program id: ' . $program_id . ', ip: ' . $ip .
      ', user agent: ' . $user_agent . ', referrer: ' . $referrer);
    if ($user !== null)
    {
      $this->logger->addDebug('user: ' . $user->getUsername());
    }
    else
    {
      $this->logger->addDebug('user: anon.');
    }

    $results = $this->geocoder->geocode($ip);

    $result = $results->first();

    $latitude = $result->getCoordinates()->getLatitude();
    $longitude = $result->getCoordinates()->getLongitude();
    $country_code = $result->getCountry()->getCode();
    $country_name = $result->getCountry()->getName();
    if ($country_name == '' && $country_code != '')
    {
      $country_name = $this->countryCodeToCountry($country_code);
    }

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

    if ($rec_by_page_id != null && RecommendedPageId::isValidRecommendedPageId($rec_by_page_id))
    {
      // all recommendations (except tag-recommendations -> see below)
      $program_download_statistic->setRecommendedByPageId($rec_by_page_id);
      $rec_by_program = ($rec_by_program_id != null) ? $this->programmanager->find($rec_by_program_id) : null;
      $program_download_statistic->setUserSpecificRecommendation($is_user_specific_recommendation);

      if ($rec_by_program != null)
      {
        /**
         * @var $rec_by_program Program
         */
        $program_download_statistic->setRecommendedByProgram($rec_by_program);
      }
    }
    else
    {
      if ($rec_tag_by_program_id != null)
      {
        // tag-recommendations
        $rec_program = $this->programmanager->find($rec_tag_by_program_id);
        if ($rec_program != null)
        {
          $program_download_statistic->setRecommendedFromProgramViaTag($rec_program);
        }
      }
    }

    try
    {
      $this->entity_manager->persist($program_download_statistic);
      $program->addProgramDownloads($program_download_statistic);
      $this->entity_manager->persist($program);
      $this->entity_manager->flush();
    } catch (\Exception $e)
    {
      $this->logger->addError($e->getMessage());

      return false;
    }

    return true;
  }

  /**
   * @return ProgramManager
   */
  public function getProgrammanager(): ProgramManager
  {
    return $this->programmanager;
  }

  /**
   * @param ProgramManager $programmanager
   */
  public function setProgrammanager(ProgramManager $programmanager): void
  {
    $this->programmanager = $programmanager;
  }

  /**
   * @return mixed
   */
  public function getEntityManager()
  {
    return $this->entity_manager;
  }

  /**
   * @param mixed $entity_manager
   */
  public function setEntityManager($entity_manager): void
  {
    $this->entity_manager = $entity_manager;
  }

  /**
   * @return mixed
   */
  public function getGeocoder()
  {
    return $this->geocoder;
  }

  /**
   * @param mixed $geocoder
   */
  public function setGeocoder($geocoder): void
  {
    $this->geocoder = $geocoder;
  }

  /**
   * @return Logger
   */
  public function getLogger(): Logger
  {
    return $this->logger;
  }

  /**
   * @param Logger $logger
   */
  public function setLogger(Logger $logger): void
  {
    $this->logger = $logger;
  }

  /**
   * @return mixed
   */
  public function getSecurityTokenStorage()
  {
    return $this->security_token_storage;
  }

  /**
   * @param mixed $security_token_storage
   */
  public function setSecurityTokenStorage($security_token_storage): void
  {
    $this->security_token_storage = $security_token_storage;
  }

  /**
   * @param      $request
   * @param      $type
   * @param      $rec_from_id
   * @param      $rec_program_id
   * @param      $tag_id
   * @param      $extension_name
   * @param      $referrer
   * @param null $locale
   * @param bool $is_recommended_program_a_scratch_program
   * @param bool $is_user_specific_recommendation
   *
   * @return bool
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Geocoder\Exception\Exception
   * @throws \Exception
   */
  public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name,
                                        $referrer, $locale = null, $is_recommended_program_a_scratch_program = false,
                                        $is_user_specific_recommendation = false)
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    if ($session_user === 'anon.')
    {
      $user = null;
    }
    else
    {
      $user = $session_user;
    }

    $this->logger->addDebug('create download stats for program id: ' . $rec_from_id . ', ip: ' . $ip .
      ', user agent: ' . $user_agent . ', referrer: ' . $referrer);
    if ($user !== null)
    {
      $this->logger->addDebug('user: ' . $user->getUsername());
    }
    else
    {
      $this->logger->addDebug('user: anon.');
    }

    $results = $this->geocoder->geocode($ip);

    $result = $results->first();

    $country_code = $result->getCountry()->getCode();
    $country_name = $result->getCountry()->getName();
    if ($country_name == '' && $country_code != '')
    {
      $country_name = $this->countryCodeToCountry($country_code);
    }

    $this->logger->addDebug('Received geocoded data - , country code: ' . $country_code . ', country name: ' . $country_name);

    if (in_array($type, ['programs', 'rec_homepage', 'rec_remix_graph', 'rec_remix_notification', 'rec_specific_programs', 'show_remix_graph']))
    {
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

      if ($rec_from_id > 0)
      {
        /**
         * @var $recommended_from Program
         */
        $recommended_from = $this->programmanager->find($rec_from_id);
        $click_statistics->setRecommendedFromProgram($recommended_from);
      }

      if ($is_recommended_program_a_scratch_program)
      {
        $click_statistics->setScratchProgramId($rec_program_id);
      }
      else
      {
        /**
         * @var $recommended_program Program
         */
        $recommended_program = $this->programmanager->find($rec_program_id);
        $click_statistics->setProgram($recommended_program);
      }

      $this->entity_manager->persist($click_statistics);
      $this->entity_manager->flush();
    }
    else
    {
      if ($type == "tags")
      {
        $tag_repo = $this->entity_manager->getRepository("\App\Entity\Tag");
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
      }
      else
      {
        if ($type == "extensions")
        {
          $extensions_repo = $this->entity_manager->getRepository("\App\Entity\Extension");

          $extension = $extensions_repo->getExtensionByName($extension_name);

          if ($extension == null)
          {
            return false;
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
      }
    }

    return true;
  }

  /**
   * @param $request
   * @param $type
   * @param $program_id
   * @param $referrer
   * @param $locale
   *
   * @return bool
   * @throws \Exception
   */
  public function createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale)
  {
    /**
     * @var $program Program
     */
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    if ($session_user === 'anon.')
    {
      $user = null;
    }
    else
    {
      $user = $session_user;
    }

    $this->logger->addDebug('create click stats for program id: ' . $program_id . ', ip: ' . $ip .
      ', user agent: ' . $user_agent . ', referrer: ' . $referrer);
    if ($user !== null)
    {
      $this->logger->addDebug('user: ' . $user->getUsername());
    }
    else
    {
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

  /**
   * @param $request
   *
   * @return mixed
   */
  private function getUserAgent($request)
  {
    return $request->headers->get('User-Agent');
  }

  /**
   * @return mixed
   */
  private function getSessionUser()
  {
    return $this->security_token_storage->getToken()->getUser();
  }

  /**
   * @param $request Request
   *
   * @return bool|string
   */
  private function getOriginalClientIp($request)
  {
    $ip = $request->getClientIp();
    if (strpos($ip, ',') !== false)
    {
      $ip = substr($ip, 0, strpos($ip, ','));
    }

    return $ip;
  }

  /**
   * @param $code
   *
   * @return string
   */
  function countryCodeToCountry($code)
  {
    $code = strtoupper($code);
    if ($code == 'AF')
    {
      return 'Afghanistan';
    }
    if ($code == 'AX')
    {
      return 'Aland Islands';
    }
    if ($code == 'AL')
    {
      return 'Albania';
    }
    if ($code == 'DZ')
    {
      return 'Algeria';
    }
    if ($code == 'AS')
    {
      return 'American Samoa';
    }
    if ($code == 'AD')
    {
      return 'Andorra';
    }
    if ($code == 'AO')
    {
      return 'Angola';
    }
    if ($code == 'AI')
    {
      return 'Anguilla';
    }
    if ($code == 'AQ')
    {
      return 'Antarctica';
    }
    if ($code == 'AG')
    {
      return 'Antigua and Barbuda';
    }
    if ($code == 'AR')
    {
      return 'Argentina';
    }
    if ($code == 'AM')
    {
      return 'Armenia';
    }
    if ($code == 'AW')
    {
      return 'Aruba';
    }
    if ($code == 'AU')
    {
      return 'Australia';
    }
    if ($code == 'AT')
    {
      return 'Austria';
    }
    if ($code == 'AZ')
    {
      return 'Azerbaijan';
    }
    if ($code == 'BS')
    {
      return 'Bahamas the';
    }
    if ($code == 'BH')
    {
      return 'Bahrain';
    }
    if ($code == 'BD')
    {
      return 'Bangladesh';
    }
    if ($code == 'BB')
    {
      return 'Barbados';
    }
    if ($code == 'BY')
    {
      return 'Belarus';
    }
    if ($code == 'BE')
    {
      return 'Belgium';
    }
    if ($code == 'BZ')
    {
      return 'Belize';
    }
    if ($code == 'BJ')
    {
      return 'Benin';
    }
    if ($code == 'BM')
    {
      return 'Bermuda';
    }
    if ($code == 'BT')
    {
      return 'Bhutan';
    }
    if ($code == 'BO')
    {
      return 'Bolivia';
    }
    if ($code == 'BA')
    {
      return 'Bosnia and Herzegovina';
    }
    if ($code == 'BW')
    {
      return 'Botswana';
    }
    if ($code == 'BV')
    {
      return 'Bouvet Island (Bouvetoya)';
    }
    if ($code == 'BR')
    {
      return 'Brazil';
    }
    if ($code == 'IO')
    {
      return 'British Indian Ocean Territory (Chagos Archipelago)';
    }
    if ($code == 'VG')
    {
      return 'British Virgin Islands';
    }
    if ($code == 'BN')
    {
      return 'Brunei Darussalam';
    }
    if ($code == 'BG')
    {
      return 'Bulgaria';
    }
    if ($code == 'BF')
    {
      return 'Burkina Faso';
    }
    if ($code == 'BI')
    {
      return 'Burundi';
    }
    if ($code == 'KH')
    {
      return 'Cambodia';
    }
    if ($code == 'CM')
    {
      return 'Cameroon';
    }
    if ($code == 'CA')
    {
      return 'Canada';
    }
    if ($code == 'CV')
    {
      return 'Cape Verde';
    }
    if ($code == 'KY')
    {
      return 'Cayman Islands';
    }
    if ($code == 'CF')
    {
      return 'Central African Republic';
    }
    if ($code == 'TD')
    {
      return 'Chad';
    }
    if ($code == 'CL')
    {
      return 'Chile';
    }
    if ($code == 'CN')
    {
      return 'China';
    }
    if ($code == 'CX')
    {
      return 'Christmas Island';
    }
    if ($code == 'CC')
    {
      return 'Cocos (Keeling) Islands';
    }
    if ($code == 'CO')
    {
      return 'Colombia';
    }
    if ($code == 'KM')
    {
      return 'Comoros the';
    }
    if ($code == 'CD')
    {
      return 'Congo';
    }
    if ($code == 'CG')
    {
      return 'Congo the';
    }
    if ($code == 'CK')
    {
      return 'Cook Islands';
    }
    if ($code == 'CR')
    {
      return 'Costa Rica';
    }
    if ($code == 'CI')
    {
      return 'Cote d\'Ivoire';
    }
    if ($code == 'HR')
    {
      return 'Croatia';
    }
    if ($code == 'CU')
    {
      return 'Cuba';
    }
    if ($code == 'CY')
    {
      return 'Cyprus';
    }
    if ($code == 'CZ')
    {
      return 'Czech Republic';
    }
    if ($code == 'DK')
    {
      return 'Denmark';
    }
    if ($code == 'DJ')
    {
      return 'Djibouti';
    }
    if ($code == 'DM')
    {
      return 'Dominica';
    }
    if ($code == 'DO')
    {
      return 'Dominican Republic';
    }
    if ($code == 'EC')
    {
      return 'Ecuador';
    }
    if ($code == 'EG')
    {
      return 'Egypt';
    }
    if ($code == 'SV')
    {
      return 'El Salvador';
    }
    if ($code == 'GQ')
    {
      return 'Equatorial Guinea';
    }
    if ($code == 'ER')
    {
      return 'Eritrea';
    }
    if ($code == 'EE')
    {
      return 'Estonia';
    }
    if ($code == 'ET')
    {
      return 'Ethiopia';
    }
    if ($code == 'FO')
    {
      return 'Faroe Islands';
    }
    if ($code == 'FK')
    {
      return 'Falkland Islands (Malvinas)';
    }
    if ($code == 'FJ')
    {
      return 'Fiji the Fiji Islands';
    }
    if ($code == 'FI')
    {
      return 'Finland';
    }
    if ($code == 'FR')
    {
      return 'France, French Republic';
    }
    if ($code == 'GF')
    {
      return 'French Guiana';
    }
    if ($code == 'PF')
    {
      return 'French Polynesia';
    }
    if ($code == 'TF')
    {
      return 'French Southern Territories';
    }
    if ($code == 'GA')
    {
      return 'Gabon';
    }
    if ($code == 'GM')
    {
      return 'Gambia the';
    }
    if ($code == 'GE')
    {
      return 'Georgia';
    }
    if ($code == 'DE')
    {
      return 'Germany';
    }
    if ($code == 'GH')
    {
      return 'Ghana';
    }
    if ($code == 'GI')
    {
      return 'Gibraltar';
    }
    if ($code == 'GR')
    {
      return 'Greece';
    }
    if ($code == 'GL')
    {
      return 'Greenland';
    }
    if ($code == 'GD')
    {
      return 'Grenada';
    }
    if ($code == 'GP')
    {
      return 'Guadeloupe';
    }
    if ($code == 'GU')
    {
      return 'Guam';
    }
    if ($code == 'GT')
    {
      return 'Guatemala';
    }
    if ($code == 'GG')
    {
      return 'Guernsey';
    }
    if ($code == 'GN')
    {
      return 'Guinea';
    }
    if ($code == 'GW')
    {
      return 'Guinea-Bissau';
    }
    if ($code == 'GY')
    {
      return 'Guyana';
    }
    if ($code == 'HT')
    {
      return 'Haiti';
    }
    if ($code == 'HM')
    {
      return 'Heard Island and McDonald Islands';
    }
    if ($code == 'VA')
    {
      return 'Holy See (Vatican City State)';
    }
    if ($code == 'HN')
    {
      return 'Honduras';
    }
    if ($code == 'HK')
    {
      return 'Hong Kong';
    }
    if ($code == 'HU')
    {
      return 'Hungary';
    }
    if ($code == 'IS')
    {
      return 'Iceland';
    }
    if ($code == 'IN')
    {
      return 'India';
    }
    if ($code == 'ID')
    {
      return 'Indonesia';
    }
    if ($code == 'IR')
    {
      return 'Iran';
    }
    if ($code == 'IQ')
    {
      return 'Iraq';
    }
    if ($code == 'IE')
    {
      return 'Ireland';
    }
    if ($code == 'IM')
    {
      return 'Isle of Man';
    }
    if ($code == 'IL')
    {
      return 'Israel';
    }
    if ($code == 'IT')
    {
      return 'Italy';
    }
    if ($code == 'JM')
    {
      return 'Jamaica';
    }
    if ($code == 'JP')
    {
      return 'Japan';
    }
    if ($code == 'JE')
    {
      return 'Jersey';
    }
    if ($code == 'JO')
    {
      return 'Jordan';
    }
    if ($code == 'KZ')
    {
      return 'Kazakhstan';
    }
    if ($code == 'KE')
    {
      return 'Kenya';
    }
    if ($code == 'KI')
    {
      return 'Kiribati';
    }
    if ($code == 'KP')
    {
      return 'Korea';
    }
    if ($code == 'KR')
    {
      return 'Korea';
    }
    if ($code == 'KW')
    {
      return 'Kuwait';
    }
    if ($code == 'KG')
    {
      return 'Kyrgyz Republic';
    }
    if ($code == 'LA')
    {
      return 'Lao';
    }
    if ($code == 'LV')
    {
      return 'Latvia';
    }
    if ($code == 'LB')
    {
      return 'Lebanon';
    }
    if ($code == 'LS')
    {
      return 'Lesotho';
    }
    if ($code == 'LR')
    {
      return 'Liberia';
    }
    if ($code == 'LY')
    {
      return 'Libyan Arab Jamahiriya';
    }
    if ($code == 'LI')
    {
      return 'Liechtenstein';
    }
    if ($code == 'LT')
    {
      return 'Lithuania';
    }
    if ($code == 'LU')
    {
      return 'Luxembourg';
    }
    if ($code == 'MO')
    {
      return 'Macao';
    }
    if ($code == 'MK')
    {
      return 'Macedonia';
    }
    if ($code == 'MG')
    {
      return 'Madagascar';
    }
    if ($code == 'MW')
    {
      return 'Malawi';
    }
    if ($code == 'MY')
    {
      return 'Malaysia';
    }
    if ($code == 'MV')
    {
      return 'Maldives';
    }
    if ($code == 'ML')
    {
      return 'Mali';
    }
    if ($code == 'MT')
    {
      return 'Malta';
    }
    if ($code == 'MH')
    {
      return 'Marshall Islands';
    }
    if ($code == 'MQ')
    {
      return 'Martinique';
    }
    if ($code == 'MR')
    {
      return 'Mauritania';
    }
    if ($code == 'MU')
    {
      return 'Mauritius';
    }
    if ($code == 'YT')
    {
      return 'Mayotte';
    }
    if ($code == 'MX')
    {
      return 'Mexico';
    }
    if ($code == 'FM')
    {
      return 'Micronesia';
    }
    if ($code == 'MD')
    {
      return 'Moldova';
    }
    if ($code == 'MC')
    {
      return 'Monaco';
    }
    if ($code == 'MN')
    {
      return 'Mongolia';
    }
    if ($code == 'ME')
    {
      return 'Montenegro';
    }
    if ($code == 'MS')
    {
      return 'Montserrat';
    }
    if ($code == 'MA')
    {
      return 'Morocco';
    }
    if ($code == 'MZ')
    {
      return 'Mozambique';
    }
    if ($code == 'MM')
    {
      return 'Myanmar';
    }
    if ($code == 'NA')
    {
      return 'Namibia';
    }
    if ($code == 'NR')
    {
      return 'Nauru';
    }
    if ($code == 'NP')
    {
      return 'Nepal';
    }
    if ($code == 'AN')
    {
      return 'Netherlands Antilles';
    }
    if ($code == 'NL')
    {
      return 'Netherlands the';
    }
    if ($code == 'NC')
    {
      return 'New Caledonia';
    }
    if ($code == 'NZ')
    {
      return 'New Zealand';
    }
    if ($code == 'NI')
    {
      return 'Nicaragua';
    }
    if ($code == 'NE')
    {
      return 'Niger';
    }
    if ($code == 'NG')
    {
      return 'Nigeria';
    }
    if ($code == 'NU')
    {
      return 'Niue';
    }
    if ($code == 'NF')
    {
      return 'Norfolk Island';
    }
    if ($code == 'MP')
    {
      return 'Northern Mariana Islands';
    }
    if ($code == 'NO')
    {
      return 'Norway';
    }
    if ($code == 'OM')
    {
      return 'Oman';
    }
    if ($code == 'PK')
    {
      return 'Pakistan';
    }
    if ($code == 'PW')
    {
      return 'Palau';
    }
    if ($code == 'PS')
    {
      return 'Palestinian Territory';
    }
    if ($code == 'PA')
    {
      return 'Panama';
    }
    if ($code == 'PG')
    {
      return 'Papua New Guinea';
    }
    if ($code == 'PY')
    {
      return 'Paraguay';
    }
    if ($code == 'PE')
    {
      return 'Peru';
    }
    if ($code == 'PH')
    {
      return 'Philippines';
    }
    if ($code == 'PN')
    {
      return 'Pitcairn Islands';
    }
    if ($code == 'PL')
    {
      return 'Poland';
    }
    if ($code == 'PT')
    {
      return 'Portugal, Portuguese Republic';
    }
    if ($code == 'PR')
    {
      return 'Puerto Rico';
    }
    if ($code == 'QA')
    {
      return 'Qatar';
    }
    if ($code == 'RE')
    {
      return 'Reunion';
    }
    if ($code == 'RO')
    {
      return 'Romania';
    }
    if ($code == 'RU')
    {
      return 'Russian Federation';
    }
    if ($code == 'RW')
    {
      return 'Rwanda';
    }
    if ($code == 'BL')
    {
      return 'Saint Barthelemy';
    }
    if ($code == 'SH')
    {
      return 'Saint Helena';
    }
    if ($code == 'KN')
    {
      return 'Saint Kitts and Nevis';
    }
    if ($code == 'LC')
    {
      return 'Saint Lucia';
    }
    if ($code == 'MF')
    {
      return 'Saint Martin';
    }
    if ($code == 'PM')
    {
      return 'Saint Pierre and Miquelon';
    }
    if ($code == 'VC')
    {
      return 'Saint Vincent and the Grenadines';
    }
    if ($code == 'WS')
    {
      return 'Samoa';
    }
    if ($code == 'SM')
    {
      return 'San Marino';
    }
    if ($code == 'ST')
    {
      return 'Sao Tome and Principe';
    }
    if ($code == 'SA')
    {
      return 'Saudi Arabia';
    }
    if ($code == 'SN')
    {
      return 'Senegal';
    }
    if ($code == 'RS')
    {
      return 'Serbia';
    }
    if ($code == 'SC')
    {
      return 'Seychelles';
    }
    if ($code == 'SL')
    {
      return 'Sierra Leone';
    }
    if ($code == 'SG')
    {
      return 'Singapore';
    }
    if ($code == 'SK')
    {
      return 'Slovakia (Slovak Republic)';
    }
    if ($code == 'SI')
    {
      return 'Slovenia';
    }
    if ($code == 'SB')
    {
      return 'Solomon Islands';
    }
    if ($code == 'SO')
    {
      return 'Somalia, Somali Republic';
    }
    if ($code == 'ZA')
    {
      return 'South Africa';
    }
    if ($code == 'GS')
    {
      return 'South Georgia and the South Sandwich Islands';
    }
    if ($code == 'ES')
    {
      return 'Spain';
    }
    if ($code == 'LK')
    {
      return 'Sri Lanka';
    }
    if ($code == 'SD')
    {
      return 'Sudan';
    }
    if ($code == 'SR')
    {
      return 'Suriname';
    }
    if ($code == 'SJ')
    {
      return 'Svalbard & Jan Mayen Islands';
    }
    if ($code == 'SZ')
    {
      return 'Swaziland';
    }
    if ($code == 'SE')
    {
      return 'Sweden';
    }
    if ($code == 'CH')
    {
      return 'Switzerland, Swiss Confederation';
    }
    if ($code == 'SY')
    {
      return 'Syrian Arab Republic';
    }
    if ($code == 'TW')
    {
      return 'Taiwan';
    }
    if ($code == 'TJ')
    {
      return 'Tajikistan';
    }
    if ($code == 'TZ')
    {
      return 'Tanzania';
    }
    if ($code == 'TH')
    {
      return 'Thailand';
    }
    if ($code == 'TL')
    {
      return 'Timor-Leste';
    }
    if ($code == 'TG')
    {
      return 'Togo';
    }
    if ($code == 'TK')
    {
      return 'Tokelau';
    }
    if ($code == 'TO')
    {
      return 'Tonga';
    }
    if ($code == 'TT')
    {
      return 'Trinidad and Tobago';
    }
    if ($code == 'TN')
    {
      return 'Tunisia';
    }
    if ($code == 'TR')
    {
      return 'Turkey';
    }
    if ($code == 'TM')
    {
      return 'Turkmenistan';
    }
    if ($code == 'TC')
    {
      return 'Turks and Caicos Islands';
    }
    if ($code == 'TV')
    {
      return 'Tuvalu';
    }
    if ($code == 'UG')
    {
      return 'Uganda';
    }
    if ($code == 'UA')
    {
      return 'Ukraine';
    }
    if ($code == 'AE')
    {
      return 'United Arab Emirates';
    }
    if ($code == 'GB')
    {
      return 'United Kingdom';
    }
    if ($code == 'US')
    {
      return 'United States of America';
    }
    if ($code == 'UM')
    {
      return 'United States Minor Outlying Islands';
    }
    if ($code == 'VI')
    {
      return 'United States Virgin Islands';
    }
    if ($code == 'UY')
    {
      return 'Uruguay, Eastern Republic of';
    }
    if ($code == 'UZ')
    {
      return 'Uzbekistan';
    }
    if ($code == 'VU')
    {
      return 'Vanuatu';
    }
    if ($code == 'VE')
    {
      return 'Venezuela';
    }
    if ($code == 'VN')
    {
      return 'Vietnam';
    }
    if ($code == 'WF')
    {
      return 'Wallis and Futuna';
    }
    if ($code == 'EH')
    {
      return 'Western Sahara';
    }
    if ($code == 'YE')
    {
      return 'Yemen';
    }
    if ($code == 'XK')
    {
      return 'Kosovo';
    }
    if ($code == 'ZM')
    {
      return 'Zambia';
    }
    if ($code == 'ZW')
    {
      return 'Zimbabwe';
    }

    return '';
  }
}
