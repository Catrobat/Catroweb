<?php

namespace App\Catrobat\CatrobatCode;

use App\Catrobat\CatrobatCode\Statements\AddItemToUserListStatement;
use App\Catrobat\CatrobatCode\Statements\BroadcastMessageStatement;
use App\Catrobat\CatrobatCode\Statements\BroadcastScriptStatement;
use App\Catrobat\CatrobatCode\Statements\BroadcastStatement;
use App\Catrobat\CatrobatCode\Statements\BroadcastWaitStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeBrightnessByNStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeSizeByNStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeTransparencyByNStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeVariableStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeVolumeByNStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeXByNStatement;
use App\Catrobat\CatrobatCode\Statements\ChangeYByNStatement;
use App\Catrobat\CatrobatCode\Statements\ClearGraphicEffectStatement;
use App\Catrobat\CatrobatCode\Statements\ComeToFrontStatement;
use App\Catrobat\CatrobatCode\Statements\DeleteItemOfUserListStatement;
use App\Catrobat\CatrobatCode\Statements\FileNameStatement;
use App\Catrobat\CatrobatCode\Statements\ForeverStatement;
use App\Catrobat\CatrobatCode\Statements\FormulaListStatement;
use App\Catrobat\CatrobatCode\Statements\FormulaStatement;
use App\Catrobat\CatrobatCode\Statements\GlideToStatement;
use App\Catrobat\CatrobatCode\Statements\GoNStepsBackStatement;
use App\Catrobat\CatrobatCode\Statements\HideStatement;
use App\Catrobat\CatrobatCode\Statements\HideTextStatement;
use App\Catrobat\CatrobatCode\Statements\IfLogicBeginStatement;
use App\Catrobat\CatrobatCode\Statements\IfLogicElseStatement;
use App\Catrobat\CatrobatCode\Statements\IfLogicEndStatement;
use App\Catrobat\CatrobatCode\Statements\IfOnEdgeBounceStatement;
use App\Catrobat\CatrobatCode\Statements\InsertItemIntoUserListStatement;
use App\Catrobat\CatrobatCode\Statements\LedOffStatement;
use App\Catrobat\CatrobatCode\Statements\LedOnStatement;
use App\Catrobat\CatrobatCode\Statements\LeftChildStatement;
use App\Catrobat\CatrobatCode\Statements\LookListStatement;
use App\Catrobat\CatrobatCode\Statements\LookStatement;
use App\Catrobat\CatrobatCode\Statements\LoopEndlessStatement;
use App\Catrobat\CatrobatCode\Statements\LoopEndStatement;
use App\Catrobat\CatrobatCode\Statements\MoveNStepsStatement;
use App\Catrobat\CatrobatCode\Statements\NextLookStatement;
use App\Catrobat\CatrobatCode\Statements\NoteStatement;
use App\Catrobat\CatrobatCode\Statements\ObjectStatement;
use App\Catrobat\CatrobatCode\Statements\PlaceAtStatement;
use App\Catrobat\CatrobatCode\Statements\PlaySoundStatement;
use App\Catrobat\CatrobatCode\Statements\PointInDirectionStatement;
use App\Catrobat\CatrobatCode\Statements\PointToStatement;
use App\Catrobat\CatrobatCode\Statements\ReceivedMessageStatement;
use App\Catrobat\CatrobatCode\Statements\RepeatStatement;
use App\Catrobat\CatrobatCode\Statements\ReplaceItemInUserListStatement;
use App\Catrobat\CatrobatCode\Statements\RightChildStatement;
use App\Catrobat\CatrobatCode\Statements\ScriptListStatement;
use App\Catrobat\CatrobatCode\Statements\SetBrightnessStatement;
use App\Catrobat\CatrobatCode\Statements\SetLookStatement;
use App\Catrobat\CatrobatCode\Statements\SetSizeToStatement;
use App\Catrobat\CatrobatCode\Statements\SetTransparencyStatement;
use App\Catrobat\CatrobatCode\Statements\SetVariableStatement;
use App\Catrobat\CatrobatCode\Statements\SetVolumeToStatement;
use App\Catrobat\CatrobatCode\Statements\SetXStatement;
use App\Catrobat\CatrobatCode\Statements\SetYStatement;
use App\Catrobat\CatrobatCode\Statements\ShowStatement;
use App\Catrobat\CatrobatCode\Statements\ShowTextStatement;
use App\Catrobat\CatrobatCode\Statements\SoundListStatement;
use App\Catrobat\CatrobatCode\Statements\SoundStatement;
use App\Catrobat\CatrobatCode\Statements\SpeakStatement;
use App\Catrobat\CatrobatCode\Statements\StopAllSoundsStatement;
use App\Catrobat\CatrobatCode\Statements\TappedScriptStatement;
use App\Catrobat\CatrobatCode\Statements\TurnLeftStatement;
use App\Catrobat\CatrobatCode\Statements\TurnRightStatement;
use App\Catrobat\CatrobatCode\Statements\UnknownStatement;
use App\Catrobat\CatrobatCode\Statements\UserListStatement;
use App\Catrobat\CatrobatCode\Statements\UserVariableStatement;
use App\Catrobat\CatrobatCode\Statements\ValueStatement;
use App\Catrobat\CatrobatCode\Statements\VibrationStatement;
use App\Catrobat\CatrobatCode\Statements\WaitStatement;
use App\Catrobat\CatrobatCode\Statements\WhenScriptStatement;


