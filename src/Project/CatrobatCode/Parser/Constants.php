<?php

namespace App\Project\CatrobatCode\Parser;

class Constants
{
  // Attributes
  public const TYPE_ATTRIBUTE = 'type';
  public const REFERENCE_ATTRIBUTE = 'reference';
  public const NAME_ATTRIBUTE = 'name';
  public const CATEGORY_ATTRIBUTE = 'category';

  // Object types
  public const SINGLE_SPRITE_TYPE = 'SingleSprite';
  public const GROUP_SPRITE_TYPE = 'GroupSprite';
  public const GROUP_ITEM_SPRITE_TYPE = 'GroupItemSprite';

  // Bricks & Scripts
  public const UNKNOWN_SCRIPT = 'UnknownScript';
  public const UNKNOWN_BRICK = 'UnknownBrick';

  // Motion
  public const PLACE_AT_BRICK = 'PlaceAtBrick';
  public const SET_X_BRICK = 'SetXBrick';
  public const SET_Y_BRICK = 'SetYBrick';
  public const GO_TO_BRICK = 'GoToBrick';
  public const CHANGE_X_BY_N_BRICK = 'ChangeXByNBrick';
  public const CHANGE_Y_BY_N_BRICK = 'ChangeYByNBrick';
  public const IF_ON_EDGE_BOUNCE_BRICK = 'IfOnEdgeBounceBrick';
  public const MOVE_N_STEPS_BRICK = 'MoveNStepsBrick';
  public const TURN_LEFT_BRICK = 'TurnLeftBrick';
  public const TURN_RIGHT_BRICK = 'TurnRightBrick';
  public const POINT_IN_DIRECTION_BRICK = 'PointInDirectionBrick';
  public const POINT_TO_BRICK = 'PointToBrick';
  public const SET_ROTATION_STYLE_BRICK = 'SetRotationStyleBrick';
  public const GLIDE_TO_BRICK = 'GlideToBrick';
  public const GO_N_STEPS_BACK_BRICK = 'GoNStepsBackBrick';
  public const COME_TO_FRONT_BRICK = 'ComeToFrontBrick';
  public const VIBRATION_BRICK = 'VibrationBrick';
  public const SET_PHYSICS_OBJECT_TYPE_BRICK = 'SetPhysicsObjectTypeBrick';
  public const SET_VELOCITY_BRICK = 'SetVelocityBrick';
  public const TURN_LEFT_SPEED_BRICK = 'TurnLeftSpeedBrick';
  public const TURN_RIGHT_SPEED_BRICK = 'TurnRightSpeedBrick';
  public const SET_GRAVITY_BRICK = 'SetGravityBrick';
  public const SET_MASS_BRICK = 'SetMassBrick';
  public const SET_BOUNCE_BRICK = 'SetBounceBrick';
  public const SET_FRICTION_BRICK = 'SetFrictionBrick';
  public const SET_TEXT_BRICK = 'SetTextBrick';

  // Event
  public const START_SCRIPT = 'StartScript';
  public const WHEN_STARTED_BRICK = 'WhenStartedBrick';
  public const WHEN_SCRIPT = 'WhenScript';
  public const WHEN_BG_CHANGE_SCRIPT = 'WhenBackgroundChangesScript';
  public const WHEN_BG_CHANGE_BRICK = 'WhenBackgroundChangesBrick';
  public const WHEN_CLONED_SCRIPT = 'WhenClonedScript';
  public const WHEN_CLONED_BRICK = 'WhenClonedBrick';
  public const WHEN_TOUCH_SCRIPT = 'WhenTouchDownScript';
  public const WHEN_TOUCH_BRICK = 'WhenTouchDownBrick';
  public const WHEN_CONDITION_SCRIPT = 'WhenConditionScript';
  public const WHEN_CONDITION_BRICK = 'WhenConditionBrick';
  public const BROADCAST_SCRIPT = 'BroadcastScript';
  public const BROADCAST_BRICK = 'BroadcastBrick';
  public const BROADCAST_WAIT_BRICK = 'BroadcastWaitBrick';
  public const BROADCAST_RECEIVER_BRICK = 'BroadcastReceiverBrick';
  public const WHEN_BRICK = 'WhenBrick';
  public const WHEN_BOUNCE_OFF_SCRIPT = 'WhenBounceOffScript';
  public const WHEN_BOUNCE_OFF_BRICK = 'WhenBounceOffBrick';

