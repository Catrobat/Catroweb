<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

class Constants
{
  // Attributes
  final public const TYPE_ATTRIBUTE = 'type';
  final public const REFERENCE_ATTRIBUTE = 'reference';
  final public const NAME_ATTRIBUTE = 'name';
  final public const CATEGORY_ATTRIBUTE = 'category';

  // Object types
  final public const SINGLE_SPRITE_TYPE = 'SingleSprite';
  final public const GROUP_SPRITE_TYPE = 'GroupSprite';
  final public const GROUP_ITEM_SPRITE_TYPE = 'GroupItemSprite';

  // Bricks & Scripts
  final public const UNKNOWN_SCRIPT = 'UnknownScript';
  final public const UNKNOWN_BRICK = 'UnknownBrick';

  // Motion
  final public const PLACE_AT_BRICK = 'PlaceAtBrick';
  final public const SET_X_BRICK = 'SetXBrick';
  final public const SET_Y_BRICK = 'SetYBrick';
  final public const GO_TO_BRICK = 'GoToBrick';
  final public const CHANGE_X_BY_N_BRICK = 'ChangeXByNBrick';
  final public const CHANGE_Y_BY_N_BRICK = 'ChangeYByNBrick';
  final public const IF_ON_EDGE_BOUNCE_BRICK = 'IfOnEdgeBounceBrick';
  final public const MOVE_N_STEPS_BRICK = 'MoveNStepsBrick';
  final public const TURN_LEFT_BRICK = 'TurnLeftBrick';
  final public const TURN_RIGHT_BRICK = 'TurnRightBrick';
  final public const POINT_IN_DIRECTION_BRICK = 'PointInDirectionBrick';
  final public const POINT_TO_BRICK = 'PointToBrick';
  final public const SET_ROTATION_STYLE_BRICK = 'SetRotationStyleBrick';
  final public const GLIDE_TO_BRICK = 'GlideToBrick';
  final public const GO_N_STEPS_BACK_BRICK = 'GoNStepsBackBrick';
  final public const COME_TO_FRONT_BRICK = 'ComeToFrontBrick';
  final public const VIBRATION_BRICK = 'VibrationBrick';
  final public const SET_PHYSICS_OBJECT_TYPE_BRICK = 'SetPhysicsObjectTypeBrick';
  final public const SET_VELOCITY_BRICK = 'SetVelocityBrick';
  final public const TURN_LEFT_SPEED_BRICK = 'TurnLeftSpeedBrick';
  final public const TURN_RIGHT_SPEED_BRICK = 'TurnRightSpeedBrick';
  final public const SET_GRAVITY_BRICK = 'SetGravityBrick';
  final public const SET_MASS_BRICK = 'SetMassBrick';
  final public const SET_BOUNCE_BRICK = 'SetBounceBrick';
  final public const SET_FRICTION_BRICK = 'SetFrictionBrick';
  final public const SET_TEXT_BRICK = 'SetTextBrick';

  // Event
  final public const START_SCRIPT = 'StartScript';
  final public const WHEN_STARTED_BRICK = 'WhenStartedBrick';
  final public const WHEN_SCRIPT = 'WhenScript';
  final public const WHEN_BG_CHANGE_SCRIPT = 'WhenBackgroundChangesScript';
  final public const WHEN_BG_CHANGE_BRICK = 'WhenBackgroundChangesBrick';
  final public const WHEN_CLONED_SCRIPT = 'WhenClonedScript';
  final public const WHEN_CLONED_BRICK = 'WhenClonedBrick';
  final public const WHEN_TOUCH_SCRIPT = 'WhenTouchDownScript';
  final public const WHEN_TOUCH_BRICK = 'WhenTouchDownBrick';
  final public const WHEN_CONDITION_SCRIPT = 'WhenConditionScript';
  final public const WHEN_CONDITION_BRICK = 'WhenConditionBrick';
  final public const BROADCAST_SCRIPT = 'BroadcastScript';
  final public const BROADCAST_BRICK = 'BroadcastBrick';
  final public const BROADCAST_WAIT_BRICK = 'BroadcastWaitBrick';
  final public const BROADCAST_RECEIVER_BRICK = 'BroadcastReceiverBrick';
  final public const WHEN_BRICK = 'WhenBrick';
  final public const WHEN_BOUNCE_OFF_SCRIPT = 'WhenBounceOffScript';
  final public const WHEN_BOUNCE_OFF_BRICK = 'WhenBounceOffBrick';

