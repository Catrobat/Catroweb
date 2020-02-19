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

/**
 * Class TemplateListSerializer.
 */
class TemplateListSerializer
{
  /**
   * @var RequestStack
   */
  private $request_stack;
  /**
   * @var
   */
  private $template_path;
  /**
   * @var ScreenshotRepository
   */
  private $screenshot_repository;
  /**
   * @var ElapsedTimeStringFormatter
   */
  private $time_formatter;

  /**
   * TemplateListSerializer constructor.
   *
   * @param $template_screenshot_repository
   * @param $catrobat_template_storage_path
   */
  public function __construct($template_screenshot_repository, RequestStack $request_stack,
                              $catrobat_template_storage_path, ElapsedTimeStringFormatter $time_formatter)
  {
    $this->request_stack = $request_stack;
    $this->template_path = $catrobat_template_storage_path;
    $this->screenshot_repository = $template_screenshot_repository;
    $this->time_formatter = $time_formatter;
  }

  public function onKernelView(ViewEvent $event)
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
        if (null != $new_template)
        {
          $retArray['CatrobatTemplates'][] = $new_template;
        }
      }
    }
    $retArray['BaseUrl'] = $request->getSchemeAndHttpHost().'/';
    $retArray['ProjectsExtension'] = '.catrobat';

    $event->setResponse(JsonResponse::create($retArray));
  }

  /**
   * @param      $id
   * @param bool $landscape
   *
   * @return string
   */
  public function generateUrl($id, $landscape = true)
  {
    $prefix = TemplateManager::PORTRAIT_PREFIX;
    if ($landscape)
    {
      $prefix = TemplateManager::LANDSCAPE_PREFIX;
    }

    return ltrim($this->template_path.$prefix.$id.'.catrobat', '/');
  }

  /**
   * @param $template Template
   *
   * @return array
   */
  public function generateTemplateArray($template)
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