  // --- Looks ---
  public const SET_LOOK_BRICK = 'SetLookBrick';
  public const SET_LOOK_BY_INDEX_BRICK = 'SetLookByIndexBrick';
  public const NEXT_LOOK_BRICK = 'NextLookBrick';
  public const PREV_LOOK_BRICK = 'PreviousLookBrick';
  public const SET_SIZE_TO_BRICK = 'SetSizeToBrick';
  public const CHANGE_SIZE_BY_N_BRICK = 'ChangeSizeByNBrick';
  public const HIDE_BRICK = 'HideBrick';
  public const SHOW_BRICK = 'ShowBrick';
  public const ASK_BRICK = 'AskBrick';
  public const SAY_BUBBLE_BRICK = 'SayBubbleBrick';
  public const SAY_FOR_BUBBLE_BRICK = 'SayForBubbleBrick';
  public const THINK_BUBBLE_BRICK = 'ThinkBubbleBrick';
  public const THINK_FOR_BUBBLE_BRICK = 'ThinkForBubbleBrick';
  public const SET_TRANSPARENCY_BRICK = 'SetTransparencyBrick';
  public const CHANGE_TRANSPARENCY_BY_N_BRICK = 'ChangeTransparencyByNBrick';
  public const SET_BRIGHTNESS_BRICK = 'SetBrightnessBrick';
  public const CHANGE_BRIGHTNESS_BY_N_BRICK = 'ChangeBrightnessByNBrick';
  public const SET_COLOR_BRICK = 'SetColorBrick';
  public const CHANGE_COLOR_BY_N_BRICK = 'ChangeColorByNBrick';
  public const CLEAR_GRAPHIC_EFFECT_BRICK = 'ClearGraphicEffectBrick';
  public const SET_BACKGROUND_BRICK = 'SetBackgroundBrick';
  public const SET_BACKGROUND_BY_INDEX_BRICK = 'SetBackgroundByIndexBrick';
  public const SET_BACKGROUND_WAIT_BRICK = 'SetBackgroundAndWaitBrick';
  public const SET_BACKGROUND_BY_INDEX_AND_WAIT_BRICK = 'SetBackgroundByIndexAndWaitBrick';
  public const CAMERA_BRICK = 'CameraBrick';
  public const CHOOSE_CAMERA_BRICK = 'ChooseCameraBrick';
  public const FLASH_BRICK = 'FlashBrick';
  public const BACKGROUND_REQUEST_BRICK = 'BackgroundRequestBrick';
  public const LOOK_REQUEST_BRICK = 'LookRequestBrick';
  public const COPY_LOOK_BRICK = 'CopyLookBrick';
  public const DELETE_LOOK_BRICK = 'DeleteLookBrick';
  public const EDIT_LOOK_BRICK = 'EditLookBrick';
  public const PAINT_NEW_LOOK_BRICK = 'PaintNewLookBrick';

  // --- Pen ---
  public const PEN_DOWN_BRICK = 'PenDownBrick';
  public const PEN_UP_BRICK = 'PenUpBrick';
  public const SET_PEN_SIZE_BRICK = 'SetPenSizeBrick';
  public const SET_PEN_COLOR_BRICK = 'SetPenColorBrick';
  public const STAMP_BRICK = 'StampBrick';
  public const CLEAR_BACKGROUND_BRICK = 'ClearBackgroundBrick';

