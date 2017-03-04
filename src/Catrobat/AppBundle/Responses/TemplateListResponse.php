<?php


namespace Catrobat\AppBundle\Responses;


class TemplateListResponse
{

    private $templates;

    /**
     * TemplateListResponse constructor.
     */
    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return mixed
     */
    public function getTemplates()
    {
        return $this->templates;
    }

}