<?php

namespace App\Catrobat\Services;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Entity\ClickStatistic;
use App\Entity\HomepageClickStatistic;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramManager;
use App\Repository\ExtensionRepository;
use App\Repository\TagRepository;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StatisticsService
{
  private ProgramManager $program_manager;

  private EntityManagerInterface $entity_manager;

  private LoggerInterface $logger;

  private TokenStorageInterface $security_token_storage;

  private ExtensionRepository $extension_repository;

  private TagRepository $tag_repository;

  public function __construct(ProgramManager $program_manager, EntityManagerInterface $entity_manager,
                              LoggerInterface $logger, TokenStorageInterface $security_token_storage,
                              ExtensionRepository $extension_repository, TagRepository $tag_repository)
  {
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
    $this->logger = $logger;
    $this->security_token_storage = $security_token_storage;
    $this->extension_repository = $extension_repository;
    $this->tag_repository = $tag_repository;
  }

  /**
   * @throws Exception
   */
  public function createProgramDownloadStatistics(Request $request, string $program_id, ?string $referrer,
                                                  ?string $rec_tag_by_program_id, ?int $rec_by_page_id,
                                                  ?string $rec_by_program_id, ?string $locale, bool $is_user_specific_recommendation = false): bool
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    $user = 'anon.' === $session_user ? null : $session_user;

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

    $program = $this->program_manager->find($program_id);

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
      /** @var Program|null $rec_by_program */
      $rec_by_program = (null != $rec_by_program_id) ? $this->program_manager->find($rec_by_program_id) : null;
      $program_download_statistic->setUserSpecificRecommendation($is_user_specific_recommendation);
      if (null != $rec_by_program)
      {
        $program_download_statistic->setRecommendedByProgram($rec_by_program);
      }
    }
    elseif (null != $rec_tag_by_program_id)
    {
      // tag-recommendations
      $rec_program = $this->program_manager->find($rec_tag_by_program_id);
      if (null != $rec_program)
      {
        $program_download_statistic->setRecommendedFromProgramViaTag($rec_program);
      }
    }

    try
    {
      $this->entity_manager->persist($program_download_statistic);
      $program->addProgramDownloads($program_download_statistic);
      $this->entity_manager->persist($program);
      $this->entity_manager->flush();
    }
    catch (Exception $exception)
    {
      $this->logger->error($exception->getMessage());

      return false;
    }

    return true;
  }

  public function getProgramManager(): ProgramManager
  {
    return $this->program_manager;
  }

  public function setProgramManager(ProgramManager $program_manager): void
  {
    $this->program_manager = $program_manager;
  }

  public function getEntityManager(): EntityManagerInterface
  {
    return $this->entity_manager;
  }

  public function setEntityManager(EntityManagerInterface $entity_manager): void
  {
    $this->entity_manager = $entity_manager;
  }

  public function getLogger(): LoggerInterface
  {
    return $this->logger;
  }

  public function setLogger(LoggerInterface $logger): void
  {
    $this->logger = $logger;
  }

  public function getSecurityTokenStorage(): TokenStorageInterface
  {
    return $this->security_token_storage;
  }

  public function setSecurityTokenStorage(TokenStorageInterface $security_token_storage): void
  {
    $this->security_token_storage = $security_token_storage;
  }

  /**
   * @throws Exception
   */
  public function createClickStatistics(Request $request, string $type, ?string $rec_from_id, ?string $rec_program_id,
                                        ?int $tag_id, ?string $extension_name,
                                        ?string $referrer, ?string $locale = null, bool $is_recommended_program_a_scratch_program = false,
                                        bool $is_user_specific_recommendation = false): bool
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    $user = 'anon.' === $session_user ? null : $session_user;

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
      if (null !== $rec_from_id && '0' < $rec_from_id)
      {
        /** @var Program $recommended_from */
        $recommended_from = $this->program_manager->find($rec_from_id);
        $click_statistics->setRecommendedFromProgram($recommended_from);
      }
      if ($is_recommended_program_a_scratch_program)
      {
        $click_statistics->setScratchProgramId((int) $rec_program_id);
      }
      else
      {
        $recommended_program = $this->program_manager->find($rec_program_id);
        $click_statistics->setProgram($recommended_program);
      }
      $this->entity_manager->persist($click_statistics);
      $this->entity_manager->flush();
    }
    elseif ('tags' === $type)
    {
      $tag = $this->tag_repository->find($tag_id);
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
    elseif ('extensions' == $type)
    {
      $extension = $this->extension_repository->getExtensionByName($extension_name);
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

    return true;
  }

  /**
   * @throws Exception
   */
  public function createHomepageProgramClickStatistics(Request $request, string $type, string $program_id, ?string $referrer, ?string $locale): bool
  {
    $ip = $this->getOriginalClientIp($request);
    $user_agent = $this->getUserAgent($request);
    $session_user = $this->getSessionUser();

    $user = 'anon.' === $session_user ? null : $session_user;

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

    $program = $this->program_manager->find($program_id);
    $homepage_click_statistics->setProgram($program);
    $this->entity_manager->persist($homepage_click_statistics);
    $this->entity_manager->flush();

    return true;
  }

  private function getUserAgent(Request $request): ?string
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

  private function getOriginalClientIp(Request $request): ?string
  {
    $ip = $request->getClientIp();
    if (false !== strpos($ip, ','))
    {
      $ip = substr($ip, 0, strpos($ip, ','));
    }

    return $ip;
  }
}
