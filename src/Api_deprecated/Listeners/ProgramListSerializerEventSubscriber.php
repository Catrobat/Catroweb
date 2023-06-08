<?php

namespace App\Api_deprecated\Listeners;

use App\Api_deprecated\Responses\ProgramListResponse;
use App\DB\Entity\Project\Program;
use App\Storage\ImageRepository;
use App\Storage\ScreenshotRepository;
use App\Utils\ElapsedTimeStringFormatter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated
 */
class ProgramListSerializerEventSubscriber implements EventSubscriberInterface
{
  public function __construct(private readonly ScreenshotRepository $screenshot_repository, private readonly RequestStack $request_stack, private readonly RouterInterface $router, private readonly ElapsedTimeStringFormatter $time_formatter, private readonly ImageRepository $example_image_repository, private readonly ParameterBagInterface $parameter_bag)
  {
  }

  public function onKernelView(ViewEvent $event): void
  {
    $result = $event->getControllerResult();
    if (!($result instanceof ProgramListResponse)) {
      return;
    }

    $programs = $result->getPrograms();
    $request = $this->request_stack->getCurrentRequest();

    $retArray = [];
    $retArray['CatrobatProjects'] = [];

    /** @var Program $program */
    foreach ($programs as $program) {
      $new_program = [];
      $example = false;
      if ($program->isExample()) {
        $new_program['ExampleId'] = $program->getId();
        $new_program['Extension'] = $program->getImageType();
        $example = true;
        $program = $program->getProgram();
      }
      $new_program['ProjectId'] = $program->getId();
      $new_program['ProjectName'] = $program->getName();
      $new_program['ProjectNameShort'] = $program->getName();
      $new_program['Author'] = $program->getUser()->getUserIdentifier();
      $new_program['Description'] = $program->getDescription();
      $new_program['Version'] = $program->getCatrobatVersionName();
      $new_program['Views'] = $program->getViews();
      $new_program['Downloads'] = $program->getDownloads();
      $new_program['Private'] = $program->getPrivate();
      $new_program['Uploaded'] = $program->getUploadedAt()->getTimestamp();
      $new_program['UploadedString'] = $this->time_formatter->format($program->getUploadedAt()
        ->getTimestamp());
      if ($example) {
        $new_program['ScreenshotBig'] = $this->example_image_repository->getWebPath(intval($new_program['ExampleId']), $new_program['Extension'], false);
        $new_program['ScreenshotSmall'] = $this->example_image_repository->getWebPath(intval($new_program['ExampleId']), $new_program['Extension'], false);
      } else {
        $new_program['ScreenshotBig'] = $this->screenshot_repository->getScreenshotWebPath($program->getId());
        $new_program['ScreenshotSmall'] = $this->screenshot_repository->getThumbnailWebPath($program->getId());
      }
      $new_program['ProjectUrl'] = ltrim($this->generateUrl('program', [
        'theme' => $this->parameter_bag->get('umbrellaTheme'),
        'id' => $program->getId(),
      ]), '/');
      $new_program['DownloadUrl'] = ltrim($this->generateUrl('open_api_server_projects_projectidcatrobatget', [
        'id' => $program->getId(),
      ]), '/');
      $new_program['FileSize'] = $program->getFilesize() / 1_048_576;
      $retArray['CatrobatProjects'][] = $new_program;
    }

    $retArray['completeTerm'] = '';
    $retArray['preHeaderMessages'] = '';

    $retArray['CatrobatInformation'] = [
      'BaseUrl' => $request->getSchemeAndHttpHost().'/',
      'TotalProjects' => $result->getTotalPrograms(),
      'ProjectsExtension' => '.catrobat',
    ];

    $event->setResponse(new JsonResponse($retArray));
  }

  public function generateUrl(string $route, array $parameters = []): string
  {
    return $this->router->generate($route, $parameters);
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::VIEW => 'onKernelView'];
  }
}
