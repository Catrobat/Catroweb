<?php

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Responses\ProgramListResponse;
use App\Application\Twig\TwigExtension;
use App\DB\Entity\Project\ProgramLike;
use App\Project\ProgramManager;
use Exception;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class ProgramController extends AbstractController
{
  /**
   * @deprecated
   *
   * @Route("/api/projects/getInfoById.json", name="api_info_by_id", defaults={"_format": "json"}, methods={"GET"})
   *
   * @return JsonResponse|ProgramListResponse
   */
  public function showProgramAction(Request $request, ProgramManager $program_manager)
  {
    /** @var ProgramManager $program_manager */
    $id = $request->get('id', 0);

    $programs = [];
    $program = $program_manager->find($id);
    if (null === $program) {
      return JsonResponse::create(['Error' => 'Project not found (uploaded)', 'preHeaderMessages' => '']);
    }

    $numbOfTotalProjects = 1;
    $programs[] = $program;

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  /**
   * @deprecated
   *
   * @Route("/api/project/{id}/likes", name="api_project_likes", methods={"GET"})
   *
   * @throws Exception
   */
  public function projectLikesAction(string $id, ProgramManager $program_manager): JsonResponse
  {
    $program = $program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $program) {
      throw $this->createNotFoundException("Can't like a project that's not visible to you!; Id: ``{$id}");
    }

    $data = [];

    $user_objects = [];

    /** @var ProgramLike $like */
    foreach ($program->getLikes()->getIterator() as $like) {
      if (array_key_exists($like->getUser()->getId(), $user_objects)) {
        $obj = $user_objects[$like->getUser()->getId()];
        $obj->types[] = $like->getTypeAsString();
      } else {
        $obj = new stdClass();
        $obj->user = new stdClass();
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
   * @deprecated
   *
   * @Route("/api/project/{id}/likes/count", name="api_project_likes_count", methods={"GET"})
   *
   * @throws NotFoundHttpException
   */
  public function projectLikesCountAction(Request $request, string $id, ProgramManager $program_manager,
                                          TranslatorInterface $translator): JsonResponse
  {
    $program = $program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $program) {
      throw $this->createNotFoundException("Can't count likes of a project that's not visible to you!; Id: `{$id}`");
    }

    $user_locale = $request->getLocale();

    $data = new stdClass();
    $data->total = new stdClass();
    $data->total->value = $program_manager->totalLikeCount($id);
    $data->total->stringValue = TwigExtension::humanFriendlyNumber(
      $data->total->value, $translator, $user_locale
    );

    foreach (ProgramLike::$VALID_TYPES as $type_id) {
      $type_name = ProgramLike::$TYPE_NAMES[$type_id];
      $data->{$type_name} = new stdClass();
      $data->{$type_name}->value = $program_manager->likeTypeCount($id, $type_id);
      $data->{$type_name}->stringValue = TwigExtension::humanFriendlyNumber(
        $data->{$type_name}->value, $translator, $user_locale
      );
    }

    return JsonResponse::create($data);
  }
}
