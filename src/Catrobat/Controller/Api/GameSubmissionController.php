<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\GameJam;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Catrobat\Exceptions\Upload\NoGameJamException;
use App\Catrobat\Responses\ProgramListResponse;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


/**
 * Class GameSubmissionController
 * @package App\Catrobat\Controller\Api
 */
class GameSubmissionController extends Controller
{

  /**
   * @Route("/api/gamejam/finalize/{id}", name="gamejam_form_submission", methods={"GET"})
   *
   * @param Request $request
   * @param Program $program
   *
   * @return JsonResponse
   */
  public function formSubmittedAction(Request $request, Program $program)
  {
    if ($program->getGamejam() != null)
    {
      if (!$program->isAcceptedForGameJam())
      {
        $program->setAcceptedForGameJam(true);
        $this->persistAndFlush($program);
      }

      return JsonResponse::create([
        "statusCode" => "200",
        "message"    => "Program accepted for this gamejam",
      ]);
    }
    else
    {
      return JsonResponse::create([
        "statusCode" => "999",
        "message"    => "This program was not submitted to a gamejam",
      ]);
    }
  }


  /**
   * @Route("/api/gamejam/sampleProjects.json", name="api_gamejam_sample_programs", methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function getSampleProgramsForLatestGamejam(Request $request)
  {
    $flavor = $request->get('flavor');

    $gamejam = $this->getGameJam($flavor);

    $offset = intval($request->query->get('offset', 0));
    $limit = intval($request->query->get('limit', 20));

    $all_samples = $gamejam->getSamplePrograms();
    $count = count($all_samples);
    $returning_samples = null;

    for ($j = 0, $i = $offset; $i < $count && $i < $limit; $j++, $i++)
    {
      $returning_samples[$j] = $all_samples[$i];
    }

    return new ProgramListResponse($returning_samples, $returning_samples !== null ? count($returning_samples) : 0);
  }


  /**
   * @Route("/api/gamejam/submissions.json", name="api_gamejam_submissions", methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function getSubmissionsForLatestGamejam(Request $request)
  {
    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));

    $flavor = $request->get('flavor');

    $gamejam = $this->getGameJam($flavor);

    $criteria_count = Criteria::create()->where(Criteria::expr()->eq("gamejam_submission_accepted", true));
    $criteria = Criteria::create()->where(Criteria::expr()->eq("gamejam_submission_accepted", true))
      ->andWhere(Criteria::expr()->eq("visible", true))
      ->orderBy(["gamejam_submission_date" => Criteria::DESC])
      ->setFirstResult($offset)
      ->setMaxResults($limit);

    return new ProgramListResponse($gamejam->getPrograms()->matching($criteria), $gamejam->getPrograms()
      ->matching($criteria_count)
      ->count());
  }


  /**
   * @param $flavor
   *
   * @return mixed
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  private function getGameJam($flavor)
  {
    $gamejam = $this->get("gamejamrepository")->getLatestGameJamByFlavor($flavor);

    if ($gamejam == null)
    {
      $gamejam = $this->get("gamejamrepository")->getLatestGameJam();
    }

    if ($gamejam == null)
    {
      throw new NoGameJamException();
    }

    return $gamejam;
  }

  /**
   * @Route("/gamejam/submit/{id}", name="gamejam_web_submit", methods={"GET"})
   *
   * @param Request $request
   * @param Program $program
   *
   * @return RedirectResponse
   * @throws \Exception
   */
  public function webSubmitAction(Request $request, Program $program)
  {
    if ($this->getUser() == null)
    {
      throw new AuthenticationException();
    }
    $gamejam = $this->get("gamejamrepository")->getCurrentGameJam();
    if ($gamejam == null)
    {
      throw new \Exception("No Game Jam!");
    }

    if ($program->getGamejam() != null && $program->getGamejam() != $gamejam)
    {
      throw new \Exception("Game was alraedy submitted to another gamejam!");
    }

    if ($program->isAcceptedForGameJam())
    {
      return new RedirectResponse($this->generateUrl("program", [
        "id" => $program->getId(),
      ]));
    }

    if ($this->getUser() != $program->getUser())
    {
      return new RedirectResponse($this->generateUrl("gamejam_submit_own"));
    }

    $program->setGamejam($gamejam);
    $program->setGameJamSubmissionDate(new \DateTime());

    $this->get('catroweb.gamejamtag.check')->checkDescriptionTag($program);

    $this->persistAndFlush($program);

    $url = $this->assembleFormUrl($gamejam, $program->getUser(), $program, $request);

    if ($url != null)
    {
      return new RedirectResponse($url);
    }
    else
    {
      return new RedirectResponse($this->generateUrl("program", [
        "id" => $program->getId(),
      ]));
    }
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
    $url = str_replace("%CAT_ID%", $program->getId(), $url);
    $url = str_replace("%CAT_MAIL%", $user->getEmail(), $url);
    $url = str_replace("%CAT_NAME%", $user->getUsername(), $url);
    $url = str_replace("%CAT_LANGUAGE%", $languageCode, $url);

    return $url;
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

  /**
   * @param Program $program
   */
  private function persistAndFlush(Program $program)
  {
    $this->getDoctrine()->getManager()->persist($program);
    $this->getDoctrine()->getManager()->flush();
  }
}