<?php

namespace Catrobat\AppBundle\CatrobatCode;


use Catrobat\AppBundle\CatrobatCode\Statements\AddItemToUserListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\BroadcastMessageStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\BroadcastScriptStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\BroadcastStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\BroadcastWaitStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeBrightnessByNStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeSizeByNStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeTransparencyByNStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeVariableStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeVolumeByNStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeXByNStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ChangeYByNStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ClearGraphicEffectStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ComeToFrontStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\DeleteItemOfUserListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\FileNameStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ForeverStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\FormulaListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\FormulaStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\GlideToStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\GoNStepsBackStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\HideStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\HideTextStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\IfLogicBeginStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\IfLogicElseStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\IfLogicEndStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\IfOnEdgeBounceStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\InsertItemIntoUserListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LedOffStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LedOnStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LeftChildStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LookListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LookStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LoopEndlessStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LoopEndStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\MoveNStepsStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\NextLookStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\NoteStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ObjectStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\PlaceAtStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\PlaySoundStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\PointInDirectionStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\PointToStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ReceivedMessageStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\RepeatStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ReplaceItemInUserListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\RightChildStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ScriptListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetBrightnessStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetLookStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetSizeToStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetTransparencyStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetVariableStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetVolumeToStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetXStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SetYStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ShowStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ShowTextStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SoundListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SoundStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SpeakStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\StopAllSoundsStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\TappedScriptStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\TurnLeftStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\TurnRightStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\UnknownStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\UserListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\UserVariableStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ValueStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\VibrationStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\WaitStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\WhenScriptStatement;

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

  private $currentObject;

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

  private function generateValueStatement(\SimpleXMLElement $statement, $spaces)
  {
    $value = (string)$statement;
    $type = $this->getTypeOfValue($statement);

    return new ValueStatement($this, $statement, $spaces, $value, $type);
  }

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

  private function generateObjectStatement(\SimpleXMLElement $statement, $spaces)
  {
    $name = $statement['name'];
    $factory = new StatementFactory();
    $this->currentObject->addCodeObject($factory->createObject($statement));

    return new ObjectStatement($this, $spaces, $name);
  }

  private function generateReceivedMessageStatement(\SimpleXMLElement $statement, $spaces)
  {
    $message = (string)$statement;

    return new ReceivedMessageStatement($this, $statement, $spaces, $message);
  }

  private function generateBroadcastMessageStatement(\SimpleXMLElement $statement, $spaces)
  {
    $message = (string)$statement;

    return new BroadcastMessageStatement($this, $statement, $spaces, $message);
  }

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

  private function generateSoundStatement(\SimpleXMLElement $statement, $spaces)
  {
    $name = $this->getNameWithReference($statement);

    return new SoundStatement($this, $statement, $spaces, $name);
  }

  private function generateFileNameStatement(\SimpleXMLElement $statement, $spaces)
  {
    $message = (string)$statement;

    return new FileNameStatement($this, $statement, $spaces, $message);
  }
}

