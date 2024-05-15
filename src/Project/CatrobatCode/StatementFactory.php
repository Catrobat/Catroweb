<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode;

use App\Project\CatrobatCode\Statements\AddItemToUserListStatement;
use App\Project\CatrobatCode\Statements\BroadcastMessageStatement;
use App\Project\CatrobatCode\Statements\BroadcastScriptStatement;
use App\Project\CatrobatCode\Statements\BroadcastStatement;
use App\Project\CatrobatCode\Statements\BroadcastWaitStatement;
use App\Project\CatrobatCode\Statements\ChangeBrightnessByNStatement;
use App\Project\CatrobatCode\Statements\ChangeSizeByNStatement;
use App\Project\CatrobatCode\Statements\ChangeTransparencyByNStatement;
use App\Project\CatrobatCode\Statements\ChangeVariableStatement;
use App\Project\CatrobatCode\Statements\ChangeVolumeByNStatement;
use App\Project\CatrobatCode\Statements\ChangeXByNStatement;
use App\Project\CatrobatCode\Statements\ChangeYByNStatement;
use App\Project\CatrobatCode\Statements\ClearGraphicEffectStatement;
use App\Project\CatrobatCode\Statements\ComeToFrontStatement;
use App\Project\CatrobatCode\Statements\DeleteItemOfUserListStatement;
use App\Project\CatrobatCode\Statements\FileNameStatement;
use App\Project\CatrobatCode\Statements\ForeverStatement;
use App\Project\CatrobatCode\Statements\FormulaListStatement;
use App\Project\CatrobatCode\Statements\FormulaStatement;
use App\Project\CatrobatCode\Statements\GlideToStatement;
use App\Project\CatrobatCode\Statements\GoNStepsBackStatement;
use App\Project\CatrobatCode\Statements\HideStatement;
use App\Project\CatrobatCode\Statements\HideTextStatement;
use App\Project\CatrobatCode\Statements\IfLogicBeginStatement;
use App\Project\CatrobatCode\Statements\IfLogicElseStatement;
use App\Project\CatrobatCode\Statements\IfLogicEndStatement;
use App\Project\CatrobatCode\Statements\IfOnEdgeBounceStatement;
use App\Project\CatrobatCode\Statements\InsertItemIntoUserListStatement;
use App\Project\CatrobatCode\Statements\LedOffStatement;
use App\Project\CatrobatCode\Statements\LedOnStatement;
use App\Project\CatrobatCode\Statements\LeftChildStatement;
use App\Project\CatrobatCode\Statements\LookListStatement;
use App\Project\CatrobatCode\Statements\LookStatement;
use App\Project\CatrobatCode\Statements\LoopEndlessStatement;
use App\Project\CatrobatCode\Statements\LoopEndStatement;
use App\Project\CatrobatCode\Statements\MoveNStepsStatement;
use App\Project\CatrobatCode\Statements\NextLookStatement;
use App\Project\CatrobatCode\Statements\NoteStatement;
use App\Project\CatrobatCode\Statements\ObjectStatement;
use App\Project\CatrobatCode\Statements\PlaceAtStatement;
use App\Project\CatrobatCode\Statements\PlaySoundStatement;
use App\Project\CatrobatCode\Statements\PointInDirectionStatement;
use App\Project\CatrobatCode\Statements\PointToStatement;
use App\Project\CatrobatCode\Statements\ReceivedMessageStatement;
use App\Project\CatrobatCode\Statements\RepeatStatement;
use App\Project\CatrobatCode\Statements\ReplaceItemInUserListStatement;
use App\Project\CatrobatCode\Statements\RightChildStatement;
use App\Project\CatrobatCode\Statements\ScriptListStatement;
use App\Project\CatrobatCode\Statements\SetBrightnessStatement;
use App\Project\CatrobatCode\Statements\SetLookStatement;
use App\Project\CatrobatCode\Statements\SetSizeToStatement;
use App\Project\CatrobatCode\Statements\SetTransparencyStatement;
use App\Project\CatrobatCode\Statements\SetVariableStatement;
use App\Project\CatrobatCode\Statements\SetVolumeToStatement;
use App\Project\CatrobatCode\Statements\SetXStatement;
use App\Project\CatrobatCode\Statements\SetYStatement;
use App\Project\CatrobatCode\Statements\ShowStatement;
use App\Project\CatrobatCode\Statements\ShowTextStatement;
use App\Project\CatrobatCode\Statements\SoundListStatement;
use App\Project\CatrobatCode\Statements\SoundStatement;
use App\Project\CatrobatCode\Statements\SpeakStatement;
use App\Project\CatrobatCode\Statements\Statement;
use App\Project\CatrobatCode\Statements\StopAllSoundsStatement;
use App\Project\CatrobatCode\Statements\TappedScriptStatement;
use App\Project\CatrobatCode\Statements\TurnLeftStatement;
use App\Project\CatrobatCode\Statements\TurnRightStatement;
use App\Project\CatrobatCode\Statements\UnknownStatement;
use App\Project\CatrobatCode\Statements\UserListStatement;
use App\Project\CatrobatCode\Statements\UserVariableStatement;
use App\Project\CatrobatCode\Statements\ValueStatement;
use App\Project\CatrobatCode\Statements\VibrationStatement;
use App\Project\CatrobatCode\Statements\WaitStatement;
use App\Project\CatrobatCode\Statements\WhenScriptStatement;

