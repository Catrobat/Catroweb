<?php

namespace App\Api_deprecated\Listeners;

use App\Api_deprecated\Responses\ProgramListResponse;
use App\Catrobat\Services\ImageRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use App\Utils\ElapsedTimeStringFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated
 */
class ProgramListSerializer
{
  private RequestStack $request_stack;

  private RouterInterface $router;

  private ScreenshotRepository $screenshot_repository;

  private ElapsedTimeStringFormatter $time_formatter;

  private ImageRepository $example_image_repository;

  public function __construct(ScreenshotRepository $screenshot_repository, RequestStack $request_stack,
                              RouterInterface $router, ElapsedTimeStringFormatter $time_formatter, ImageRepository $example_image_repository)
  {
    $this->request_stack = $request_stack;
    $this->router = $router;
    $this->screenshot_repository = $screenshot_repository;
    $this->time_formatter = $time_formatter;
    $this->example_image_repository = $example_image_repository;
  }

  public function onKernelView(ViewEvent $event): void
  {
    $result = $event->getControllerResult();
    if (!($result instanceof ProgramListResponse))
    {
      return;
    }

    $programs = $result->getPrograms();
    $details = $result->getShowDetails();
    $request = $this->request_stack->getCurrentRequest();

    $retArray = [];
    $retArray['CatrobatProjects'] = [];
    if (null !== $programs)
    {
      /** @var Program $program */
      foreach ($programs as $program)
      {
        $new_program = [];
        $example = false;
        if ($program->isExample())
        {
          $new_program['ExampleId'] = $program->getId();
          $new_program['Extension'] = $program->getImageType();
          $example = true;
          $program = $program->getProgram();
        }
        $new_program['ProjectId'] = $program->getId();
        $new_program['ProjectName'] = $program->getName();
        if ($details)
        {
          $new_program['ProjectNameShort'] = $program->getName();
          $new_program['Author'] = $program->getUser()->getUserName();
          $new_program['Description'] = $program->getDescription();
          $new_program['Version'] = $program->getCatrobatVersionName();
          $new_program['Views'] = $program->getViews();
          $new_program['Downloads'] = $program->getDownloads();
          $new_program['Private'] = $program->getPrivate();
          $new_program['Uploaded'] = $program->getUploadedAt()->getTimestamp();
          $new_program['UploadedString'] = $this->time_formatter->getElapsedTime($program->getUploadedAt()
            ->getTimestamp());
          if ($example)
          {
            $new_program['ScreenshotBig'] = $this->example_image_repository->getWebPath(intval($new_program['ExampleId']), $new_program['Extension'], false);
            $new_program['ScreenshotSmall'] = $this->example_image_repository->getWebPath(intval($new_program['ExampleId']), $new_program['Extension'], false);
          }
          else
          {
            $new_program['ScreenshotBig'] = $this->screenshot_repository->getScreenshotWebPath($program->getId());
            $new_program['ScreenshotSmall'] = $this->screenshot_repository->getThumbnailWebPath($program->getId());
          }
          $new_program['ProjectUrl'] = ltrim($this->generateUrl('program', [
            'flavor' => $event->getRequest()->getSession()->get('flavor_context'),
            'id' => $program->getId(),
          ]), '/');
          $new_program['DownloadUrl'] = ltrim($this->generateUrl('download', [
            'id' => $program->getId(),
          ]), '/');
          $new_program['FileSize'] = $program->getFilesize() / 1_048_576;
        }
        $retArray['CatrobatProjects'][] = $new_program;
      }
    }
    $retArray['completeTerm'] = '';
    $retArray['preHeaderMessages'] = '';

    if ($result->isIsUserSpecificRecommendation())
    {
      $retArray['isUserSpecificRecommendation'] = true;
    }

    Request::setTrustedProxies([$request->server->get('REMOTE_ADDR')],
      Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
    $retArray['CatrobatInformation'] = [
      'BaseUrl' => $request->getSchemeAndHttpHost().'/',
      'TotalProjects' => $result->getTotalPrograms(),
      'ProjectsExtension' => '.catrobat',
    ];

    $event->setResponse(JsonResponse::create($retArray));
  }

  /**
   * @param mixed $route
   */
  public function generateUrl($route, array $parameters = []): string
  {
    return $this->router->generate($route, $parameters);
  }
}