  // --- Sound ---
  public const PLAY_SOUND_BRICK = 'PlaySoundBrick';
  public const PLAY_SOUND_WAIT_BRICK = 'PlaySoundAndWaitBrick';
  public const STOP_ALL_SOUNDS_BRICK = 'StopAllSoundsBrick';
  public const SET_VOLUME_TO_BRICK = 'SetVolumeToBrick';
  public const CHANGE_VOLUME_BY_N_BRICK = 'ChangeVolumeByNBrick';
  public const SPEAK_BRICK = 'SpeakBrick';
  public const SPEAK_WAIT_BRICK = 'SpeakAndWaitBrick';
  public const ASK_SPEECH_BRICK = 'AskSpeechBrick';
  public const STOP_SOUND_BRICK = 'StopSoundBrick';
  public const START_LISTENING_BRICK = 'StartListeningBrick';
  public const CHANGE_TEMPO_BY_N_BRICK = 'ChangeTempoByNBrick';
  public const SET_TEMPO_BRICK = 'SetTempoBrick';
  public const PAUSE_FOR_BEATS_BRICK = 'PauseForBeatsBrick';
  public const PLAY_DRUM_FOR_BEATS_BRICK = 'PlayDrumForBeatsBrick';
  public const PLAY_NOTE_FOR_BEATS_BRICK = 'PlayNoteForBeatsBrick';
  public const SET_LISTENING_LANGUAGE_BRICK = 'SetListeningLanguageBrick';

  // --- Control ---
  public const WAIT_BRICK = 'WaitBrick';
  public const NOTE_BRICK = 'NoteBrick';
  public const FOREVER_BRICK = 'ForeverBrick';
  public const IF_BRICK = 'IfLogicBeginBrick';
  public const IF_THEN_BRICK = 'IfThenLogicBeginBrick';
  public const WAIT_UNTIL_BRICK = 'WaitUntilBrick';
  public const REPEAT_BRICK = 'RepeatBrick';
  public const REPEAT_UNTIL_BRICK = 'RepeatUntilBrick';
  public const CONTINUE_SCENE_BRICK = 'SceneTransitionBrick';
  public const SCENE_START_BRICK = 'SceneStartBrick';
  public const STOP_SCRIPT_BRICK = 'StopScriptBrick';
  public const CLONE_BRICK = 'CloneBrick';
  public const DELETE_THIS_CLONE_BRICK = 'DeleteThisCloneBrick';
  public const EXIT_STAGE_BRICK = 'ExitStageBrick';
  public const SET_INSTRUMENT_BRICK = 'SetInstrumentBrick';

  // auto generated control blocks
  public const ELSE_BRICK = 'IfLogicElseBrick';
  public const ENDIF_BRICK = 'IfLogicEndBrick';
  public const ENDIF_THEN_BRICK = 'IfThenLogicEndBrick';
  public const LOOP_END_BRICK = 'LoopEndBrick';

  // --- Data ---
  public const SET_VARIABLE_BRICK = 'SetVariableBrick';
  public const CHANGE_VARIABLE_BRICK = 'ChangeVariableBrick';
  public const SHOW_TEXT_BRICK = 'ShowTextBrick';
  public const SHOW_TEXT_COLOR_SIZE_ALIGNMENT_BRICK = 'ShowTextColorSizeAlignmentBrick';
  public const HIDE_TEXT_BRICK = 'HideTextBrick';
  public const USER_VARIABLE_BRICK = 'UserVariableBrick';
  public const WRITE_VARIABLE_ON_DEVICE_BRICK = 'WriteVariableOnDeviceBrick';
  public const READ_VARIABLE_FROM_DEVICE_BRICK = 'ReadVariableFromDeviceBrick';
  public const USER_LIST_BRICK = 'UserListBrick';
  public const ADD_ITEM_LIST_BRICK = 'AddItemToUserListBrick';
  public const DELETE_ITEM_LIST_BRICK = 'DeleteItemOfUserListBrick';
  public const INSERT_ITEM_LIST_BRICK = 'InsertItemIntoUserListBrick';
  public const REPLACE_ITEM_LIST_BRICK = 'ReplaceItemInUserListBrick';
  public const CLEAR_LIST_BRICK = 'ClearUserListBrick';
  public const WRITE_LIST_ON_DEVICE_BRICK = 'WriteListOnDeviceBrick';
  public const READ_LIST_FROM_DEVICE_BRICK = 'ReadListFromDeviceBrick';
  public const STORE_CSV_INTO_USERLIST_BRICK = 'StoreCSVIntoUserListBrick';
  public const WEB_REQUEST_BRICK = 'WebRequestBrick';
  public const FOR_VARIABLE_FROM_TO_BRICK = 'ForVariableFromToBrick';
  public const READ_VARIABLE_FROM_FILE_BRICK = 'ReadVariableFromFileBrick';
  public const WRITE_VARIABLE_TO_FILE_BRICK = 'WriteVariableToFileBrick';
  public const PARAMETERIZED_BRICK = 'ParameterizedBrick';
  public const PARAMETERIZED_END_BRICK = 'ParameterizedEndBrick';
  public const FOR_ITEM_IN_USER_LIST_BRICK = 'ForItemInUserListBrick';

