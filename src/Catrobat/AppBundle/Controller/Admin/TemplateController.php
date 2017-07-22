<?php


namespace Catrobat\AppBundle\Controller\Admin;

use Catrobat\AppBundle\Entity\TemplateManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Catrobat\AppBundle\Entity\Template;
use Catrobat\AppBundle\Services\TemplateService;

class TemplateController extends CRUDController
{
    public function createAction()
    {
        $response = parent::createAction();
        $this->saveFiles();
        return $response;
    }

    public function deleteAction($id)
    {
        $templateService = $this->getTemplateService();
        $templateService->deleteTemplateFiles($id);
        return parent::deleteAction($id);
    }

    public function editAction($id = null)
    {
        $render = parent::editAction($id);
        $this->saveFiles();
        return $render;
    }


    private function saveFiles(){
        /* @var $templateService \Catrobat\AppBundle\Services\TemplateService */
        /* @var $template \Catrobat\AppBundle\Entity\Template */
        $template = $this->getTemplate();
        if($template->getId() != null){
            $templateService = $this->getTemplateService();
            $templateService->saveFiles($template);
        }
    }

    private function getTemplateService(){
        return $this->get("template_service");
    }

    private function getTemplate(){
        $object = $this->admin->getSubject();
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object'));
        }
        return $object;
    }
}