<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;

class CodeViewController extends Controller
{
  // Attributes
  const TYPE_ATTRIBUTE = 'type';
  const REFERENCE_ATTRIBUTE = 'reference';
  const NAME_ATTRIBUTE = 'name';
  const CATEGORY_ATTRIBUTE = 'category';

  // Scritps
  const START_SCRIPT = 'StartScript';
  const WHEN_SCRIPT = 'WhenScript';
  const BROADCAST_SCRIPT = 'BroadcastScript';
  const COLLISION_SCRIPT = 'CollisionScript';

  // Bricks
  const WAIT_BRICK = 'WaitBrick';
  const NOTE_BRICK = 'NoteBrick';
  const FOREVER_BRICK = 'ForeverBrick';
  const LOOP_ENDLESS_BRICK = "LoopEndlessBrick";
  const IF_BRICK = 'IfLogicBeginBrick';
  const ELSE_BRICK = 'IfLogicElseBrick';
  const ENDIF_BRICK = 'IfLogicEndBrick';
  const REPEAT_BRICK = 'RepeatBrick';
  const LOOP_END_BRICK = 'LoopEndBrick';
  const BROADCAST_BRICK = 'BroadcastBrick';
  const BROADCAST_WAIT_BRICK = 'BroadcastWaitBrick';
  const PLACE_AT_BRICK = 'PlaceAtBrick';
  const SET_X_BRICK = 'SetXBrick';
  const SET_Y_BRICK = 'SetYBrick';
  const CHANGE_X_BY_N_BRICK = 'ChangeXByNBrick';
  const CHANGE_Y_BY_N_BRICK = 'ChangeYByNBrick';
  const IF_ON_EDGE_BOUNCE_BRICK = 'IfOnEdgeBounceBrick';
  const MOVE_N_STEPS_BRICK = 'MoveNStepsBrick';
  const TURN_LEFT_BRICK = 'TurnLeftBrick';
  const TURN_RIGHT_BRICK = 'TurnRightBrick';
  const POINT_IN_DIRECTION_BRICK = 'PointInDirectionBrick';
  const GLIDE_TO_BRICK = 'GlideToBrick';
  const GO_N_STEPS_BACK_BRICK = 'GoNStepsBackBrick';
  const COME_TO_FRONT_BRICK = 'ComeToFrontBrick';
  const VIBRATION_BRICK = 'VibrationBrick';
  const SET_PHYSICS_OBJECT_TYPE_BRICK = 'SetPhysicsObjectTypeBrick';
  const SET_VELOCITY_BRICK = 'SetVelocityBrick';
  const TURN_LEFT_SPEED_BRICK = 'TurnLeftSpeedBrick';
  const TURN_RIGHT_SPEED_BRICK = 'TurnRightSpeedBrick';
  const SET_GRAVITY_BRICK = 'SetGravityBrick';
  const SET_MASS_BRICK = 'SetMassBrick';
  const SET_BOUNCE_BRICK = 'SetBounceBrick';
  const SET_FRICTION_BRICK = 'SetFrictionBrick';
  const POINT_TO_BRICK = 'PointToBrick';
  const PLAY_SOUND_BRICK = 'PlaySoundBrick';
  const STOP_ALL_SOUNDS_BRICK = 'StopAllSoundsBrick';
  const SET_VOLUME_TO_BRICK = 'SetVolumeToBrick';
  const CHANGE_VOLUME_BY_N_BRICK = 'ChangeVolumeByNBrick';
  const SPEAK_BRICK = 'SpeakBrick';
  const SET_LOOK_BRICK = 'SetLookBrick';
  const NEXT_LOOK_BRICK = 'NextLookBrick';
  const CAMERA_BRICK = 'CameraBrick';
  const CHOOSE_CAMERA_BRICK = 'ChooseCameraBrick';
  const SET_SIZE_TO_BRICK = 'SetSizeToBrick';
  const CHANGE_SIZE_BY_N_BRICK = 'ChangeSizeByNBrick';
  const HIDE_BRICK = 'HideBrick';
  const SHOW_BRICK = 'ShowBrick';
  const SET_TRANSPARENCY_BRICK = 'SetTransparencyBrick';
  const CHANGE_TRANSPARENCY_BY_N_BRICK = 'ChangeTransparencyByNBrick';
  const SET_BRIGHTNESS_BRICK = 'SetBrightnessBrick';
  const CHANGE_BRIGHTNESS_BY_N_BRICK = 'ChangeBrightnessByNBrick';
  const SET_COLOR_BRICK = 'SetColorBrick';
  const CHANGE_COLOR_BY_N_BRICK = 'ChangeColorByNBrick';
  const CLEAR_GRAPHIC_EFFECT_BRICK = 'ClearGraphicEffectBrick';
  const FLASH_BRICK = 'FlashBrick';
  const SET_VARIABLE_BRICK = 'SetVariableBrick';
  const CHANGE_VARIABLE_BRICK = 'ChangeVariableBrick';
  const SHOW_TEXT_BRICK = 'ShowTextBrick';
  const HIDE_TEXT_BRICK = 'HideTextBrick';
  const ADD_ITEM_LIST_BRICK = 'AddItemToUserListBrick';
  const DELETE_ITEM_LIST_BRICK = 'DeleteItemOfUserListBrick';
  const INSERT_ITEM_LIST_BRICK = 'InsertItemIntoUserListBrick';
  const REPLACE_ITEM_LIST_BRICK = 'ReplaceItemInUserListBrick';
  const WHEN_BRICK = 'WhenBrick';
  const UNKNOWN_BRICK = 'UnknownBrick';

