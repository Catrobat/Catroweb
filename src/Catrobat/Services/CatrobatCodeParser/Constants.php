<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

/**
 * Class Constants
 * @package App\Catrobat\Services\CatrobatCodeParser
 */
class Constants
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
  const WHEN_GAME_PAD_BUTTON_SCRIPT = 'WhenGamepadButtonScript';
  const WHEN_CLONED_SCRIPT = 'WhenClonedScript';
  const UNKNOWN_SCRIPT = "UnknownScript";

  // Bricks
  const SET_LOOK_BY_INDEX_BRICK = 'SetLookByIndexBrick';
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
  const LEGO_EV3_MOTOR_STOP_BRICK = 'LegoEv3MotorStopBrick';
  const LEGO_EV3_MOTOR_MOVE_BRICK = 'LegoEv3MotorMoveBrick';
  const LEGO_EV3_MOTOR_PLAY_TONE_BRICK = 'LegoEv3PlayToneBrick';
  const LEGO_EV3_MOTOR_TURN_ANGLE_BRICK = 'LegoEv3MotorTurnAngleBrick';
  const LEGO_EV3_SET_LED_BRICK = 'LegoEv3SetLedBrick';
  const ASK_SPEECH_BRICK = 'AskSpeechBrick';

  // Ar Drone Bricks
  const AR_DRONE_TAKE_OFF_LAND_BRICK = 'DroneTakeOffLandBrick';
  const AR_DRONE_EMERGENCY_BRICK = 'DroneEmergencyBrick';
  const AR_DRONE_MOVE_UP_BRICK = 'DroneMoveUpBrick';
  const AR_DRONE_MOVE_DOWN_BRICK = 'DroneMoveDownBrick';
  const AR_DRONE_MOVE_LEFT_BRICK = 'DroneMoveLeftBrick';
  const AR_DRONE_MOVE_RIGHT_BRICK = 'DroneMoveRightBrick';
  const AR_DRONE_MOVE_FOWARD_BRICK = 'DroneMoveForwardBrick';
  const AR_DRONE_MOVE_BACKWARD_BRICK = 'DroneMoveBackwardBrick';
  const AR_DRONE_TURN_LEFT_BRICK = 'DroneTurnLeftBrick';
  const AR_DRONE_TURN_RIGHT_BRICK = 'DroneTurnRightBrick';
  const AR_DRONE_SWITCH_CAMERA_BRICK = 'DroneSwitchCameraBrick';

  // Jump Sumo
  const JUMP_SUMO_MOVE_FOWARD_BRICK = 'JumpingSumoMoveForwardBrick';
  const JUMP_SUMO_MOVE_BACKWARD_BRICK = 'JumpingSumoMoveBackwardBrick';
  const JUMP_SUMO_ANIMATIONS_BRICK = 'JumpingSumoAnimationsBrick';
  const JUMP_SUMO_SOUND_BRICK = 'JumpingSumoSoundBrick';
  const JUMP_SUMO_NO_SOUND_BRICK = 'JumpingSumoNoSoundBrick';
  const JUMP_SUMO_JUMP_LONG_BRICK = 'JumpingSumoJumpLongBrick';
  const JUMP_SUMO_JUMP_HIGH_BRICK = 'JumpingSumoJumpHighBrick';
  const JUMP_SUMO_ROTATE_LEFT_BRICK = 'JumpingSumoRotateLeftBrick';
  const JUMP_SUMO_ROTATE_RIGHT_BRICK = 'JumpingSumoRotateRightBrick';
  const JUMP_SUMO_TURN_BRICK = 'JumpingSumoTurnBrick';
  const JUMP_SUMO_TAKING_PICTURE_BRICK = 'JumpingSumoTakingPictureBrick';

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
  const LEGO_EV3_BRICK_IMG = '1h_brick_yellow.png';
  const UNKNOWN_SCRIPT_IMG = '1h_when_grey.png';
  const UNKNOWN_BRICK_IMG = '1h_brick_grey.png';
  const AR_DRONE_BRICK_IMG = '1h_brick_yellow.png';
  const JUMPING_SUMO_BRICK_IMG = '1h_brick_blue.png';

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
  const ASK_SPEECH_QUESTION_FORMULA = 'ASK_SPEECH_QUESTION';
  const STRING_FORMULA = 'STRING';
  const PEN_SIZE_FORMULA = 'PEN_SIZE';
  const PEN_COLOR_RED_FORMULA = 'PHIRO_LIGHT_RED';
  const PEN_COLOR_BLUE_FORMULA = 'PHIRO_LIGHT_BLUE';
  const PEN_COLOR_GREEN_FORMULA = 'PHIRO_LIGHT_GREEN';
  const PEN_COLOR_RED_NEW_FORMULA = 'PEN_COLOR_RED';
  const PEN_COLOR_BLUE_NEW_FORMULA = 'PEN_COLOR_BLUE';
  const PEN_COLOR_GREEN_NEW_FORMULA = 'PEN_COLOR_GREEN';
  const LEGO_EV3_POWER_FORMULA = 'LEGO_EV3_POWER';
  const LEGO_EV3_PERIOD_IN_SECONDS_FORMULA = 'LEGO_EV3_PERIOD_IN_SECONDS';
  const LEGO_EV3_DURATION_IN_SECONDS_FORMULA = 'LEGO_EV3_DURATION_IN_SECONDS';
  const LEGO_EV3_VOLUME_FORMULA = 'LEGO_EV3_VOLUME';
  const LEGO_EV3_FREQUENCY_FORMULA = 'LEGO_EV3_FREQUENCY';
  const LEGO_EV3_DEGREES_FORMULA = 'LEGO_EV3_DEGREES';

  // AR DRONE FORMULA
  const AR_DRONE_TIME_TO_FLY_IN_SECONDS = 'DRONE_TIME_TO_FLY_IN_SECONDS';
  const AR_DRONE_POWER_IN_PERCENT = 'DRONE_POWER_IN_PERCENT';

  // JUMP SUMO FORMULA
  const JUMP_SUMO_SPEED = 'JUMPING_SUMO_SPEED';
  const JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS = 'JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS';
  const JUMPING_SUMO_ROTATE = 'JUMPING_SUMO_ROTATE';

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
  const LOOK_INDEX = "LOOK_INDEX";
}