<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class DeleteItemOfUserListBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class DeleteItemOfUserListBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::DELETE_ITEM_LIST_BRICK;

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
    $this->caption = "Delete item from list " . $user_list_name . " at position "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::LIST_DELETE_ITEM_FORMULA];

    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}