/**
 * Class StatementFactory
 * @package App\Catrobat\CatrobatCode
 */
class StatementFactory
{
  const WAIT_STMT = 'WaitBrick';
  const PLAY_SOUND_STMT = 'PlaySoundBrick';
  const STOP_ALL_STMT = 'StopAllSoundsBrick';
  const SET_VOLUME_TO_STMT = 'SetVolumeToBrick';
  const SPEAK_STMT = 'SpeakBrick';
  const CHANGE_VOLUME_BY_N_STMT = 'ChangeVolumeByNBrick';
  const BROADCAST_WAIT_STMT = 'BroadcastWaitBrick';
  const REPEAT_STMT = 'RepeatBrick';
  const NOTE_STMT = 'NoteBrick';
  const LOOP_END_STMT = 'LoopEndBrick';
  const FOREVER_STMT = 'ForeverBrick';
  const LOOP_ENDLESS_STMT = 'LoopEndlessBrick';
  const BROADCAST_STMT = 'BroadcastBrick';
  const SET_Y_STMT = 'SetYBrick';
  const CHANGE_Y_BY_N_STMT = 'ChangeYByNBrick';
  const TURN_LEFT_STMT = 'TurnLeftBrick';
  const TURN_RIGHT_STMT = 'TurnRightBrick';
  const MOVE_N_STEPS_STMT = 'MoveNStepsBrick';
  const POINT_TO_STMT = 'PointToBrick';
  const IF_LOGIC_BEGIN_STMT = 'IfLogicBeginBrick';
  const IF_LOGIC_ELSE_STMT = 'IfLogicElseBrick';
  const IF_LOGIC_END_STMT = 'IfLogicEndBrick';
  const SET_VARIABLE_STMT = 'SetVariableBrick';
  const REPLACE_ITEM_IN_USER_LIST_STMT = 'ReplaceItemInUserListBrick';
  const INSERT_ITEM_IN_INTO_USER_LIST_STMT = 'InsertItemIntoUserListBrick';
  const DELETE_ITEM_OF_USER_LIST_STMT = 'DeleteItemOfUserListBrick';
  const ADD_ITEM_TO_USER_LIST_STMT = 'AddItemToUserListBrick';
  const CHANGE_VARIABLE_STMT = 'ChangeVariableBrick';
  const SET_LOOK_STMT = 'SetLookBrick';
  const HIDE_STMT = 'HideBrick';
  const LED_ON_STMT = 'LedOnBrick';
  const LED_OFF_STMT = 'LedOffBrick';
  const CLEAR_GRAPHICS_EFFECT_STMT = 'ClearGraphicEffectBrick';
  const CHANGE_BRIGHTNESS_BY_N_STMT = 'ChangeBrightnessByNBrick';
  const SET_BRIGHTNESS_STMT = 'SetBrightnessBrick';
  const CHANGE_TRANSPARENCY_BY_N_STMT = 'ChangeTransparencyByNBrick';
  const SET_TRANSPARENCY_STMT = 'SetTransparencyBrick';
  const SHOW_STMT = 'ShowBrick';
  const NEXT_LOOK_STMT = 'NextLookBrick';
  const SET_SIZE_TO_STMT = 'SetSizeToBrick';
  const CHANGE_SIZE_BY_N_STMT = 'ChangeSizeByNBrick';
  const POINT_IN_DIRECTION_STMT = 'PointInDirectionBrick';
  const VIBRATION_STMT = 'VibrationBrick';
  const COME_TO_FRONT_STMT = 'ComeToFrontBrick';
  const GO_N_STEPS_BACK_STMT = 'GoNStepsBackBrick';
  const GLIDE_TO_STMT = 'GlideToBrick';
  const IF_ON_EDGE_BOUNCE_STMT = 'IfOnEdgeBounceBrick';
  const CHANGE_X_BY_N_STMT = 'ChangeXByNBrick';
  const PLACE_AT_STMT = 'PlaceAtBrick';
  const SET_X_STMT = 'SetXBrick';
  const SCRIPT_STMT = 'script';
  const BRICK_STMT = 'brick';
  const SCRIPT_LIST = 'scriptList';
  const BRICK_LIST = 'brickList';
  const LOOK_LIST = 'lookList';
  const SOUND_LIST = 'soundList';
  const START_SCRIPT = 'StartScript';
  const BROADCAST_SCRIPT = 'BroadcastScript';
  const WHEN_SCRIPT = 'WhenScript';
  const NAME_ATTRIBUTE = 'name';
  const TYPE_ATTRIBUTE = 'type';
  const CATEGORY_ATTRIBUTE = 'category';
  const REFERENCE_ATTRIBUTE = 'reference';
  const USER_VARIABLE_STMT = 'userVariable';
  const RIGHT_CHILD_STMT = 'rightChild';
  const LEFT_CHILD_STMT = 'leftChild';
  const USER_LIST_STMT = 'userList';
  const VALUE_STMT = 'value';
  const LOOK_STMT = 'look';
  const SOUND_STMT = 'sound';
  const POINTED_OBJECT_STMT = 'pointedObject';
  const FILE_NAME_STMT = 'fileName';
  const RECEIVED_MESSAGE_STMT = 'receivedMessage';
  const BROADCAST_MESSAGE_STMT = 'broadcastMessage';
  const FORMULA = 'formula';
  const FORMULA_LIST = 'formulaList';
  const SHOW_TEXT_STMT = 'ShowTextBrick';
  const SHOW_TEXT_WITHOUT_ALIAS_STMT = 'org.catrobat.catroid.content.bricks.ShowTextBrick';
  const HIDE_TEXT_STMT = 'HideTextBrick';
  const HIDE_TEXT_WITHOUT_ALIAS_STMT = 'org.catrobat.catroid.content.bricks.HideTextBrick';
  const USER_BRICKS = 'userBricks';
  const IS_USER_BRICK = 'inUserBrick';
  const IS_USER_SCRIPT = 'isUserScript';
  const ACTION = 'action';
  const USER_VARIABLE_NAME = 'userVariableName';

