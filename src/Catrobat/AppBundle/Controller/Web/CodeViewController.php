<?php

namespace Catrobat\AppBundle\Controller\Web;

use ClassesWithParents\E;
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

  // Object types
  const SINGLE_SPRITE_TYPE = 'SingleSprite';
  const GROUP_SPRITE_TYPE = 'GroupSprite';
  const GROUP_ITEM_SPRITE_TYPE = 'GroupItemSprite';

  // Scritps
  const START_SCRIPT = 'StartScript';
  const WHEN_SCRIPT = 'WhenScript';
  const WHEN_TOUCH_SCRIPT = 'WhenTouchDownScript';
  const BROADCAST_SCRIPT = 'BroadcastScript';
  const WHEN_CONDITION_SCRIPT = 'WhenConditionScript';
  const COLLISION_SCRIPT = 'CollisionScript';
  const WHEN_BG_CHANGE_SCRIPT = 'WhenBackgroundChangesScript';
  const WHEN_CLONED_SCRIPT = 'WhenClonedScript';

  // Bricks
  const WAIT_BRICK = 'WaitBrick';
  const NOTE_BRICK = 'NoteBrick';
  const FOREVER_BRICK = 'ForeverBrick';
  const LOOP_ENDLESS_BRICK = "LoopEndlessBrick";
  const IF_BRICK = 'IfLogicBeginBrick';
  const IF_THEN_BRICK = 'IfThenLogicBeginBrick';
  const ELSE_BRICK = 'IfLogicElseBrick';
  const ENDIF_BRICK = 'IfLogicEndBrick';
  const ENDIF_THEN_BRICK = 'IfThenLogicEndBrick';
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
  const WAIT_UNTIL_BRICK = 'WaitUntilBrick';
  const REPEAT_UNTIL_BRICK = 'RepeatUntilBrick';
  const STOP_SCRIPT_BRICK = 'StopScriptBrick';
  const SCENE_START_BRICK = 'SceneStartBrick';
  const CLONE_BRICK = 'CloneBrick';
  const DELETE_THIS_CLONE_BRICK = 'DeleteThisCloneBrick';
  const CONTINUE_SCENE_BRICK = 'SceneTransitionBrick';
  const GO_TO_BRICK = 'GoToBrick';
  const SET_ROTATION_STYLE_BRICK = 'SetRotationStyleBrick';
  const PLAY_SOUND_WAIT_BRICK = 'PlaySoundAndWaitBrick';
  const SPEAK_WAIT_BRICK = 'SpeakAndWaitBrick';
  const PREV_LOOK_BRICK = 'PreviousLookBrick';
  const ASK_BRICK = 'AskBrick';
  const SAY_BUBBLE_BRICK = 'SayBubbleBrick';
  const SAY_FOR_BUBBLE_BRICK = 'SayForBubbleBrick';
  const THINK_BUBBLE_BRICK = 'ThinkBubbleBrick';
  const THINK_FOR_BUBBLE_BRICK = 'ThinkForBubbleBrick';
  const SET_BACKGROUND_BRICK = 'SetBackgroundBrick';
  const SET_BACKGROUND_WAIT_BRICK = 'SetBackgroundAndWaitBrick';
  const PEN_DOWN_BRICK = 'PenDownBrick';
  const PEN_UP_BRICK = 'PenUpBrick';
  const SET_PEN_SIZE_BRICK = 'SetPenSizeBrick';
  const SET_PEN_COLOR_BRICK = 'SetPenColorBrick';
  const STAMP_BRICK = 'StampBrick';
  const CLEAR_BACKGROUND_BRICK = 'ClearBackgroundBrick';

  // Brick Images
  const EVENT_SCRIPT_IMG = '1h_when_brown.png';
  const EVENT_BRICK_IMG = '1h_brick_brown.png';
  const CONTROL_SCRIPT_IMG = '1h_when_orange.png';
  const CONTROL_BRICK_IMG = '1h_brick_orange.png';
  const MOTION_BRICK_IMG = '1h_brick_blue.png';
  const SOUND_BRICK_IMG = '1h_brick_violet.png';
  const LOOKS_BRICK_IMG = '1h_brick_green.png';
  const DATA_BRICK_IMG = '1h_brick_red.png';
  const PEN_BRICK_IMG = '1h_brick_darkgreen.png';
  const UNKNOWN_SCRIPT_IMG = '1h_when_grey.png';
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
  const REPEAT_UNTIL_CONDITION_FORMULA = 'REPEAT_UNTIL_CONDITION';
  const ASK_QUESTION_FORMULA = 'ASK_QUESTION';
  const STRING_FORMULA = 'STRING';
  const PEN_SIZE_FORMULA = 'PEN_SIZE';
  const PEN_COLOR_RED_FORMULA = 'PHIRO_LIGHT_RED';
  const PEN_COLOR_BLUE_FORMULA = 'PHIRO_LIGHT_BLUE';
  const PEN_COLOR_GREEN_FORMULA = 'PHIRO_LIGHT_GREEN';

  const OPERATOR_FORMULA_TYPE = 'OPERATOR';
  const FUNCTION_FORMULA_TYPE = 'FUNCTION';
  const BRACKET_FORMULA_TYPE = 'BRACKET';

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


  private $program_statistic = [];
  private $brick_type_register = [];

  private function initProgramStatistic()
  {
    $this->program_statistic = array(
      'totalNumScripts' => 0,
      'totalNumBricks' => 0,
      'eventBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'controlBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'motionBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'soundBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'looksBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'penBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'dataBricks' => array(
        'numTotal' => 0,
        'numDifferent' => 0
      ),
      'totalNumObjects' => 0,
      'totalNumLooks' => 0,
      'totalNumSounds' => 0,
      'totalNumGlobalVars' => null,
      'totalNumLocalVars' => null,
    );

    $this->brick_type_register = array(
      'eventBricks' => array(),
      'controlBricks' => array(),
      'motionBricks' => array(),
      'soundBricks' => array(),
      'looksBricks' => array(),
      'penBricks' => array(),
      'dataBricks' => array()
    );
  }

  private function updateProgramStatistic($statID, $brick_img_file = null, $brick_type = null)
  {
    $this->program_statistic[$statID]++;

    if ($brick_img_file)
    {
      switch($brick_img_file) {
        // Normal Bricks
        case self::EVENT_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'eventBricks');
          break;
        case self::CONTROL_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'controlBricks');
          break;
        case self::MOTION_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'motionBricks');
          break;
        case self::SOUND_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'soundBricks');
          break;
        case self::LOOKS_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'looksBricks');
          break;
        case self::PEN_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'penBricks');
          break;
        case self::DATA_BRICK_IMG:
          $this->updateBrickStatistic($brick_type, 'dataBricks');
          break;

        // Script Bricks
        case self::EVENT_SCRIPT_IMG:
          $this->updateBrickStatistic($brick_type, 'eventBricks');
          break;
        case self::CONTROL_SCRIPT_IMG:
          $this->updateBrickStatistic($brick_type, 'controlBricks');
          break;
      }
    }
  }

  private function updateBrickStatistic($brick_type, $brick_category)
  {
    $this->program_statistic[$brick_category]['numTotal']++;
    if (!in_array($brick_type, $this->brick_type_register[$brick_category]))
    {
      $this->program_statistic[$brick_category]['numDifferent']++;
      $this->brick_type_register[$brick_category][] = $brick_type;
    }
  }

  private function computeVariableStatistic($extracted_program)
  {
    $program_xml_tree = $extracted_program->getProgramXmlProperties();

    $this->updateNumGlobalVariables($program_xml_tree);
    $this->updateNumLocalVariables($program_xml_tree);
  }

  private function updateNumGlobalVariables($program_xml_tree)
  {
    try
    {
      $this->program_statistic['totalNumGlobalVars'] =
        count($program_xml_tree->xpath('//programVariableList//userVariable')) +
        count($program_xml_tree->xpath('//programListOfLists//userVariable'));
    }
    catch(\Exception $e)
    {
      $this->program_statistic['totalNumGlobalVars'] = null;
    }

  }

  private function updateNumLocalVariables($program_xml_tree)
  {
    try
    {
      $this->program_statistic['totalNumLocalVars'] =
        count($program_xml_tree->xpath('//objectListOfList//userVariable')) +
        count($program_xml_tree->xpath('//objectVariableList//userVariable'));
    }
    catch(\Exception $e)
    {
      $this->program_statistic['totalNumLocalVars'] = null;
    }
  }

  /**
   * @Method({"GET"})
   */
  public function viewCodeAction($id)
  {
    $program = $this->get('programmanager')->find($id);
    $extracted_file_repository = $this->get('extractedfilerepository');

    $this->initProgramStatistic();

    try
    {
      $extracted_program = $extracted_file_repository->loadProgramExtractedFile($program);
      $twig_params = $this->computeTwigParams($extracted_program);

      $this->computeVariableStatistic($extracted_program);
      $twig_params['program_statistic'] = $this->program_statistic;
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
    $twig_params = array(
      'path' => $extracted_program->getWebPath()
    );

    if ($extracted_program->hasScenes())
    {
      $scene_list = array();
      $scenes_xml = $this->getScenesAsXml($program_xml_tree);
      foreach ($scenes_xml as $scene_xml) {
        $scene_list[] = $this->retrieveSceneFromXml($scene_xml);
      }
      $twig_params['scene_list'] = $scene_list;
    }
    else
    {
      $objects_xml = $this->getObjectsAsXml($program_xml_tree); // simplified
      $objects = $this->retrieveObjectListFromXml($objects_xml);
      $twig_params = array_merge($twig_params, $objects);
    }

    return $twig_params;
  }

  private function getScenesAsXml($program_xml_tree)
  {
    $scenes = array();
    foreach ($program_xml_tree->scenes->scene as $scene) {
      $scenes[] = $scene;
    }
    return $scenes;
  }

  private function getObjectsAsXml($program_xml_tree) // fixed
  {
    $objects = array();

    foreach ($program_xml_tree->objectList->object as $object_xml) {
      $dereferenced_object_xml = $this->resolveReferences($object_xml);

      if ($this->hasName($dereferenced_object_xml)) {
        $objects[] = $dereferenced_object_xml;

        $pointed_objects = $dereferenced_object_xml->xpath('scriptList//' . self::POINTED_OBJECT_TAG);
        foreach ($pointed_objects as $pointed_obj) {
          $dereferenced_pointed_obj = $this->resolveReferences($pointed_obj);

          if ($this->hasName($dereferenced_pointed_obj))
            $objects[] = $dereferenced_pointed_obj;
        }
      }
    }

    return $objects;
  }

  private function resolveReferences($object_xml)
  {
    $resolved_object_xml = null;

    if ($object_xml[self::REFERENCE_ATTRIBUTE] != null) {
      $resolved_object_xml = $this->resolveReferences($object_xml->xpath($object_xml[self::REFERENCE_ATTRIBUTE])[0]);
    } else {
      $resolved_object_xml = $object_xml;
    }

    return $resolved_object_xml;
  }

  private function hasName($object_xml)
  {
    return ($object_xml[self::NAME_ATTRIBUTE] != null) or (count($object_xml->name) != 0);
  }

  private function retrieveSceneFromXml($scene_xml)
  {
    $scene = array(
      'name' => $scene_xml->name
    );

    $objects_xml = $this->getObjectsAsXml($scene_xml);
    $objects = $this->retrieveObjectListFromXml($objects_xml);

    return array_merge($scene, $objects);
  }

  /*
   * Note that groups are only parsed correctly if there are no groups of groups.
   * However, so far this is not possible with the pocketcode App either.
   */
  private function retrieveObjectListFromXml($objects_xml)
  {
    $background = null;
    $object_list = array();

    $has_background = false;

    $current_group = array();
    $is_group = false;
    $group_objects = array();
    foreach ($objects_xml as $object_xml) {
      if ($has_background) {
        switch($object_xml[self::TYPE_ATTRIBUTE]) {
          case self::GROUP_SPRITE_TYPE:
            $this->insertGroupInObjectListAndResetIfGroup($is_group, $group_objects, $current_group, $object_list);
            $is_group = true;
            $current_group['name'] = $object_xml[self::NAME_ATTRIBUTE];
            break;
          case self::GROUP_ITEM_SPRITE_TYPE:
            $group_objects[] = $this->retrieveObjectFromXml($object_xml);
            break;
          default:
            $this->insertGroupInObjectListAndResetIfGroup($is_group, $group_objects, $current_group, $object_list);
            $object_list[] = $this->retrieveObjectFromXml($object_xml);
            break;
        }
      } else {
        $background = $this->retrieveObjectFromXml($object_xml);
        $has_background = true;
      }
    }
    $this->insertGroupInObjectListAndResetIfGroup($is_group, $group_objects, $current_group, $object_list);

    return array(
      'background' => $background,
      'object_list' => $object_list
    );
  }

  private function insertGroupInObjectListAndResetIfGroup(&$is_group, &$group_objects, &$current_group, &$object_list)
  {
    if ($is_group) {
      // Adding group to final object_list
      $current_group['object_list'] = $group_objects;
      $object_list[] = $current_group;

      // Resetting variables for next group
      $is_group = false;
      $current_group = array();
      $group_objects = array();
    }
  }

  private function retrieveObjectFromXml($object)
  {
    $name = null;
    if ($object[self::NAME_ATTRIBUTE] != null)
      $name = $object[self::NAME_ATTRIBUTE];
    else
      $name = $object->name;

    $this->updateProgramStatistic('totalNumObjects');

    return array(
      'name' => $name,
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
        $look_url = (string)$look->fileName;
      } else {
        $referenced_look = $look->xpath($look[self::REFERENCE_ATTRIBUTE])[0];
        $look_name = (string)$referenced_look[self::NAME_ATTRIBUTE];
        $look_url = (string)$referenced_look->fileName;
      }
      $looks[] = array(
        'look_name' => $look_name,
        'look_url' => $look_url
      );

      $this->updateProgramStatistic('totalNumLooks');

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
        $sound_name = (string)$sound->name;
        $sound_url = (string)$sound->fileName;
      } else {
        $referenced_sound = $sound->xpath($sound[self::REFERENCE_ATTRIBUTE])[0];
        $sound_name = (string)$referenced_sound->name;
        $sound_url = (string)$referenced_sound->fileName;
      }
      $sounds[] = array(
        'sound_name' => $sound_name,
        'sound_url' => $sound_url
      );

      $this->updateProgramStatistic('totalNumSounds');

    }
    return $sounds;
  }

  private function retrieveScriptsFromXml($script_list)
  {
    $scripts = array();
    foreach ($script_list->children() as $script) {
      $resolved_script = null;

      if ($script[self::REFERENCE_ATTRIBUTE] != null) {
        $resolved_script = $this->resolveScript($script->xpath($script[self::REFERENCE_ATTRIBUTE])[0]);
      } else {
        $resolved_script = $this->resolveScript($script);
      }

      // Check if commented and change color if true
      if ($this->isCommentedOut($script)) {
        $resolved_script['img_file'] = self::UNKNOWN_SCRIPT_IMG;
        if ($script['bricks'] != null)
          foreach($script['bricks'] as $brick)
            $brick['img_file'] = self::UNKNOWN_BRICK_IMG;
      }

      $scripts[] = $resolved_script;

      $this->updateProgramStatistic('totalNumScripts', $resolved_script['img_file'], $resolved_script['type']);
      $this->updateProgramStatistic('totalNumBricks');
    }
    return $scripts;
  }

  private function resolveScript($script)
  {
    $resolved_script = null;
    switch((string)$script[self::TYPE_ATTRIBUTE]) {
      case self::START_SCRIPT:
        $resolved_script = array(
          'type' => "When program started",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        break;
      case self::WHEN_SCRIPT:
        $resolved_script = array(
          'type' => "When tapped",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        break;
      case self::WHEN_TOUCH_SCRIPT:
        $resolved_script = array(
          'type' => "When screen is touched",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        break;
      case self::BROADCAST_SCRIPT:
        $resolved_script = array(
          'type' => "When I receive",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'message' => $script->receivedMessage,
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        break;
      case self::WHEN_CONDITION_SCRIPT:
        $resolved_script = array(
          'type' => "When " . $this->retrieveFormulasFromXml($script->formulaMap)[self::IF_CONDITION_FORMULA] . " becomes true",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        break;
      case self::COLLISION_SCRIPT:
        $resolved_script = array(
          'type' => "When physical collision with",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'message' => $script->receivedMessage,
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        break;
      case self::WHEN_BG_CHANGE_SCRIPT:
        $resolved_script = array(
          'type' => "When background changes to",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'img_file' => self::EVENT_SCRIPT_IMG
        );
        if (count($script->look) != 0) {
          if ($script->look[self::REFERENCE_ATTRIBUTE] != null)
            $resolved_script['look'] = $script->look->xpath($script->look[self::REFERENCE_ATTRIBUTE])[0]->fileName;
          else
            $resolved_script['look'] = $script->look->fileName;
        }
        break;
      case self::WHEN_CLONED_SCRIPT:
        $resolved_script = array(
          'type' => "When I start as a clone",
          'bricks' => $this->retrieveBricksFromXml($script->brickList),
          'img_file' => self::CONTROL_SCRIPT_IMG
        );
        break;
      default:
        $resolved_script = array(
          'type' => "Unknown script",
          'bricks' => array(),
          'img_file' => self::UNKNOWN_SCRIPT_IMG
        );
        break;
    }
    return $resolved_script;
  }

  private function retrieveBricksFromXml($brick_list)
  {
    $bricks = array();

    foreach($brick_list->children() as $brick) {
      $resolved_brick = null;

      if ($brick[self::REFERENCE_ATTRIBUTE] != null) {
        $resolved_brick = $this->resolveBrick($brick->xpath($brick[self::REFERENCE_ATTRIBUTE])[0]);
      } else {
        $resolved_brick = $this->resolveBrick($brick);
      }

      // Check if commented and change brick-color if true
      if ($this->isCommentedOut($brick))
        $resolved_brick['img_file'] = self::UNKNOWN_BRICK_IMG;

      $bricks[] = $resolved_brick;

      $this->updateProgramStatistic('totalNumBricks', $resolved_brick['img_file'], $resolved_brick['name']);
    }

    return $bricks;
  }

  private function resolveBrick($brick)
  {
    $resolved_brick = null;

    switch ((string)$brick[self::TYPE_ATTRIBUTE]) {
      // EVENT Bricks
      case self::BROADCAST_BRICK:
        $resolved_brick = $this->writeBroadcastBrick($brick);
        break;
      case self::BROADCAST_WAIT_BRICK:
        $resolved_brick = $this->writeBroadcastWaitBrick($brick);
        break;
      case self::WHEN_BRICK:
        $resolved_brick = $this->writeWhenBrick();
        break;

      // CONTROL Bricks
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
      case self::IF_THEN_BRICK:
      case self::IF_BRICK:
        $resolved_brick = $this->writeIfBrick($brick);
        break;
      case self::ELSE_BRICK:
        $resolved_brick = $this->writeElseBrick();
        break;
      case self::ENDIF_THEN_BRICK:
      case self::ENDIF_BRICK:
        $resolved_brick = $this->writeEndIfBrick();
        break;
      case self::REPEAT_BRICK:
        $resolved_brick = $this->writeRepeatBrick($brick);
        break;
      case self::LOOP_END_BRICK:
        $resolved_brick = $this->writeLoopEndBrick();
        break;
      case self::WAIT_UNTIL_BRICK:
        $resolved_brick = $this->writeWaitUntilBrick($brick);
        break;
      case self::REPEAT_UNTIL_BRICK:
        $resolved_brick = $this->writeRepeatUntilBrick($brick);
        break;
      case self::STOP_SCRIPT_BRICK:
        $resolved_brick = $this->writeStopScripBrick($brick);
        break;
      case self::SCENE_START_BRICK:
        $resolved_brick = $this->writeSceneStartBrick($brick);
        break;
      case self::CLONE_BRICK:
        $resolved_brick = $this->writeCloneBrick($brick);
        break;
      case self::DELETE_THIS_CLONE_BRICK:
        $resolved_brick = $this->writeDeleteThisCloneBrick();
        break;
      case self::CONTINUE_SCENE_BRICK:
        $resolved_brick = $this->writeContinueSceneBrick($brick);
        break;

      // MOTION Bricks
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
      case self::GO_TO_BRICK:
        $resolved_brick = $this->writeGoToBrick($brick);
        break;
      case self::SET_ROTATION_STYLE_BRICK:
        $resolved_brick = $this->writeSetRotationStyleBrick($brick);
        break;

      // SOUND Bricks
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
      case self::PLAY_SOUND_WAIT_BRICK:
        $resolved_brick = $this->writePlaySoundWaitBrick($brick);
        break;
      case self::SPEAK_WAIT_BRICK:
        $resolved_brick = $this->writeSpeakWaitBrick($brick);
        break;

      // LOOK Bricks
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
      case self::PREV_LOOK_BRICK:
        $resolved_brick = $this->writePrevLookBrick();
        break;
      case self::ASK_BRICK:
        $resolved_brick = $this->writeAskBrick($brick);
        break;
      case self::SAY_BUBBLE_BRICK:
        $resolved_brick = $this->writeSayBubbleBrick($brick);
        break;
      case self::SAY_FOR_BUBBLE_BRICK:
        $resolved_brick = $this->writeSayForBubbleBrick($brick);
        break;
      case self::THINK_BUBBLE_BRICK:
        $resolved_brick = $this->writeThinkBubbleBrick($brick);
        break;
      case self::THINK_FOR_BUBBLE_BRICK:
        $resolved_brick = $this->writeThinkForBubbleBrick($brick);
        break;
      case self::SET_BACKGROUND_BRICK:
        $resolved_brick = $this->writeSetBackgroundBrick($brick);
        break;
      case self::SET_BACKGROUND_WAIT_BRICK:
        $resolved_brick = $this->writeSetBackgroundWaitBrick($brick);
        break;

      // DATA Bricks
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

      // PEN Bricks
      case self::PEN_DOWN_BRICK:
        $resolved_brick = $this->writePenDownBrick();
        break;
      case self::PEN_UP_BRICK:
        $resolved_brick = $this->writePenUpBrick();
        break;
      case self::SET_PEN_SIZE_BRICK:
        $resolved_brick = $this->writeSetPenSizeBrick($brick);
        break;
      case self::SET_PEN_COLOR_BRICK:
        $resolved_brick = $this->writeSetPenColorBrick($brick);
        break;
      case self::STAMP_BRICK:
        $resolved_brick = $this->writeStampBrick();
        break;
      case self::CLEAR_BACKGROUND_BRICK:
        $resolved_brick = $this->writeClearBackgroundBrick();
        break;

      // OTHER Bricks
      default:
        $resolved_brick = $this->writeUnknownBrick();
        break;
    }

    return $resolved_brick;
  }

  private function isCommentedOut($brick)
  {
    return ($brick->commentedOut != null and $brick->commentedOut == 'true');
  }

  private function writeBroadcastBrick($brick)
  {
    return array(
      'name' => self::BROADCAST_BRICK,
      'text' => "Broadcast \"" . $brick->broadcastMessage . "\"",
      'img_file' => self::EVENT_BRICK_IMG
    );
  }

  private function writeBroadcastWaitBrick($brick)
  {
    return array(
      'name' => self::BROADCAST_WAIT_BRICK,
      'text' => "Broadcast and wait \"" . $brick->broadcastMessage . "\"",
      'img_file' => self::EVENT_BRICK_IMG
    );
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

  private function writeWaitUntilBrick($brick)
  {
    return array(
      'name' => self::WAIT_UNTIL_BRICK,
      'text' => "Wait until " . $this->retrieveFormulasFromXml($brick->formulaList)[self::IF_CONDITION_FORMULA] . " is true",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeRepeatUntilBrick($brick)
  {
    return array(
      'name' => self::REPEAT_UNTIL_BRICK,
      'text' => "Repeat until " . $this->retrieveFormulasFromXml($brick->formulaList)[self::REPEAT_UNTIL_CONDITION_FORMULA] . " is true",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeStopScripBrick($brick)
  {
    return array(
      'name' => self::STOP_SCRIPT_BRICK,
      'text' => $brick->xpath('spinnerValue/string')[(int)$brick->spinnerSelection],
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeSceneStartBrick($brick)
  {
    return array(
      'name' => self::SCENE_START_BRICK,
      'text' => "Start scene " . $brick->sceneToStart,
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeCloneBrick($brick)
  {
    $clone_name = null;
    if (count($brick->objectToClone) != 0)
      $clone_name = $brick->objectToClone->xpath($brick->objectToClone[self::REFERENCE_ATTRIBUTE])[0][self::NAME_ATTRIBUTE];
    else
      $clone_name = "myself";

    return array(
      'name' => self::CLONE_BRICK,
      'text' => "Create clone of " . $clone_name,
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeDeleteThisCloneBrick()
  {
    return array(
      'name' => self::DELETE_THIS_CLONE_BRICK,
      'text' => "Delete this",
      'img_file' => self::CONTROL_BRICK_IMG
    );
  }

  private function writeContinueSceneBrick($brick)
  {
    return array(
      'name' => self::CONTINUE_SCENE_BRICK,
      'text' => "Continue scene " . $brick->sceneForTransition,
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
    if ($brick->pointedObject[self::REFERENCE_ATTRIBUTE != null])
      $pointed_object_name = (string)$brick->pointedObject->xpath($brick->pointedObject[self::REFERENCE_ATTRIBUTE])[0][self::NAME_ATTRIBUTE];
    else
      $pointed_object_name = (string)$brick->pointedObject[self::NAME_ATTRIBUTE];

    return array(
      'name' => self::POINT_TO_BRICK,
      'text' => "Point towards " . $pointed_object_name,
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeGoToBrick($brick)
  {
    $destination = null;
    switch((string)$brick->spinnerSelection) {
      case 80:
        $destination = 'Touch position';
        break;
      case 81:
        $destination = 'Random position';
        break;
      case 82:
        $destination = (string)$brick->destinationSprite->xpath($brick->destinationSprite[self::REFERENCE_ATTRIBUTE])[0][self::NAME_ATTRIBUTE];
        break;
      default:
        $destination = 'unknown';
        break;
    }
    return array(
      'name' => self::GO_TO_BRICK,
      'text' => "Go to " . $destination,
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writeSetRotationStyleBrick($brick)
  {
    $rotation_style = null;
    switch((string)$brick->selection) {
      case 0:
        $rotation_style = 'left-right only';
        break;
      case 1:
        $rotation_style = 'all-around';
        break;
      case 2:
        $rotation_style = "don't rotate";
        break;
      default:
        $rotation_style = "unknown";
        break;
    }
    return array(
      'name' => self::SET_ROTATION_STYLE_BRICK,
      'text' => "Set rotation style " . $rotation_style,
      'img_file' => self::MOTION_BRICK_IMG
    );
  }

  private function writePlaySoundBrick($brick)
  {
    $sound = null;
    if ($brick->sound[self::REFERENCE_ATTRIBUTE != null])
      $sound = $brick->sound->xpath($brick->sound[self::REFERENCE_ATTRIBUTE])[0]->fileName;
    else
      $sound = $brick->sound->fileName;

    return array(
      'name' => self::PLAY_SOUND_BRICK,
      'text' => "Start sound",
      'img_file' => self::SOUND_BRICK_IMG,
      'sound' => $sound
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

  private function writePlaySoundWaitBrick($brick)
  {
    $sound_file_name = null;
    if ($brick->sound[self::REFERENCE_ATTRIBUTE] != null)
      $sound_file_name = $brick->sound->xpath($brick->sound[self::REFERENCE_ATTRIBUTE])[0]->fileName;
    else
      $sound_file_name = $brick->sound->fileName;

    return array(
      'name' => self::PLAY_SOUND_WAIT_BRICK,
      'text' => "Start sound and wait",
      'img_file' => self::SOUND_BRICK_IMG,
      'sound' => $sound_file_name
    );
  }

  private function writeSpeakWaitBrick($brick)
  {
    return array(
      'name' => self::SPEAK_WAIT_BRICK,
      'text' => "Speak \"" . $this->retrieveFormulasFromXml($brick->formulaList)[self::SPEAK_FORMULA] . "\" and wait",
      'img_file' => self::SOUND_BRICK_IMG
    );
  }

  private function writeSetLookBrick($brick)
  {
    $look_file_name = null;
    if ($brick->look[self::REFERENCE_ATTRIBUTE] != null)
      $look_file_name = $brick->look->xpath($brick->look[self::REFERENCE_ATTRIBUTE])[0]->fileName;
    else
      $look_file_name = $brick->look->fileName;

    return array(
      'name' => self::SET_LOOK_BRICK,
      'text' => "Switch to look",
      'img_file' => self::LOOKS_BRICK_IMG,
      'look' => $look_file_name
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
      'text' => "Turn camera " . $brick->xpath('spinnerValues/string')[(int)$brick->spinnerSelectionID],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeChooseCameraBrick($brick)
  {
    return array(
      'name' => self::CHOOSE_CAMERA_BRICK,
      'text' => "Use camera " . $brick->xpath('spinnerValues/string')[(int)$brick->spinnerSelectionID],
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
      'text' => "Turn flashlight " . $brick->xpath('spinnerValues/string')[(int)$brick->spinnerSelectionID],
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writePrevLookBrick()
  {
    return array(
      'name' => self::PREV_LOOK_BRICK,
      'text' => "Previous look",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeAskBrick($brick)
  {
    $variable = null;
    if ($brick->userVariable[self::REFERENCE_ATTRIBUTE] != null)
      $variable = $brick->userVariable->xpath($brick->userVariable[self::REFERENCE_ATTRIBUTE])[0];
    else
      $variable = $brick->userVariable;
    return array(
      'name' => self::ASK_BRICK,
      'text' => "Ask \"" . $this->retrieveFormulasFromXml($brick->formulaList)[self::ASK_QUESTION_FORMULA] . "\" and store written answer in " . $variable,
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSayBubbleBrick($brick)
  {
    return array(
      'name' => self::SAY_BUBBLE_BRICK,
      'text' => "Say \"" . $this->retrieveFormulasFromXml($brick->formulaList)[self::STRING_FORMULA] . "\"",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSayForBubbleBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::SAY_FOR_BUBBLE_BRICK,
      'text' => "Say \"" . $formulas[self::STRING_FORMULA] ."\" for " . $formulas[self::DURATION_IN_SECONDS_FORMULA] . " second/s",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeThinkBubbleBrick($brick)
  {
    return array(
      'name' => self::THINK_BUBBLE_BRICK,
      'text' => "Think \"" . $this->retrieveFormulasFromXml($brick->formulaList)[self::STRING_FORMULA] . "\"",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeThinkForBubbleBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::THINK_FOR_BUBBLE_BRICK,
      'text' => "Think \"" . $formulas[self::STRING_FORMULA] . "\" for " . $formulas[self::DURATION_IN_SECONDS_FORMULA] . " second/s",
      'img_file' => self::LOOKS_BRICK_IMG
    );
  }

  private function writeSetBackgroundBrick($brick)
  {
    return array(
      'name' => self::SET_BACKGROUND_BRICK,
      'text' => "Set background",
      'img_file' => self::LOOKS_BRICK_IMG,
      'look' => $brick->look->xpath($brick->look[self::REFERENCE_ATTRIBUTE])[0]->fileName
    );
  }

  private function writeSetBackgroundWaitBrick($brick)
  {
    return array(
      'name' => self::SET_BACKGROUND_WAIT_BRICK,
      'text' => "Set background and wait",
      'img_file' => self::LOOKS_BRICK_IMG,
      'look' => $brick->look->xpath($brick->look[self::REFERENCE_ATTRIBUTE])[0]->fileName
    );
  }

  private function writeSetVariableBrick($brick)
  {
    $user_variable = null;

    if ($brick->userVariable[self::REFERENCE_ATTRIBUTE] != null)
      $user_variable = (string)$brick->userVariable->xpath($brick->userVariable[self::REFERENCE_ATTRIBUTE])[0];
    else
      $user_variable = (string)$brick->userVariable;

    return array(
      'name' => self::SET_VARIABLE_BRICK,
      'text' => "Set variable " . $user_variable . " to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VARIABLE_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeChangeVariableBrick($brick)
  {
    $user_variable = null;

    if ($brick->userVariable[self::REFERENCE_ATTRIBUTE] != null)
      $user_variable = (string)$brick->userVariable->xpath($brick->userVariable[self::REFERENCE_ATTRIBUTE])[0];
    else
      $user_variable = (string)$brick->userVariable;

    return array(
      'name' => self::CHANGE_VARIABLE_BRICK,
      'text' => "Change variable " . $user_variable . " by " . $this->retrieveFormulasFromXml($brick->formulaList)[self::VARIABLE_CHANGE_FORMULA],
      'img_file' => self::DATA_BRICK_IMG
    );
  }

  private function writeShowTextBrick($brick)
  {
    $user_variable = null;

    if ($brick->userVariable[self::REFERENCE_ATTRIBUTE] != null)
      $user_variable = (string)$brick->userVariable->xpath($brick->userVariable[self::REFERENCE_ATTRIBUTE])[0];
    else
      $user_variable = (string)$brick->userVariable;

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

    if ($brick->userVariable[self::REFERENCE_ATTRIBUTE] != null)
      $user_variable = (string)$brick->userVariable->xpath($brick->userVariable[self::REFERENCE_ATTRIBUTE])[0];
    else
      $user_variable = (string)$brick->userVariable;

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

  private function writePenDownBrick()
  {
    return array(
      'name' => self::PEN_DOWN_BRICK,
      'text' => "Pen down",
      'img_file' => self::PEN_BRICK_IMG
    );
  }

  private function writePenUpBrick()
  {
    return array(
      'name' => self::PEN_UP_BRICK,
      'text' => "Pen up",
      'img_file' => self::PEN_BRICK_IMG
    );
  }

  private function writeSetPenSizeBrick($brick)
  {
    return array(
      'name' => self::SET_PEN_SIZE_BRICK,
      'text' => "Set pen size to " . $this->retrieveFormulasFromXml($brick->formulaList)[self::PEN_SIZE_FORMULA],
      'img_file' => self::PEN_BRICK_IMG
    );
  }

  private function writeSetPenColorBrick($brick)
  {
    $formulas = $this->retrieveFormulasFromXml($brick->formulaList);
    return array(
      'name' => self::SET_PEN_COLOR_BRICK,
      'text' => "Set pen color to Red: " . $formulas[self::PEN_COLOR_RED_FORMULA] . " Green: " . $formulas[self::PEN_COLOR_GREEN_FORMULA] . " Blue: " . $formulas[self::PEN_COLOR_BLUE_FORMULA],
      'img_file' => self::PEN_BRICK_IMG
    );
  }

  private function writeStampBrick()
  {
    return array(
      'name' => self::STAMP_BRICK,
      'text' => "Stamp",
      'img_file' => self::PEN_BRICK_IMG
    );
  }

  private function writeClearBackgroundBrick()
  {
    return array(
      'name' => self::CLEAR_BACKGROUND_BRICK,
      'text' => "Clear",
      'img_file' => self::PEN_BRICK_IMG
    );
  }

  private function writeWhenBrick()
  {
    return array(
      'name' => self::WHEN_BRICK,
      'text' => "When tapped",
      'img_file' => self::EVENT_BRICK_IMG
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
        case self::BRACKET_FORMULA_TYPE:
          $resolved_formula = $this->resolveFormulaCaseBracket($formula);
          break;
        default:
          $resolved_formula = $formula->value;
          break;
      }
    }
    return $resolved_formula;
  }

  private function resolveFormulaCaseBracket($formula)
  {
    return "(" . $this->resolveFormula($formula->rightChild) . ")";
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