  // --- Looks ---
  final public const SET_LOOK_BRICK = 'SetLookBrick';
  final public const SET_LOOK_BY_INDEX_BRICK = 'SetLookByIndexBrick';
  final public const NEXT_LOOK_BRICK = 'NextLookBrick';
  final public const PREV_LOOK_BRICK = 'PreviousLookBrick';
  final public const SET_SIZE_TO_BRICK = 'SetSizeToBrick';
  final public const CHANGE_SIZE_BY_N_BRICK = 'ChangeSizeByNBrick';
  final public const HIDE_BRICK = 'HideBrick';
  final public const SHOW_BRICK = 'ShowBrick';
  final public const ASK_BRICK = 'AskBrick';
  final public const SAY_BUBBLE_BRICK = 'SayBubbleBrick';
  final public const SAY_FOR_BUBBLE_BRICK = 'SayForBubbleBrick';
  final public const THINK_BUBBLE_BRICK = 'ThinkBubbleBrick';
  final public const THINK_FOR_BUBBLE_BRICK = 'ThinkForBubbleBrick';
  final public const SET_TRANSPARENCY_BRICK = 'SetTransparencyBrick';
  final public const CHANGE_TRANSPARENCY_BY_N_BRICK = 'ChangeTransparencyByNBrick';
  final public const SET_BRIGHTNESS_BRICK = 'SetBrightnessBrick';
  final public const CHANGE_BRIGHTNESS_BY_N_BRICK = 'ChangeBrightnessByNBrick';
  final public const SET_COLOR_BRICK = 'SetColorBrick';
  final public const CHANGE_COLOR_BY_N_BRICK = 'ChangeColorByNBrick';
  final public const CLEAR_GRAPHIC_EFFECT_BRICK = 'ClearGraphicEffectBrick';
  final public const SET_BACKGROUND_BRICK = 'SetBackgroundBrick';
  final public const SET_BACKGROUND_BY_INDEX_BRICK = 'SetBackgroundByIndexBrick';
  final public const SET_BACKGROUND_WAIT_BRICK = 'SetBackgroundAndWaitBrick';
  final public const SET_BACKGROUND_BY_INDEX_AND_WAIT_BRICK = 'SetBackgroundByIndexAndWaitBrick';
  final public const CAMERA_BRICK = 'CameraBrick';
  final public const CHOOSE_CAMERA_BRICK = 'ChooseCameraBrick';
  final public const FLASH_BRICK = 'FlashBrick';
  final public const BACKGROUND_REQUEST_BRICK = 'BackgroundRequestBrick';
  final public const LOOK_REQUEST_BRICK = 'LookRequestBrick';
  final public const COPY_LOOK_BRICK = 'CopyLookBrick';
  final public const DELETE_LOOK_BRICK = 'DeleteLookBrick';
  final public const EDIT_LOOK_BRICK = 'EditLookBrick';
  final public const PAINT_NEW_LOOK_BRICK = 'PaintNewLookBrick';

  // --- Pen ---
  final public const PEN_DOWN_BRICK = 'PenDownBrick';
  final public const PEN_UP_BRICK = 'PenUpBrick';
  final public const SET_PEN_SIZE_BRICK = 'SetPenSizeBrick';
  final public const SET_PEN_COLOR_BRICK = 'SetPenColorBrick';
  final public const STAMP_BRICK = 'StampBrick';
  final public const CLEAR_BACKGROUND_BRICK = 'ClearBackgroundBrick';

