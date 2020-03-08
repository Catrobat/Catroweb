<?php

namespace App\Catrobat\Services;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Entity\ClickStatistic;
use App\Entity\HomepageClickStatistic;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class StatisticsService.
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
   * @var LoggerInterface
   */
  private $logger;
  /**
   * @var TokenStorage
   */
  private $security_token_storage;

  /**
   * StatisticsService constructor.
   *
   * @param $entity_manager
   * @param $security_token_storage
   */
  public function __construct(ProgramManager $program_manager, EntityManagerInterface $entity_manager,
                              LoggerInterface $logger, TokenStorageInterface $security_token_storage)
  {
    $this->programmanager = $program_manager;
    $this->entity_manager = $entity_manager;
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
   * @throws \Exception
   *
   * @return bool
   */
  public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id,
                                                  $rec_by_program_id, $locale, $is_user_specific_recommendation = false)
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    if ('anon.' === $session_user)
    {
      $user = null;
    }
    else
    {
      $user = $session_user;
    }

    $this->logger->debug('create download stats for program id: '.$program_id.', ip: '.$ip.
      ', user agent: '.$user_agent.', referrer: '.$referrer);
    if (null !== $user)
    {
      $this->logger->debug('user: '.$user->getUsername());
    }
    else
    {
      $this->logger->debug('user: anon.');
    }

    // geocoder disabled, license needed
    // see SHARE-80
    $country_name = null;
    $country_code = null;

    $program = $this->programmanager->find($program_id);

    $program_download_statistic = new ProgramDownloads();
    $program_download_statistic->setProgram($program);
    $program_download_statistic->setUserAgent($user_agent);
    $program_download_statistic->setUser($user);
    $program_download_statistic->setReferrer($referrer);
    $program_download_statistic->setDownloadedAt(TimeUtils::getDateTime());
    $program_download_statistic->setIp($ip);
    $program_download_statistic->setCountryCode($country_code);
    $program_download_statistic->setCountryName($country_name);
    $program_download_statistic->setLocale($locale);

    if (null != $rec_by_page_id && RecommendedPageId::isValidRecommendedPageId($rec_by_page_id))
    {
      // all recommendations (except tag-recommendations -> see below)
      $program_download_statistic->setRecommendedByPageId($rec_by_page_id);
      $rec_by_program = (null != $rec_by_program_id) ? $this->programmanager->find($rec_by_program_id) : null;
      $program_download_statistic->setUserSpecificRecommendation($is_user_specific_recommendation);

      if (null != $rec_by_program)
      {
        /*
         * @var $rec_by_program Program
         */
        $program_download_statistic->setRecommendedByProgram($rec_by_program);
      }
    }
    else
    {
      if (null != $rec_tag_by_program_id)
      {
        // tag-recommendations
        $rec_program = $this->programmanager->find($rec_tag_by_program_id);
        if (null != $rec_program)
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
    }
    catch (\Exception $e)
    {
      $this->logger->error($e->getMessage());

      return false;
    }

    return true;
  }

  public function getProgrammanager(): ProgramManager
  {
    return $this->programmanager;
  }

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
   * @return Logger
   */
  public function getLogger(): LoggerInterface
  {
    return $this->logger;
  }

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
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws \Exception
   *
   * @return bool
   */
  public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name,
                                        $referrer, $locale = null, $is_recommended_program_a_scratch_program = false,
                                        $is_user_specific_recommendation = false)
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    if ('anon.' === $session_user)
    {
      $user = null;
    }
    else
    {
      $user = $session_user;
    }

    $this->logger->debug('create download stats for project id: '.$rec_from_id.', ip: '.$ip.
      ', user agent: '.$user_agent.', referrer: '.$referrer);
    if (null !== $user)
    {
      $this->logger->debug('user: '.$user->getUsername());
    }
    else
    {
      $this->logger->debug('user: anon.');
    }

    // geocoder disabled, license needed
    // see SHARE-80
    $country_code = null;
    $country_name = null;

    if (in_array($type, ['project', 'rec_homepage', 'rec_remix_graph', 'rec_remix_notification', 'rec_specific_programs', 'show_remix_graph'], true))
    {
      $click_statistics = new ClickStatistic();
      $click_statistics->setType($type);
      $click_statistics->setUserAgent($user_agent);
      $click_statistics->setUser($user);
      $click_statistics->setReferrer($referrer);
      $click_statistics->setClickedAt(TimeUtils::getDateTime());
      $click_statistics->setIp($ip);
      $click_statistics->setCountryCode($country_code);
      $click_statistics->setCountryName($country_name);
      $click_statistics->setLocale($locale);
      $click_statistics->setUserSpecificRecommendation($is_user_specific_recommendation);

      if ($rec_from_id > 0)
      {
        /**
         * @var Program
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
         * @var Program
         */
        $recommended_program = $this->programmanager->find($rec_program_id);
        $click_statistics->setProgram($recommended_program);
      }

      $this->entity_manager->persist($click_statistics);
      $this->entity_manager->flush();
    }
    else
    {
      if ('tags' == $type)
      {
        $tag_repo = $this->entity_manager->getRepository('\\App\\Entity\\Tag');
        $tag = $tag_repo->find($tag_id);

        $click_statistics = new ClickStatistic();
        $click_statistics->setType($type);
        $click_statistics->setTag($tag);
        $click_statistics->setUserAgent($user_agent);
        $click_statistics->setUser($user);
        $click_statistics->setReferrer($referrer);
        $click_statistics->setClickedAt(TimeUtils::getDateTime());
        $click_statistics->setIp($ip);
        $click_statistics->setCountryCode($country_code);
        $click_statistics->setCountryName($country_name);
        $click_statistics->setLocale($locale);

        $this->entity_manager->persist($click_statistics);
        $this->entity_manager->flush();
      }
      else
      {
        if ('extensions' == $type)
        {
          $extensions_repo = $this->entity_manager->getRepository('\\App\\Entity\\Extension');

          $extension = $extensions_repo->getExtensionByName($extension_name);

          if (null == $extension)
          {
            return false;
          }

          $click_statistics = new ClickStatistic();
          $click_statistics->setType($type);
          $click_statistics->setExtension($extension[0]);
          $click_statistics->setUserAgent($user_agent);
          $click_statistics->setUser($user);
          $click_statistics->setReferrer($referrer);
          $click_statistics->setClickedAt(TimeUtils::getDateTime());
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
   * @throws \Exception
   *
   * @return bool
   */
  public function createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale)
  {
    /**
     * @var Program
     */
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    if ('anon.' === $session_user)
    {
      $user = null;
    }
    else
    {
      $user = $session_user;
    }

    $this->logger->debug('create click stats for program id: '.$program_id.', ip: '.$ip.
      ', user agent: '.$user_agent.', referrer: '.$referrer);
    if (null !== $user)
    {
      $this->logger->debug('user: '.$user->getUsername());
    }
    else
    {
      $this->logger->debug('user: anon.');
    }

    $homepage_click_statistics = new HomepageClickStatistic();
    $homepage_click_statistics->setType($type);
    $homepage_click_statistics->setUserAgent($user_agent);
    $homepage_click_statistics->setUser($user);
    $homepage_click_statistics->setReferrer($referrer);
    $homepage_click_statistics->setClickedAt(TimeUtils::getDateTime());
    $homepage_click_statistics->setIp($ip);
    $homepage_click_statistics->setLocale($locale);
    $program = $this->programmanager->find($program_id);
    $homepage_click_statistics->setProgram($program);
    $this->entity_manager->persist($homepage_click_statistics);
    $this->entity_manager->flush();

    return true;
  }

  /**
   * @param $code
   *
   * @return string
   */
  public function countryCodeToCountry($code)
  {
    $code = strtoupper($code);
    if ('AF' == $code)
    {
      return 'Afghanistan';
    }
    if ('AX' == $code)
    {
      return 'Aland Islands';
    }
    if ('AL' == $code)
    {
      return 'Albania';
    }
    if ('DZ' == $code)
    {
      return 'Algeria';
    }
    if ('AS' == $code)
    {
      return 'American Samoa';
    }
    if ('AD' == $code)
    {
      return 'Andorra';
    }
    if ('AO' == $code)
    {
      return 'Angola';
    }
    if ('AI' == $code)
    {
      return 'Anguilla';
    }
    if ('AQ' == $code)
    {
      return 'Antarctica';
    }
    if ('AG' == $code)
    {
      return 'Antigua and Barbuda';
    }
    if ('AR' == $code)
    {
      return 'Argentina';
    }
    if ('AM' == $code)
    {
      return 'Armenia';
    }
    if ('AW' == $code)
    {
      return 'Aruba';
    }
    if ('AU' == $code)
    {
      return 'Australia';
    }
    if ('AT' == $code)
    {
      return 'Austria';
    }
    if ('AZ' == $code)
    {
      return 'Azerbaijan';
    }
    if ('BS' == $code)
    {
      return 'Bahamas the';
    }
    if ('BH' == $code)
    {
      return 'Bahrain';
    }
    if ('BD' == $code)
    {
      return 'Bangladesh';
    }
    if ('BB' == $code)
    {
      return 'Barbados';
    }
    if ('BY' == $code)
    {
      return 'Belarus';
    }
    if ('BE' == $code)
    {
      return 'Belgium';
    }
    if ('BZ' == $code)
    {
      return 'Belize';
    }
    if ('BJ' == $code)
    {
      return 'Benin';
    }
    if ('BM' == $code)
    {
      return 'Bermuda';
    }
    if ('BT' == $code)
    {
      return 'Bhutan';
    }
    if ('BO' == $code)
    {
      return 'Bolivia';
    }
    if ('BA' == $code)
    {
      return 'Bosnia and Herzegovina';
    }
    if ('BW' == $code)
    {
      return 'Botswana';
    }
    if ('BV' == $code)
    {
      return 'Bouvet Island (Bouvetoya)';
    }
    if ('BR' == $code)
    {
      return 'Brazil';
    }
    if ('IO' == $code)
    {
      return 'British Indian Ocean Territory (Chagos Archipelago)';
    }
    if ('VG' == $code)
    {
      return 'British Virgin Islands';
    }
    if ('BN' == $code)
    {
      return 'Brunei Darussalam';
    }
    if ('BG' == $code)
    {
      return 'Bulgaria';
    }
    if ('BF' == $code)
    {
      return 'Burkina Faso';
    }
    if ('BI' == $code)
    {
      return 'Burundi';
    }
    if ('KH' == $code)
    {
      return 'Cambodia';
    }
    if ('CM' == $code)
    {
      return 'Cameroon';
    }
    if ('CA' == $code)
    {
      return 'Canada';
    }
    if ('CV' == $code)
    {
      return 'Cape Verde';
    }
    if ('KY' == $code)
    {
      return 'Cayman Islands';
    }
    if ('CF' == $code)
    {
      return 'Central African Republic';
    }
    if ('TD' == $code)
    {
      return 'Chad';
    }
    if ('CL' == $code)
    {
      return 'Chile';
    }
    if ('CN' == $code)
    {
      return 'China';
    }
    if ('CX' == $code)
    {
      return 'Christmas Island';
    }
    if ('CC' == $code)
    {
      return 'Cocos (Keeling) Islands';
    }
    if ('CO' == $code)
    {
      return 'Colombia';
    }
    if ('KM' == $code)
    {
      return 'Comoros the';
    }
    if ('CD' == $code)
    {
      return 'Congo';
    }
    if ('CG' == $code)
    {
      return 'Congo the';
    }
    if ('CK' == $code)
    {
      return 'Cook Islands';
    }
    if ('CR' == $code)
    {
      return 'Costa Rica';
    }
    if ('CI' == $code)
    {
      return 'Cote d\'Ivoire';
    }
    if ('HR' == $code)
    {
      return 'Croatia';
    }
    if ('CU' == $code)
    {
      return 'Cuba';
    }
    if ('CY' == $code)
    {
      return 'Cyprus';
    }
    if ('CZ' == $code)
    {
      return 'Czech Republic';
    }
    if ('DK' == $code)
    {
      return 'Denmark';
    }
    if ('DJ' == $code)
    {
      return 'Djibouti';
    }
    if ('DM' == $code)
    {
      return 'Dominica';
    }
    if ('DO' == $code)
    {
      return 'Dominican Republic';
    }
    if ('EC' == $code)
    {
      return 'Ecuador';
    }
    if ('EG' == $code)
    {
      return 'Egypt';
    }
    if ('SV' == $code)
    {
      return 'El Salvador';
    }
    if ('GQ' == $code)
    {
      return 'Equatorial Guinea';
    }
    if ('ER' == $code)
    {
      return 'Eritrea';
    }
    if ('EE' == $code)
    {
      return 'Estonia';
    }
    if ('ET' == $code)
    {
      return 'Ethiopia';
    }
    if ('FO' == $code)
    {
      return 'Faroe Islands';
    }
    if ('FK' == $code)
    {
      return 'Falkland Islands (Malvinas)';
    }
    if ('FJ' == $code)
    {
      return 'Fiji the Fiji Islands';
    }
    if ('FI' == $code)
    {
      return 'Finland';
    }
    if ('FR' == $code)
    {
      return 'France, French Republic';
    }
    if ('GF' == $code)
    {
      return 'French Guiana';
    }
    if ('PF' == $code)
    {
      return 'French Polynesia';
    }
    if ('TF' == $code)
    {
      return 'French Southern Territories';
    }
    if ('GA' == $code)
    {
      return 'Gabon';
    }
    if ('GM' == $code)
    {
      return 'Gambia the';
    }
    if ('GE' == $code)
    {
      return 'Georgia';
    }
    if ('DE' == $code)
    {
      return 'Germany';
    }
    if ('GH' == $code)
    {
      return 'Ghana';
    }
    if ('GI' == $code)
    {
      return 'Gibraltar';
    }
    if ('GR' == $code)
    {
      return 'Greece';
    }
    if ('GL' == $code)
    {
      return 'Greenland';
    }
    if ('GD' == $code)
    {
      return 'Grenada';
    }
    if ('GP' == $code)
    {
      return 'Guadeloupe';
    }
    if ('GU' == $code)
    {
      return 'Guam';
    }
    if ('GT' == $code)
    {
      return 'Guatemala';
    }
    if ('GG' == $code)
    {
      return 'Guernsey';
    }
    if ('GN' == $code)
    {
      return 'Guinea';
    }
    if ('GW' == $code)
    {
      return 'Guinea-Bissau';
    }
    if ('GY' == $code)
    {
      return 'Guyana';
    }
    if ('HT' == $code)
    {
      return 'Haiti';
    }
    if ('HM' == $code)
    {
      return 'Heard Island and McDonald Islands';
    }
    if ('VA' == $code)
    {
      return 'Holy See (Vatican City State)';
    }
    if ('HN' == $code)
    {
      return 'Honduras';
    }
    if ('HK' == $code)
    {
      return 'Hong Kong';
    }
    if ('HU' == $code)
    {
      return 'Hungary';
    }
    if ('IS' == $code)
    {
      return 'Iceland';
    }
    if ('IN' == $code)
    {
      return 'India';
    }
    if ('ID' == $code)
    {
      return 'Indonesia';
    }
    if ('IR' == $code)
    {
      return 'Iran';
    }
    if ('IQ' == $code)
    {
      return 'Iraq';
    }
    if ('IE' == $code)
    {
      return 'Ireland';
    }
    if ('IM' == $code)
    {
      return 'Isle of Man';
    }
    if ('IL' == $code)
    {
      return 'Israel';
    }
    if ('IT' == $code)
    {
      return 'Italy';
    }
    if ('JM' == $code)
    {
      return 'Jamaica';
    }
    if ('JP' == $code)
    {
      return 'Japan';
    }
    if ('JE' == $code)
    {
      return 'Jersey';
    }
    if ('JO' == $code)
    {
      return 'Jordan';
    }
    if ('KZ' == $code)
    {
      return 'Kazakhstan';
    }
    if ('KE' == $code)
    {
      return 'Kenya';
    }
    if ('KI' == $code)
    {
      return 'Kiribati';
    }
    if ('KP' == $code)
    {
      return 'Korea';
    }
    if ('KR' == $code)
    {
      return 'Korea';
    }
    if ('KW' == $code)
    {
      return 'Kuwait';
    }
    if ('KG' == $code)
    {
      return 'Kyrgyz Republic';
    }
    if ('LA' == $code)
    {
      return 'Lao';
    }
    if ('LV' == $code)
    {
      return 'Latvia';
    }
    if ('LB' == $code)
    {
      return 'Lebanon';
    }
    if ('LS' == $code)
    {
      return 'Lesotho';
    }
    if ('LR' == $code)
    {
      return 'Liberia';
    }
    if ('LY' == $code)
    {
      return 'Libyan Arab Jamahiriya';
    }
    if ('LI' == $code)
    {
      return 'Liechtenstein';
    }
    if ('LT' == $code)
    {
      return 'Lithuania';
    }
    if ('LU' == $code)
    {
      return 'Luxembourg';
    }
    if ('MO' == $code)
    {
      return 'Macao';
    }
    if ('MK' == $code)
    {
      return 'Macedonia';
    }
    if ('MG' == $code)
    {
      return 'Madagascar';
    }
    if ('MW' == $code)
    {
      return 'Malawi';
    }
    if ('MY' == $code)
    {
      return 'Malaysia';
    }
    if ('MV' == $code)
    {
      return 'Maldives';
    }
    if ('ML' == $code)
    {
      return 'Mali';
    }
    if ('MT' == $code)
    {
      return 'Malta';
    }
    if ('MH' == $code)
    {
      return 'Marshall Islands';
    }
    if ('MQ' == $code)
    {
      return 'Martinique';
    }
    if ('MR' == $code)
    {
      return 'Mauritania';
    }
    if ('MU' == $code)
    {
      return 'Mauritius';
    }
    if ('YT' == $code)
    {
      return 'Mayotte';
    }
    if ('MX' == $code)
    {
      return 'Mexico';
    }
    if ('FM' == $code)
    {
      return 'Micronesia';
    }
    if ('MD' == $code)
    {
      return 'Moldova';
    }
    if ('MC' == $code)
    {
      return 'Monaco';
    }
    if ('MN' == $code)
    {
      return 'Mongolia';
    }
    if ('ME' == $code)
    {
      return 'Montenegro';
    }
    if ('MS' == $code)
    {
      return 'Montserrat';
    }
    if ('MA' == $code)
    {
      return 'Morocco';
    }
    if ('MZ' == $code)
    {
      return 'Mozambique';
    }
    if ('MM' == $code)
    {
      return 'Myanmar';
    }
    if ('NA' == $code)
    {
      return 'Namibia';
    }
    if ('NR' == $code)
    {
      return 'Nauru';
    }
    if ('NP' == $code)
    {
      return 'Nepal';
    }
    if ('AN' == $code)
    {
      return 'Netherlands Antilles';
    }
    if ('NL' == $code)
    {
      return 'Netherlands the';
    }
    if ('NC' == $code)
    {
      return 'New Caledonia';
    }
    if ('NZ' == $code)
    {
      return 'New Zealand';
    }
    if ('NI' == $code)
    {
      return 'Nicaragua';
    }
    if ('NE' == $code)
    {
      return 'Niger';
    }
    if ('NG' == $code)
    {
      return 'Nigeria';
    }
    if ('NU' == $code)
    {
      return 'Niue';
    }
    if ('NF' == $code)
    {
      return 'Norfolk Island';
    }
    if ('MP' == $code)
    {
      return 'Northern Mariana Islands';
    }
    if ('NO' == $code)
    {
      return 'Norway';
    }
    if ('OM' == $code)
    {
      return 'Oman';
    }
    if ('PK' == $code)
    {
      return 'Pakistan';
    }
    if ('PW' == $code)
    {
      return 'Palau';
    }
    if ('PS' == $code)
    {
      return 'Palestinian Territory';
    }
    if ('PA' == $code)
    {
      return 'Panama';
    }
    if ('PG' == $code)
    {
      return 'Papua New Guinea';
    }
    if ('PY' == $code)
    {
      return 'Paraguay';
    }
    if ('PE' == $code)
    {
      return 'Peru';
    }
    if ('PH' == $code)
    {
      return 'Philippines';
    }
    if ('PN' == $code)
    {
      return 'Pitcairn Islands';
    }
    if ('PL' == $code)
    {
      return 'Poland';
    }
    if ('PT' == $code)
    {
      return 'Portugal, Portuguese Republic';
    }
    if ('PR' == $code)
    {
      return 'Puerto Rico';
    }
    if ('QA' == $code)
    {
      return 'Qatar';
    }
    if ('RE' == $code)
    {
      return 'Reunion';
    }
    if ('RO' == $code)
    {
      return 'Romania';
    }
    if ('RU' == $code)
    {
      return 'Russian Federation';
    }
    if ('RW' == $code)
    {
      return 'Rwanda';
    }
    if ('BL' == $code)
    {
      return 'Saint Barthelemy';
    }
    if ('SH' == $code)
    {
      return 'Saint Helena';
    }
    if ('KN' == $code)
    {
      return 'Saint Kitts and Nevis';
    }
    if ('LC' == $code)
    {
      return 'Saint Lucia';
    }
    if ('MF' == $code)
    {
      return 'Saint Martin';
    }
    if ('PM' == $code)
    {
      return 'Saint Pierre and Miquelon';
    }
    if ('VC' == $code)
    {
      return 'Saint Vincent and the Grenadines';
    }
    if ('WS' == $code)
    {
      return 'Samoa';
    }
    if ('SM' == $code)
    {
      return 'San Marino';
    }
    if ('ST' == $code)
    {
      return 'Sao Tome and Principe';
    }
    if ('SA' == $code)
    {
      return 'Saudi Arabia';
    }
    if ('SN' == $code)
    {
      return 'Senegal';
    }
    if ('RS' == $code)
    {
      return 'Serbia';
    }
    if ('SC' == $code)
    {
      return 'Seychelles';
    }
    if ('SL' == $code)
    {
      return 'Sierra Leone';
    }
    if ('SG' == $code)
    {
      return 'Singapore';
    }
    if ('SK' == $code)
    {
      return 'Slovakia (Slovak Republic)';
    }
    if ('SI' == $code)
    {
      return 'Slovenia';
    }
    if ('SB' == $code)
    {
      return 'Solomon Islands';
    }
    if ('SO' == $code)
    {
      return 'Somalia, Somali Republic';
    }
    if ('ZA' == $code)
    {
      return 'South Africa';
    }
    if ('GS' == $code)
    {
      return 'South Georgia and the South Sandwich Islands';
    }
    if ('ES' == $code)
    {
      return 'Spain';
    }
    if ('LK' == $code)
    {
      return 'Sri Lanka';
    }
    if ('SD' == $code)
    {
      return 'Sudan';
    }
    if ('SR' == $code)
    {
      return 'Suriname';
    }
    if ('SJ' == $code)
    {
      return 'Svalbard & Jan Mayen Islands';
    }
    if ('SZ' == $code)
    {
      return 'Swaziland';
    }
    if ('SE' == $code)
    {
      return 'Sweden';
    }
    if ('CH' == $code)
    {
      return 'Switzerland, Swiss Confederation';
    }
    if ('SY' == $code)
    {
      return 'Syrian Arab Republic';
    }
    if ('TW' == $code)
    {
      return 'Taiwan';
    }
    if ('TJ' == $code)
    {
      return 'Tajikistan';
    }
    if ('TZ' == $code)
    {
      return 'Tanzania';
    }
    if ('TH' == $code)
    {
      return 'Thailand';
    }
    if ('TL' == $code)
    {
      return 'Timor-Leste';
    }
    if ('TG' == $code)
    {
      return 'Togo';
    }
    if ('TK' == $code)
    {
      return 'Tokelau';
    }
    if ('TO' == $code)
    {
      return 'Tonga';
    }
    if ('TT' == $code)
    {
      return 'Trinidad and Tobago';
    }
    if ('TN' == $code)
    {
      return 'Tunisia';
    }
    if ('TR' == $code)
    {
      return 'Turkey';
    }
    if ('TM' == $code)
    {
      return 'Turkmenistan';
    }
    if ('TC' == $code)
    {
      return 'Turks and Caicos Islands';
    }
    if ('TV' == $code)
    {
      return 'Tuvalu';
    }
    if ('UG' == $code)
    {
      return 'Uganda';
    }
    if ('UA' == $code)
    {
      return 'Ukraine';
    }
    if ('AE' == $code)
    {
      return 'United Arab Emirates';
    }
    if ('GB' == $code)
    {
      return 'United Kingdom';
    }
    if ('US' == $code)
    {
      return 'United States of America';
    }
    if ('UM' == $code)
    {
      return 'United States Minor Outlying Islands';
    }
    if ('VI' == $code)
    {
      return 'United States Virgin Islands';
    }
    if ('UY' == $code)
    {
      return 'Uruguay, Eastern Republic of';
    }
    if ('UZ' == $code)
    {
      return 'Uzbekistan';
    }
    if ('VU' == $code)
    {
      return 'Vanuatu';
    }
    if ('VE' == $code)
    {
      return 'Venezuela';
    }
    if ('VN' == $code)
    {
      return 'Vietnam';
    }
    if ('WF' == $code)
    {
      return 'Wallis and Futuna';
    }
    if ('EH' == $code)
    {
      return 'Western Sahara';
    }
    if ('YE' == $code)
    {
      return 'Yemen';
    }
    if ('XK' == $code)
    {
      return 'Kosovo';
    }
    if ('ZM' == $code)
    {
      return 'Zambia';
    }
    if ('ZW' == $code)
    {
      return 'Zimbabwe';
    }

    return '';
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
    if (false !== strpos($ip, ','))
    {
      $ip = substr($ip, 0, strpos($ip, ','));
    }

    return $ip;
  }
}
