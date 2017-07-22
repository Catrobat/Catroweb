<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

abstract class Brick
{
    protected $brick_xml_properties;
    protected $type;
    protected $caption;
    private $img_file;

    public function __construct(\SimpleXMLElement $brick_xml_properties)
    {
        $this->brick_xml_properties = $brick_xml_properties;
        $this->create();
    }

    abstract protected function create();

    public function getType()
    {
        return $this->type;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function getImgFile()
    {
        return $this->img_file;
    }

    protected function setImgFile($img_file)
    {
        if ($this->isCommentedOut() or $this->hasCommentedOutParentScript())
            $this->commentOut();
        else
            $this->img_file = $img_file;
    }

    private function isCommentedOut()
    {
        return ($this->brick_xml_properties->commentedOut != null
          and $this->brick_xml_properties->commentedOut == 'true');
    }

    private function hasCommentedOutParentScript()
    {
        $xpath_query_result = $this->brick_xml_properties->xpath('../../commentedOut');
        return ($xpath_query_result != null and $xpath_query_result[0] == 'true');
    }

    public function commentOut()
    {
        $this->img_file = Constants::UNKNOWN_BRICK_IMG;
    }
}