  /**
   * @var CodeObject
   */
  private $currentObject;


  /**
   * @param \SimpleXMLElement $objectTree
   *
   * @return CodeObject|null
   */
  public function createObject(\SimpleXMLElement $objectTree)
  {
    $this->currentObject = new CodeObject();
    $this->currentObject->setName($objectTree[self::NAME_ATTRIBUTE]);

    $this->currentObject->addAllScripts($this->createStatement($objectTree, 1));
    if ($this->currentObject->getName() == null)
    {
      return null;
    }

    return $this->currentObject;
  }


  /**
   * @param \SimpleXMLElement $xmlTree
   * @param                   $spaces
   *
   * @return array
   */
  public function createStatement(\SimpleXMLElement $xmlTree, $spaces)
  {
    $statements = [];
    if ($xmlTree->count() == 0)
    {
      return $statements;
    }
    $children = $xmlTree->children();

    foreach ($children as $statement)
    {
      $tmpStatement = null;

      switch ($statement->getName())
      {
        case self::SCRIPT_LIST:
          $tmpStatement = new ScriptListStatement($this, $statement, $spaces);
          break;
        case self::SCRIPT_STMT:
          $tmpStatement = $this->generateScriptStatement($statement, $spaces);
          break;
        case self::BRICK_STMT:
          $tmpStatement = $this->generateBrickStatement($statement, $spaces);
          break;
        case self::BRICK_LIST:
          $tmpStatement = new ScriptListStatement($this, $statement, $spaces);
          break;
        case self::SOUND_LIST:
          $tmpStatement = new SoundListStatement($this, $statement, $spaces);
          break;
        case self::LOOK_LIST:
          $tmpStatement = new LookListStatement($this, $statement, $spaces);
          break;
        case self::FORMULA_LIST:
          $tmpStatement = new FormulaListStatement($this, $statement, 0);
          break;
        case self::FORMULA:
          $tmpStatement = new FormulaStatement($this, $statement, 0, (string)$statement[self::CATEGORY_ATTRIBUTE]);
          break;
        case self::RIGHT_CHILD_STMT:
          $tmpStatement = new RightChildStatement($this, $statement, 0);
          break;
        case self::LEFT_CHILD_STMT:
          $tmpStatement = new LeftChildStatement($this, $statement, 0);
          break;
        case self::VALUE_STMT:
          $tmpStatement = $this->generateValueStatement($statement, 0);
          break;
        case self::USER_VARIABLE_STMT:
          $tmpStatement = $this->generateUserVariableStatement($statement, 0);
          break;
        case self::POINTED_OBJECT_STMT:
          $tmpStatement = $this->generateObjectStatement($statement, 0);
          break;
        case self::RECEIVED_MESSAGE_STMT:
          $tmpStatement = $this->generateReceivedMessageStatement($statement, 0);
          break;
        case self::BROADCAST_MESSAGE_STMT:
          $tmpStatement = $this->generateBroadcastMessageStatement($statement, 0);
          break;
        case self::SHOW_TEXT_WITHOUT_ALIAS_STMT:
          $tmpStatement = $this->generateBrickStatement($statement, $spaces);
          break;
        case self::HIDE_TEXT_WITHOUT_ALIAS_STMT:
          $tmpStatement = $this->generateBrickStatement($statement, $spaces);
          break;
        case self::LOOK_STMT:
          $tmpStatement = $this->generateLookStatement($statement, $spaces);
          break;
        case self::USER_LIST_STMT:
          $tmpStatement = $this->generateUserListStatement($statement, $spaces);
          break;
        case self::NAME_ATTRIBUTE:
          $tmpStatement = $this->generateValueStatement($statement, 0);
          break;
        case self::SOUND_STMT:
          $tmpStatement = $this->generateSoundStatement($statement, $spaces);
          break;
        case self::FILE_NAME_STMT:
          $tmpStatement = $this->generateFileNameStatement($statement, $spaces);
          break;
        case self::USER_BRICKS:
          break;
        case self::IS_USER_BRICK:
          break;
        case self::IS_USER_SCRIPT:
          break;
        case self::ACTION:
          break;
        case self::USER_VARIABLE_NAME:
          break;
        default:
          $tmpStatement = new UnknownStatement($this, $statement, $spaces);
          break;
      }

      if ($tmpStatement != null)
      {
        if ($tmpStatement instanceof UserVariableStatement)
        {
          array_unshift($statements, $tmpStatement);
        }
        else
        {
          $statements[] = $tmpStatement;
        }
        $spaces = $tmpStatement->getSpacesForNextBrick();
      }
    }

    return $statements;
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return BroadcastScriptStatement|TappedScriptStatement|UnknownStatement|WhenScriptStatement|null
   */
  private function generateScriptStatement(\SimpleXMLElement $statement, $spaces)
  {
    $stmt = null;
    $children = $statement;
    switch ((string)$statement[self::TYPE_ATTRIBUTE])
    {
      case self::WHEN_SCRIPT:
        $stmt = new TappedScriptStatement($this, $children, $spaces);
        break;
      case self::START_SCRIPT:
        $stmt = new WhenScriptStatement($this, $children, $spaces);
        break;
      case self::BROADCAST_SCRIPT:
        $stmt = new BroadcastScriptStatement($this, $children, $spaces);
        break;
      default:
        $stmt = new UnknownStatement($this, $statement, $spaces);
        break;
    }

    return $stmt;
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return BroadcastWaitStatement|ChangeVolumeByNStatement|
   *          PlaySoundStatement|SetVolumeToStatement|SpeakStatement|StopAllSoundsStatement|null
   */
  public function generateBrickStatement(\SimpleXMLElement $statement, $spaces)
  {
    $stmt = null;
    $children = $statement;
    switch ((string)$statement[self::TYPE_ATTRIBUTE])
    {
      case self::PLAY_SOUND_STMT:
        $stmt = new PlaySoundStatement($this, $children, $spaces);
        break;
      case self::STOP_ALL_STMT:
        $stmt = new StopAllSoundsStatement($this, $children, $spaces);
        break;
      case self::SET_VOLUME_TO_STMT:
        $stmt = new SetVolumeToStatement($this, $children, $spaces);
        break;
      case self::SPEAK_STMT:
        $stmt = new SpeakStatement($this, $children, $spaces);
        break;
      case self::CHANGE_VOLUME_BY_N_STMT:
        $stmt = new ChangeVolumeByNStatement($this, $children, $spaces);
        break;
      case self::BROADCAST_WAIT_STMT:
        $stmt = new BroadcastWaitStatement($this, $children, $spaces);
        break;
      case self::REPEAT_STMT:
        $stmt = new RepeatStatement($this, $children, $spaces);
        break;
      case self::NOTE_STMT:
        $stmt = new NoteStatement($this, $children, $spaces);
        break;
      case self::LOOP_END_STMT:
        $stmt = new LoopEndStatement($this, $children, $spaces);
        break;
      case self::FOREVER_STMT:
        $stmt = new ForeverStatement($this, $children, $spaces);
        break;
      case self::LOOP_ENDLESS_STMT:
        $stmt = new LoopEndlessStatement($this, $children, $spaces);
        break;
      case self::BROADCAST_STMT:
        $stmt = new BroadcastStatement($this, $children, $spaces);
        break;
      case self::SET_Y_STMT:
        $stmt = new SetYStatement($this, $children, $spaces);
        break;
      case self::CHANGE_Y_BY_N_STMT:
        $stmt = new ChangeYByNStatement($this, $children, $spaces);
        break;
      case self::TURN_LEFT_STMT:
        $stmt = new TurnLeftStatement($this, $children, $spaces);
        break;
      case self::TURN_RIGHT_STMT:
        $stmt = new TurnRightStatement($this, $children, $spaces);
        break;
      case self::MOVE_N_STEPS_STMT:
        $stmt = new MoveNStepsStatement($this, $children, $spaces);
        break;
      case self::POINT_TO_STMT:
        $stmt = new PointToStatement($this, $children, $spaces);
        break;
      case self::IF_LOGIC_BEGIN_STMT:
        $stmt = new IfLogicBeginStatement($this, $children, $spaces);
        break;
      case self::IF_LOGIC_ELSE_STMT:
        $stmt = new IfLogicElseStatement($this, $children, $spaces);
        break;
      case self::IF_LOGIC_END_STMT:
        $stmt = new IfLogicEndStatement($this, $children, $spaces);
        break;
      case self::SET_VARIABLE_STMT:
        $stmt = new SetVariableStatement($this, $children, $spaces);
        break;
      case self::REPLACE_ITEM_IN_USER_LIST_STMT:
        $stmt = new ReplaceItemInUserListStatement($this, $children, $spaces);
        break;
      case self::INSERT_ITEM_IN_INTO_USER_LIST_STMT:
        $stmt = new InsertItemIntoUserListStatement($this, $children, $spaces);
        break;
      case self::DELETE_ITEM_OF_USER_LIST_STMT:
        $stmt = new DeleteItemOfUserListStatement($this, $children, $spaces);
        break;
      case self::ADD_ITEM_TO_USER_LIST_STMT:
        $stmt = new AddItemToUserListStatement($this, $children, $spaces);
        break;
      case self::CHANGE_VARIABLE_STMT:
        $stmt = new ChangeVariableStatement($this, $children, $spaces);
        break;
      case self::SET_LOOK_STMT:
        $stmt = new SetLookStatement($this, $children, $spaces);
        break;
      case self::HIDE_STMT:
        $stmt = new HideStatement($this, $children, $spaces);
        break;
      case self::LED_ON_STMT:
        $stmt = new LedOnStatement($this, $children, $spaces);
        break;
      case self::LED_OFF_STMT:
        $stmt = new LedOffStatement($this, $children, $spaces);
        break;
      case self::CLEAR_GRAPHICS_EFFECT_STMT:
        $stmt = new ClearGraphicEffectStatement($this, $children, $spaces);
        break;
      case self::CHANGE_BRIGHTNESS_BY_N_STMT:
        $stmt = new ChangeBrightnessByNStatement($this, $children, $spaces);
        break;
      case self::SET_BRIGHTNESS_STMT:
        $stmt = new SetBrightnessStatement($this, $children, $spaces);
        break;
      case self::CHANGE_TRANSPARENCY_BY_N_STMT:
        $stmt = new ChangeTransparencyByNStatement($this, $children, $spaces);
        break;
      case self::SET_TRANSPARENCY_STMT:
        $stmt = new SetTransparencyStatement($this, $children, $spaces);
        break;
      case self::SHOW_STMT:
        $stmt = new ShowStatement($this, $children, $spaces);
        break;
      case self::NEXT_LOOK_STMT:
        $stmt = new NextLookStatement($this, $children, $spaces);
        break;
      case self::SET_SIZE_TO_STMT:
        $stmt = new SetSizeToStatement($this, $children, $spaces);
        break;
      case self::CHANGE_SIZE_BY_N_STMT:
        $stmt = new ChangeSizeByNStatement($this, $children, $spaces);
        break;
      case self::POINT_IN_DIRECTION_STMT:
        $stmt = new PointInDirectionStatement($this, $children, $spaces);
        break;
      case self::VIBRATION_STMT:
        $stmt = new VibrationStatement($this, $children, $spaces);
        break;
      case self::COME_TO_FRONT_STMT:
        $stmt = new ComeToFrontStatement($this, $children, $spaces);
        break;
      case self::GO_N_STEPS_BACK_STMT:
        $stmt = new GoNStepsBackStatement($this, $children, $spaces);
        break;
      case self::GLIDE_TO_STMT:
        $stmt = new GlideToStatement($this, $children, $spaces);
        break;
      case self::IF_ON_EDGE_BOUNCE_STMT:
        $stmt = new IfOnEdgeBounceStatement($this, $children, $spaces);
        break;
      case self::CHANGE_X_BY_N_STMT:
        $stmt = new ChangeXByNStatement($this, $children, $spaces);
        break;
      case self::PLACE_AT_STMT:
        $stmt = new PlaceAtStatement($this, $children, $spaces);
        break;
      case self::SET_X_STMT:
        $stmt = new SetXStatement($this, $children, $spaces);
        break;
      case self::WAIT_STMT:
        $stmt = new WaitStatement($this, $children, $spaces);
        break;
      case self::SHOW_TEXT_STMT:
        $stmt = new ShowTextStatement($this, $children, $spaces);
        break;
      case self::HIDE_TEXT_STMT:
        $stmt = new HideTextStatement($this, $children, $spaces);
        break;
      case self::USER_BRICKS:
        break;
      default:
        $stmt = new UnknownStatement($this, $statement, $spaces);
        break;
    }

    return $stmt;
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return ValueStatement
   */
  private function generateValueStatement(\SimpleXMLElement $statement, $spaces)
  {
    $value = (string)$statement;
    $type = $this->getTypeOfValue($statement);

    return new ValueStatement($this, $statement, $spaces, $value, $type);
  }


  /**
   * @param \SimpleXMLElement $statement
   *
   * @return string|null
   */
  private function getTypeOfValue(\SimpleXMLElement $statement)
  {
    $siblings = $statement->xpath('preceding-sibling::* | following-sibling::*');
    foreach ($siblings as $element)
    {
      if ($element->getName() == self::TYPE_ATTRIBUTE)
      {
        return (string)$element;
      }
    }

    return null;
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return UserVariableStatement
   */
  private function generateUserVariableStatement(\SimpleXMLElement $statement, $spaces)
  {
    $variableName = (string)$statement;
    if ($variableName == null)
    {
      $reference = (string)$statement[self::REFERENCE_ATTRIBUTE];
      $variableName = (string)($statement->xpath($reference)[0]);
    }

    $parent = 'parent::*';
    if ($this->isTypeExisting($statement, $parent, self::SHOW_TEXT_STMT) ||
      $this->isTypeExisting($statement, $parent, self::HIDE_TEXT_STMT)
    )
    {

      return new UserVariableStatement($this, $statement, $spaces, $variableName, true);
    }

    return new UserVariableStatement($this, $statement, $spaces, $variableName);
  }

  private function isTypeExisting(\SimpleXMLElement $statement, $reference, $type)
  {
    $elements = $statement->xpath($reference);
    foreach ($elements as $element)
    {
      if ((string)$element[self::TYPE_ATTRIBUTE] == $type)
      {
        return true;
      }
    }

    return false;
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return ObjectStatement
   */
  private function generateObjectStatement(\SimpleXMLElement $statement, $spaces)
  {
    $name = $statement['name'];
    $factory = new StatementFactory();
    $this->currentObject->addCodeObject($factory->createObject($statement));

    return new ObjectStatement($this, $spaces, $name);
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return ReceivedMessageStatement
   */
  private function generateReceivedMessageStatement(\SimpleXMLElement $statement, $spaces)
  {
    $message = (string)$statement;

    return new ReceivedMessageStatement($this, $statement, $spaces, $message);
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return BroadcastMessageStatement
   */
  private function generateBroadcastMessageStatement(\SimpleXMLElement $statement, $spaces)
  {
    $message = (string)$statement;

    return new BroadcastMessageStatement($this, $statement, $spaces, $message);
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return LookStatement
   */
  private function generateLookStatement(\SimpleXMLElement $statement, $spaces)
  {
    $lookName = (string)$statement[self::NAME_ATTRIBUTE];
    if ($lookName == null)
    {
      $reference = (string)$statement[self::REFERENCE_ATTRIBUTE];
      $look = $statement->xpath($reference)[0];
      $lookName = (string)$look[self::NAME_ATTRIBUTE];
    }

    return new LookStatement($this, $statement, $spaces, $lookName);
  }

  private function generateUserListStatement(\SimpleXMLElement $statement, $spaces)
  {
    $userListName = $this->getNameWithReference($statement);

    return new UserListStatement($this, $statement, $spaces, $userListName);
  }


  /**
   * @param \SimpleXMLElement $statement
   *
   * @return string
   */
  private function getNameWithReference(\SimpleXMLElement $statement)
  {
    $name = "";
    $reference = (string)$statement[self::REFERENCE_ATTRIBUTE];

    if ($reference != null)
    {
      $reference = (string)$statement[self::REFERENCE_ATTRIBUTE];
      $userListReference = $statement->xpath($reference)[0];
      foreach ($userListReference->children() as $child)
      {
        if ($child->getName() == self::NAME_ATTRIBUTE)
        {
          $name = (string)$child;
        }
      }
    }

    return $name;
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return SoundStatement
   */
  private function generateSoundStatement(\SimpleXMLElement $statement, $spaces)
  {
    $name = $this->getNameWithReference($statement);

    return new SoundStatement($this, $statement, $spaces, $name);
  }


  /**
   * @param \SimpleXMLElement $statement
   * @param                   $spaces
   *
   * @return FileNameStatement
   */
  private function generateFileNameStatement(\SimpleXMLElement $statement, $spaces)
  {
    $message = (string)$statement;

    return new FileNameStatement($this, $statement, $spaces, $message);
  }
}