  // Brick Images
  const CONTROL_BRICK_IMG = '1h_brick_orange.png';
  const MOTION_BRICK_IMG = '1h_brick_blue.png';
  const SOUND_BRICK_IMG = '1h_brick_violet.png';
  const LOOKS_BRICK_IMG = '1h_brick_green.png';
  const DATA_BRICK_IMG = '1h_brick_red.png';
  const UNKNOWN_BRICK_IMG = '1h_brick_grey.png';

  // Formula Categories
  const TIME_TO_WAIT_IN_SECONDS_FORMULA = 'TIME_TO_WAIT_IN_SECONDS';
  const NOTE_FORMULA = 'NOTE';
  const IF_CONDITION_FORMULA = 'IF_CONDITION';
  const TIMES_TO_REPEAT_FORMULA = 'TIMES_TO_REPEAT';
  const X_POSITION_FORMULA = 'X_POSITION';
  const Y_POSITION_FORMULA = 'Y_POSITION';
  const X_POSITION_CHANGE_FORMULA = 'X_POSITION_CHANGE';
  const Y_POSITION_CHANGE_FORMULA = 'Y_POSITION_CHANGE';
  const STEPS_FORMUlA = 'STEPS';
  const TURN_LEFT_DEGREES_FORMULA = 'TURN_LEFT_DEGREES';
  const TURN_RIGHT_DEGREES_FORMULA = 'TURN_RIGHT_DEGREES';
  const DEGREES_FORMULA = 'DEGREES';
  const DURATION_IN_SECONDS_FORMULA = 'DURATION_IN_SECONDS';
  const Y_DESTINATION_FORMUlA = 'Y_DESTINATION';
  const X_DESTINATION_FORMULA = 'X_DESTINATION';
  const VIBRATE_DURATION_IN_SECONDS_FORMULA = 'VIBRATE_DURATION_IN_SECONDS';
  const VELOCITY_X_FORMULA = 'PHYSICS_VELOCITY_X';
  const VELOCITY_Y_FORMULA = 'PHYSICS_VELOCITY_Y';
  const TURN_LEFT_SPEED_FORMULA = 'PHYSICS_TURN_LEFT_SPEED';
  const TURN_RIGHT_SPEED_FORMULA = 'PHYSICS_TURN_RIGHT_SPEED';
  const GRAVITY_Y_FORMULA = 'PHYSICS_GRAVITY_Y';
  const GRAVITY_X_FORMULA = 'PHYSICS_GRAVITY_X';
  const MASS_FORMULA = 'PHYSICS_MASS';
  const BOUNCE_FACTOR_FORMULA = 'PHYSICS_BOUNCE_FACTOR';
  const FRICTION_FORMULA = 'PHYSICS_FRICTION';
  const VOLUME_FORMUlA = 'VOLUME';
  const VOLUME_CHANGE_FORMULA = 'VOLUME_CHANGE';
  const SPEAK_FORMULA = 'SPEAK';
  const SIZE_FORMULA = 'SIZE';
  const SIZE_CHANGE_FORMULA = 'SIZE_CHANGE';
  const TRANSPARENCY_FORMULA = 'TRANSPARENCY';
  const TRANSPARENCY_CHANGE_FORMULA = 'TRANSPARENCY_CHANGE';
  const BRIGHTNESS_FORMULA = 'BRIGHTNESS';
  const BRIGHTNESS_CHANGE_FORMULA = 'BRIGHTNESS_CHANGE';
  const COLOR_FORMUlA = 'COLOR';
  const COLOR_CHANGE_FORMULA = 'COLOR_CHANGE';
  const VARIABLE_FORMULA = 'VARIABLE';
  const VARIABLE_CHANGE_FORMULA = 'VARIABLE_CHANGE';
  const LIST_ADD_ITEM_FORMULA = 'LIST_ADD_ITEM';
  const LIST_DELETE_ITEM_FORMULA = 'LIST_DELETE_ITEM';
  const INSERT_ITEM_LIST_VALUE_FORMULA = 'INSERT_ITEM_INTO_USERLIST_VALUE';
  const INSERT_ITEM_LIST_INDEX_FORMULA = 'INSERT_ITEM_INTO_USERLIST_INDEX';
  const REPLACE_ITEM_LIST_VALUE_FORMULA = 'REPLACE_ITEM_IN_USERLIST_VALUE';
  const REPLACE_ITEM_LIST_INDEX_FORMULA = 'REPLACE_ITEM_IN_USERLIST_INDEX';

  const OPERATOR_FORMULA_TYPE = 'OPERATOR';
  const FUNCTION_FORMULA_TYPE = 'FUNCTION';

  const PLUS_OPERATOR = 'PLUS';
  const MINUS_OPERATOR = 'MINUS';
  const MULT_OPERATOR = 'MULT';
  const DIVIDE_OPERATOR = 'DIVIDE';
  const EQUAL_OPERATOR = 'EQUAL';
  const NOT_EQUAL_OPERATOR = 'NOT_EQUAL';
  const GREATER_OPERATOR = 'GREATER_THAN';
  const GREATER_EQUAL_OPERATOR = 'GREATER_OR_EQUAL';
  const SMALLER_OPERATOR = 'SMALLER_THAN';
  const SMALLER_EQUAL_OPERATOR = 'SMALLER_OR_EQUAL';
  const NOT_OPERATOR = 'LOGICAL_NOT';
  const AND_OPERATOR = 'LOGICAL_AND';
  const OR_OPERATOR = 'LOGICAL_OR';