  // --- Device ---
  public const OPEN_URL_BRICK = 'OpenUrlBrick';
  public const RESET_TIMER_BRICK = 'ResetTimerBrick';
  public const TOUCH_AND_SLIDE_BRICK = 'TouchAndSlideBrick';

  // Your Bricks
  public const USER_DEFINED_BRICK = 'UserDefinedBrick';
  public const USER_DEFINED_RECEIVER_BRICK = 'UserDefinedReceiverBrick';

  // Your Scripts
  public const USER_DEFINED_SCRIPT = 'UserDefinedScript';

  // Embroidery
  public const STITCH_BRICK = 'StitchBrick';
  public const RUNNING_STITCH_BRICK = 'RunningStitchBrick';
  public const STOP_RUNNING_STITCH_BRICK = 'StopRunningStitchBrick';
  public const TRIPLE_STITCH_BRICK = 'TripleStitchBrick';
  public const ZIG_ZAG_STITCH_BRICK = 'ZigZagStitchBrick';
  public const SEW_UP_BRICK = 'SewUpBrick';
  public const WRITE_EMBROIDERY_TO_FILE_BRICK = 'WriteEmbroideryToFileBrick';

  // --- Lego NXT ---
  public const LEGO_NXT_MOTOR_TURN_ANGLE_BRICK = 'LegoNxtMotorTurnAngleBrick';
  public const LEGO_NXT_MOTOR_STOP_BRICK = 'LegoNxtMotorStopBrick';
  public const LEGO_NXT_MOTOR_MOVE_BRICK = 'LegoNxtMotorMoveBrick';
  public const LEGO_NXT_PLAY_TONE_BRICK = 'LegoNxtPlayToneBrick';

  // --- Lego EV3 ---
  public const LEGO_EV3_MOTOR_TURN_ANGLE_BRICK = 'LegoEv3MotorTurnAngleBrick';
  public const LEGO_EV3_MOTOR_MOVE_BRICK = 'LegoEv3MotorMoveBrick';
  public const LEGO_EV3_MOTOR_STOP_BRICK = 'LegoEv3MotorStopBrick';
  public const LEGO_EV3_MOTOR_PLAY_TONE_BRICK = 'LegoEv3PlayToneBrick';
  public const LEGO_EV3_SET_LED_BRICK = 'LegoEv3SetLedBrick';

