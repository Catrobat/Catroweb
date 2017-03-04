<?php
namespace Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use Catrobat\AppBundle\Requests\TemplateRequest;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Symfony\Component\Filesystem\Exception\IOException;

class TemplateManager
{

    const LANDSCAPE_PREFIX = 'l_';
    const PORTRAIT_PREFIX = 'p_';

    protected $file_repository;

    protected $screenshot_repository;

    protected $entity_manager;

    protected $template_repository;


    public function __construct($file_repository, $screenshot_repository, $entity_manager, $template_repository)
    {
        $this->file_repository = $file_repository;
        $this->screenshot_repository = $screenshot_repository;
        $this->entity_manager = $entity_manager;
        $this->template_repository = $template_repository;
    }

    private function saveThumbnail(Template $template){
        $file = $template->getThumbnail();
        if($file == null){
            return;
        }
        /* @var $thumbnail \Symfony\Component\HttpFoundation\File\UploadedFile*/
        $thumbnail = $template->getThumbnail();
        $this->screenshot_repository->saveProgramAssets($thumbnail->getPathname(), $template->getId());
    }

    private function saveLandscapeProgram(Template $template){
        $file = $template->getLandscapeProgramFile();
        $this->saveTemplateProgram($file, self::LANDSCAPE_PREFIX.$template->getId());

    }

    private function savePortraitProgram(Template $template){
        $file = $template->getPortraitProgramFile();
        $this->saveTemplateProgram($file, self::PORTRAIT_PREFIX.$template->getId());

    }

    private function saveTemplateProgram($file, $id){
        if($file == null){
            return;
        }
        $this->file_repository->saveProgramFile($file, $id);

    }

    public function saveTemplateFiles(Template $template){
        if($template->getId() != null) {
            $this->saveThumbnail($template);
            $this->savePortraitProgram($template);
            $this->saveLandscapeProgram($template);
        }
    }

    public function findOneByName($templateName)
    {
        return $this->template_repository->findOneByName($templateName);
    }

    public function findAll()
    {
        return $this->template_repository->findAll();
    }

    public function findAllActive()
    {
        return $this->template_repository->findByActive(true);
    }

    public function deleteTemplateFiles($id){
        $this->file_repository->deleteTemplateFiles(self::LANDSCAPE_PREFIX . $id);
        $this->file_repository->deleteTemplateFiles(self::PORTRAIT_PREFIX . $id);
        $this->screenshot_repository->deleteThumbnail($id);
        $this->screenshot_repository->deleteScreenshot($id);
    }
}