  const POINTED_OBJECT_TAG = 'pointedObject';


  /**
   * @Method({"GET"})
   */
  public function codeViewAction($id)
  {
    $program = $this->get('programmanager')->find($id);
    $extracted_file_repository = $this->get('extractedfilerepository');

    try
    {
      $extracted_program = $extracted_file_repository->loadProgramExtractedFile($program);
      $twig_params = $this->computeTwigParams($extracted_program);
    }
    catch (\Exception $e)
    {
      $twig_params = array(
        'no_code' => true,
      );
    }

    return $this->get('templating')->renderResponse('::codeview.html.twig', $twig_params);
  }

  private function computeTwigParams($extracted_program) {
    $program_xml_tree = $extracted_program->getProgramXmlProperties();
    $objects = $this->getObjectsAsXml($program_xml_tree); // simplified

    $twig_params = null;
    if (!empty($objects)) {
      $background = null;
      $object_list = array();

      $counter = 0;
      foreach ($objects as $object) {
        if ($counter === 0)
          $background = $this->retrieveObjectFromXml($object);
        else
          $object_list[] = $this->retrieveObjectFromXml($object);
        $counter++;
      }

      $twig_params = array(
        'path' => $extracted_program->getWebPath(),
        'background' => $background,
        'object_list' => $object_list
      );
    } else {
      $twig_params = array(
        'no_code' => true
      );
    }

    return $twig_params;
  }

  private function getObjectsAsXml($program_xml_tree)
  {
    $pointed_objects = $program_xml_tree->xpath('//' . self::POINTED_OBJECT_TAG);
    $child_objects = array();
    foreach ($program_xml_tree->objectList->object as $object) {
      $child_objects[] = $object;
    }
    return array_merge($child_objects, $pointed_objects);
  }

  private function retrieveObjectFromXml($object)
  {
    return array(
      'name' => $object[self::NAME_ATTRIBUTE],
      'looks' => $this->retrieveLooksFromXml($object->lookList),
      'sounds' => $this->retrieveSoundsFromXml($object->soundList),
      'scripts' => $this->retrieveScriptsFromXml($object->scriptList)
    );
  }

  private function retrieveLooksFromXml($look_list)
  {
    $looks = array();
    foreach($look_list->children() as $look) {
      $look_name = null;
      $look_url = null;
      if ($look[self::REFERENCE_ATTRIBUTE] == null) {
        $look_name = (string)$look[self::NAME_ATTRIBUTE];
        $look_url = $look->fileName;
      } else {
        $referenced_look = $look->xpath($look[self::REFERENCE_ATTRIBUTE])[0];
        $look_name = (string)$referenced_look[self::NAME_ATTRIBUTE];
        $look_url = $referenced_look->fileName;
      }
      $looks[] = array(
        'look_name' => $look_name,
        'look_url' => $look_url
      );
    }
    return $looks;
  }

  private function retrieveSoundsFromXml($sound_list)
  {
    $sounds = array();
    foreach ($sound_list->children() as $sound) {
      $sound_name = null;
      $sound_url = null;
      if ($sound[self::REFERENCE_ATTRIBUTE] == null) {
        $sound_name = $sound->name;
        $sound_url = $sound->fileName;
      } else {
        $referenced_sound = $sound->xpath($sound[self::REFERENCE_ATTRIBUTE])[0];
        $sound_name = $referenced_sound->name;
        $sound_url = $referenced_sound->fileName;
      }
      $sounds[] = array(
        'sound_name' => $sound_name,
        'sound_url' => $sound_url
      );
    }
    return $sounds;
  }

  private function retrieveScriptsFromXml($script_list)
  {
    $scripts = array();
    foreach ($script_list->children() as $script) {
      switch((string)$script[self::TYPE_ATTRIBUTE]) {
        case self::START_SCRIPT:
          $scripts[] = array(
            'type' => "When program started",
            'bricks' => $this->retrieveBricksFromXml($script->brickList)
          );
          break;
        case self::WHEN_SCRIPT:
          $scripts[] = array(
            'type' => "When tapped",
            'bricks' => $this->retrieveBricksFromXml($script->brickList)
          );
          break;
        case self::BROADCAST_SCRIPT:
          $scripts[] = array(
            'type' => "When I receive",
            'bricks' => $this->retrieveBricksFromXml($script->brickList),
            'message' => $script->receivedMessage
          );
          break;
        case self::COLLISION_SCRIPT:
          $scripts[] = array(
            'type' => "When physical collision with",
            'bricks' => $this->retrieveBricksFromXml($script->brickList),
            'message' => $script->receivedMessage
          );
          break;
        default:
          $scripts[] = array(
            'type' => "Unknown script",
            'bricks' => array()
          );
          break;
      }
    }
    return $scripts;
  }

  private function retrieveBricksFromXml($brick_list)
  {
    $bricks = array();

    foreach($brick_list->children() as $brick) {
      $bricks[] = $this->resolveBrick($brick);
    }

    return $bricks;
  }