  // --- Ar Drone Bricks ---
  public const AR_DRONE_TAKE_OFF_LAND_BRICK = 'DroneTakeOffLandBrick';
  public const AR_DRONE_EMERGENCY_BRICK = 'DroneEmergencyBrick';
  public const AR_DRONE_MOVE_UP_BRICK = 'DroneMoveUpBrick';
  public const AR_DRONE_MOVE_DOWN_BRICK = 'DroneMoveDownBrick';
  public const AR_DRONE_MOVE_LEFT_BRICK = 'DroneMoveLeftBrick';
  public const AR_DRONE_MOVE_RIGHT_BRICK = 'DroneMoveRightBrick';
  public const AR_DRONE_MOVE_FORWARD_BRICK = 'DroneMoveForwardBrick';
  public const AR_DRONE_MOVE_BACKWARD_BRICK = 'DroneMoveBackwardBrick';
  public const AR_DRONE_TURN_LEFT_BRICK = 'DroneTurnLeftBrick';
  public const AR_DRONE_TURN_RIGHT_BRICK = 'DroneTurnRightBrick';
  public const AR_DRONE_FLIP_BRICK = 'DroneFlipBrick';
  public const AR_DRONE_PLAYED_ANIMATION_BRICK = 'DronePlayLedAnimationBrick';
  public const AR_DRONE_SWITCH_CAMERA_BRICK = 'DroneSwitchCameraBrick';

  // --- Jump Sumo ---
  public const JUMP_SUMO_MOVE_FORWARD_BRICK = 'JumpingSumoMoveForwardBrick';
  public const JUMP_SUMO_MOVE_BACKWARD_BRICK = 'JumpingSumoMoveBackwardBrick';
  public const JUMP_SUMO_ANIMATIONS_BRICK = 'JumpingSumoAnimationsBrick';
  public const JUMP_SUMO_SOUND_BRICK = 'JumpingSumoSoundBrick';
  public const JUMP_SUMO_NO_SOUND_BRICK = 'JumpingSumoNoSoundBrick';
  public const JUMP_SUMO_JUMP_LONG_BRICK = 'JumpingSumoJumpLongBrick';
  public const JUMP_SUMO_JUMP_HIGH_BRICK = 'JumpingSumoJumpHighBrick';
  public const JUMP_SUMO_ROTATE_LEFT_BRICK = 'JumpingSumoRotateLeftBrick';
  public const JUMP_SUMO_ROTATE_RIGHT_BRICK = 'JumpingSumoRotateRightBrick';
  public const JUMP_SUMO_TURN_BRICK = 'JumpingSumoTurnBrick';
  public const JUMP_SUMO_TAKING_PICTURE_BRICK = 'JumpingSumoTakingPictureBrick';

  // --- Phiro ---
  public const PHIRO_MOTOR_MOVE_FORWARD_BRICK = 'PhiroMotorMoveForwardBrick';
  public const PHIRO_MOTOR_MOVE_BACKWARD_BRICK = 'PhiroMotorMoveBackwardBrick';
  public const PHIRO_MOTOR_STOP_BRICK = 'PhiroMotorStopBrick';
  public const PHIRO_PLAY_TONE_BRICK = 'PhiroPlayToneBrick';
  public const PHIRO_RGB_LIGHT_BRICK = 'PhiroRGBLightBrick';
  public const PHIRO_IF_LOGIC_BEGIN_BRICK = 'PhiroIfLogicBeginBrick';

  // --- Arduino ---
  public const ARDUINO_SEND_DIGITAL_VALUE_BRICK = 'ArduinoSendDigitalValueBrick';
  public const ARDUINO_SEND_PMW_VALUE_BRICK = 'ArduinoSendPWMValueBrick';

  // --- Chromecast ---
  public const WHEN_GAME_PAD_BUTTON_SCRIPT = 'WhenGamepadButtonScript';
  public const WHEN_GAME_PAD_BUTTON_BRICK = 'WhenGamepadButtonBrick';

  // --- Raspberry Pi ---
  public const WHEN_RASPI_PIN_CHANGED_BRICK = 'WhenRaspiPinChangedBrick';
  public const WHEN_RASPI_PIN_CHANGED_SCRIPT = 'RaspiInterruptScript';
  public const RASPI_IF_LOGIC_BEGIN_BRICK = 'RaspiIfLogicBeginBrick';
  public const RASPI_SEND_DIGITAL_VALUE_BRICK = 'RaspiSendDigitalValueBrick';
  public const RASPI_PWM_BRICK = 'RaspiPwmBrick';