  // --- Sound ---
  final public const PLAY_SOUND_BRICK = 'PlaySoundBrick';
  final public const PLAY_SOUND_WAIT_BRICK = 'PlaySoundAndWaitBrick';
  final public const STOP_ALL_SOUNDS_BRICK = 'StopAllSoundsBrick';
  final public const SET_VOLUME_TO_BRICK = 'SetVolumeToBrick';
  final public const CHANGE_VOLUME_BY_N_BRICK = 'ChangeVolumeByNBrick';
  final public const SPEAK_BRICK = 'SpeakBrick';
  final public const SPEAK_WAIT_BRICK = 'SpeakAndWaitBrick';
  final public const ASK_SPEECH_BRICK = 'AskSpeechBrick';
  final public const STOP_SOUND_BRICK = 'StopSoundBrick';
  final public const START_LISTENING_BRICK = 'StartListeningBrick';
  final public const CHANGE_TEMPO_BY_N_BRICK = 'ChangeTempoByNBrick';
  final public const SET_TEMPO_BRICK = 'SetTempoBrick';
  final public const PAUSE_FOR_BEATS_BRICK = 'PauseForBeatsBrick';
  final public const PLAY_DRUM_FOR_BEATS_BRICK = 'PlayDrumForBeatsBrick';
  final public const PLAY_NOTE_FOR_BEATS_BRICK = 'PlayNoteForBeatsBrick';
  final public const SET_LISTENING_LANGUAGE_BRICK = 'SetListeningLanguageBrick';

  // --- Control ---
  final public const WAIT_BRICK = 'WaitBrick';
  final public const NOTE_BRICK = 'NoteBrick';
  final public const FOREVER_BRICK = 'ForeverBrick';
  final public const IF_BRICK = 'IfLogicBeginBrick';
  final public const IF_THEN_BRICK = 'IfThenLogicBeginBrick';
  final public const WAIT_UNTIL_BRICK = 'WaitUntilBrick';
  final public const REPEAT_BRICK = 'RepeatBrick';
  final public const REPEAT_UNTIL_BRICK = 'RepeatUntilBrick';
  final public const CONTINUE_SCENE_BRICK = 'SceneTransitionBrick';
  final public const SCENE_START_BRICK = 'SceneStartBrick';
  final public const STOP_SCRIPT_BRICK = 'StopScriptBrick';
  final public const CLONE_BRICK = 'CloneBrick';
  final public const DELETE_THIS_CLONE_BRICK = 'DeleteThisCloneBrick';
  final public const EXIT_STAGE_BRICK = 'ExitStageBrick';
  final public const SET_INSTRUMENT_BRICK = 'SetInstrumentBrick';

  // auto generated control blocks
  final public const ELSE_BRICK = 'IfLogicElseBrick';
  final public const ENDIF_BRICK = 'IfLogicEndBrick';
  final public const ENDIF_THEN_BRICK = 'IfThenLogicEndBrick';
  final public const LOOP_END_BRICK = 'LoopEndBrick';

  // --- Data ---
  final public const SET_VARIABLE_BRICK = 'SetVariableBrick';
  final public const CHANGE_VARIABLE_BRICK = 'ChangeVariableBrick';
  final public const SHOW_TEXT_BRICK = 'ShowTextBrick';
  final public const SHOW_TEXT_COLOR_SIZE_ALIGNMENT_BRICK = 'ShowTextColorSizeAlignmentBrick';
  final public const HIDE_TEXT_BRICK = 'HideTextBrick';
  final public const USER_VARIABLE_BRICK = 'UserVariableBrick';
  final public const WRITE_VARIABLE_ON_DEVICE_BRICK = 'WriteVariableOnDeviceBrick';
  final public const READ_VARIABLE_FROM_DEVICE_BRICK = 'ReadVariableFromDeviceBrick';
  final public const USER_LIST_BRICK = 'UserListBrick';
  final public const ADD_ITEM_LIST_BRICK = 'AddItemToUserListBrick';
  final public const DELETE_ITEM_LIST_BRICK = 'DeleteItemOfUserListBrick';
  final public const INSERT_ITEM_LIST_BRICK = 'InsertItemIntoUserListBrick';
  final public const REPLACE_ITEM_LIST_BRICK = 'ReplaceItemInUserListBrick';
  final public const CLEAR_LIST_BRICK = 'ClearUserListBrick';
  final public const WRITE_LIST_ON_DEVICE_BRICK = 'WriteListOnDeviceBrick';
  final public const READ_LIST_FROM_DEVICE_BRICK = 'ReadListFromDeviceBrick';
  final public const STORE_CSV_INTO_USERLIST_BRICK = 'StoreCSVIntoUserListBrick';
  final public const WEB_REQUEST_BRICK = 'WebRequestBrick';
  final public const FOR_VARIABLE_FROM_TO_BRICK = 'ForVariableFromToBrick';
  final public const READ_VARIABLE_FROM_FILE_BRICK = 'ReadVariableFromFileBrick';
  final public const WRITE_VARIABLE_TO_FILE_BRICK = 'WriteVariableToFileBrick';
  final public const PARAMETERIZED_BRICK = 'ParameterizedBrick';
  final public const PARAMETERIZED_END_BRICK = 'ParameterizedEndBrick';
  final public const FOR_ITEM_IN_USER_LIST_BRICK = 'ForItemInUserListBrick';