  private function resolveBrick($brick)
  {
    $resolved_brick = null;

    if ($brick[self::TYPE_ATTRIBUTE] != null) {
      switch ((string)$brick[self::TYPE_ATTRIBUTE]) {

        case self::WAIT_BRICK:
          $resolved_brick = $this->writeWaitBrick($brick);
          break;
        case self::NOTE_BRICK:
          $resolved_brick = $this->writeNoteBrick($brick);
          break;
        case self::FOREVER_BRICK:
          $resolved_brick = $this->writeForeverBrick();
          break;
        case self::LOOP_ENDLESS_BRICK:
          $resolved_brick = $this->writeLoopEndlessBrick();
          break;
        case self::IF_BRICK:
          $resolved_brick = $this->writeIfBrick($brick);
          break;
        case self::ELSE_BRICK:
          $resolved_brick = $this->writeElseBrick();
          break;
        case self::ENDIF_BRICK:
          $resolved_brick = $this->writeEndIfBrick();
          break;
        case self::REPEAT_BRICK:
          $resolved_brick = $this->writeRepeatBrick($brick);
          break;
        case self::LOOP_END_BRICK:
          $resolved_brick = $this->writeLoopEndBrick();
          break;
        case self::BROADCAST_BRICK:
          $resolved_brick = $this->writeBroadcastBrick($brick);
          break;
        case self::BROADCAST_WAIT_BRICK:
          $resolved_brick = $this->writeBroadcastWaitBrick($brick);
          break;
        case self::PLACE_AT_BRICK:
          $resolved_brick = $this->writePlaceAtBrick($brick);
          break;
        case self::SET_X_BRICK:
          $resolved_brick = $this->writeSetXBrick($brick);
          break;
        case self::SET_Y_BRICK:
          $resolved_brick = $this->writeSetYBrick($brick);
          break;
        case self::CHANGE_X_BY_N_BRICK:
          $resolved_brick = $this->writeChangeXByNBrick($brick);
          break;
        case self::CHANGE_Y_BY_N_BRICK:
          $resolved_brick = $this->writeChangeYByNBrick($brick);
          break;
        case self::IF_ON_EDGE_BOUNCE_BRICK:
          $resolved_brick = $this->writeIfOnEdgeBounceBrick();
          break;
        case self::MOVE_N_STEPS_BRICK:
          $resolved_brick = $this->writeMoveNStepsBrick($brick);
          break;
        case self::TURN_LEFT_BRICK:
          $resolved_brick = $this->writeTurnLeftBrick($brick);
          break;
        case self::TURN_RIGHT_BRICK:
          $resolved_brick = $this->writeTurnRightBrick($brick);
          break;
        case self::POINT_IN_DIRECTION_BRICK:
          $resolved_brick = $this->writePointInDirectionBrick($brick);
          break;
        case self::GLIDE_TO_BRICK:
          $resolved_brick = $this->writeGlideToBrick($brick);
          break;
        case self::GO_N_STEPS_BACK_BRICK:
          $resolved_brick = $this->writeGoNStepsBackBrick($brick);
          break;
        case self::COME_TO_FRONT_BRICK:
          $resolved_brick = $this->writeComeToFronBrick();
          break;
        case self::VIBRATION_BRICK:
          $resolved_brick = $this->writeVibrationBrick($brick);
          break;
        case self::SET_PHYSICS_OBJECT_TYPE_BRICK:
          $resolved_brick = $this->writeSetPhysicsObjectTypeBrick($brick);
          break;
        case self::SET_VELOCITY_BRICK:
          $resolved_brick = $this->writeSetVelocityBrick($brick);
          break;
        case self::TURN_LEFT_SPEED_BRICK:
          $resolved_brick = $this->writeTurnLeftSpeedBrick($brick);
          break;
        case self::TURN_RIGHT_SPEED_BRICK:
          $resolved_brick = $this->writeTurnRightSpeedBrick($brick);
          break;
        case self::SET_GRAVITY_BRICK:
          $resolved_brick = $this->writeSetGravityBrick($brick);
          break;
        case self::SET_MASS_BRICK:
          $resolved_brick = $this->writeSetMassBrick($brick);
          break;
        case self::SET_BOUNCE_BRICK:
          $resolved_brick = $this->writeSetBounceBrick($brick);
          break;
        case self::SET_FRICTION_BRICK:
          $resolved_brick = $this->writeSetFrictionBrick($brick);
          break;
        case self::POINT_TO_BRICK:
          $resolved_brick = $this->writePointToBrick($brick);
          break;
        case self::PLAY_SOUND_BRICK:
          $resolved_brick = $this->writePlaySoundBrick($brick);
          break;
        case self::STOP_ALL_SOUNDS_BRICK:
          $resolved_brick = $this->writeStopAllSoundsBrick();
          break;
        case self::SET_VOLUME_TO_BRICK:
          $resolved_brick = $this->writeSetVolumeToBrick($brick);
          break;
        case self::CHANGE_VOLUME_BY_N_BRICK:
          $resolved_brick = $this->writeChangeVolumeByNBrick($brick);
          break;
        case self::SPEAK_BRICK:
          $resolved_brick = $this->writeSpeakBrick($brick);
          break;
        case self::SET_LOOK_BRICK:
          $resolved_brick = $this->writeSetLookBrick($brick);
          break;
        case self::NEXT_LOOK_BRICK:
          $resolved_brick = $this->writeNextLookBrick();
          break;
        case self::CAMERA_BRICK:
          $resolved_brick = $this->writeCameraBrick($brick);
          break;
        case self::CHOOSE_CAMERA_BRICK:
          $resolved_brick = $this->writeChooseCameraBrick($brick);
          break;
        case self::SET_SIZE_TO_BRICK:
          $resolved_brick = $this->writeSetSizeToBrick($brick);
          break;
        case self::CHANGE_SIZE_BY_N_BRICK:
          $resolved_brick = $this->writeChangeSizeByNBrick($brick);
          break;
        case self::HIDE_BRICK:
          $resolved_brick = $this->writeHideBrick();
          break;
        case self::SHOW_BRICK:
          $resolved_brick = $this->writeShowBrick();
          break;
        case self::SET_TRANSPARENCY_BRICK:
          $resolved_brick = $this->writeSetTransparencyBrick($brick);
          break;
        case self::CHANGE_TRANSPARENCY_BY_N_BRICK:
          $resolved_brick = $this->writeChangeTransparencyByNBrick($brick);
          break;
        case self::SET_BRIGHTNESS_BRICK:
          $resolved_brick = $this->writeSetBrightnessBrick($brick);
          break;
        case self::CHANGE_BRIGHTNESS_BY_N_BRICK:
          $resolved_brick = $this->writeChangeBrightnessByNBrick($brick);
          break;
        case self::SET_COLOR_BRICK:
          $resolved_brick = $this->writeSetColorBrick($brick);
          break;
        case self::CHANGE_COLOR_BY_N_BRICK:
          $resolved_brick = $this->writeChangeColorByNBrick($brick);
          break;
        case self::CLEAR_GRAPHIC_EFFECT_BRICK:
          $resolved_brick = $this->writeClearGraphicEffectBrick();
          break;
        case self::FLASH_BRICK:
          $resolved_brick = $this->writeFlashBrick($brick);
          break;
        case self::SET_VARIABLE_BRICK:
          $resolved_brick = $this->writeSetVariableBrick($brick);
          break;
        case self::CHANGE_VARIABLE_BRICK:
          $resolved_brick = $this->writeChangeVariableBrick($brick);
          break;
        case self::SHOW_TEXT_BRICK:
          $resolved_brick = $this->writeShowTextBrick($brick);
          break;
        case self::HIDE_TEXT_BRICK:
          $resolved_brick = $this->writeHideTextBrick($brick);
          break;
        case self::ADD_ITEM_LIST_BRICK:
          $resolved_brick = $this->writeAddItemToUserListBrick($brick);
          break;
        case self::DELETE_ITEM_LIST_BRICK:
          $resolved_brick = $this->writeDeleteItemOfUserListBrick($brick);
          break;
        case self::INSERT_ITEM_LIST_BRICK:
          $resolved_brick = $this->writeInsertItemIntoUserListBrick($brick);
          break;
        case self::REPLACE_ITEM_LIST_BRICK:
          $resolved_brick = $this->writeReplaceItemInUserListBrick($brick);
          break;
        case self::WHEN_BRICK:
          $resolved_brick = $this->writeWhenBrick($brick);
          break;
        default:
          $resolved_brick = $this->writeUnknownBrick();
          break;
      }
    } elseif($brick[self::REFERENCE_ATTRIBUTE] != null) {
      $resolved_brick = $this->resolveBrick($brick->xpath($brick[self::REFERENCE_ATTRIBUTE])[0]);
    }

    return $resolved_brick;
  }