  // --- NFC ---
  public const WHEN_NFC_SCRIPT = 'WhenNfcScript';
  public const WHEN_NFC_BRICK = 'WhenNfcBrick';
  public const SET_NFC_TAG_BRICK = 'SetNfcTagBrick';

  // --- Testing
  public const ASSERT_EQUALS_BRICK = 'AssertEqualsBrick';
  public const WAIT_TILL_IDLE_BRICK = 'WaitTillIdleBrick';
  public const TAP_AT_BRICK = 'TapAtBrick';
  public const TAP_FOR_BRICK = 'TapForBrick';
  public const FINISH_STAGE_BRICK = 'FinishStageBrick';
  public const ASSERT_USER_LISTS_BRICK = 'AssertUserListsBrick';

  // --- Deprecated old bricks - to still provide old projects with correct statistics
  //                             even when the app is not using those bricks anymore
  public const COLLISION_SCRIPT = 'CollisionScript';
  public const LOOP_ENDLESS_BRICK = 'LoopEndlessBrick';

  // -------------------------------------------------------------------------------------------------------------------
  // Brick/Script Images -> needed for code view + to decide which brick belongs to which group
  //
  public const EVENT_SCRIPT_IMG = '1h_when_brown.png';
  public const EVENT_BRICK_IMG = '1h_brick_brown.png';
  public const CONTROL_SCRIPT_IMG = '1h_when_orange.png';
  public const CONTROL_BRICK_IMG = '1h_brick_orange.png';
  public const MOTION_BRICK_IMG = '1h_brick_blue.png';
  public const MOTION_SCRIPT_IMG = '1h_when_blue.png';
  public const SOUND_BRICK_IMG = '1h_brick_violet.png';
  public const LOOKS_BRICK_IMG = '1h_brick_green.png';
  public const DATA_BRICK_IMG = '1h_brick_red.png';
  public const PEN_BRICK_IMG = '1h_brick_darkgreen.png';
  public const DEVICE_BRICK_IMG = '1h_brick_gold.png';

  public const UNKNOWN_SCRIPT_IMG = '1h_when_grey.png';
  public const UNKNOWN_BRICK_IMG = '1h_brick_grey.png';
  public const DEPRECATED_SCRIPT_IMG = '1h_when_grey.png';
  public const DEPRECATED_BRICK_IMG = '1h_brick_grey.png';

  public const LEGO_EV3_BRICK_IMG = '1h_brick_yellow.png';
  public const LEGO_NXT_BRICK_IMG = '1h_brick_yellow.png';

  public const ARDUINO_BRICK_IMG = '1h_brick_light_blue.png';

  public const EMBROIDERY_BRICK_IMG = '1h_brick_pink.png';

  public const JUMPING_SUMO_BRICK_IMG = self::MOTION_BRICK_IMG;

  public const AR_DRONE_MOTION_BRICK_IMG = self::MOTION_BRICK_IMG;
  public const AR_DRONE_LOOKS_BRICK_IMG = self::LOOKS_BRICK_IMG;

  public const RASPI_BRICK_IMG = '1h_brick_light_blue.png';
  public const RASPI_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;
  public const RASPI_EVENT_SCRIPT_IMG = self::EVENT_SCRIPT_IMG;

  public const PHIRO_BRICK_IMG = '1h_brick_light_blue.png';
  public const PHIRO_SOUND_BRICK_IMG = self::SOUND_BRICK_IMG;
  public const PHIRO_LOOK_BRICK_IMG = self::LOOKS_BRICK_IMG;
  public const PHIRO_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;

  public const TESTING_BRICK_IMG = '1h_brick_light_blue.png';

  public const YOUR_BRICK_IMG = '1h_brick_light_blue.png';
  public const YOUR_SCRIPT_IMG = '1h_brick_light_blue.png';