  // --- Device ---
  final public const OPEN_URL_BRICK = 'OpenUrlBrick';
  final public const RESET_TIMER_BRICK = 'ResetTimerBrick';
  final public const TOUCH_AND_SLIDE_BRICK = 'TouchAndSlideBrick';

  // Your Bricks
  final public const USER_DEFINED_BRICK = 'UserDefinedBrick';
  final public const USER_DEFINED_RECEIVER_BRICK = 'UserDefinedReceiverBrick';

  // Your Scripts
  final public const USER_DEFINED_SCRIPT = 'UserDefinedScript';

  // Embroidery
  final public const STITCH_BRICK = 'StitchBrick';
  final public const RUNNING_STITCH_BRICK = 'RunningStitchBrick';
  final public const STOP_RUNNING_STITCH_BRICK = 'StopRunningStitchBrick';
  final public const TRIPLE_STITCH_BRICK = 'TripleStitchBrick';
  final public const ZIG_ZAG_STITCH_BRICK = 'ZigZagStitchBrick';
  final public const SEW_UP_BRICK = 'SewUpBrick';
  final public const WRITE_EMBROIDERY_TO_FILE_BRICK = 'WriteEmbroideryToFileBrick';

  // --- Lego NXT ---
  final public const LEGO_NXT_MOTOR_TURN_ANGLE_BRICK = 'LegoNxtMotorTurnAngleBrick';
  final public const LEGO_NXT_MOTOR_STOP_BRICK = 'LegoNxtMotorStopBrick';
  final public const LEGO_NXT_MOTOR_MOVE_BRICK = 'LegoNxtMotorMoveBrick';
  final public const LEGO_NXT_PLAY_TONE_BRICK = 'LegoNxtPlayToneBrick';

  // --- Lego EV3 ---
  final public const LEGO_EV3_MOTOR_TURN_ANGLE_BRICK = 'LegoEv3MotorTurnAngleBrick';
  final public const LEGO_EV3_MOTOR_MOVE_BRICK = 'LegoEv3MotorMoveBrick';
  final public const LEGO_EV3_MOTOR_STOP_BRICK = 'LegoEv3MotorStopBrick';
  final public const LEGO_EV3_MOTOR_PLAY_TONE_BRICK = 'LegoEv3PlayToneBrick';
  final public const LEGO_EV3_SET_LED_BRICK = 'LegoEv3SetLedBrick';

