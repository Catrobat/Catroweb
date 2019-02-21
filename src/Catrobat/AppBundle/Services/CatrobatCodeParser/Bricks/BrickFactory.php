<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class BrickFactory
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class BrickFactory
{
  /**
   * @param \SimpleXMLElement $brick_xml_properties
   *
   * @return BroadcastBrick|BroadcastWaitBrick|ForeverBrick|NoteBrick|WaitBrick|WhenBrick|null
   */
  public static function generate(\SimpleXMLElement $brick_xml_properties)
  {
    $generated_brick = null;

    switch ((string)$brick_xml_properties[Constants::TYPE_ATTRIBUTE])
    {
      // EVENT Bricks
      case Constants::BROADCAST_BRICK:
        $generated_brick = new BroadcastBrick($brick_xml_properties);
        break;
      case Constants::BROADCAST_WAIT_BRICK:
        $generated_brick = new BroadcastWaitBrick($brick_xml_properties);
        break;
      case Constants::WHEN_BRICK:
        $generated_brick = new WhenBrick($brick_xml_properties);
        break;

      // CONTROL Bricks
      case Constants::WAIT_BRICK:
        $generated_brick = new WaitBrick($brick_xml_properties);
        break;
      case Constants::NOTE_BRICK:
        $generated_brick = new NoteBrick($brick_xml_properties);
        break;
      case Constants::FOREVER_BRICK:
        $generated_brick = new ForeverBrick($brick_xml_properties);
        break;
      case Constants::LOOP_ENDLESS_BRICK:
        $generated_brick = new LoopEndlessBrick($brick_xml_properties);
        break;
      case Constants::IF_THEN_BRICK:
      case Constants::IF_BRICK:
        $generated_brick = new IfBrick($brick_xml_properties);
        break;
      case Constants::ELSE_BRICK:
        $generated_brick = new ElseBrick($brick_xml_properties);
        break;
      case Constants::ENDIF_THEN_BRICK:
      case Constants::ENDIF_BRICK:
        $generated_brick = new EndIfBrick($brick_xml_properties);
        break;
      case Constants::REPEAT_BRICK:
        $generated_brick = new RepeatBrick($brick_xml_properties);
        break;
      case Constants::LOOP_END_BRICK:
        $generated_brick = new LoopEndBrick($brick_xml_properties);
        break;
      case Constants::WAIT_UNTIL_BRICK:
        $generated_brick = new WaitUntilBrick($brick_xml_properties);
        break;
      case Constants::REPEAT_UNTIL_BRICK:
        $generated_brick = new RepeatUntilBrick($brick_xml_properties);
        break;
      case Constants::STOP_SCRIPT_BRICK:
        $generated_brick = new StopScriptBrick($brick_xml_properties);
        break;
      case Constants::SCENE_START_BRICK:
        $generated_brick = new SceneStartBrick($brick_xml_properties);
        break;
      case Constants::CLONE_BRICK:
        $generated_brick = new CloneBrick($brick_xml_properties);
        break;
      case Constants::DELETE_THIS_CLONE_BRICK:
        $generated_brick = new DeleteThisCloneBrick($brick_xml_properties);
        break;
      case Constants::CONTINUE_SCENE_BRICK:
        $generated_brick = new ContinueSceneBrick($brick_xml_properties);
        break;

      // MOTION Bricks
      case Constants::PLACE_AT_BRICK:
        $generated_brick = new PlaceAtBrick($brick_xml_properties);
        break;
      case Constants::SET_X_BRICK:
        $generated_brick = new SetXBrick($brick_xml_properties);
        break;
      case Constants::SET_Y_BRICK:
        $generated_brick = new SetYBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_X_BY_N_BRICK:
        $generated_brick = new ChangeXByNBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_Y_BY_N_BRICK:
        $generated_brick = new ChangeYByNBrick($brick_xml_properties);
        break;
      case Constants::IF_ON_EDGE_BOUNCE_BRICK:
        $generated_brick = new IfOnEdgeBounceBrick($brick_xml_properties);
        break;
      case Constants::MOVE_N_STEPS_BRICK:
        $generated_brick = new MoveNStepsBrick($brick_xml_properties);
        break;
      case Constants::TURN_LEFT_BRICK:
        $generated_brick = new TurnLeftBrick($brick_xml_properties);
        break;
      case Constants::TURN_RIGHT_BRICK:
        $generated_brick = new TurnRightBrick($brick_xml_properties);
        break;
      case Constants::POINT_IN_DIRECTION_BRICK:
        $generated_brick = new PointInDirectionBrick($brick_xml_properties);
        break;
      case Constants::GLIDE_TO_BRICK:
        $generated_brick = new GlideToBrick($brick_xml_properties);
        break;
      case Constants::GO_N_STEPS_BACK_BRICK:
        $generated_brick = new GoNStepsBackBrick($brick_xml_properties);
        break;
      case Constants::COME_TO_FRONT_BRICK:
        $generated_brick = new ComeToFrontBrick($brick_xml_properties);
        break;
      case Constants::VIBRATION_BRICK:
        $generated_brick = new VibrationBrick($brick_xml_properties);
        break;
      case Constants::SET_PHYSICS_OBJECT_TYPE_BRICK:
        $generated_brick = new SetPhysicsObjectTypeBrick($brick_xml_properties);
        break;
      case Constants::SET_VELOCITY_BRICK:
        $generated_brick = new SetVelocityBrick($brick_xml_properties);
        break;
      case Constants::TURN_LEFT_SPEED_BRICK:
        $generated_brick = new TurnLeftSpeedBrick($brick_xml_properties);
        break;
      case Constants::TURN_RIGHT_SPEED_BRICK:
        $generated_brick = new TurnRightSpeedBrick($brick_xml_properties);
        break;
      case Constants::SET_GRAVITY_BRICK:
        $generated_brick = new SetGravityBrick($brick_xml_properties);
        break;
      case Constants::SET_MASS_BRICK:
        $generated_brick = new SetMassBrick($brick_xml_properties);
        break;
      case Constants::SET_BOUNCE_BRICK:
        $generated_brick = new SetBounceBrick($brick_xml_properties);
        break;
      case Constants::SET_FRICTION_BRICK:
        $generated_brick = new SetFrictionBrick($brick_xml_properties);
        break;
      case Constants::POINT_TO_BRICK:
        $generated_brick = new PointToBrick($brick_xml_properties);
        break;
      case Constants::GO_TO_BRICK:
        $generated_brick = new GoToBrick($brick_xml_properties);
        break;
      case Constants::SET_ROTATION_STYLE_BRICK:
        $generated_brick = new SetRotationStyleBrick($brick_xml_properties);
        break;

      // SOUND Bricks
      case Constants::PLAY_SOUND_BRICK:
        $generated_brick = new PlaySoundBrick($brick_xml_properties);
        break;
      case Constants::STOP_ALL_SOUNDS_BRICK:
        $generated_brick = new StopAllSoundsBrick($brick_xml_properties);
        break;
      case Constants::SET_VOLUME_TO_BRICK:
        $generated_brick = new SetVolumeToBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_VOLUME_BY_N_BRICK:
        $generated_brick = new ChangeVolumeByNBrick($brick_xml_properties);
        break;
      case Constants::SPEAK_BRICK:
        $generated_brick = new SpeakBrick($brick_xml_properties);
        break;
      case Constants::PLAY_SOUND_WAIT_BRICK:
        $generated_brick = new PlaySoundWaitBrick($brick_xml_properties);
        break;
      case Constants::SPEAK_WAIT_BRICK:
        $generated_brick = new SpeakWaitBrick($brick_xml_properties);
        break;
      case Constants::ASK_SPEECH_BRICK:
        $generated_brick = new AskSpeechBrick($brick_xml_properties);
        break;

      // LOOK Bricks
      case Constants::SET_LOOK_BRICK:
        $generated_brick = new SetLookBrick($brick_xml_properties);
        break;
      case Constants::NEXT_LOOK_BRICK:
        $generated_brick = new NextLookBrick($brick_xml_properties);
        break;
      case Constants::CAMERA_BRICK:
        $generated_brick = new CameraBrick($brick_xml_properties);
        break;
      case Constants::CHOOSE_CAMERA_BRICK:
        $generated_brick = new ChooseCameraBrick($brick_xml_properties);
        break;
      case Constants::SET_SIZE_TO_BRICK:
        $generated_brick = new SetSizeToBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_SIZE_BY_N_BRICK:
        $generated_brick = new ChangeSizeByNBrick($brick_xml_properties);
        break;
      case Constants::HIDE_BRICK:
        $generated_brick = new HideBrick($brick_xml_properties);
        break;
      case Constants::SHOW_BRICK:
        $generated_brick = new ShowBrick($brick_xml_properties);
        break;
      case Constants::SET_TRANSPARENCY_BRICK:
        $generated_brick = new SetTransparencyBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_TRANSPARENCY_BY_N_BRICK:
        $generated_brick = new ChangeTransparencyByNBrick($brick_xml_properties);
        break;
      case Constants::SET_BRIGHTNESS_BRICK:
        $generated_brick = new SetBrightnessBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_BRIGHTNESS_BY_N_BRICK:
        $generated_brick = new ChangeBrightnessByNBrick($brick_xml_properties);
        break;
      case Constants::SET_COLOR_BRICK:
        $generated_brick = new SetColorBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_COLOR_BY_N_BRICK:
        $generated_brick = new ChangeColorByNBrick($brick_xml_properties);
        break;
      case Constants::CLEAR_GRAPHIC_EFFECT_BRICK:
        $generated_brick = new ClearGraphicEffectBrick($brick_xml_properties);
        break;
      case Constants::FLASH_BRICK:
        $generated_brick = new FlashBrick($brick_xml_properties);
        break;
      case Constants::PREV_LOOK_BRICK:
        $generated_brick = new PrevLookBrick($brick_xml_properties);
        break;
      case Constants::ASK_BRICK:
        $generated_brick = new AskBrick($brick_xml_properties);
        break;
      case Constants::SAY_BUBBLE_BRICK:
        $generated_brick = new SayBubbleBrick($brick_xml_properties);
        break;
      case Constants::SAY_FOR_BUBBLE_BRICK:
        $generated_brick = new SayForBubbleBrick($brick_xml_properties);
        break;
      case Constants::THINK_BUBBLE_BRICK:
        $generated_brick = new ThinkBubbleBrick($brick_xml_properties);
        break;
      case Constants::THINK_FOR_BUBBLE_BRICK:
        $generated_brick = new ThinkForBubbleBrick($brick_xml_properties);
        break;
      case Constants::SET_BACKGROUND_BRICK:
        $generated_brick = new SetBackgroundBrick($brick_xml_properties);
        break;
      case Constants::SET_BACKGROUND_WAIT_BRICK:
        $generated_brick = new SetBackgroundWaitBrick($brick_xml_properties);
        break;
      case Constants::SET_LOOK_BY_INDEX_BRICK:
        $generated_brick = new SetLookByIndexBrick($brick_xml_properties);
        break;

      // DATA Bricks
      case Constants::SET_VARIABLE_BRICK:
        $generated_brick = new SetVariableBrick($brick_xml_properties);
        break;
      case Constants::CHANGE_VARIABLE_BRICK:
        $generated_brick = new ChangeVariableBrick($brick_xml_properties);
        break;
      case Constants::SHOW_TEXT_BRICK:
        $generated_brick = new ShowTextBrick($brick_xml_properties);
        break;
      case Constants::HIDE_TEXT_BRICK:
        $generated_brick = new HideTextBrick($brick_xml_properties);
        break;
      case Constants::ADD_ITEM_LIST_BRICK:
        $generated_brick = new AddItemToUserListBrick($brick_xml_properties);
        break;
      case Constants::DELETE_ITEM_LIST_BRICK:
        $generated_brick = new DeleteItemOfUserListBrick($brick_xml_properties);
        break;
      case Constants::INSERT_ITEM_LIST_BRICK:
        $generated_brick = new InsertItemIntoUserListBrick($brick_xml_properties);
        break;
      case Constants::REPLACE_ITEM_LIST_BRICK:
        $generated_brick = new ReplaceItemInUserListBrick($brick_xml_properties);
        break;

      // PEN Bricks
      case Constants::PEN_DOWN_BRICK:
        $generated_brick = new PenDownBrick($brick_xml_properties);
        break;
      case Constants::PEN_UP_BRICK:
        $generated_brick = new PenUpBrick($brick_xml_properties);
        break;
      case Constants::SET_PEN_SIZE_BRICK:
        $generated_brick = new SetPenSizeBrick($brick_xml_properties);
        break;
      case Constants::SET_PEN_COLOR_BRICK:
        $generated_brick = new SetPenColorBrick($brick_xml_properties);
        break;
      case Constants::STAMP_BRICK:
        $generated_brick = new StampBrick($brick_xml_properties);
        break;
      case Constants::CLEAR_BACKGROUND_BRICK:
        $generated_brick = new ClearBackgroundBrick($brick_xml_properties);
        break;

      // LEGO EV3 Bricks
      case Constants::LEGO_EV3_MOTOR_STOP_BRICK:
        $generated_brick = new LegoEV3MotorStopBrick($brick_xml_properties);
        break;
      case Constants::LEGO_EV3_MOTOR_MOVE_BRICK:
        $generated_brick = new LegoEV3MotorMoveBrick($brick_xml_properties);
        break;
      case Constants::LEGO_EV3_MOTOR_PLAY_TONE_BRICK:
        $generated_brick = new LegoEV3MotorPlayToneBrick($brick_xml_properties);
        break;
      case Constants::LEGO_EV3_MOTOR_TURN_ANGLE_BRICK:
        $generated_brick = new LegoEV3MotorTurnAngleBrick($brick_xml_properties);
        break;
      case Constants::LEGO_EV3_SET_LED_BRICK:
        $generated_brick = new LegoEV3SetLedBrick($brick_xml_properties);
        break;

      // AR DRONE BRICKS
      case Constants::AR_DRONE_EMERGENCY_BRICK:
        $generated_brick = new DroneEmergencyBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_MOVE_BACKWARD_BRICK:
        $generated_brick = new DroneMoveBackwardBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_MOVE_DOWN_BRICK:
        $generated_brick = new DroneMoveDownBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_MOVE_FOWARD_BRICK:
        $generated_brick = new DroneMoveFowardBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_MOVE_LEFT_BRICK:
        $generated_brick = new DroneMoveLeftBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_MOVE_RIGHT_BRICK:
        $generated_brick = new DroneMoveRightBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_MOVE_UP_BRICK:
        $generated_brick = new DroneMoveUpBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_SWITCH_CAMERA_BRICK:
        $generated_brick = new DroneSwitchCameraBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_TAKE_OFF_LAND_BRICK:
        $generated_brick = new DroneTakeOffLandBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_TURN_LEFT_BRICK:
        $generated_brick = new DroneTurnLeftBrick($brick_xml_properties);
        break;

      case Constants::AR_DRONE_TURN_RIGHT_BRICK:
        $generated_brick = new DroneTurnRightBrick($brick_xml_properties);
        break;

      // Jumping Sumo
      case Constants::JUMP_SUMO_ANIMATIONS_BRICK:
        $generated_brick = new JumpingSumoAnimationBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_JUMP_HIGH_BRICK:
        $generated_brick = new JumpingSumoJumpHighBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_JUMP_LONG_BRICK:
        $generated_brick = new JumpingSumoJumpLongBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_MOVE_BACKWARD_BRICK:
        $generated_brick = new JumpingSumoMoveBackwardBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_MOVE_FOWARD_BRICK:
        $generated_brick = new JumpingSumoMoveFowardBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_NO_SOUND_BRICK:
        $generated_brick = new JumpingSumoNoSoundBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_ROTATE_LEFT_BRICK:
        $generated_brick = new JumpingSumoRotateLeftBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_ROTATE_RIGHT_BRICK:
        $generated_brick = new JumpingSumoRotateRightBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_SOUND_BRICK:
        $generated_brick = new JumpingSumoSoundBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_TAKING_PICTURE_BRICK:
        $generated_brick = new JumpingSumoTakingPictureBrick($brick_xml_properties);
        break;

      case Constants::JUMP_SUMO_TURN_BRICK:
        $generated_brick = new JumpingSumoTurnBrick($brick_xml_properties);
        break;

      // OTHER Bricks
      default:
        $generated_brick = new UnknownBrick($brick_xml_properties);
        break;
    }

    return $generated_brick;
  }
}