class StatementFactory
{
  final public const string WAIT_STMT = 'WaitBrick';
  final public const string PLAY_SOUND_STMT = 'PlaySoundBrick';
  final public const string STOP_ALL_STMT = 'StopAllSoundsBrick';
  final public const string SET_VOLUME_TO_STMT = 'SetVolumeToBrick';
  final public const string SPEAK_STMT = 'SpeakBrick';
  final public const string CHANGE_VOLUME_BY_N_STMT = 'ChangeVolumeByNBrick';
  final public const string BROADCAST_WAIT_STMT = 'BroadcastWaitBrick';
  final public const string REPEAT_STMT = 'RepeatBrick';
  final public const string NOTE_STMT = 'NoteBrick';
  final public const string LOOP_END_STMT = 'LoopEndBrick';
  final public const string FOREVER_STMT = 'ForeverBrick';
  final public const string LOOP_ENDLESS_STMT = 'LoopEndlessBrick';
  final public const string BROADCAST_STMT = 'BroadcastBrick';
  final public const string SET_Y_STMT = 'SetYBrick';
  final public const string CHANGE_Y_BY_N_STMT = 'ChangeYByNBrick';
  final public const string TURN_LEFT_STMT = 'TurnLeftBrick';
  final public const string TURN_RIGHT_STMT = 'TurnRightBrick';
  final public const string MOVE_N_STEPS_STMT = 'MoveNStepsBrick';
  final public const string POINT_TO_STMT = 'PointToBrick';
  final public const string IF_LOGIC_BEGIN_STMT = 'IfLogicBeginBrick';
  final public const string IF_LOGIC_ELSE_STMT = 'IfLogicElseBrick';
  final public const string IF_LOGIC_END_STMT = 'IfLogicEndBrick';
  final public const string SET_VARIABLE_STMT = 'SetVariableBrick';
  final public const string REPLACE_ITEM_IN_USER_LIST_STMT = 'ReplaceItemInUserListBrick';
  final public const string INSERT_ITEM_IN_INTO_USER_LIST_STMT = 'InsertItemIntoUserListBrick';
  final public const string DELETE_ITEM_OF_USER_LIST_STMT = 'DeleteItemOfUserListBrick';
  final public const string ADD_ITEM_TO_USER_LIST_STMT = 'AddItemToUserListBrick';
  final public const string CHANGE_VARIABLE_STMT = 'ChangeVariableBrick';
  final public const string SET_LOOK_STMT = 'SetLookBrick';
  final public const string HIDE_STMT = 'HideBrick';
  final public const string LED_ON_STMT = 'LedOnBrick';
  final public const string LED_OFF_STMT = 'LedOffBrick';
  final public const string CLEAR_GRAPHICS_EFFECT_STMT = 'ClearGraphicEffectBrick';
  final public const string CHANGE_BRIGHTNESS_BY_N_STMT = 'ChangeBrightnessByNBrick';
  final public const string SET_BRIGHTNESS_STMT = 'SetBrightnessBrick';
  final public const string CHANGE_TRANSPARENCY_BY_N_STMT = 'ChangeTransparencyByNBrick';
  final public const string SET_TRANSPARENCY_STMT = 'SetTransparencyBrick';
  final public const string SHOW_STMT = 'ShowBrick';
  final public const string NEXT_LOOK_STMT = 'NextLookBrick';
  final public const string SET_SIZE_TO_STMT = 'SetSizeToBrick';
  final public const string CHANGE_SIZE_BY_N_STMT = 'ChangeSizeByNBrick';
  final public const string POINT_IN_DIRECTION_STMT = 'PointInDirectionBrick';
  final public const string VIBRATION_STMT = 'VibrationBrick';
  final public const string COME_TO_FRONT_STMT = 'ComeToFrontBrick';
  final public const string GO_N_STEPS_BACK_STMT = 'GoNStepsBackBrick';
  final public const string GLIDE_TO_STMT = 'GlideToBrick';
  final public const string IF_ON_EDGE_BOUNCE_STMT = 'IfOnEdgeBounceBrick';
  final public const string CHANGE_X_BY_N_STMT = 'ChangeXByNBrick';
  final public const string PLACE_AT_STMT = 'PlaceAtBrick';
  final public const string SET_X_STMT = 'SetXBrick';
  final public const string SCRIPT_STMT = 'script';
  final public const string BRICK_STMT = 'brick';
  final public const string SCRIPT_LIST = 'scriptList';
  final public const string BRICK_LIST = 'brickList';
  final public const string LOOK_LIST = 'lookList';
  final public const string SOUND_LIST = 'soundList';
  final public const string START_SCRIPT = 'StartScript';
  final public const string BROADCAST_SCRIPT = 'BroadcastScript';
  final public const string WHEN_SCRIPT = 'WhenScript';
  final public const string NAME_ATTRIBUTE = 'name';
  final public const string TYPE_ATTRIBUTE = 'type';
  final public const string CATEGORY_ATTRIBUTE = 'category';
  final public const string REFERENCE_ATTRIBUTE = 'reference';
  final public const string USER_VARIABLE_STMT = 'userVariable';
  final public const string RIGHT_CHILD_STMT = 'rightChild';
  final public const string LEFT_CHILD_STMT = 'leftChild';
  final public const string USER_LIST_STMT = 'userList';
  final public const string VALUE_STMT = 'value';
  final public const string LOOK_STMT = 'look';
  final public const string SOUND_STMT = 'sound';
  final public const string POINTED_OBJECT_STMT = 'pointedObject';
  final public const string FILE_NAME_STMT = 'fileName';
  final public const string RECEIVED_MESSAGE_STMT = 'receivedMessage';
  final public const string BROADCAST_MESSAGE_STMT = 'broadcastMessage';
  final public const string FORMULA = 'formula';
  final public const string FORMULA_LIST = 'formulaList';
  final public const string SHOW_TEXT_STMT = 'ShowTextBrick';
  final public const string SHOW_TEXT_WITHOUT_ALIAS_STMT = 'org.catrobat.catroid.content.bricks.ShowTextBrick';
  final public const string HIDE_TEXT_STMT = 'HideTextBrick';
  final public const string HIDE_TEXT_WITHOUT_ALIAS_STMT = 'org.catrobat.catroid.content.bricks.HideTextBrick';
  final public const string USER_BRICKS = 'userBricks';
  final public const string IS_USER_BRICK = 'inUserBrick';
  final public const string IS_USER_SCRIPT = 'isUserScript';
  final public const string ACTION = 'action';
  final public const string USER_VARIABLE_NAME = 'userVariableName';

