<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Exceptions\Upload\NoGameJamException;
use App\Catrobat\Listeners\GameJamTagListener;
use App\Catrobat\Responses\ProgramListResponse;
use App\Entity\GameJam;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Repository\GameJamRepository;
use App\Utils\TimeUtils;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class GameSubmissionController.
 */
class GameSubmissionController extends AbstractController
{
  /**
   * @Route("/api/gamejam/finalize/{id}", name="gamejam_form_submission", methods={"GET"})
   *
   * @param mixed $id
   *
   * @return JsonResponse
   */
  public function formSubmittedAction(Request $request, $id, Program $program)
  {
    if (null != $program->getGamejam())
    {
      if (!$program->isAcceptedForGameJam())
      {
        $program->setAcceptedForGameJam(true);
        $this->persistAndFlush($program);
      }

      return JsonResponse::create([
        'statusCode' => '200',
        'message' => 'Program accepted for this gamejam',
      ]);
    }

    return JsonResponse::create([
      'statusCode' => '999',
      'message' => 'This program was not submitted to a gamejam',
    ]);
  }

  /**
   *   /**
   * @Route("/api/gamejam/sampleProjects.json", name="api_gamejam_sample_programs", methods={"GET"})
   *
   * @throws NonUniqueResultException
   *
   * @return ProgramListResponse
   */
  public function getSampleProgramsForLatestGamejam(Request $request, GameJamRepository $game_jam_repository)
  {
    $flavor = $request->get('flavor');

    $gamejam = $this->getGameJam($flavor, $game_jam_repository);

    $offset = intval($request->query->get('offset', 0));
    $limit = intval($request->query->get('limit', 20));

    $all_samples = $gamejam->getSamplePrograms();
    $count = count($all_samples);
    $returning_samples = null;

    for ($j = 0, $i = $offset; $i < $count && $i < $limit; $j++, $i++)
    {
      $returning_samples[$j] = $all_samples[$i];
    }

    return new ProgramListResponse($returning_samples, null !== $returning_samples ? count($returning_samples) : 0);
  }

  /**
   * @Route("/api/gamejam/submissions.json", name="api_gamejam_submissions", methods={"GET"})
   *
   * @throws NonUniqueResultException
   *
   * @return ProgramListResponse
   */
  public function getSubmissionsForLatestGamejam(Request $request,
                                                 GameJamRepository $game_jam_repository,
                                                 ProgramManager $program_manager)
  {
    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));

    $flavor = $request->get('flavor');

    /** @var GameJam $game_jam */
    $game_jam = $this->getGameJam($flavor, $game_jam_repository);

    $accepted_game_jam_projects = $program_manager->findBy(
      ['visible' => true, 'gamejam' => $game_jam->getId(), 'gamejam_submission_accepted' => true],
      ['gamejam_submission_date' => Criteria::DESC],
      $limit,
      $offset
    );

    return new ProgramListResponse(
      $accepted_game_jam_projects,
      sizeof($accepted_game_jam_projects)
    );
  }

  /**
   * @Route("/gamejam/submit/{id}", name="gamejam_web_submit", methods={"GET"})
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   *
   * @return RedirectResponse
   */
  public function webSubmitAction(Request $request, Program $program, GameJamRepository $game_jam_repository, GameJamTagListener $gameJamTagListener)
  {
    if (null == $this->getUser())
    {
      throw new AuthenticationException();
    }
    $gamejam = $game_jam_repository->getCurrentGameJam();
    if (null == $gamejam)
    {
      throw new \Exception('No Game Jam!');
    }

    if (null != $program->getGamejam() && $program->getGamejam() != $gamejam)
    {
      throw new \Exception('Game was alraedy submitted to another gamejam!');
    }

    if ($program->isAcceptedForGameJam())
    {
      return new RedirectResponse($this->generateUrl('program', [
        'id' => $program->getId(),
      ]));
    }

    if ($this->getUser() != $program->getUser())
    {
      return new RedirectResponse($this->generateUrl('gamejam_submit_own'));
    }

    $program->setGamejam($gamejam);
    $program->setGameJamSubmissionDate(TimeUtils::getDateTime());

    $gameJamTagListener->checkDescriptionTag($program);

    $this->persistAndFlush($program);

    $url = $this->assembleFormUrl($gamejam, $program->getUser(), $program, $request);

    if (null != $url)
    {
      return new RedirectResponse($url);
    }

    return new RedirectResponse($this->generateUrl('program', [
      'id' => $program->getId(),
    ]));
  }

  /**
   * @param $flavor
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   *
   * @return mixed
   */
  private function getGameJam($flavor, GameJamRepository $game_jam_repository)
  {
    $gamejam = $game_jam_repository->getLatestGameJamByFlavor($flavor);

    if (null == $gamejam)
    {
      $gamejam = $game_jam_repository->getLatestGameJam();
    }

    if (null == $gamejam)
    {
      throw new NoGameJamException();
    }

    return $gamejam;
  }

  /**
   * @param $gamejam GameJam
   * @param $user User
   * @param $program Program
   * @param $request Request
   *
   * @return mixed
   */
  private function assembleFormUrl($gamejam, $user, $program, $request)
  {
    $languageCode = $this->getLanguageCode($request);

    $url = $gamejam->getFormUrl();
    $url = str_replace('%CAT_ID%', $program->getId(), $url);
    $url = str_replace('%CAT_MAIL%', $user->getEmail(), $url);
    $url = str_replace('%CAT_NAME%', $user->getUsername(), $url);

    return str_replace('%CAT_LANGUAGE%', $languageCode, $url);
  }

  /**
   * @param $request Request
   *
   * @return string
   */
  private function getLanguageCode($request)
  {
    $languageCode = strtoupper(substr($request->getLocale(), 0, 2));

    switch ($languageCode)
    {
      case 'DE':
      case 'IT':
      case 'PL':
      case 'ES':
        break;
      default:
        $languageCode = 'EN';
    }

    return $languageCode;
  }

  private function persistAndFlush(Program $program)
  {
    $this->getDoctrine()->getManager()->persist($program);
    $this->getDoctrine()->getManager()->flush();
  }
}