  // --- Ar Drone Bricks ---
  final public const AR_DRONE_TAKE_OFF_LAND_BRICK = 'DroneTakeOffLandBrick';
  final public const AR_DRONE_EMERGENCY_BRICK = 'DroneEmergencyBrick';
  final public const AR_DRONE_MOVE_UP_BRICK = 'DroneMoveUpBrick';
  final public const AR_DRONE_MOVE_DOWN_BRICK = 'DroneMoveDownBrick';
  final public const AR_DRONE_MOVE_LEFT_BRICK = 'DroneMoveLeftBrick';
  final public const AR_DRONE_MOVE_RIGHT_BRICK = 'DroneMoveRightBrick';
  final public const AR_DRONE_MOVE_FORWARD_BRICK = 'DroneMoveForwardBrick';
  final public const AR_DRONE_MOVE_BACKWARD_BRICK = 'DroneMoveBackwardBrick';
  final public const AR_DRONE_TURN_LEFT_BRICK = 'DroneTurnLeftBrick';
  final public const AR_DRONE_TURN_RIGHT_BRICK = 'DroneTurnRightBrick';
  final public const AR_DRONE_FLIP_BRICK = 'DroneFlipBrick';
  final public const AR_DRONE_PLAYED_ANIMATION_BRICK = 'DronePlayLedAnimationBrick';
  final public const AR_DRONE_SWITCH_CAMERA_BRICK = 'DroneSwitchCameraBrick';

  // --- Jump Sumo ---
  final public const JUMP_SUMO_MOVE_FORWARD_BRICK = 'JumpingSumoMoveForwardBrick';
  final public const JUMP_SUMO_MOVE_BACKWARD_BRICK = 'JumpingSumoMoveBackwardBrick';
  final public const JUMP_SUMO_ANIMATIONS_BRICK = 'JumpingSumoAnimationsBrick';
  final public const JUMP_SUMO_SOUND_BRICK = 'JumpingSumoSoundBrick';
  final public const JUMP_SUMO_NO_SOUND_BRICK = 'JumpingSumoNoSoundBrick';
  final public const JUMP_SUMO_JUMP_LONG_BRICK = 'JumpingSumoJumpLongBrick';
  final public const JUMP_SUMO_JUMP_HIGH_BRICK = 'JumpingSumoJumpHighBrick';
  final public const JUMP_SUMO_ROTATE_LEFT_BRICK = 'JumpingSumoRotateLeftBrick';
  final public const JUMP_SUMO_ROTATE_RIGHT_BRICK = 'JumpingSumoRotateRightBrick';
  final public const JUMP_SUMO_TURN_BRICK = 'JumpingSumoTurnBrick';
  final public const JUMP_SUMO_TAKING_PICTURE_BRICK = 'JumpingSumoTakingPictureBrick';

  // --- Phiro ---
  final public const PHIRO_MOTOR_MOVE_FORWARD_BRICK = 'PhiroMotorMoveForwardBrick';
  final public const PHIRO_MOTOR_MOVE_BACKWARD_BRICK = 'PhiroMotorMoveBackwardBrick';
  final public const PHIRO_MOTOR_STOP_BRICK = 'PhiroMotorStopBrick';
  final public const PHIRO_PLAY_TONE_BRICK = 'PhiroPlayToneBrick';
  final public const PHIRO_RGB_LIGHT_BRICK = 'PhiroRGBLightBrick';
  final public const PHIRO_IF_LOGIC_BEGIN_BRICK = 'PhiroIfLogicBeginBrick';

  // --- Arduino ---
  final public const ARDUINO_SEND_DIGITAL_VALUE_BRICK = 'ArduinoSendDigitalValueBrick';
  final public const ARDUINO_SEND_PMW_VALUE_BRICK = 'ArduinoSendPWMValueBrick';

  // --- Chromecast ---
  final public const WHEN_GAME_PAD_BUTTON_SCRIPT = 'WhenGamepadButtonScript';
  final public const WHEN_GAME_PAD_BUTTON_BRICK = 'WhenGamepadButtonBrick';

  // --- Raspberry Pi ---
  final public const WHEN_RASPI_PIN_CHANGED_BRICK = 'WhenRaspiPinChangedBrick';
  final public const WHEN_RASPI_PIN_CHANGED_SCRIPT = 'RaspiInterruptScript';
  final public const RASPI_IF_LOGIC_BEGIN_BRICK = 'RaspiIfLogicBeginBrick';
  final public const RASPI_SEND_DIGITAL_VALUE_BRICK = 'RaspiSendDigitalValueBrick';
  final public const RASPI_PWM_BRICK = 'RaspiPwmBrick';

  // --- NFC ---
  final public const WHEN_NFC_SCRIPT = 'WhenNfcScript';
  final public const WHEN_NFC_BRICK = 'WhenNfcBrick';
  final public const SET_NFC_TAG_BRICK = 'SetNfcTagBrick';