  private ?CodeObject $currentObject = null;

  public function createObject(\SimpleXMLElement $objectTree): ?CodeObject
  {
    $this->currentObject = new CodeObject();
    $this->currentObject->setName($objectTree[self::NAME_ATTRIBUTE]?->__toString());
    if (null == $this->currentObject->getName()) {
      return null;
    }

    $this->currentObject->addAllScripts($this->createStatement($objectTree, 1));

    return $this->currentObject;
  }

  public function createStatement(\SimpleXMLElement $xmlTree, ?int $spaces): array
  {
    $statements = [];
    if (0 == $xmlTree->count()) {
      return $statements;
    }
    $children = $xmlTree->children();

    foreach ($children as $statement) {
      $tmpStatement = null;

      switch ($statement->getName()) {
        case self::BRICK_LIST:
        case self::SCRIPT_LIST:
          $tmpStatement = new ScriptListStatement($this, $statement, $spaces);
          break;
        case self::SCRIPT_STMT:
          $tmpStatement = $this->generateScriptStatement($statement, $spaces);
          break;
        case self::SHOW_TEXT_WITHOUT_ALIAS_STMT:
        case self::HIDE_TEXT_WITHOUT_ALIAS_STMT:
        case self::BRICK_STMT:
          $tmpStatement = $this->generateBrickStatement($statement, $spaces);
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
          $tmpStatement = new FormulaStatement($this, $statement, 0, (string) $statement[self::CATEGORY_ATTRIBUTE]);
          break;
        case self::RIGHT_CHILD_STMT:
          $tmpStatement = new RightChildStatement($this, $statement, 0);
          break;
        case self::LEFT_CHILD_STMT:
          $tmpStatement = new LeftChildStatement($this, $statement, 0);
          break;
        case self::NAME_ATTRIBUTE:
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
        case self::LOOK_STMT:
          $tmpStatement = $this->generateLookStatement($statement, $spaces);
          break;
        case self::USER_LIST_STMT:
          $tmpStatement = $this->generateUserListStatement($statement, $spaces);
          break;
        case self::SOUND_STMT:
          $tmpStatement = $this->generateSoundStatement($statement, $spaces);
          break;
        case self::FILE_NAME_STMT:
          $tmpStatement = $this->generateFileNameStatement($statement, $spaces);
          break;
        case self::IS_USER_BRICK:
        case self::IS_USER_SCRIPT:
        case self::ACTION:
        case self::USER_VARIABLE_NAME:
        case self::USER_BRICKS:
          break;
        default:
          $tmpStatement = new UnknownStatement($this, $statement, $spaces);
          break;
      }

      if (null != $tmpStatement) {
        if ($tmpStatement instanceof UserVariableStatement) {
          array_unshift($statements, $tmpStatement);
        } else {
          $statements[] = $tmpStatement;
        }
        $spaces = $tmpStatement->getSpacesForNextBrick();
      }
    }

    return $statements;
  }

