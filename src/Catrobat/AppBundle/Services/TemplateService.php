<?php

namespace Catrobat\AppBundle\Services;


use Catrobat\AppBundle\Entity\Template;
use Catrobat\AppBundle\Entity\TemplateManager;
use Catrobat\AppBundle\Requests\TemplateRequest;

class TemplateService
{

    /* @var $templateManager \Catrobat\AppBundle\Entity\TemplateManager*/
    private $templateManager;

    public function __construct(TemplateManager $templateManager) {
        $this->templateManager = $templateManager;
    }

    public function saveFiles(Template $template){
        $this->templateManager->saveTemplateFiles($template);
    }

    public function deleteTemplateFiles($id){
        $this->templateManager->deleteTemplateFiles($id);
    }
}