  // --- Testing
  final public const ASSERT_EQUALS_BRICK = 'AssertEqualsBrick';
  final public const WAIT_TILL_IDLE_BRICK = 'WaitTillIdleBrick';
  final public const TAP_AT_BRICK = 'TapAtBrick';
  final public const TAP_FOR_BRICK = 'TapForBrick';
  final public const FINISH_STAGE_BRICK = 'FinishStageBrick';
  final public const ASSERT_USER_LISTS_BRICK = 'AssertUserListsBrick';

  // --- Deprecated old bricks - to still provide old projects with correct statistics
  //                             even when the app is not using those bricks anymore
  final public const COLLISION_SCRIPT = 'CollisionScript';
  final public const LOOP_ENDLESS_BRICK = 'LoopEndlessBrick';

  // -------------------------------------------------------------------------------------------------------------------
  // Brick/Script Images -> needed for code view + to decide which brick belongs to which group
  //
  final public const EVENT_SCRIPT_IMG = '1h_when_brown.png';
  final public const EVENT_BRICK_IMG = '1h_brick_brown.png';
  final public const CONTROL_SCRIPT_IMG = '1h_when_orange.png';
  final public const CONTROL_BRICK_IMG = '1h_brick_orange.png';
  final public const MOTION_BRICK_IMG = '1h_brick_blue.png';
  final public const MOTION_SCRIPT_IMG = '1h_when_blue.png';
  final public const SOUND_BRICK_IMG = '1h_brick_violet.png';
  final public const LOOKS_BRICK_IMG = '1h_brick_green.png';
  final public const DATA_BRICK_IMG = '1h_brick_red.png';
  final public const PEN_BRICK_IMG = '1h_brick_darkgreen.png';
  final public const DEVICE_BRICK_IMG = '1h_brick_gold.png';

  final public const UNKNOWN_SCRIPT_IMG = '1h_when_grey.png';
  final public const UNKNOWN_BRICK_IMG = '1h_brick_grey.png';
  final public const DEPRECATED_SCRIPT_IMG = '1h_when_grey.png';
  final public const DEPRECATED_BRICK_IMG = '1h_brick_grey.png';

  final public const LEGO_EV3_BRICK_IMG = '1h_brick_yellow.png';
  final public const LEGO_NXT_BRICK_IMG = '1h_brick_yellow.png';

  final public const ARDUINO_BRICK_IMG = '1h_brick_light_blue.png';

  final public const EMBROIDERY_BRICK_IMG = '1h_brick_pink.png';

  final public const JUMPING_SUMO_BRICK_IMG = self::MOTION_BRICK_IMG;

  final public const AR_DRONE_MOTION_BRICK_IMG = self::MOTION_BRICK_IMG;
  final public const AR_DRONE_LOOKS_BRICK_IMG = self::LOOKS_BRICK_IMG;

  final public const RASPI_BRICK_IMG = '1h_brick_light_blue.png';
  final public const RASPI_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;
  final public const RASPI_EVENT_SCRIPT_IMG = self::EVENT_SCRIPT_IMG;

  final public const PHIRO_BRICK_IMG = '1h_brick_light_blue.png';
  final public const PHIRO_SOUND_BRICK_IMG = self::SOUND_BRICK_IMG;
  final public const PHIRO_LOOK_BRICK_IMG = self::LOOKS_BRICK_IMG;
  final public const PHIRO_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;

  final public const TESTING_BRICK_IMG = '1h_brick_light_blue.png';

  final public const YOUR_BRICK_IMG = '1h_brick_light_blue.png';
  final public const YOUR_SCRIPT_IMG = '1h_brick_light_blue.png';

