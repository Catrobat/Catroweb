<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

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

  // Bricks & Scripts
  const UNKNOWN_SCRIPT = 'UnknownScript';
  const UNKNOWN_BRICK = 'UnknownBrick';

  // Motion
  const PLACE_AT_BRICK = 'PlaceAtBrick';
  const SET_X_BRICK = 'SetXBrick';
  const SET_Y_BRICK = 'SetYBrick';
  const GO_TO_BRICK = 'GoToBrick';
  const CHANGE_X_BY_N_BRICK = 'ChangeXByNBrick';
  const CHANGE_Y_BY_N_BRICK = 'ChangeYByNBrick';
  const IF_ON_EDGE_BOUNCE_BRICK = 'IfOnEdgeBounceBrick';
  const MOVE_N_STEPS_BRICK = 'MoveNStepsBrick';
  const TURN_LEFT_BRICK = 'TurnLeftBrick';
  const TURN_RIGHT_BRICK = 'TurnRightBrick';
  const POINT_IN_DIRECTION_BRICK = 'PointInDirectionBrick';
  const POINT_TO_BRICK = 'PointToBrick';
  const SET_ROTATION_STYLE_BRICK = 'SetRotationStyleBrick';
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
  const SET_TEXT_BRICK = 'SetTextBrick';

  // Event
  const START_SCRIPT = 'StartScript';
  const WHEN_STARTED_BRICK = 'WhenStartedBrick';
  const WHEN_SCRIPT = 'WhenScript';
  const WHEN_BG_CHANGE_SCRIPT = 'WhenBackgroundChangesScript';
  const WHEN_BG_CHANGE_BRICK = 'WhenBackgroundChangesBrick';
  const WHEN_CLONED_SCRIPT = 'WhenClonedScript';
  const WHEN_CLONED_BRICK = 'WhenClonedBrick';
  const WHEN_TOUCH_SCRIPT = 'WhenTouchDownScript';
  const WHEN_TOUCH_BRICK = 'WhenTouchDownBrick';
  const WHEN_CONDITION_SCRIPT = 'WhenConditionScript';
  const WHEN_CONDITION_BRICK = 'WhenConditionBrick';
  const BROADCAST_SCRIPT = 'BroadcastScript';
  const BROADCAST_BRICK = 'BroadcastBrick';
  const BROADCAST_WAIT_BRICK = 'BroadcastWaitBrick';
  const BROADCAST_RECEIVER_BRICK = 'BroadcastReceiverBrick';
  const WHEN_BRICK = 'WhenBrick';
  const WHEN_BOUNCE_OFF_SCRIPT = 'WhenBounceOffScript';
  const WHEN_BOUNCE_OFF_BRICK = 'WhenBounceOffBrick';

  // --- Looks ---
  const SET_LOOK_BRICK = 'SetLookBrick';
  const SET_LOOK_BY_INDEX_BRICK = 'SetLookByIndexBrick';
  const NEXT_LOOK_BRICK = 'NextLookBrick';
  const PREV_LOOK_BRICK = 'PreviousLookBrick';
  const SET_SIZE_TO_BRICK = 'SetSizeToBrick';
  const CHANGE_SIZE_BY_N_BRICK = 'ChangeSizeByNBrick';
  const HIDE_BRICK = 'HideBrick';
  const SHOW_BRICK = 'ShowBrick';
  const ASK_BRICK = 'AskBrick';
  const SAY_BUBBLE_BRICK = 'SayBubbleBrick';
  const SAY_FOR_BUBBLE_BRICK = 'SayForBubbleBrick';
  const THINK_BUBBLE_BRICK = 'ThinkBubbleBrick';
  const THINK_FOR_BUBBLE_BRICK = 'ThinkForBubbleBrick';
  const SET_TRANSPARENCY_BRICK = 'SetTransparencyBrick';
  const CHANGE_TRANSPARENCY_BY_N_BRICK = 'ChangeTransparencyByNBrick';
  const SET_BRIGHTNESS_BRICK = 'SetBrightnessBrick';
  const CHANGE_BRIGHTNESS_BY_N_BRICK = 'ChangeBrightnessByNBrick';
  const SET_COLOR_BRICK = 'SetColorBrick';
  const CHANGE_COLOR_BY_N_BRICK = 'ChangeColorByNBrick';
  const CLEAR_GRAPHIC_EFFECT_BRICK = 'ClearGraphicEffectBrick';
  const SET_BACKGROUND_BRICK = 'SetBackgroundBrick';
  const SET_BACKGROUND_BY_INDEX_BRICK = 'SetBackgroundByIndexBrick';
  const SET_BACKGROUND_WAIT_BRICK = 'SetBackgroundAndWaitBrick';
  const SET_BACKGROUND_BY_INDEX_AND_WAIT_BRICK = 'SetBackgroundByIndexAndWaitBrick';
  const CAMERA_BRICK = 'CameraBrick';
  const CHOOSE_CAMERA_BRICK = 'ChooseCameraBrick';
  const FLASH_BRICK = 'FlashBrick';

  // --- Pen ---
  const PEN_DOWN_BRICK = 'PenDownBrick';
  const PEN_UP_BRICK = 'PenUpBrick';
  const SET_PEN_SIZE_BRICK = 'SetPenSizeBrick';
  const SET_PEN_COLOR_BRICK = 'SetPenColorBrick';
  const STAMP_BRICK = 'StampBrick';
  const CLEAR_BACKGROUND_BRICK = 'ClearBackgroundBrick';

  // --- Sound ---
  const PLAY_SOUND_BRICK = 'PlaySoundBrick';
  const PLAY_SOUND_WAIT_BRICK = 'PlaySoundAndWaitBrick';
  const STOP_ALL_SOUNDS_BRICK = 'StopAllSoundsBrick';
  const SET_VOLUME_TO_BRICK = 'SetVolumeToBrick';
  const CHANGE_VOLUME_BY_N_BRICK = 'ChangeVolumeByNBrick';
  const SPEAK_BRICK = 'SpeakBrick';
  const SPEAK_WAIT_BRICK = 'SpeakAndWaitBrick';
  const ASK_SPEECH_BRICK = 'AskSpeechBrick';

  // --- Control ---
  const WAIT_BRICK = 'WaitBrick';
  const NOTE_BRICK = 'NoteBrick';
  const FOREVER_BRICK = 'ForeverBrick';
  const IF_BRICK = 'IfLogicBeginBrick';
  const IF_THEN_BRICK = 'IfThenLogicBeginBrick';
  const WAIT_UNTIL_BRICK = 'WaitUntilBrick';
  const REPEAT_BRICK = 'RepeatBrick';
  const REPEAT_UNTIL_BRICK = 'RepeatUntilBrick';
  const CONTINUE_SCENE_BRICK = 'SceneTransitionBrick';
  const SCENE_START_BRICK = 'SceneStartBrick';
  const STOP_SCRIPT_BRICK = 'StopScriptBrick';
  const CLONE_BRICK = 'CloneBrick';
  const DELETE_THIS_CLONE_BRICK = 'DeleteThisCloneBrick';
  const WEB_REQUEST_BRICK = 'WebRequestBrick';

  // auto generated control blocks
  const ELSE_BRICK = 'IfLogicElseBrick';
  const ENDIF_BRICK = 'IfLogicEndBrick';
  const ENDIF_THEN_BRICK = 'IfThenLogicEndBrick';
  const LOOP_END_BRICK = 'LoopEndBrick';

  // --- Data ---
  const SET_VARIABLE_BRICK = 'SetVariableBrick';
  const CHANGE_VARIABLE_BRICK = 'ChangeVariableBrick';
  const SHOW_TEXT_BRICK = 'ShowTextBrick';
  const SHOW_TEXT_COLOR_SIZE_ALIGNMENT_BRICK = 'ShowTextColorSizeAlignmentBrick';
  const HIDE_TEXT_BRICK = 'HideTextBrick';
  const USER_VARIABLE_BRICK = 'UserVariableBrick';
  const WRITE_VARIABLE_ON_DEVICE_BRICK = 'WriteVariableOnDeviceBrick';
  const READ_VARIABLE_FROM_DEVICE_BRICK = 'ReadVariableFromDeviceBrick';
  const USER_LIST_BRICK = 'UserListBrick';
  const ADD_ITEM_LIST_BRICK = 'AddItemToUserListBrick';
  const DELETE_ITEM_LIST_BRICK = 'DeleteItemOfUserListBrick';
  const INSERT_ITEM_LIST_BRICK = 'InsertItemIntoUserListBrick';
  const REPLACE_ITEM_LIST_BRICK = 'ReplaceItemInUserListBrick';
  const CLEAR_LIST_BRICK = 'ClearUserListBrick';
  const WRITE_LIST_ON_DEVICE_BRICK = 'WriteListOnDeviceBrick';
  const READ_LIST_FROM_DEVICE_BRICK = 'ReadListFromDeviceBrick';

  // Embroidery
  const STITCH_BRICK = 'StitchBrick';
  const RUNNING_STITCH_BRICK = 'RunningStitchBrick';
  const STOP_RUNNING_STITCH_BRICK = 'StopRunningStitchBrick';
  const TRIPLE_STITCH_BRICK = 'TripleStitchBrick';
  const ZIG_ZAG_STITCH_BRICK = 'ZigZagStitchBrick';

  // --- Lego NXT ---
  const LEGO_NXT_MOTOR_TURN_ANGLE_BRICK = 'LegoNxtMotorTurnAngleBrick';
  const LEGO_NXT_MOTOR_STOP_BRICK = 'LegoNxtMotorStopBrick';
  const LEGO_NXT_MOTOR_MOVE_BRICK = 'LegoNxtMotorMoveBrick';
  const LEGO_NXT_PLAY_TONE_BRICK = 'LegoNxtPlayToneBrick';

  // --- Lego EV3 ---
  const LEGO_EV3_MOTOR_TURN_ANGLE_BRICK = 'LegoEv3MotorTurnAngleBrick';
  const LEGO_EV3_MOTOR_MOVE_BRICK = 'LegoEv3MotorMoveBrick';
  const LEGO_EV3_MOTOR_STOP_BRICK = 'LegoEv3MotorStopBrick';
  const LEGO_EV3_MOTOR_PLAY_TONE_BRICK = 'LegoEv3PlayToneBrick';
  const LEGO_EV3_SET_LED_BRICK = 'LegoEv3SetLedBrick';

  // --- Ar Drone Bricks ---
  const AR_DRONE_TAKE_OFF_LAND_BRICK = 'DroneTakeOffLandBrick';
  const AR_DRONE_EMERGENCY_BRICK = 'DroneEmergencyBrick';
  const AR_DRONE_MOVE_UP_BRICK = 'DroneMoveUpBrick';
  const AR_DRONE_MOVE_DOWN_BRICK = 'DroneMoveDownBrick';
  const AR_DRONE_MOVE_LEFT_BRICK = 'DroneMoveLeftBrick';
  const AR_DRONE_MOVE_RIGHT_BRICK = 'DroneMoveRightBrick';
  const AR_DRONE_MOVE_FORWARD_BRICK = 'DroneMoveForwardBrick';
  const AR_DRONE_MOVE_BACKWARD_BRICK = 'DroneMoveBackwardBrick';
  const AR_DRONE_TURN_LEFT_BRICK = 'DroneTurnLeftBrick';
  const AR_DRONE_TURN_RIGHT_BRICK = 'DroneTurnRightBrick';
  const AR_DRONE_FLIP_BRICK = 'DroneFlipBrick';
  const AR_DRONE_PLAYED_ANIMATION_BRICK = 'DronePlayLedAnimationBrick';
  const AR_DRONE_SWITCH_CAMERA_BRICK = 'DroneSwitchCameraBrick';

  // --- Jump Sumo ---
  const JUMP_SUMO_MOVE_FORWARD_BRICK = 'JumpingSumoMoveForwardBrick';
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

  // --- Phiro ---
  const PHIRO_MOTOR_MOVE_FORWARD_BRICK = 'PhiroMotorMoveForwardBrick';
  const PHIRO_MOTOR_MOVE_BACKWARD_BRICK = 'PhiroMotorMoveBackwardBrick';
  const PHIRO_MOTOR_STOP_BRICK = 'PhiroMotorStopBrick';
  const PHIRO_PLAY_TONE_BRICK = 'PhiroPlayToneBrick';
  const PHIRO_RGB_LIGHT_BRICK = 'PhiroRGBLightBrick';
  const PHIRO_IF_LOGIC_BEGIN_BRICK = 'PhiroIfLogicBeginBrick';

  // --- Arduino ---
  const ARDUINO_SEND_DIGITAL_VALUE_BRICK = 'ArduinoSendDigitalValueBrick';
  const ARDUINO_SEND_PMW_VALUE_BRICK = 'ArduinoSendPWMValueBrick';

  // --- Chromecast ---
  const WHEN_GAME_PAD_BUTTON_SCRIPT = 'WhenGamepadButtonScript';
  const WHEN_GAME_PAD_BUTTON_BRICK = 'WhenGamepadButtonBrick';

  // --- Raspberry Pi ---
  const WHEN_RASPI_PIN_CHANGED_BRICK = 'WhenRaspiPinChangedBrick';
  const WHEN_RASPI_PIN_CHANGED_SCRIPT = 'RaspiInterruptScript';
  const RASPI_IF_LOGIC_BEGIN_BRICK = 'RaspiIfLogicBeginBrick';
  const RASPI_SEND_DIGITAL_VALUE_BRICK = 'RaspiSendDigitalValueBrick';
  const RASPI_PWM_BRICK = 'RaspiPwmBrick';

  // --- NFC ---
  const WHEN_NFC_SCRIPT = 'WhenNfcScript';
  const WHEN_NFC_BRICK = 'WhenNfcBrick';
  const SET_NFC_TAG_BRICK = 'SetNfcTagBrick';

  // --- Testing
  const ASSERT_EQUALS_BRICK = 'AssertEqualsBrick';
  const WAIT_TILL_IDLE_BRICK = 'WaitTillIdleBrick';
  const TAP_AT_BRICK = 'TapAtBrick';
  const FINISH_STAGE_BRICK = 'FinishStageBrick';

  // --- Deprecated old bricks - to still provide old projects with correct statistics
  //                             even when the app is not using those bricks anymore
  const COLLISION_SCRIPT = 'CollisionScript';
  const LOOP_ENDLESS_BRICK = 'LoopEndlessBrick';

  // -------------------------------------------------------------------------------------------------------------------
  // Brick/Script Images -> needed for code view + to decide which brick belongs to which group
  //
  const EVENT_SCRIPT_IMG = '1h_when_brown.png';
  const EVENT_BRICK_IMG = '1h_brick_brown.png';
  const CONTROL_SCRIPT_IMG = '1h_when_orange.png';
  const CONTROL_BRICK_IMG = '1h_brick_orange.png';
  const MOTION_BRICK_IMG = '1h_brick_blue.png';
  const MOTION_SCRIPT_IMG = '1h_when_blue.png';
  const SOUND_BRICK_IMG = '1h_brick_violet.png';
  const LOOKS_BRICK_IMG = '1h_brick_green.png';
  const DATA_BRICK_IMG = '1h_brick_red.png';
  const PEN_BRICK_IMG = '1h_brick_darkgreen.png';

  const UNKNOWN_SCRIPT_IMG = '1h_when_grey.png';
  const UNKNOWN_BRICK_IMG = '1h_brick_grey.png';
  const DEPRECATED_SCRIPT_IMG = '1h_when_grey.png';
  const DEPRECATED_BRICK_IMG = '1h_brick_grey.png';

  const LEGO_EV3_BRICK_IMG = '1h_brick_yellow.png';
  const LEGO_NXT_BRICK_IMG = '1h_brick_yellow.png';

  const ARDUINO_BRICK_IMG = '1h_brick_light_blue.png';

  const EMBROIDERY_BRICK_IMG = '1h_brick_pink.png';

  const JUMPING_SUMO_BRICK_IMG = self::MOTION_BRICK_IMG;

  const AR_DRONE_MOTION_BRICK_IMG = self::MOTION_BRICK_IMG;
  const AR_DRONE_LOOKS_BRICK_IMG = self::LOOKS_BRICK_IMG;

  const RASPI_BRICK_IMG = '1h_brick_light_blue.png';
  const RASPI_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;
  const RASPI_EVENT_SCRIPT_IMG = self::EVENT_SCRIPT_IMG;

  const PHIRO_BRICK_IMG = '1h_brick_light_blue.png';
  const PHIRO_SOUND_BRICK_IMG = self::SOUND_BRICK_IMG;
  const PHIRO_LOOK_BRICK_IMG = self::LOOKS_BRICK_IMG;
  const PHIRO_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;

  const TESTING_BRICK_IMG = '1h_brick_light_blue.png';

  const YOUR_BRICK_IMG = '1h_brick_light_blue.png';

  // -------------------------------------------------------------------------------------------------------------------
  // Formula Categories
  //
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

  // --- AR DRONE FORMULA ---
  const AR_DRONE_TIME_TO_FLY_IN_SECONDS = 'DRONE_TIME_TO_FLY_IN_SECONDS';
  const AR_DRONE_POWER_IN_PERCENT = 'DRONE_POWER_IN_PERCENT';

  // --- JUMP SUMO FORMULA ---
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
  const LOOK_INDEX = 'LOOK_INDEX';
}
