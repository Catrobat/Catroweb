<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\ProgramListResponse;
use App\Catrobat\Twig\AppExtension;
use App\Entity\Program;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProgramController.
 */
class ProgramController extends AbstractController
{
  /**
   * @Route("/api/projects/getInfoById.json", name="api_info_by_id", defaults={"_format": "json"}, methods={"GET"})
   *
   * @return ProgramListResponse|JsonResponse
   */
  public function showProgramAction(Request $request, ProgramManager $program_manager)
  {
    /** @var ProgramManager $program_manager */
    $id = $request->get('id', 0);

    $programs = [];
    $program = $program_manager->find($id);
    if (null === $program)
    {
      return JsonResponse::create(['Error' => 'Project not found (uploaded)', 'preHeaderMessages' => '']);
    }

    $numbOfTotalProjects = 1;
    $programs[] = $program;

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  /**
   * @Route("/api/project/{id}/likes", name="api_project_likes", methods={"GET"})
   *
   * @param $id
   *
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function projectLikesAction($id, ProgramManager $program_manager)
  {
    /** @var Program $program */
    $program = $program_manager->find($id);
    if (!$program || !$program_manager->isProjectVisibleForCurrentUser($program))
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $data = [];

    $user_objects = [];

    /** @var ProgramLike $like */
    foreach ($program->getLikes()->getIterator() as $like)
    {
      if (array_key_exists($like->getUser()->getId(), $user_objects))
      {
        $obj = $user_objects[$like->getUser()->getId()];
        $obj->types[] = $like->getTypeAsString();
      }
      else
      {
        $obj = new \stdClass();
        $obj->user = new \stdClass();
        $obj->user->id = $like->getUser()->getId();
        $obj->user->name = $like->getUser()->getUsername();
        $obj->types = [$like->getTypeAsString()];
        $data[] = $obj;
        $user_objects[$like->getUser()->getId()] = $obj;
      }
    }

    return JsonResponse::create($data);
  }

  /**
   * @Route("/api/project/{id}/likes/count", name="api_project_likes_count", methods={"GET"})
   *
   * @param $id
   *
   * @throws NotFoundHttpException
   *
   * @return JsonResponse
   */
  public function projectLikesCountAction(Request $request, $id, ProgramManager $program_manager,
                                          TranslatorInterface $translator)
  {
    /** @var Program $program */
    $program = $program_manager->find($id);
    if (!$program || !$program_manager->isProjectVisibleForCurrentUser($program))
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $user_locale = $request->getLocale();

    $data = new \stdClass();
    $data->total = new \stdClass();
    $data->total->value = $program_manager->totalLikeCount($program->getId());
    $data->total->stringValue = AppExtension::humanFriendlyNumber(
      $data->total->value, $translator, $user_locale
    );

    foreach (ProgramLike::$VALID_TYPES as $type_id)
    {
      $type_name = ProgramLike::$TYPE_NAMES[$type_id];
      $data->{$type_name} = new \stdClass();
      $data->{$type_name}->value = $program_manager->likeTypeCount($program->getId(), $type_id);
      $data->{$type_name}->stringValue = AppExtension::humanFriendlyNumber(
        $data->{$type_name}->value, $translator, $user_locale
      );
    }

    return JsonResponse::create($data);
  }
}