  // -------------------------------------------------------------------------------------------------------------------
  // Formula Categories
  //
  public const TIME_TO_WAIT_IN_SECONDS_FORMULA = 'TIME_TO_WAIT_IN_SECONDS';
  public const NOTE_FORMULA = 'NOTE';
  public const IF_CONDITION_FORMULA = 'IF_CONDITION';
  public const TIMES_TO_REPEAT_FORMULA = 'TIMES_TO_REPEAT';
  public const X_POSITION_FORMULA = 'X_POSITION';
  public const Y_POSITION_FORMULA = 'Y_POSITION';
  public const X_POSITION_CHANGE_FORMULA = 'X_POSITION_CHANGE';
  public const Y_POSITION_CHANGE_FORMULA = 'Y_POSITION_CHANGE';
  public const STEPS_FORMUlA = 'STEPS';
  public const TURN_LEFT_DEGREES_FORMULA = 'TURN_LEFT_DEGREES';
  public const TURN_RIGHT_DEGREES_FORMULA = 'TURN_RIGHT_DEGREES';
  public const DEGREES_FORMULA = 'DEGREES';
  public const DURATION_IN_SECONDS_FORMULA = 'DURATION_IN_SECONDS';
  public const Y_DESTINATION_FORMUlA = 'Y_DESTINATION';
  public const X_DESTINATION_FORMULA = 'X_DESTINATION';
  public const VIBRATE_DURATION_IN_SECONDS_FORMULA = 'VIBRATE_DURATION_IN_SECONDS';
  public const VELOCITY_X_FORMULA = 'PHYSICS_VELOCITY_X';
  public const VELOCITY_Y_FORMULA = 'PHYSICS_VELOCITY_Y';
  public const TURN_LEFT_SPEED_FORMULA = 'PHYSICS_TURN_LEFT_SPEED';
  public const TURN_RIGHT_SPEED_FORMULA = 'PHYSICS_TURN_RIGHT_SPEED';
  public const GRAVITY_Y_FORMULA = 'PHYSICS_GRAVITY_Y';
  public const GRAVITY_X_FORMULA = 'PHYSICS_GRAVITY_X';
  public const MASS_FORMULA = 'PHYSICS_MASS';
  public const BOUNCE_FACTOR_FORMULA = 'PHYSICS_BOUNCE_FACTOR';
  public const FRICTION_FORMULA = 'PHYSICS_FRICTION';
  public const VOLUME_FORMUlA = 'VOLUME';
  public const VOLUME_CHANGE_FORMULA = 'VOLUME_CHANGE';
  public const SPEAK_FORMULA = 'SPEAK';
  public const SIZE_FORMULA = 'SIZE';
  public const SIZE_CHANGE_FORMULA = 'SIZE_CHANGE';
  public const TRANSPARENCY_FORMULA = 'TRANSPARENCY';
  public const TRANSPARENCY_CHANGE_FORMULA = 'TRANSPARENCY_CHANGE';
  public const BRIGHTNESS_FORMULA = 'BRIGHTNESS';
  public const BRIGHTNESS_CHANGE_FORMULA = 'BRIGHTNESS_CHANGE';
  public const COLOR_FORMUlA = 'COLOR';
  public const COLOR_CHANGE_FORMULA = 'COLOR_CHANGE';
  public const VARIABLE_FORMULA = 'VARIABLE';
  public const VARIABLE_CHANGE_FORMULA = 'VARIABLE_CHANGE';
  public const LIST_ADD_ITEM_FORMULA = 'LIST_ADD_ITEM';
  public const LIST_DELETE_ITEM_FORMULA = 'LIST_DELETE_ITEM';
  public const INSERT_ITEM_LIST_VALUE_FORMULA = 'INSERT_ITEM_INTO_USERLIST_VALUE';
  public const INSERT_ITEM_LIST_INDEX_FORMULA = 'INSERT_ITEM_INTO_USERLIST_INDEX';
  public const REPLACE_ITEM_LIST_VALUE_FORMULA = 'REPLACE_ITEM_IN_USERLIST_VALUE';
  public const REPLACE_ITEM_LIST_INDEX_FORMULA = 'REPLACE_ITEM_IN_USERLIST_INDEX';
  public const STORE_CSV_INTO_LIST_COLUMN_FORMULA = 'STORE_CSV_INTO_USERLIST_COLUMN';
  public const STORE_CSV_INTO_LIST_CSV_FORMULA = 'STORE_CSV_INTO_USERLIST_CSV';
  public const REPEAT_UNTIL_CONDITION_FORMULA = 'REPEAT_UNTIL_CONDITION';
  public const ASK_QUESTION_FORMULA = 'ASK_QUESTION';
  public const ASK_SPEECH_QUESTION_FORMULA = 'ASK_SPEECH_QUESTION';
  public const STRING_FORMULA = 'STRING';
  public const PEN_SIZE_FORMULA = 'PEN_SIZE';
  public const PEN_COLOR_RED_FORMULA = 'PHIRO_LIGHT_RED';
  public const PEN_COLOR_BLUE_FORMULA = 'PHIRO_LIGHT_BLUE';
  public const PEN_COLOR_GREEN_FORMULA = 'PHIRO_LIGHT_GREEN';
  public const PEN_COLOR_RED_NEW_FORMULA = 'PEN_COLOR_RED';
  public const PEN_COLOR_BLUE_NEW_FORMULA = 'PEN_COLOR_BLUE';
  public const PEN_COLOR_GREEN_NEW_FORMULA = 'PEN_COLOR_GREEN';
  public const LEGO_EV3_POWER_FORMULA = 'LEGO_EV3_POWER';
  public const LEGO_EV3_PERIOD_IN_SECONDS_FORMULA = 'LEGO_EV3_PERIOD_IN_SECONDS';
  public const LEGO_EV3_DURATION_IN_SECONDS_FORMULA = 'LEGO_EV3_DURATION_IN_SECONDS';
  public const LEGO_EV3_VOLUME_FORMULA = 'LEGO_EV3_VOLUME';
  public const LEGO_EV3_FREQUENCY_FORMULA = 'LEGO_EV3_FREQUENCY';
  public const LEGO_EV3_DEGREES_FORMULA = 'LEGO_EV3_DEGREES';
  public const BACKGROUND_REQUEST_FORMULA = 'BACKGROUND_REQUEST';
  public const LOOK_REQUEST_FORMULA = 'LOOK_REQUEST';

