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
use App\Catrobat\CatrobatCode\Statements\Statement;
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
use SimpleXMLElement;

class StatementFactory
{
  /**
   * @var string
   */
  const WAIT_STMT = 'WaitBrick';
  /**
   * @var string
   */
  const PLAY_SOUND_STMT = 'PlaySoundBrick';
  /**
   * @var string
   */
  const STOP_ALL_STMT = 'StopAllSoundsBrick';
  /**
   * @var string
   */
  const SET_VOLUME_TO_STMT = 'SetVolumeToBrick';
  /**
   * @var string
   */
  const SPEAK_STMT = 'SpeakBrick';
  /**
   * @var string
   */
  const CHANGE_VOLUME_BY_N_STMT = 'ChangeVolumeByNBrick';
  /**
   * @var string
   */
  const BROADCAST_WAIT_STMT = 'BroadcastWaitBrick';
  /**
   * @var string
   */
  const REPEAT_STMT = 'RepeatBrick';
  /**
   * @var string
   */
  const NOTE_STMT = 'NoteBrick';
  /**
   * @var string
   */
  const LOOP_END_STMT = 'LoopEndBrick';
  /**
   * @var string
   */
  const FOREVER_STMT = 'ForeverBrick';
  /**
   * @var string
   */
  const LOOP_ENDLESS_STMT = 'LoopEndlessBrick';
  /**
   * @var string
   */
  const BROADCAST_STMT = 'BroadcastBrick';
  /**
   * @var string
   */
  const SET_Y_STMT = 'SetYBrick';
  /**
   * @var string
   */
  const CHANGE_Y_BY_N_STMT = 'ChangeYByNBrick';
  /**
   * @var string
   */
  const TURN_LEFT_STMT = 'TurnLeftBrick';
  /**
   * @var string
   */
  const TURN_RIGHT_STMT = 'TurnRightBrick';
  /**
   * @var string
   */
  const MOVE_N_STEPS_STMT = 'MoveNStepsBrick';
  /**
   * @var string
   */
  const POINT_TO_STMT = 'PointToBrick';
  /**
   * @var string
   */
  const IF_LOGIC_BEGIN_STMT = 'IfLogicBeginBrick';
  /**
   * @var string
   */
  const IF_LOGIC_ELSE_STMT = 'IfLogicElseBrick';
  /**
   * @var string
   */
  const IF_LOGIC_END_STMT = 'IfLogicEndBrick';
  /**
   * @var string
   */
  const SET_VARIABLE_STMT = 'SetVariableBrick';
  /**
   * @var string
   */
  const REPLACE_ITEM_IN_USER_LIST_STMT = 'ReplaceItemInUserListBrick';
  /**
   * @var string
   */
  const INSERT_ITEM_IN_INTO_USER_LIST_STMT = 'InsertItemIntoUserListBrick';
  /**
   * @var string
   */
  const DELETE_ITEM_OF_USER_LIST_STMT = 'DeleteItemOfUserListBrick';
  /**
   * @var string
   */
  const ADD_ITEM_TO_USER_LIST_STMT = 'AddItemToUserListBrick';
  /**
   * @var string
   */
  const CHANGE_VARIABLE_STMT = 'ChangeVariableBrick';
  /**
   * @var string
   */
  const SET_LOOK_STMT = 'SetLookBrick';
  /**
   * @var string
   */
  const HIDE_STMT = 'HideBrick';
  /**
   * @var string
   */
  const LED_ON_STMT = 'LedOnBrick';
  /**
   * @var string
   */
  const LED_OFF_STMT = 'LedOffBrick';
  /**
   * @var string
   */
  const CLEAR_GRAPHICS_EFFECT_STMT = 'ClearGraphicEffectBrick';
  /**
   * @var string
   */
  const CHANGE_BRIGHTNESS_BY_N_STMT = 'ChangeBrightnessByNBrick';
  /**
   * @var string
   */
  const SET_BRIGHTNESS_STMT = 'SetBrightnessBrick';
  /**
   * @var string
   */
  const CHANGE_TRANSPARENCY_BY_N_STMT = 'ChangeTransparencyByNBrick';
  /**
   * @var string
   */
  const SET_TRANSPARENCY_STMT = 'SetTransparencyBrick';
  /**
   * @var string
   */
  const SHOW_STMT = 'ShowBrick';
  /**
   * @var string
   */
  const NEXT_LOOK_STMT = 'NextLookBrick';
  /**
   * @var string
   */
  const SET_SIZE_TO_STMT = 'SetSizeToBrick';
  /**
   * @var string
   */
  const CHANGE_SIZE_BY_N_STMT = 'ChangeSizeByNBrick';
  /**
   * @var string
   */
  const POINT_IN_DIRECTION_STMT = 'PointInDirectionBrick';
  /**
   * @var string
   */
  const VIBRATION_STMT = 'VibrationBrick';
  /**
   * @var string
   */
  const COME_TO_FRONT_STMT = 'ComeToFrontBrick';
  /**
   * @var string
   */
  const GO_N_STEPS_BACK_STMT = 'GoNStepsBackBrick';
  /**
   * @var string
   */
  const GLIDE_TO_STMT = 'GlideToBrick';
  /**
   * @var string
   */
  const IF_ON_EDGE_BOUNCE_STMT = 'IfOnEdgeBounceBrick';
  /**
   * @var string
   */
  const CHANGE_X_BY_N_STMT = 'ChangeXByNBrick';
  /**
   * @var string
   */
  const PLACE_AT_STMT = 'PlaceAtBrick';
  /**
   * @var string
   */
  const SET_X_STMT = 'SetXBrick';
  /**
   * @var string
   */
  const SCRIPT_STMT = 'script';
  /**
   * @var string
   */
  const BRICK_STMT = 'brick';
  /**
   * @var string
   */
  const SCRIPT_LIST = 'scriptList';
  /**
   * @var string
   */
  const BRICK_LIST = 'brickList';
  /**
   * @var string
   */
  const LOOK_LIST = 'lookList';
  /**
   * @var string
   */
  const SOUND_LIST = 'soundList';
  /**
   * @var string
   */
  const START_SCRIPT = 'StartScript';
  /**
   * @var string
   */
  const BROADCAST_SCRIPT = 'BroadcastScript';
  /**
   * @var string
   */
  const WHEN_SCRIPT = 'WhenScript';
  /**
   * @var string
   */
  const NAME_ATTRIBUTE = 'name';
  /**
   * @var string
   */
  const TYPE_ATTRIBUTE = 'type';
  /**
   * @var string
   */
  const CATEGORY_ATTRIBUTE = 'category';
  /**
   * @var string
   */
  const REFERENCE_ATTRIBUTE = 'reference';
  /**
   * @var string
   */
  const USER_VARIABLE_STMT = 'userVariable';
  /**
   * @var string
   */
  const RIGHT_CHILD_STMT = 'rightChild';
  /**
   * @var string
   */
  const LEFT_CHILD_STMT = 'leftChild';
  /**
   * @var string
   */
  const USER_LIST_STMT = 'userList';
  /**
   * @var string
   */
  const VALUE_STMT = 'value';
  /**
   * @var string
   */
  const LOOK_STMT = 'look';
  /**
   * @var string
   */
  const SOUND_STMT = 'sound';
  /**
   * @var string
   */
  const POINTED_OBJECT_STMT = 'pointedObject';
  /**
   * @var string
   */
  const FILE_NAME_STMT = 'fileName';
  /**
   * @var string
   */
  const RECEIVED_MESSAGE_STMT = 'receivedMessage';
  /**
   * @var string
   */
  const BROADCAST_MESSAGE_STMT = 'broadcastMessage';
  /**
   * @var string
   */
  const FORMULA = 'formula';
  /**
   * @var string
   */
  const FORMULA_LIST = 'formulaList';
  /**
   * @var string
   */
  const SHOW_TEXT_STMT = 'ShowTextBrick';
  /**
   * @var string
   */
  const SHOW_TEXT_WITHOUT_ALIAS_STMT = 'org.catrobat.catroid.content.bricks.ShowTextBrick';
  /**
   * @var string
   */
  const HIDE_TEXT_STMT = 'HideTextBrick';
  /**
   * @var string
   */
  const HIDE_TEXT_WITHOUT_ALIAS_STMT = 'org.catrobat.catroid.content.bricks.HideTextBrick';
  /**
   * @var string
   */
  const USER_BRICKS = 'userBricks';
  /**
   * @var string
   */
  const IS_USER_BRICK = 'inUserBrick';
  /**
   * @var string
   */
  const IS_USER_SCRIPT = 'isUserScript';
  /**
   * @var string
   */
  const ACTION = 'action';
  /**
   * @var string
   */
  const USER_VARIABLE_NAME = 'userVariableName';

