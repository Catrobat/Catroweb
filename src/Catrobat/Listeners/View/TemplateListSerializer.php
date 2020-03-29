<?php

namespace App\Catrobat\Listeners\View;

use App\Catrobat\Responses\TemplateListResponse;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Template;
use App\Entity\TemplateManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class TemplateListSerializer
{
  private RequestStack $request_stack;

  private string $template_path;

  private ScreenshotRepository $screenshot_repository;

  private ElapsedTimeStringFormatter $time_formatter;

  public function __construct(ScreenshotRepository $template_screenshot_repository, RequestStack $request_stack,
                              string $catrobat_template_storage_path, ElapsedTimeStringFormatter $time_formatter)
  {
    $this->request_stack = $request_stack;
    $this->template_path = $catrobat_template_storage_path;
    $this->screenshot_repository = $template_screenshot_repository;
    $this->time_formatter = $time_formatter;
  }

  public function onKernelView(ViewEvent $event): void
  {
    $result = $event->getControllerResult();
    if (!($result instanceof TemplateListResponse))
    {
      return;
    }

    $templates = $result->getTemplates();
    $request = $this->request_stack->getCurrentRequest();

    $retArray = [];
    $retArray['CatrobatTemplates'] = [];
    if (null != $templates)
    {
      foreach ($templates as $template)
      {
        $new_template = $this->generateTemplateArray($template);
        if (null !== $new_template)
        {
          $retArray['CatrobatTemplates'][] = $new_template;
        }
      }
    }
    $retArray['BaseUrl'] = $request->getSchemeAndHttpHost().'/';
    $retArray['ProjectsExtension'] = '.catrobat';

    $event->setResponse(JsonResponse::create($retArray));
  }

  public function generateUrl(int $id, bool $landscape = true): string
  {
    $prefix = TemplateManager::PORTRAIT_PREFIX;
    if ($landscape)
    {
      $prefix = TemplateManager::LANDSCAPE_PREFIX;
    }

    return ltrim($this->template_path.$prefix.$id.'.catrobat', '/');
  }

  public function generateTemplateArray(Template $template): ?array
  {
    $landscape = $this->generateUrl($template->getId());
    $portrait = $this->generateUrl($template->getId(), false);

    if (!file_exists($landscape) && !file_exists($portrait))
    {
      return null;
    }

    $new_template = [];
    $new_template['id'] = $template->getId();
    $new_template['name'] = $template->getName();
    $new_template['thumbnail'] = $this->screenshot_repository->getThumbnailWebPath($template->getId());

    if (file_exists($landscape))
    {
      $new_template['landscape'] = $landscape;
    }

    if (file_exists($portrait))
    {
      $new_template['portrait'] = $portrait;
    }

    return $new_template;
  }
}