  // -------------------------------------------------------------------------------------------------------------------
  // Formula Categories
  //
  final public const TIME_TO_WAIT_IN_SECONDS_FORMULA = 'TIME_TO_WAIT_IN_SECONDS';
  final public const NOTE_FORMULA = 'NOTE';
  final public const IF_CONDITION_FORMULA = 'IF_CONDITION';
  final public const TIMES_TO_REPEAT_FORMULA = 'TIMES_TO_REPEAT';
  final public const X_POSITION_FORMULA = 'X_POSITION';
  final public const Y_POSITION_FORMULA = 'Y_POSITION';
  final public const X_POSITION_CHANGE_FORMULA = 'X_POSITION_CHANGE';
  final public const Y_POSITION_CHANGE_FORMULA = 'Y_POSITION_CHANGE';
  final public const STEPS_FORMUlA = 'STEPS';
  final public const TURN_LEFT_DEGREES_FORMULA = 'TURN_LEFT_DEGREES';
  final public const TURN_RIGHT_DEGREES_FORMULA = 'TURN_RIGHT_DEGREES';
  final public const DEGREES_FORMULA = 'DEGREES';
  final public const DURATION_IN_SECONDS_FORMULA = 'DURATION_IN_SECONDS';
  final public const Y_DESTINATION_FORMUlA = 'Y_DESTINATION';
  final public const X_DESTINATION_FORMULA = 'X_DESTINATION';
  final public const VIBRATE_DURATION_IN_SECONDS_FORMULA = 'VIBRATE_DURATION_IN_SECONDS';
  final public const VELOCITY_X_FORMULA = 'PHYSICS_VELOCITY_X';
  final public const VELOCITY_Y_FORMULA = 'PHYSICS_VELOCITY_Y';
  final public const TURN_LEFT_SPEED_FORMULA = 'PHYSICS_TURN_LEFT_SPEED';
  final public const TURN_RIGHT_SPEED_FORMULA = 'PHYSICS_TURN_RIGHT_SPEED';
  final public const GRAVITY_Y_FORMULA = 'PHYSICS_GRAVITY_Y';
  final public const GRAVITY_X_FORMULA = 'PHYSICS_GRAVITY_X';
  final public const MASS_FORMULA = 'PHYSICS_MASS';
  final public const BOUNCE_FACTOR_FORMULA = 'PHYSICS_BOUNCE_FACTOR';
  final public const FRICTION_FORMULA = 'PHYSICS_FRICTION';
  final public const VOLUME_FORMUlA = 'VOLUME';
  final public const VOLUME_CHANGE_FORMULA = 'VOLUME_CHANGE';
  final public const SPEAK_FORMULA = 'SPEAK';
  final public const SIZE_FORMULA = 'SIZE';
  final public const SIZE_CHANGE_FORMULA = 'SIZE_CHANGE';
  final public const TRANSPARENCY_FORMULA = 'TRANSPARENCY';
  final public const TRANSPARENCY_CHANGE_FORMULA = 'TRANSPARENCY_CHANGE';
  final public const BRIGHTNESS_FORMULA = 'BRIGHTNESS';
  final public const BRIGHTNESS_CHANGE_FORMULA = 'BRIGHTNESS_CHANGE';
  final public const COLOR_FORMUlA = 'COLOR';
  final public const COLOR_CHANGE_FORMULA = 'COLOR_CHANGE';
  final public const VARIABLE_FORMULA = 'VARIABLE';
  final public const VARIABLE_CHANGE_FORMULA = 'VARIABLE_CHANGE';
  final public const LIST_ADD_ITEM_FORMULA = 'LIST_ADD_ITEM';
  final public const LIST_DELETE_ITEM_FORMULA = 'LIST_DELETE_ITEM';
  final public const INSERT_ITEM_LIST_VALUE_FORMULA = 'INSERT_ITEM_INTO_USERLIST_VALUE';
  final public const INSERT_ITEM_LIST_INDEX_FORMULA = 'INSERT_ITEM_INTO_USERLIST_INDEX';
  final public const REPLACE_ITEM_LIST_VALUE_FORMULA = 'REPLACE_ITEM_IN_USERLIST_VALUE';
  final public const REPLACE_ITEM_LIST_INDEX_FORMULA = 'REPLACE_ITEM_IN_USERLIST_INDEX';
  final public const STORE_CSV_INTO_LIST_COLUMN_FORMULA = 'STORE_CSV_INTO_USERLIST_COLUMN';
  final public const STORE_CSV_INTO_LIST_CSV_FORMULA = 'STORE_CSV_INTO_USERLIST_CSV';
  final public const REPEAT_UNTIL_CONDITION_FORMULA = 'REPEAT_UNTIL_CONDITION';
  final public const ASK_QUESTION_FORMULA = 'ASK_QUESTION';
  final public const ASK_SPEECH_QUESTION_FORMULA = 'ASK_SPEECH_QUESTION';
  final public const STRING_FORMULA = 'STRING';
  final public const PEN_SIZE_FORMULA = 'PEN_SIZE';
  final public const PEN_COLOR_RED_FORMULA = 'PHIRO_LIGHT_RED';
  final public const PEN_COLOR_BLUE_FORMULA = 'PHIRO_LIGHT_BLUE';
  final public const PEN_COLOR_GREEN_FORMULA = 'PHIRO_LIGHT_GREEN';
  final public const PEN_COLOR_RED_NEW_FORMULA = 'PEN_COLOR_RED';
  final public const PEN_COLOR_BLUE_NEW_FORMULA = 'PEN_COLOR_BLUE';
  final public const PEN_COLOR_GREEN_NEW_FORMULA = 'PEN_COLOR_GREEN';
  final public const LEGO_EV3_POWER_FORMULA = 'LEGO_EV3_POWER';
  final public const LEGO_EV3_PERIOD_IN_SECONDS_FORMULA = 'LEGO_EV3_PERIOD_IN_SECONDS';
  final public const LEGO_EV3_DURATION_IN_SECONDS_FORMULA = 'LEGO_EV3_DURATION_IN_SECONDS';
  final public const LEGO_EV3_VOLUME_FORMULA = 'LEGO_EV3_VOLUME';
  final public const LEGO_EV3_FREQUENCY_FORMULA = 'LEGO_EV3_FREQUENCY';
  final public const LEGO_EV3_DEGREES_FORMULA = 'LEGO_EV3_DEGREES';
  final public const BACKGROUND_REQUEST_FORMULA = 'BACKGROUND_REQUEST';
  final public const LOOK_REQUEST_FORMULA = 'LOOK_REQUEST';

