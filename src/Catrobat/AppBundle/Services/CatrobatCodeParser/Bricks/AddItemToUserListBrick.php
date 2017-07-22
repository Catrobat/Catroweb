<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class AddItemToUserListBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::ADD_ITEM_LIST_BRICK;

        $user_list = null;
        if ($this->brick_xml_properties->userList[Constants::REFERENCE_ATTRIBUTE] == null)
            $user_list = $this->brick_xml_properties->userList->name;
        else
            $user_list = $this->brick_xml_properties->userList
              ->xpath($this->brick_xml_properties->userList[Constants::REFERENCE_ATTRIBUTE])[0];
        $this->caption = "Add "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::LIST_ADD_ITEM_FORMULA]
          . " to list " . $user_list;

        $this->setImgFile(Constants::DATA_BRICK_IMG);
    }
}