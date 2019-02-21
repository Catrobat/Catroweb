<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ReplaceItemInUserListBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class ReplaceItemInUserListBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::REPLACE_ITEM_LIST_BRICK;

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
    $this->caption = "Replace item in list " . $user_list_name . " at position "
      . $formulas[Constants::REPLACE_ITEM_LIST_INDEX_FORMULA] . " with "
      . $formulas[Constants::REPLACE_ITEM_LIST_VALUE_FORMULA];

    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}