  // --- AR DRONE FORMULA ---
  public const AR_DRONE_TIME_TO_FLY_IN_SECONDS = 'DRONE_TIME_TO_FLY_IN_SECONDS';
  public const AR_DRONE_POWER_IN_PERCENT = 'DRONE_POWER_IN_PERCENT';

  // --- JUMP SUMO FORMULA ---
  public const JUMP_SUMO_SPEED = 'JUMPING_SUMO_SPEED';
  public const JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS = 'JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS';
  public const JUMPING_SUMO_ROTATE = 'JUMPING_SUMO_ROTATE';

  public const OPERATOR_FORMULA_TYPE = 'OPERATOR';
  public const FUNCTION_FORMULA_TYPE = 'FUNCTION';
  public const BRACKET_FORMULA_TYPE = 'BRACKET';

  public const PLUS_OPERATOR = 'PLUS';
  public const MINUS_OPERATOR = 'MINUS';
  public const MULT_OPERATOR = 'MULT';
  public const DIVIDE_OPERATOR = 'DIVIDE';
  public const EQUAL_OPERATOR = 'EQUAL';
  public const NOT_EQUAL_OPERATOR = 'NOT_EQUAL';
  public const GREATER_OPERATOR = 'GREATER_THAN';
  public const GREATER_EQUAL_OPERATOR = 'GREATER_OR_EQUAL';
  public const SMALLER_OPERATOR = 'SMALLER_THAN';
  public const SMALLER_EQUAL_OPERATOR = 'SMALLER_OR_EQUAL';
  public const NOT_OPERATOR = 'LOGICAL_NOT';
  public const AND_OPERATOR = 'LOGICAL_AND';
  public const OR_OPERATOR = 'LOGICAL_OR';

  public const POINTED_OBJECT_TAG = 'pointedObject';
  public const LOOK_INDEX = 'LOOK_INDEX';
}
