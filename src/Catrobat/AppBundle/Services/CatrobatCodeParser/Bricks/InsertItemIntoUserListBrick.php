<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class InsertItemIntoUserListBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::INSERT_ITEM_LIST_BRICK;

    $user_list_name = null;
    if ($this->brick_xml_properties->userList[Constants::REFERENCE_ATTRIBUTE] == null)
    {
      $user_list_name = $this->brick_xml_properties->userList->name;
    }
    else
    {
      $user_list_name = $this->brick_xml_properties->userList
        ->xpath($this->brick_xml_properties->userList[Constants::REFERENCE_ATTRIBUTE])[0]->name;
    }
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Insert " . $formulas[Constants::INSERT_ITEM_LIST_VALUE_FORMULA] . " into list "
      . $user_list_name . " at position " . $formulas[Constants::INSERT_ITEM_LIST_INDEX_FORMULA];

    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}