  public function generateBrickStatement(\SimpleXMLElement $statement, ?int $spaces): UnknownStatement|HideTextStatement|ShowTextStatement|WaitStatement|SetXStatement|PlaceAtStatement|ChangeXByNStatement|IfOnEdgeBounceStatement|GlideToStatement|GoNStepsBackStatement|ComeToFrontStatement|VibrationStatement|PointInDirectionStatement|ChangeSizeByNStatement|SetSizeToStatement|NextLookStatement|ShowStatement|SetTransparencyStatement|ChangeTransparencyByNStatement|SetBrightnessStatement|ChangeBrightnessByNStatement|ClearGraphicEffectStatement|LedOffStatement|LedOnStatement|HideStatement|SetLookStatement|ChangeVariableStatement|AddItemToUserListStatement|DeleteItemOfUserListStatement|InsertItemIntoUserListStatement|ReplaceItemInUserListStatement|SetVariableStatement|IfLogicEndStatement|IfLogicElseStatement|IfLogicBeginStatement|PointToStatement|MoveNStepsStatement|TurnRightStatement|TurnLeftStatement|ChangeYByNStatement|SetYStatement|BroadcastStatement|LoopEndlessStatement|ForeverStatement|LoopEndStatement|NoteStatement|RepeatStatement|BroadcastWaitStatement|ChangeVolumeByNStatement|SpeakStatement|SetVolumeToStatement|StopAllSoundsStatement|PlaySoundStatement|null
  {
    $stmt = null;
    $children = $statement;
    switch ((string) $statement[self::TYPE_ATTRIBUTE]) {
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

  private function generateScriptStatement(\SimpleXMLElement $statement, int $spaces): Statement
  {
    $children = $statement;

    return match ((string) $statement[self::TYPE_ATTRIBUTE]) {
      self::WHEN_SCRIPT => new TappedScriptStatement($this, $children, $spaces),
      self::START_SCRIPT => new WhenScriptStatement($this, $children, $spaces),
      self::BROADCAST_SCRIPT => new BroadcastScriptStatement($this, $children, $spaces),
      default => new UnknownStatement($this, $statement, $spaces),
    };
  }

  private function generateValueStatement(\SimpleXMLElement $statement, int $spaces): ValueStatement
  {
    $value = (string) $statement;
    $type = $this->getTypeOfValue($statement);

    return new ValueStatement($this, $statement, $spaces, $value, $type);
  }

  private function getTypeOfValue(\SimpleXMLElement $statement): ?string
  {
    $siblings = $statement->xpath('preceding-sibling::* | following-sibling::*');
    foreach ($siblings as $element) {
      if (self::TYPE_ATTRIBUTE == $element->getName()) {
        return (string) $element;
      }
    }

    return null;
  }

  private function generateUserVariableStatement(\SimpleXMLElement $statement, int $spaces): UserVariableStatement
  {
    $variableName = (string) $statement;
    if (null == $variableName) {
      $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];
      $variableName = (string) $statement->xpath($reference)[0];
    }

    $parent = 'parent::*';
    if ($this->isTypeExisting($statement, $parent, self::SHOW_TEXT_STMT)
      || $this->isTypeExisting($statement, $parent, self::HIDE_TEXT_STMT)
    ) {
      return new UserVariableStatement($this, $statement, $spaces, $variableName, true);
    }

    return new UserVariableStatement($this, $statement, $spaces, $variableName);
  }

  private function isTypeExisting(\SimpleXMLElement $statement, mixed $reference, mixed $type): bool
  {
    $elements = $statement->xpath($reference);
    foreach ($elements as $element) {
      if ((string) $element[self::TYPE_ATTRIBUTE] == $type) {
        return true;
      }
    }

    return false;
  }

  private function generateObjectStatement(\SimpleXMLElement $statement, int $spaces): ObjectStatement
  {
    $name = (string) $statement['name'];
    $factory = new StatementFactory();
    $this->currentObject->addCodeObject($factory->createObject($statement));

    return new ObjectStatement($this, $spaces, $name);
  }

  private function generateReceivedMessageStatement(\SimpleXMLElement $statement, int $spaces): ReceivedMessageStatement
  {
    $message = (string) $statement;

    return new ReceivedMessageStatement($this, $statement, $spaces, $message);
  }

  private function generateBroadcastMessageStatement(\SimpleXMLElement $statement, int $spaces): BroadcastMessageStatement
  {
    $message = (string) $statement;

    return new BroadcastMessageStatement($this, $statement, $spaces, $message);
  }

  private function generateLookStatement(\SimpleXMLElement $statement, int $spaces): LookStatement
  {
    $lookName = (string) $statement[self::NAME_ATTRIBUTE];
    if (null == $lookName) {
      $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];
      $look = $statement->xpath($reference)[0];
      $lookName = (string) $look[self::NAME_ATTRIBUTE];
    }

    return new LookStatement($this, $statement, $spaces, $lookName);
  }

  private function generateUserListStatement(\SimpleXMLElement $statement, int $spaces): UserListStatement
  {
    $userListName = $this->getNameWithReference($statement);

    return new UserListStatement($this, $statement, $spaces, $userListName);
  }

  private function getNameWithReference(\SimpleXMLElement $statement): string
  {
    $name = '';
    $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];

    if (null != $reference) {
      $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];
      $userListReference = $statement->xpath($reference)[0];
      foreach ($userListReference->children() as $child) {
        if (self::NAME_ATTRIBUTE == $child->getName()) {
          $name = (string) $child;
        }
      }
    }

    return $name;
  }

  private function generateSoundStatement(\SimpleXMLElement $statement, int $spaces): SoundStatement
  {
    $name = $this->getNameWithReference($statement);

    return new SoundStatement($this, $statement, $spaces, $name);
  }

  private function generateFileNameStatement(\SimpleXMLElement $statement, int $spaces): FileNameStatement
  {
    $message = (string) $statement;

    return new FileNameStatement($this, $statement, $spaces, $message);
  }
}