  // --- AR DRONE FORMULA ---
  final public const AR_DRONE_TIME_TO_FLY_IN_SECONDS = 'DRONE_TIME_TO_FLY_IN_SECONDS';
  final public const AR_DRONE_POWER_IN_PERCENT = 'DRONE_POWER_IN_PERCENT';

  // --- JUMP SUMO FORMULA ---
  final public const JUMP_SUMO_SPEED = 'JUMPING_SUMO_SPEED';
  final public const JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS = 'JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS';
  final public const JUMPING_SUMO_ROTATE = 'JUMPING_SUMO_ROTATE';

  final public const OPERATOR_FORMULA_TYPE = 'OPERATOR';
  final public const FUNCTION_FORMULA_TYPE = 'FUNCTION';
  final public const BRACKET_FORMULA_TYPE = 'BRACKET';

  final public const PLUS_OPERATOR = 'PLUS';
  final public const MINUS_OPERATOR = 'MINUS';
  final public const MULT_OPERATOR = 'MULT';
  final public const DIVIDE_OPERATOR = 'DIVIDE';
  final public const EQUAL_OPERATOR = 'EQUAL';
  final public const NOT_EQUAL_OPERATOR = 'NOT_EQUAL';
  final public const GREATER_OPERATOR = 'GREATER_THAN';
  final public const GREATER_EQUAL_OPERATOR = 'GREATER_OR_EQUAL';
  final public const SMALLER_OPERATOR = 'SMALLER_THAN';
  final public const SMALLER_EQUAL_OPERATOR = 'SMALLER_OR_EQUAL';
  final public const NOT_OPERATOR = 'LOGICAL_NOT';
  final public const AND_OPERATOR = 'LOGICAL_AND';
  final public const OR_OPERATOR = 'LOGICAL_OR';

  final public const POINTED_OBJECT_TAG = 'pointedObject';
  final public const LOOK_INDEX = 'LOOK_INDEX';
}