  private function writeWaitBrick($brick)
  {
    return array(
      'name' => self::WAIT_BRICK,
      'text' => "Wait " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TIME_TO_WAIT_IN_SECONDS_FORMULA] . " second(s)",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeNoteBrick($brick)
  {
    return array(
      'name' => self::NOTE_BRICK,
      'text' => "Note " . $this->retrieveFormulasFromXml($brick->formulaList)[self::NOTE_FORMULA],
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeForeverBrick()
  {
    return array(
      'name' => self::FOREVER_BRICK,
      'text' => "Forever",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeLoopEndlessBrick()
  {
    return array(
      'name' => self::LOOP_ENDLESS_BRICK,
      'text' => "End of loop",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeIfBrick($brick)
  {
    return array(
      'name' => self::IF_BRICK,
      'text' => "If " . $this->retrieveFormulasFromXml($brick->formulaList)[self::IF_CONDITION_FORMULA] . " is true then",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeElseBrick()
  {
    return array(
      'name' => self::ELSE_BRICK,
      'text' => "Else",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeEndIfBrick()
  {
    return array(
      'name' => self::ENDIF_BRICK,
      'text' => "End If",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeRepeatBrick($brick)
  {
    return array(
      'name' => self::REPEAT_BRICK,
      'text' => "Repeat " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TIMES_TO_REPEAT_FORMULA] . " times",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeLoopEndBrick()
  {
    return array(
      'name' => self::LOOP_END_BRICK,
      'text' => "End of loop",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeBroadcastBrick($brick)
  {
    return array(
      'name' => self::BROADCAST_BRICK,
      'text' => "Broadcast " . $brick->broadcastMessage,
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeBroadcastWaitBrick($brick)
  {
    return array(
      'name' => self::BROADCAST_WAIT_BRICK,
      'text' => "Broadcast and wait " . $brick->broadcastMessage,
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writePlaceAtBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::PLACE_AT_BRICK,
      'text' => "Place at X: " . $formulas[self::X_POSITION_FORMULA] . " Y: " . $formulas[self::Y_POSITION_FORMULA],
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetXBrick($brick)
  {
    return array(
      'name' => self::SET_X_BRICK,
      'text' => "Set X to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::X_POSITION_FORMULA],
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetYBrick($brick)
  {
    return array(
      'name' => self::SET_Y_BRICK,
      'text' => "Set Y to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::Y_POSITION_FORMULA],
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeChangeXByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_X_BY_N_BRICK,
      'text' => "Change X by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::X_POSITION_CHANGE_FORMULA],
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeChangeYByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_X_BY_N_BRICK,
      'text' => "Change Y by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::Y_POSITION_CHANGE_FORMULA],
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeIfOnEdgeBounceBrick()
  {
    return array(
      'name' => self::IF_ON_EDGE_BOUNCE_BRICK,
      'text' => "If on edge bounce",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeMoveNStepsBrick($brick)
  {
    return array(
      'name' => self::MOVE_N_STEPS_BRICK,
      'text' => "Move " . $this->retrieveFormulasFromXml($brick->formulaList)[self::STEPS_FORMUlA] . " steps",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeTurnLeftBrick($brick)
  {
    return array(
      'name' => self::TURN_LEFT_BRICK,
      'text' => "Turn left " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TURN_LEFT_DEGREES_FORMULA] . " degrees",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeTurnRightBrick($brick)
  {
    return array(
      'name' => self::TURN_RIGHT_BRICK,
      'text' => "Turn right " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TURN_RIGHT_DEGREES_FORMULA] . " degrees",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writePointInDirectionBrick($brick)
  {
    return array(
      'name' => self::POINT_IN_DIRECTION_BRICK,
      'text' => "Point in direction " . $this->retrieveFormulasFromXml($brick->formulaList)[self::DEGREES_FORMULA] . " degrees",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeGlideToBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::GLIDE_TO_BRICK,
      'text' => "Glide " . $formulas[self::DURATION_IN_SECONDS_FORMULA] . " second(s) to X: " . $formulas[self::X_DESTINATION_FORMULA] . " Y: " . $formulas[self::Y_DESTINATION_FORMUlA],
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeGoNStepsBackBrick($brick)
  {
    return array(
      'name' => self::GO_N_STEPS_BACK_BRICK,
      'text' => "Go back " . $this->retrieveFormulasFromXml($brick->formulaList)[self::STEPS_FORMUlA] . " layer",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeComeToFronBrick()
  {
    return array(
      'name' => self::COME_TO_FRONT_BRICK,
      'text' => "Go to front",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeVibrationBrick($brick)
  {
    return array(
      'name' => self::VIBRATION_BRICK,
      'text' => "Vibrate for " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VIBRATE_DURATION_IN_SECONDS_FORMULA] . " second(s)",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetPhysicsObjectTypeBrick($brick)
  {
    return array(
      'name' => self::SET_PHYSICS_OBJECT_TYPE_BRICK,
      'text' => "Set motion type to " . $brick->type,
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetVelocityBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::SET_VELOCITY_BRICK,
      'text' => "Set velocity to X: " . $formulas[self::VELOCITY_X_FORMULA] . " Y: " . $formulas[self::VELOCITY_Y_FORMULA] . " steps/second",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeTurnLeftSpeedBrick($brick)
  {
    return array(
      'name' => self::TURN_LEFT_SPEED_BRICK,
      'text' => "Rotate left " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TURN_LEFT_SPEED_FORMULA] . " degrees/second",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeTurnRightSpeedBrick($brick)
  {
    return array(
      'name' => self::TURN_RIGHT_SPEED_BRICK,
      'text' => "Rotate right " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TURN_RIGHT_SPEED_FORMULA] . " degrees/second",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetGravityBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::SET_VELOCITY_BRICK,
      'text' => "Set gravity for all objects to X: " . $formulas[self::GRAVITY_X_FORMULA] . " Y: " . $formulas[self::GRAVITY_Y_FORMULA] . " steps/second²",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetMassBrick($brick)
  {
    return array(
      'name' => self::SET_MASS_BRICK,
      'text' => "Set mass to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::MASS_FORMULA] . " kilogram",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetBounceBrick($brick)
  {
    return array(
      'name' => self::SET_BOUNCE_BRICK,
      'text' => "Set bounce factor to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::BOUNCE_FACTOR_FORMULA] . "%",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetFrictionBrick($brick)
  {
    return array(
      'name' => self::SET_FRICTION_BRICK,
      'text' => "Set friction to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::FRICTION_FORMULA] . "%",
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writePointToBrick($brick)
  {
    $pointed_object_name = null;
    if ($brick[self::REFERENCE_ATTRIBUTE != null])
      $pointed_object_name = $brick->xpath($brick[self::REFERENCE_ATTRIBUTE])[0][self::NAME_ATTRIBUTE];
    else
      $pointed_object_name = $brick[self::NAME_ATTRIBUTE];
    return array(
      'name' => self::POINT_TO_BRICK,
      'text' => "Point towards " . $pointed_object_name,
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writePlaySoundBrick($brick)
  {
    return array(
      'name' => self::PLAY_SOUND_BRICK,
      'text' => "Start sound",
      'img_file' => self::SOUND_BRICK_IMG,
      'sound' => $brick->sound->xpath($brick->sound[self::REFERENCE_ATTRIBUTE])[0]->fileName
    );
  }

  private function writeStopAllSoundsBrick()
  {
    return array(
      'name' => self::STOP_ALL_SOUNDS_BRICK,
      'text' => "Stop all sounds",
      'img_file' => self::SOUND_BRICK_IMG
    );
  }

  private function writeSetVolumeToBrick($brick)
  {
    return array(
      'name' => self::SET_VOLUME_TO_BRICK,
      'text' => "Set volume to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VOLUME_FORMUlA] . "%",
      'img_file' => self::SOUND_BRICK_IMG
    );
  }

  private function writeChangeVolumeByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_VOLUME_BY_N_BRICK,
      'text' => "Change volume by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VOLUME_CHANGE_FORMULA],
      'img_file' => self::SOUND_BRICK_IMG
    );
  }

  private function writeSpeakBrick($brick)
  {
    return array(
      'name' => self::SPEAK_BRICK,
      'text' => "Speak \"" . $this->retrieveFormulasFromXml($brick->formulaList)[self::SPEAK_FORMULA] . "\"",
      'img_file' => self::SOUND_BRICK_IMG
    );
  }

  private function writeSetLookBrick($brick)
  {
    return array(
      'name' => self::SET_LOOK_BRICK,
      'text' => "Switch to look",
      'img_file' => self::LOOKS_BRICK_IMG,
      'look' => $brick->look->xpath($brick->look[self::REFERENCE_ATTRIBUTE])[0]->fileName
    );
  }

  private function writeNextLookBrick()
  {
    return array(
      'name' => self::NEXT_LOOK_BRICK,
      'text' => "Next look",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeCameraBrick($brick)
  {
    return array(
      'name' => self::CAMERA_BRICK,
      'text' => "Turn camera " . $brick->spinnerValues->children()[$brick->spinnerSelectionID],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeChooseCameraBrick($brick)
  {
    return array(
      'name' => self::CHOOSE_CAMERA_BRICK,
      'text' => "Use camera " . $brick->spinnerValues->children()[$brick->spinnerSelectionID],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSetSizeToBrick($brick)
  {
    return array(
      'name' => self::SET_SIZE_TO_BRICK,
      'text' => "Set size to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::SIZE_FORMULA] . "%",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeChangeSizeByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_SIZE_BY_N_BRICK,
      'text' => "Change size by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::SIZE_CHANGE_FORMULA],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeHideBrick()
  {
    return array(
      'name' => self::HIDE_BRICK,
      'text' => "Hide",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeShowBrick()
  {
    return array(
      'name' => self::SHOW_BRICK,
      'text' => "Show",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSetTransparencyBrick($brick)
  {
    return array(
      'name' => self::SET_TRANSPARENCY_BRICK,
      'text' => "Set transparency to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TRANSPARENCY_FORMULA] . "%",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeChangeTransparencyByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_TRANSPARENCY_BY_N_BRICK,
      'text' => "Change transparency by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::TRANSPARENCY_CHANGE_FORMULA],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSetBrightnessBrick($brick)
  {
    return array(
      'name' => self::SET_BRIGHTNESS_BRICK,
      'text' => "Set brightness to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::BRIGHTNESS_FORMULA] . "%",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeChangeBrightnessByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_BRIGHTNESS_BY_N_BRICK,
      'text' => "Change brightness by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::BRIGHTNESS_CHANGE_FORMULA],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSetColorBrick($brick)
  {
    return array(
      'name' => self::SET_COLOR_BRICK,
      'text' => "Set color to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::COLOR_FORMUlA] . "%",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeChangeColorByNBrick($brick)
  {
    return array(
      'name' => self::CHANGE_COLOR_BY_N_BRICK,
      'text' => "Change color by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::COLOR_CHANGE_FORMULA],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeClearGraphicEffectBrick()
  {
    return array(
      'name' => self::CLEAR_GRAPHIC_EFFECT_BRICK,
      'text' => "Clear graphic effects",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeFlashBrick($brick)
  {
    return array(
      'name' => self::FLASH_BRICK,
      'text' => "Turn flashlight " . $brick->spinnerValues->children()[$brick->spinnerSelectionID],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSetVariableBrick($brick)
  {
    $user_variable = null;
    if ($brick->userVariableName == null)
      $user_variable = $brick->userVariable;
    else
      $user_variable = $brick->userVariableName;
    return array(
      'name' => self::SET_VARIABLE_BRICK,
      'text' => "Set variable " . $user_variable . " to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VARIABLE_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeChangeVariableBrick($brick)
  {
    $user_variable = null;
    if ($brick->userVariableName == null)
      $user_variable = $brick->userVariable;
    else
      $user_variable = $brick->userVariableName;
    return array(
      'name' => self::CHANGE_VARIABLE_BRICK,
      'text' => "Change variable " . $user_variable . " by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VARIABLE_CHANGE_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeShowTextBrick($brick)
  {
    $user_variable = null;
    if ($brick->userVariableName == null)
      $user_variable = $brick->userVariable;
    else
      $user_variable = $brick->userVariableName;
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::SHOW_TEXT_BRICK,
      'text' => "Show variable " . $user_variable . " at X: " . $formulas[self::X_POSITION_FORMULA] . " Y: " . $formulas[self::Y_POSITION_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeHideTextBrick($brick)
  {
    $user_variable = null;
    if ($brick->userVariableName == null)
      $user_variable = $brick->userVariable;
    else
      $user_variable = $brick->userVariableName;
    return array(
      'name' => self::HIDE_TEXT_BRICK,
      'text' => "Hide variable " . $user_variable,
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeAddItemToUserListBrick($brick)
  {
    $user_list = null;
    if ($brick->userList[self::REFERENCE_ATTRIBUTE] == null)
      $user_list = $brick->userList->name;
    else
      $user_list = $brick->userList->xpath($brick->userList[self::REFERENCE_ATTRIBUTE])[0];
    return array(
      'name' => self::ADD_ITEM_LIST_BRICK,
      'text' => "Add " . $this->retrieveFormulasFromXml($brick->formulaList)[self::LIST_ADD_ITEM_FORMULA] . " to list " . $user_list,
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeDeleteItemOfUserListBrick($brick)
  {
    $user_list = null;
    if ($brick->userList[self::REFERENCE_ATTRIBUTE] == null)
      $user_list = $brick->userList->name;
    else
      $user_list = $brick->userList->xpath($brick->userList[self::REFERENCE_ATTRIBUTE])[0];
    return array(
      'name' => self::DELETE_ITEM_LIST_BRICK,
      'text' => "Delete item from list " . $user_list . " at position " . $this->retrieveFormulasFromXml($brick->formulaList)[self::LIST_DELETE_ITEM_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeInsertItemIntoUserListBrick($brick)
  {
    $user_list = null;
    if ($brick->userList[self::REFERENCE_ATTRIBUTE] == null)
      $user_list = $brick->userList->name;
    else
      $user_list = $brick->userList->xpath($brick->userList[self::REFERENCE_ATTRIBUTE])[0];
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::INSERT_ITEM_LIST_BRICK,
      'text' => "Inser " . $formulas[self::INSERT_ITEM_LIST_VALUE_FORMULA] . " into list " . $user_list . " at position " . $formulas[self::INSERT_ITEM_LIST_INDEX_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeReplaceItemInUserListBrick($brick)
  {
    $user_list = null;
    if ($brick->userList[self::REFERENCE_ATTRIBUTE] == null)
      $user_list = $brick->userList->name;
    else
      $user_list = $brick->userList->xpath($brick->userList[self::REFERENCE_ATTRIBUTE])[0];
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::REPLACE_ITEM_LIST_BRICK,
      'text' => "Replace item in list " . $user_list . " at position " . $formulas[self::REPLACE_ITEM_LIST_INDEX_FORMULA] . " with " . $formulas[self::REPLACE_ITEM_LIST_VALUE_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeWhenBrick($brick)
  {
    return array(
        'name' => self::WHEN_BRICK,
        'text' => "When tapped",
        'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeUnknownBrick()
  {
    return array(
      'name' => self::UNKNOWN_BRICK,
      'text' => "Unknown Brick",
      'img_file' => self::UNKNOWN_BRICK_IMG
    );
  }

  private function retrieveFormulasFromXml($formula_list)
  {
    $formulas = array();
    foreach($formula_list->children() as $formula) {
      $formulas[(string)$formula[self::CATEGORY_ATTRIBUTE]] = $this->resolveFormula($formula);
    }
    return $formulas;
  }

  private function resolveFormula($formula)
  {
    $resolved_formula = null;
    if ($formula != null) {
      switch($formula->type) {
        case self::OPERATOR_FORMULA_TYPE:
          $resolved_formula = $this->resolveFormulaCaseOperator($formula);
          break;
        case self::FUNCTION_FORMULA_TYPE:
          $resolved_formula = $this->resolveFormulaCaseFunction($formula);
          break;
        default:
          $resolved_formula = $formula->value;
          break;
      }
    }
    return $resolved_formula;
  }

  private function resolveFormulaCaseOperator($formula)
  {
    return $this->resolveFormula($formula->leftChild) . " " . $this->resolveOperator($formula->value) . " " . $this->resolveFormula($formula->rightChild);
  }

  private function resolveFormulaCaseFunction($formula)
  {
    $resolved_formula = null;

    if ($formula->value == 'TRUE') {
      $resolved_formula = "true";
    } else if ($formula->value == 'FALSE') {
      $resolved_formula = "false";
    } else {
      if ($formula->rightChild != null)
        $function_input_formula = $this->resolveFormula($formula->leftChild) . ", " . $this->resolveFormula($formula->rightChild);
      else
        $function_input_formula = $this->resolveFormula($formula->leftChild);
      $resolved_formula = strtolower($formula->value) ."( " . $function_input_formula . " )";
    }

    return $resolved_formula;
  }

  private function resolveOperator($operator)
  {
    $resolved_operator = null;
    switch($operator) {
      case self::PLUS_OPERATOR:
        $resolved_operator = '+';
        break;
      case self::MINUS_OPERATOR:
        $resolved_operator = '-';
        break;
      case self::MULT_OPERATOR:
        $resolved_operator = '*';
        break;
      case self::DIVIDE_OPERATOR:
        $resolved_operator = '/';
        break;
      case self::EQUAL_OPERATOR:
        $resolved_operator = '=';
        break;
      case self::NOT_EQUAL_OPERATOR:
        $resolved_operator = '!=';
        break;
      case self::GREATER_OPERATOR:
        $resolved_operator = '>';
        break;
      case self::GREATER_EQUAL_OPERATOR:
        $resolved_operator = '>=';
        break;
      case self::SMALLER_OPERATOR:
        $resolved_operator = '<';
        break;
      case self::SMALLER_EQUAL_OPERATOR:
        $resolved_operator = '<=';
        break;
      case self::NOT_OPERATOR:
        $resolved_operator = 'NOT';
        break;
      case self::OR_OPERATOR:
        $resolved_operator = 'OR';
        break;
      case self::AND_OPERATOR:
        $resolved_operator = 'AND';
        break;
      default:
        $resolved_operator = null;
        break;
    }
    return $resolved_operator;
  }
}