  private ?CodeObject $currentObject = null;

  public function createObject(SimpleXMLElement $objectTree): ?CodeObject
  {
    $this->currentObject = new CodeObject();
    $this->currentObject->setName($objectTree[self::NAME_ATTRIBUTE]);
    if (null == $this->currentObject->getName())
    {
      return null;
    }

    $this->currentObject->addAllScripts($this->createStatement($objectTree, 1));

    return $this->currentObject;
  }

  public function createStatement(SimpleXMLElement $xmlTree, ?int $spaces): array
  {
    $statements = [];
    if (0 == $xmlTree->count())
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
          $tmpStatement = new FormulaStatement($this, $statement, 0, (string) $statement[self::CATEGORY_ATTRIBUTE]);
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

      if (null != $tmpStatement)
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
   * @return Statement|null
   */
  public function generateBrickStatement(SimpleXMLElement $statement, ?int $spaces)
  {
    $stmt = null;
    $children = $statement;
    switch ((string) $statement[self::TYPE_ATTRIBUTE])
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

  private function generateScriptStatement(SimpleXMLElement $statement, int $spaces): Statement
  {
    $children = $statement;
    switch ((string) $statement[self::TYPE_ATTRIBUTE])
    {
      case self::WHEN_SCRIPT:
        return new TappedScriptStatement($this, $children, $spaces);
      case self::START_SCRIPT:
        return new WhenScriptStatement($this, $children, $spaces);
      case self::BROADCAST_SCRIPT:
        return new BroadcastScriptStatement($this, $children, $spaces);
      default:
        return new UnknownStatement($this, $statement, $spaces);
    }
  }

  private function generateValueStatement(SimpleXMLElement $statement, int $spaces): ValueStatement
  {
    $value = (string) $statement;
    $type = $this->getTypeOfValue($statement);

    return new ValueStatement($this, $statement, $spaces, $value, $type);
  }

  private function getTypeOfValue(SimpleXMLElement $statement): ?string
  {
    $siblings = $statement->xpath('preceding-sibling::* | following-sibling::*');
    foreach ($siblings as $element)
    {
      if (self::TYPE_ATTRIBUTE == $element->getName())
      {
        return (string) $element;
      }
    }

    return null;
  }

  private function generateUserVariableStatement(SimpleXMLElement $statement, int $spaces): UserVariableStatement
  {
    $variableName = (string) $statement;
    if (null == $variableName)
    {
      $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];
      $variableName = (string) ($statement->xpath($reference)[0]);
    }

    $parent = 'parent::*';
    if ($this->isTypeExisting($statement, $parent, self::SHOW_TEXT_STMT) ||
      $this->isTypeExisting($statement, $parent, self::HIDE_TEXT_STMT)
    ) {
      return new UserVariableStatement($this, $statement, $spaces, $variableName, true);
    }

    return new UserVariableStatement($this, $statement, $spaces, $variableName);
  }

  /**
   * @param mixed $reference
   * @param mixed $type
   */
  private function isTypeExisting(SimpleXMLElement $statement, $reference, $type): bool
  {
    $elements = $statement->xpath($reference);
    foreach ($elements as $element)
    {
      if ((string) $element[self::TYPE_ATTRIBUTE] == $type)
      {
        return true;
      }
    }

    return false;
  }

  private function generateObjectStatement(SimpleXMLElement $statement, int $spaces): ObjectStatement
  {
    $name = $statement['name'];
    $factory = new StatementFactory();
    $this->currentObject->addCodeObject($factory->createObject($statement));

    return new ObjectStatement($this, $spaces, $name);
  }

  private function generateReceivedMessageStatement(SimpleXMLElement $statement, int $spaces): ReceivedMessageStatement
  {
    $message = (string) $statement;

    return new ReceivedMessageStatement($this, $statement, $spaces, $message);
  }

  private function generateBroadcastMessageStatement(SimpleXMLElement $statement, int $spaces): BroadcastMessageStatement
  {
    $message = (string) $statement;

    return new BroadcastMessageStatement($this, $statement, $spaces, $message);
  }

  private function generateLookStatement(SimpleXMLElement $statement, int $spaces): LookStatement
  {
    $lookName = (string) $statement[self::NAME_ATTRIBUTE];
    if (null == $lookName)
    {
      $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];
      $look = $statement->xpath($reference)[0];
      $lookName = (string) $look[self::NAME_ATTRIBUTE];
    }

    return new LookStatement($this, $statement, $spaces, $lookName);
  }

  private function generateUserListStatement(SimpleXMLElement $statement, int $spaces): UserListStatement
  {
    $userListName = $this->getNameWithReference($statement);

    return new UserListStatement($this, $statement, $spaces, $userListName);
  }

  private function getNameWithReference(SimpleXMLElement $statement): string
  {
    $name = '';
    $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];

    if (null != $reference)
    {
      $reference = (string) $statement[self::REFERENCE_ATTRIBUTE];
      $userListReference = $statement->xpath($reference)[0];
      foreach ($userListReference->children() as $child)
      {
        if (self::NAME_ATTRIBUTE == $child->getName())
        {
          $name = (string) $child;
        }
      }
    }

    return $name;
  }

  private function generateSoundStatement(SimpleXMLElement $statement, int $spaces): SoundStatement
  {
    $name = $this->getNameWithReference($statement);

    return new SoundStatement($this, $statement, $spaces, $name);
  }

  private function generateFileNameStatement(SimpleXMLElement $statement, int $spaces): FileNameStatement
  {
    $message = (string) $statement;

    return new FileNameStatement($this, $statement, $spaces, $message);
  }
}
