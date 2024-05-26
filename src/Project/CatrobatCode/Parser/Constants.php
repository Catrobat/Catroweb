<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

class Constants
{
  // Attributes
  final public const string TYPE_ATTRIBUTE = 'type';

  final public const string REFERENCE_ATTRIBUTE = 'reference';

  final public const string NAME_ATTRIBUTE = 'name';

  final public const string CATEGORY_ATTRIBUTE = 'category';

  // Object types
  final public const string SINGLE_SPRITE_TYPE = 'SingleSprite';

  final public const string GROUP_SPRITE_TYPE = 'GroupSprite';

  final public const string GROUP_ITEM_SPRITE_TYPE = 'GroupItemSprite';

  // Bricks & Scripts
  final public const string UNKNOWN_SCRIPT = 'UnknownScript';

  final public const string UNKNOWN_BRICK = 'UnknownBrick';

  // Motion
  final public const string PLACE_AT_BRICK = 'PlaceAtBrick';

  final public const string SET_X_BRICK = 'SetXBrick';

  final public const string SET_Y_BRICK = 'SetYBrick';

  final public const string GO_TO_BRICK = 'GoToBrick';

  final public const string CHANGE_X_BY_N_BRICK = 'ChangeXByNBrick';

  final public const string CHANGE_Y_BY_N_BRICK = 'ChangeYByNBrick';

  final public const string IF_ON_EDGE_BOUNCE_BRICK = 'IfOnEdgeBounceBrick';

  final public const string MOVE_N_STEPS_BRICK = 'MoveNStepsBrick';

  final public const string TURN_LEFT_BRICK = 'TurnLeftBrick';

  final public const string TURN_RIGHT_BRICK = 'TurnRightBrick';

  final public const string POINT_IN_DIRECTION_BRICK = 'PointInDirectionBrick';

  final public const string POINT_TO_BRICK = 'PointToBrick';

  final public const string SET_ROTATION_STYLE_BRICK = 'SetRotationStyleBrick';

  final public const string GLIDE_TO_BRICK = 'GlideToBrick';

  final public const string GO_N_STEPS_BACK_BRICK = 'GoNStepsBackBrick';

  final public const string COME_TO_FRONT_BRICK = 'ComeToFrontBrick';

  final public const string VIBRATION_BRICK = 'VibrationBrick';

  final public const string SET_PHYSICS_OBJECT_TYPE_BRICK = 'SetPhysicsObjectTypeBrick';

  final public const string SET_VELOCITY_BRICK = 'SetVelocityBrick';

  final public const string TURN_LEFT_SPEED_BRICK = 'TurnLeftSpeedBrick';

  final public const string TURN_RIGHT_SPEED_BRICK = 'TurnRightSpeedBrick';

  final public const string SET_GRAVITY_BRICK = 'SetGravityBrick';

  final public const string SET_MASS_BRICK = 'SetMassBrick';

  final public const string SET_BOUNCE_BRICK = 'SetBounceBrick';

  final public const string SET_FRICTION_BRICK = 'SetFrictionBrick';

  final public const string SET_TEXT_BRICK = 'SetTextBrick';

  // Event
  final public const string START_SCRIPT = 'StartScript';

  final public const string WHEN_STARTED_BRICK = 'WhenStartedBrick';

  final public const string WHEN_SCRIPT = 'WhenScript';

  final public const string WHEN_BG_CHANGE_SCRIPT = 'WhenBackgroundChangesScript';

  final public const string WHEN_BG_CHANGE_BRICK = 'WhenBackgroundChangesBrick';

  final public const string WHEN_CLONED_SCRIPT = 'WhenClonedScript';

  final public const string WHEN_CLONED_BRICK = 'WhenClonedBrick';

  final public const string WHEN_TOUCH_SCRIPT = 'WhenTouchDownScript';

  final public const string WHEN_TOUCH_BRICK = 'WhenTouchDownBrick';

  final public const string WHEN_CONDITION_SCRIPT = 'WhenConditionScript';

  final public const string WHEN_CONDITION_BRICK = 'WhenConditionBrick';

  final public const string BROADCAST_SCRIPT = 'BroadcastScript';

  final public const string BROADCAST_BRICK = 'BroadcastBrick';

  final public const string BROADCAST_WAIT_BRICK = 'BroadcastWaitBrick';

  final public const string BROADCAST_RECEIVER_BRICK = 'BroadcastReceiverBrick';

  final public const string WHEN_BRICK = 'WhenBrick';

  final public const string WHEN_BOUNCE_OFF_SCRIPT = 'WhenBounceOffScript';

  final public const string WHEN_BOUNCE_OFF_BRICK = 'WhenBounceOffBrick';

  // --- Looks ---
  final public const string SET_LOOK_BRICK = 'SetLookBrick';

  final public const string SET_LOOK_BY_INDEX_BRICK = 'SetLookByIndexBrick';

  final public const string NEXT_LOOK_BRICK = 'NextLookBrick';

  final public const string PREV_LOOK_BRICK = 'PreviousLookBrick';

  final public const string SET_SIZE_TO_BRICK = 'SetSizeToBrick';

  final public const string CHANGE_SIZE_BY_N_BRICK = 'ChangeSizeByNBrick';

  final public const string HIDE_BRICK = 'HideBrick';

  final public const string SHOW_BRICK = 'ShowBrick';

  final public const string ASK_BRICK = 'AskBrick';

  final public const string SAY_BUBBLE_BRICK = 'SayBubbleBrick';

  final public const string SAY_FOR_BUBBLE_BRICK = 'SayForBubbleBrick';

  final public const string THINK_BUBBLE_BRICK = 'ThinkBubbleBrick';

  final public const string THINK_FOR_BUBBLE_BRICK = 'ThinkForBubbleBrick';

  final public const string SET_TRANSPARENCY_BRICK = 'SetTransparencyBrick';

  final public const string CHANGE_TRANSPARENCY_BY_N_BRICK = 'ChangeTransparencyByNBrick';

  final public const string SET_BRIGHTNESS_BRICK = 'SetBrightnessBrick';

  final public const string CHANGE_BRIGHTNESS_BY_N_BRICK = 'ChangeBrightnessByNBrick';

  final public const string SET_COLOR_BRICK = 'SetColorBrick';

  final public const string CHANGE_COLOR_BY_N_BRICK = 'ChangeColorByNBrick';

  final public const string CLEAR_GRAPHIC_EFFECT_BRICK = 'ClearGraphicEffectBrick';

  final public const string SET_BACKGROUND_BRICK = 'SetBackgroundBrick';

  final public const string SET_BACKGROUND_BY_INDEX_BRICK = 'SetBackgroundByIndexBrick';

  final public const string SET_BACKGROUND_WAIT_BRICK = 'SetBackgroundAndWaitBrick';

  final public const string SET_BACKGROUND_BY_INDEX_AND_WAIT_BRICK = 'SetBackgroundByIndexAndWaitBrick';

  final public const string CAMERA_BRICK = 'CameraBrick';

  final public const string CHOOSE_CAMERA_BRICK = 'ChooseCameraBrick';

  final public const string FLASH_BRICK = 'FlashBrick';

  final public const string BACKGROUND_REQUEST_BRICK = 'BackgroundRequestBrick';

  final public const string LOOK_REQUEST_BRICK = 'LookRequestBrick';

  final public const string COPY_LOOK_BRICK = 'CopyLookBrick';

  final public const string DELETE_LOOK_BRICK = 'DeleteLookBrick';

  final public const string EDIT_LOOK_BRICK = 'EditLookBrick';

  final public const string PAINT_NEW_LOOK_BRICK = 'PaintNewLookBrick';

  // --- Pen ---
  final public const string PEN_DOWN_BRICK = 'PenDownBrick';

  final public const string PEN_UP_BRICK = 'PenUpBrick';

  final public const string SET_PEN_SIZE_BRICK = 'SetPenSizeBrick';

  final public const string SET_PEN_COLOR_BRICK = 'SetPenColorBrick';

  final public const string STAMP_BRICK = 'StampBrick';

  final public const string CLEAR_BACKGROUND_BRICK = 'ClearBackgroundBrick';

  // --- Sound ---
  final public const string PLAY_SOUND_BRICK = 'PlaySoundBrick';

  final public const string PLAY_SOUND_WAIT_BRICK = 'PlaySoundAndWaitBrick';

  final public const string STOP_ALL_SOUNDS_BRICK = 'StopAllSoundsBrick';

  final public const string SET_VOLUME_TO_BRICK = 'SetVolumeToBrick';

  final public const string CHANGE_VOLUME_BY_N_BRICK = 'ChangeVolumeByNBrick';

  final public const string SPEAK_BRICK = 'SpeakBrick';

  final public const string SPEAK_WAIT_BRICK = 'SpeakAndWaitBrick';

  final public const string ASK_SPEECH_BRICK = 'AskSpeechBrick';

  final public const string STOP_SOUND_BRICK = 'StopSoundBrick';

  final public const string START_LISTENING_BRICK = 'StartListeningBrick';

  final public const string CHANGE_TEMPO_BY_N_BRICK = 'ChangeTempoByNBrick';

  final public const string SET_TEMPO_BRICK = 'SetTempoBrick';

  final public const string PAUSE_FOR_BEATS_BRICK = 'PauseForBeatsBrick';

  final public const string PLAY_DRUM_FOR_BEATS_BRICK = 'PlayDrumForBeatsBrick';

  final public const string PLAY_NOTE_FOR_BEATS_BRICK = 'PlayNoteForBeatsBrick';

  final public const string SET_LISTENING_LANGUAGE_BRICK = 'SetListeningLanguageBrick';

  // --- Control ---
  final public const string WAIT_BRICK = 'WaitBrick';

  final public const string NOTE_BRICK = 'NoteBrick';

  final public const string FOREVER_BRICK = 'ForeverBrick';

  final public const string IF_BRICK = 'IfLogicBeginBrick';

  final public const string IF_THEN_BRICK = 'IfThenLogicBeginBrick';

  final public const string WAIT_UNTIL_BRICK = 'WaitUntilBrick';

  final public const string REPEAT_BRICK = 'RepeatBrick';

  final public const string REPEAT_UNTIL_BRICK = 'RepeatUntilBrick';

  final public const string CONTINUE_SCENE_BRICK = 'SceneTransitionBrick';

  final public const string SCENE_START_BRICK = 'SceneStartBrick';

  final public const string STOP_SCRIPT_BRICK = 'StopScriptBrick';

  final public const string CLONE_BRICK = 'CloneBrick';

  final public const string DELETE_THIS_CLONE_BRICK = 'DeleteThisCloneBrick';

  final public const string EXIT_STAGE_BRICK = 'ExitStageBrick';

  final public const string SET_INSTRUMENT_BRICK = 'SetInstrumentBrick';

  // auto generated control blocks
  final public const string ELSE_BRICK = 'IfLogicElseBrick';

  final public const string ENDIF_BRICK = 'IfLogicEndBrick';

  final public const string ENDIF_THEN_BRICK = 'IfThenLogicEndBrick';

  final public const string LOOP_END_BRICK = 'LoopEndBrick';

  // --- Data ---
  final public const string SET_VARIABLE_BRICK = 'SetVariableBrick';

  final public const string CHANGE_VARIABLE_BRICK = 'ChangeVariableBrick';

  final public const string SHOW_TEXT_BRICK = 'ShowTextBrick';

  final public const string SHOW_TEXT_COLOR_SIZE_ALIGNMENT_BRICK = 'ShowTextColorSizeAlignmentBrick';

  final public const string HIDE_TEXT_BRICK = 'HideTextBrick';

  final public const string USER_VARIABLE_BRICK = 'UserVariableBrick';

  final public const string WRITE_VARIABLE_ON_DEVICE_BRICK = 'WriteVariableOnDeviceBrick';

  final public const string READ_VARIABLE_FROM_DEVICE_BRICK = 'ReadVariableFromDeviceBrick';

  final public const string USER_LIST_BRICK = 'UserListBrick';

  final public const string ADD_ITEM_LIST_BRICK = 'AddItemToUserListBrick';

  final public const string DELETE_ITEM_LIST_BRICK = 'DeleteItemOfUserListBrick';

  final public const string INSERT_ITEM_LIST_BRICK = 'InsertItemIntoUserListBrick';

  final public const string REPLACE_ITEM_LIST_BRICK = 'ReplaceItemInUserListBrick';

  final public const string CLEAR_LIST_BRICK = 'ClearUserListBrick';

  final public const string WRITE_LIST_ON_DEVICE_BRICK = 'WriteListOnDeviceBrick';

  final public const string READ_LIST_FROM_DEVICE_BRICK = 'ReadListFromDeviceBrick';

  final public const string STORE_CSV_INTO_USERLIST_BRICK = 'StoreCSVIntoUserListBrick';

  final public const string WEB_REQUEST_BRICK = 'WebRequestBrick';

  final public const string FOR_VARIABLE_FROM_TO_BRICK = 'ForVariableFromToBrick';

  final public const string READ_VARIABLE_FROM_FILE_BRICK = 'ReadVariableFromFileBrick';

  final public const string WRITE_VARIABLE_TO_FILE_BRICK = 'WriteVariableToFileBrick';

  final public const string PARAMETERIZED_BRICK = 'ParameterizedBrick';

  final public const string PARAMETERIZED_END_BRICK = 'ParameterizedEndBrick';

  final public const string FOR_ITEM_IN_USER_LIST_BRICK = 'ForItemInUserListBrick';

  // --- Device ---
  final public const string OPEN_URL_BRICK = 'OpenUrlBrick';

  final public const string RESET_TIMER_BRICK = 'ResetTimerBrick';

  final public const string TOUCH_AND_SLIDE_BRICK = 'TouchAndSlideBrick';

  // Your Bricks
  final public const string USER_DEFINED_BRICK = 'UserDefinedBrick';

  final public const string USER_DEFINED_RECEIVER_BRICK = 'UserDefinedReceiverBrick';

  // Your Scripts
  final public const string USER_DEFINED_SCRIPT = 'UserDefinedScript';

  // Embroidery
  final public const string STITCH_BRICK = 'StitchBrick';

  final public const string RUNNING_STITCH_BRICK = 'RunningStitchBrick';

  final public const string STOP_RUNNING_STITCH_BRICK = 'StopRunningStitchBrick';

  final public const string TRIPLE_STITCH_BRICK = 'TripleStitchBrick';

  final public const string ZIG_ZAG_STITCH_BRICK = 'ZigZagStitchBrick';

  final public const string SEW_UP_BRICK = 'SewUpBrick';

  final public const string WRITE_EMBROIDERY_TO_FILE_BRICK = 'WriteEmbroideryToFileBrick';

  // --- Lego NXT ---
  final public const string LEGO_NXT_MOTOR_TURN_ANGLE_BRICK = 'LegoNxtMotorTurnAngleBrick';

  final public const string LEGO_NXT_MOTOR_STOP_BRICK = 'LegoNxtMotorStopBrick';

  final public const string LEGO_NXT_MOTOR_MOVE_BRICK = 'LegoNxtMotorMoveBrick';

  final public const string LEGO_NXT_PLAY_TONE_BRICK = 'LegoNxtPlayToneBrick';

  // --- Lego EV3 ---
  final public const string LEGO_EV3_MOTOR_TURN_ANGLE_BRICK = 'LegoEv3MotorTurnAngleBrick';

  final public const string LEGO_EV3_MOTOR_MOVE_BRICK = 'LegoEv3MotorMoveBrick';

  final public const string LEGO_EV3_MOTOR_STOP_BRICK = 'LegoEv3MotorStopBrick';

  final public const string LEGO_EV3_MOTOR_PLAY_TONE_BRICK = 'LegoEv3PlayToneBrick';

  final public const string LEGO_EV3_SET_LED_BRICK = 'LegoEv3SetLedBrick';

  // --- Ar Drone Bricks ---
  final public const string AR_DRONE_TAKE_OFF_LAND_BRICK = 'DroneTakeOffLandBrick';

  final public const string AR_DRONE_EMERGENCY_BRICK = 'DroneEmergencyBrick';

  final public const string AR_DRONE_MOVE_UP_BRICK = 'DroneMoveUpBrick';

  final public const string AR_DRONE_MOVE_DOWN_BRICK = 'DroneMoveDownBrick';

  final public const string AR_DRONE_MOVE_LEFT_BRICK = 'DroneMoveLeftBrick';

  final public const string AR_DRONE_MOVE_RIGHT_BRICK = 'DroneMoveRightBrick';

  final public const string AR_DRONE_MOVE_FORWARD_BRICK = 'DroneMoveForwardBrick';

  final public const string AR_DRONE_MOVE_BACKWARD_BRICK = 'DroneMoveBackwardBrick';

  final public const string AR_DRONE_TURN_LEFT_BRICK = 'DroneTurnLeftBrick';

  final public const string AR_DRONE_TURN_RIGHT_BRICK = 'DroneTurnRightBrick';

  final public const string AR_DRONE_FLIP_BRICK = 'DroneFlipBrick';

  final public const string AR_DRONE_PLAYED_ANIMATION_BRICK = 'DronePlayLedAnimationBrick';

  final public const string AR_DRONE_SWITCH_CAMERA_BRICK = 'DroneSwitchCameraBrick';

  // --- Jump Sumo ---
  final public const string JUMP_SUMO_MOVE_FORWARD_BRICK = 'JumpingSumoMoveForwardBrick';

  final public const string JUMP_SUMO_MOVE_BACKWARD_BRICK = 'JumpingSumoMoveBackwardBrick';

  final public const string JUMP_SUMO_ANIMATIONS_BRICK = 'JumpingSumoAnimationsBrick';

  final public const string JUMP_SUMO_SOUND_BRICK = 'JumpingSumoSoundBrick';

  final public const string JUMP_SUMO_NO_SOUND_BRICK = 'JumpingSumoNoSoundBrick';

  final public const string JUMP_SUMO_JUMP_LONG_BRICK = 'JumpingSumoJumpLongBrick';

  final public const string JUMP_SUMO_JUMP_HIGH_BRICK = 'JumpingSumoJumpHighBrick';

  final public const string JUMP_SUMO_ROTATE_LEFT_BRICK = 'JumpingSumoRotateLeftBrick';

  final public const string JUMP_SUMO_ROTATE_RIGHT_BRICK = 'JumpingSumoRotateRightBrick';

  final public const string JUMP_SUMO_TURN_BRICK = 'JumpingSumoTurnBrick';

  final public const string JUMP_SUMO_TAKING_PICTURE_BRICK = 'JumpingSumoTakingPictureBrick';

  // --- Phiro ---
  final public const string PHIRO_MOTOR_MOVE_FORWARD_BRICK = 'PhiroMotorMoveForwardBrick';

  final public const string PHIRO_MOTOR_MOVE_BACKWARD_BRICK = 'PhiroMotorMoveBackwardBrick';

  final public const string PHIRO_MOTOR_STOP_BRICK = 'PhiroMotorStopBrick';

  final public const string PHIRO_PLAY_TONE_BRICK = 'PhiroPlayToneBrick';

  final public const string PHIRO_RGB_LIGHT_BRICK = 'PhiroRGBLightBrick';

  final public const string PHIRO_IF_LOGIC_BEGIN_BRICK = 'PhiroIfLogicBeginBrick';

  // --- Arduino ---
  final public const string ARDUINO_SEND_DIGITAL_VALUE_BRICK = 'ArduinoSendDigitalValueBrick';

  final public const string ARDUINO_SEND_PMW_VALUE_BRICK = 'ArduinoSendPWMValueBrick';

  // --- Chromecast ---
  final public const string WHEN_GAME_PAD_BUTTON_SCRIPT = 'WhenGamepadButtonScript';

  final public const string WHEN_GAME_PAD_BUTTON_BRICK = 'WhenGamepadButtonBrick';

  // --- Raspberry Pi ---
  final public const string WHEN_RASPI_PIN_CHANGED_BRICK = 'WhenRaspiPinChangedBrick';

  final public const string WHEN_RASPI_PIN_CHANGED_SCRIPT = 'RaspiInterruptScript';

  final public const string RASPI_IF_LOGIC_BEGIN_BRICK = 'RaspiIfLogicBeginBrick';

  final public const string RASPI_SEND_DIGITAL_VALUE_BRICK = 'RaspiSendDigitalValueBrick';

  final public const string RASPI_PWM_BRICK = 'RaspiPwmBrick';

  // --- NFC ---
  final public const string WHEN_NFC_SCRIPT = 'WhenNfcScript';

  final public const string WHEN_NFC_BRICK = 'WhenNfcBrick';

  final public const string SET_NFC_TAG_BRICK = 'SetNfcTagBrick';

  // --- Testing
  final public const string ASSERT_EQUALS_BRICK = 'AssertEqualsBrick';

  final public const string WAIT_TILL_IDLE_BRICK = 'WaitTillIdleBrick';

  final public const string TAP_AT_BRICK = 'TapAtBrick';

  final public const string TAP_FOR_BRICK = 'TapForBrick';

  final public const string FINISH_STAGE_BRICK = 'FinishStageBrick';

  final public const string ASSERT_USER_LISTS_BRICK = 'AssertUserListsBrick';

  // --- Deprecated old bricks - to still provide old projects with correct statistics
  //                             even when the app is not using those bricks anymore
  final public const string COLLISION_SCRIPT = 'CollisionScript';

  final public const string LOOP_ENDLESS_BRICK = 'LoopEndlessBrick';

  // -------------------------------------------------------------------------------------------------------------------
  // Brick/Script Images -> needed for code view + to decide which brick belongs to which group
  //
  final public const string EVENT_SCRIPT_IMG = '1h_when_brown.png';

  final public const string EVENT_BRICK_IMG = '1h_brick_brown.png';

  final public const string CONTROL_SCRIPT_IMG = '1h_when_orange.png';

  final public const string CONTROL_BRICK_IMG = '1h_brick_orange.png';

  final public const string MOTION_BRICK_IMG = '1h_brick_blue.png';

  final public const string MOTION_SCRIPT_IMG = '1h_when_blue.png';

  final public const string SOUND_BRICK_IMG = '1h_brick_violet.png';

  final public const string LOOKS_BRICK_IMG = '1h_brick_green.png';

  final public const string DATA_BRICK_IMG = '1h_brick_red.png';

  final public const string PEN_BRICK_IMG = '1h_brick_darkgreen.png';

  final public const string DEVICE_BRICK_IMG = '1h_brick_gold.png';

  final public const string UNKNOWN_SCRIPT_IMG = '1h_when_grey.png';

  final public const string UNKNOWN_BRICK_IMG = '1h_brick_grey.png';

  final public const string DEPRECATED_SCRIPT_IMG = '1h_when_grey.png';

  final public const string DEPRECATED_BRICK_IMG = '1h_brick_grey.png';

  final public const string LEGO_EV3_BRICK_IMG = '1h_brick_yellow.png';

  final public const string LEGO_NXT_BRICK_IMG = '1h_brick_yellow.png';

  final public const string ARDUINO_BRICK_IMG = '1h_brick_light_blue.png';

  final public const string EMBROIDERY_BRICK_IMG = '1h_brick_pink.png';

  final public const string JUMPING_SUMO_BRICK_IMG = self::MOTION_BRICK_IMG;

  final public const string AR_DRONE_MOTION_BRICK_IMG = self::MOTION_BRICK_IMG;

  final public const string AR_DRONE_LOOKS_BRICK_IMG = self::LOOKS_BRICK_IMG;

  final public const string RASPI_BRICK_IMG = '1h_brick_light_blue.png';

  final public const string RASPI_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;

  final public const string RASPI_EVENT_SCRIPT_IMG = self::EVENT_SCRIPT_IMG;

  final public const string PHIRO_BRICK_IMG = '1h_brick_light_blue.png';

  final public const string PHIRO_SOUND_BRICK_IMG = self::SOUND_BRICK_IMG;

  final public const string PHIRO_LOOK_BRICK_IMG = self::LOOKS_BRICK_IMG;

  final public const string PHIRO_CONTROL_BRICK_IMG = self::CONTROL_BRICK_IMG;

  final public const string TESTING_BRICK_IMG = '1h_brick_light_blue.png';

  final public const string YOUR_BRICK_IMG = '1h_brick_light_blue.png';

  final public const string YOUR_SCRIPT_IMG = '1h_brick_light_blue.png';

  // -------------------------------------------------------------------------------------------------------------------
  // Formula Categories
  //
  final public const string TIME_TO_WAIT_IN_SECONDS_FORMULA = 'TIME_TO_WAIT_IN_SECONDS';

  final public const string NOTE_FORMULA = 'NOTE';

  final public const string IF_CONDITION_FORMULA = 'IF_CONDITION';

  final public const string TIMES_TO_REPEAT_FORMULA = 'TIMES_TO_REPEAT';

  final public const string X_POSITION_FORMULA = 'X_POSITION';

  final public const string Y_POSITION_FORMULA = 'Y_POSITION';

  final public const string X_POSITION_CHANGE_FORMULA = 'X_POSITION_CHANGE';

  final public const string Y_POSITION_CHANGE_FORMULA = 'Y_POSITION_CHANGE';

  final public const string STEPS_FORMUlA = 'STEPS';

  final public const string TURN_LEFT_DEGREES_FORMULA = 'TURN_LEFT_DEGREES';

  final public const string TURN_RIGHT_DEGREES_FORMULA = 'TURN_RIGHT_DEGREES';

  final public const string DEGREES_FORMULA = 'DEGREES';

  final public const string DURATION_IN_SECONDS_FORMULA = 'DURATION_IN_SECONDS';

  final public const string Y_DESTINATION_FORMUlA = 'Y_DESTINATION';

  final public const string X_DESTINATION_FORMULA = 'X_DESTINATION';

  final public const string VIBRATE_DURATION_IN_SECONDS_FORMULA = 'VIBRATE_DURATION_IN_SECONDS';

  final public const string VELOCITY_X_FORMULA = 'PHYSICS_VELOCITY_X';

  final public const string VELOCITY_Y_FORMULA = 'PHYSICS_VELOCITY_Y';

  final public const string TURN_LEFT_SPEED_FORMULA = 'PHYSICS_TURN_LEFT_SPEED';

  final public const string TURN_RIGHT_SPEED_FORMULA = 'PHYSICS_TURN_RIGHT_SPEED';

  final public const string GRAVITY_Y_FORMULA = 'PHYSICS_GRAVITY_Y';

  final public const string GRAVITY_X_FORMULA = 'PHYSICS_GRAVITY_X';

  final public const string MASS_FORMULA = 'PHYSICS_MASS';

  final public const string BOUNCE_FACTOR_FORMULA = 'PHYSICS_BOUNCE_FACTOR';

  final public const string FRICTION_FORMULA = 'PHYSICS_FRICTION';

  final public const string VOLUME_FORMUlA = 'VOLUME';

  final public const string VOLUME_CHANGE_FORMULA = 'VOLUME_CHANGE';

  final public const string SPEAK_FORMULA = 'SPEAK';

  final public const string SIZE_FORMULA = 'SIZE';

  final public const string SIZE_CHANGE_FORMULA = 'SIZE_CHANGE';

  final public const string TRANSPARENCY_FORMULA = 'TRANSPARENCY';

  final public const string TRANSPARENCY_CHANGE_FORMULA = 'TRANSPARENCY_CHANGE';

  final public const string BRIGHTNESS_FORMULA = 'BRIGHTNESS';

  final public const string BRIGHTNESS_CHANGE_FORMULA = 'BRIGHTNESS_CHANGE';

  final public const string COLOR_FORMUlA = 'COLOR';

  final public const string COLOR_CHANGE_FORMULA = 'COLOR_CHANGE';

  final public const string VARIABLE_FORMULA = 'VARIABLE';

  final public const string VARIABLE_CHANGE_FORMULA = 'VARIABLE_CHANGE';

  final public const string LIST_ADD_ITEM_FORMULA = 'LIST_ADD_ITEM';

  final public const string LIST_DELETE_ITEM_FORMULA = 'LIST_DELETE_ITEM';

  final public const string INSERT_ITEM_LIST_VALUE_FORMULA = 'INSERT_ITEM_INTO_USERLIST_VALUE';

  final public const string INSERT_ITEM_LIST_INDEX_FORMULA = 'INSERT_ITEM_INTO_USERLIST_INDEX';

  final public const string REPLACE_ITEM_LIST_VALUE_FORMULA = 'REPLACE_ITEM_IN_USERLIST_VALUE';

  final public const string REPLACE_ITEM_LIST_INDEX_FORMULA = 'REPLACE_ITEM_IN_USERLIST_INDEX';

  final public const string STORE_CSV_INTO_LIST_COLUMN_FORMULA = 'STORE_CSV_INTO_USERLIST_COLUMN';

  final public const string STORE_CSV_INTO_LIST_CSV_FORMULA = 'STORE_CSV_INTO_USERLIST_CSV';

  final public const string REPEAT_UNTIL_CONDITION_FORMULA = 'REPEAT_UNTIL_CONDITION';

  final public const string ASK_QUESTION_FORMULA = 'ASK_QUESTION';

  final public const string ASK_SPEECH_QUESTION_FORMULA = 'ASK_SPEECH_QUESTION';

  final public const string STRING_FORMULA = 'STRING';

  final public const string PEN_SIZE_FORMULA = 'PEN_SIZE';

  final public const string PEN_COLOR_RED_FORMULA = 'PHIRO_LIGHT_RED';

  final public const string PEN_COLOR_BLUE_FORMULA = 'PHIRO_LIGHT_BLUE';

  final public const string PEN_COLOR_GREEN_FORMULA = 'PHIRO_LIGHT_GREEN';

  final public const string PEN_COLOR_RED_NEW_FORMULA = 'PEN_COLOR_RED';

  final public const string PEN_COLOR_BLUE_NEW_FORMULA = 'PEN_COLOR_BLUE';

  final public const string PEN_COLOR_GREEN_NEW_FORMULA = 'PEN_COLOR_GREEN';

  final public const string LEGO_EV3_POWER_FORMULA = 'LEGO_EV3_POWER';

  final public const string LEGO_EV3_PERIOD_IN_SECONDS_FORMULA = 'LEGO_EV3_PERIOD_IN_SECONDS';

  final public const string LEGO_EV3_DURATION_IN_SECONDS_FORMULA = 'LEGO_EV3_DURATION_IN_SECONDS';

  final public const string LEGO_EV3_VOLUME_FORMULA = 'LEGO_EV3_VOLUME';

  final public const string LEGO_EV3_FREQUENCY_FORMULA = 'LEGO_EV3_FREQUENCY';

  final public const string LEGO_EV3_DEGREES_FORMULA = 'LEGO_EV3_DEGREES';

  final public const string BACKGROUND_REQUEST_FORMULA = 'BACKGROUND_REQUEST';

  final public const string LOOK_REQUEST_FORMULA = 'LOOK_REQUEST';

  // --- AR DRONE FORMULA ---
  final public const string AR_DRONE_TIME_TO_FLY_IN_SECONDS = 'DRONE_TIME_TO_FLY_IN_SECONDS';

  final public const string AR_DRONE_POWER_IN_PERCENT = 'DRONE_POWER_IN_PERCENT';

  // --- JUMP SUMO FORMULA ---
  final public const string JUMP_SUMO_SPEED = 'JUMPING_SUMO_SPEED';

  final public const string JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS = 'JUMPING_SUMO_TIME_TO_DRIVE_IN_SECONDS';

  final public const string JUMPING_SUMO_ROTATE = 'JUMPING_SUMO_ROTATE';

  final public const string OPERATOR_FORMULA_TYPE = 'OPERATOR';

  final public const string FUNCTION_FORMULA_TYPE = 'FUNCTION';

  final public const string BRACKET_FORMULA_TYPE = 'BRACKET';

  final public const string PLUS_OPERATOR = 'PLUS';

  final public const string MINUS_OPERATOR = 'MINUS';

  final public const string MULT_OPERATOR = 'MULT';

  final public const string DIVIDE_OPERATOR = 'DIVIDE';

  final public const string EQUAL_OPERATOR = 'EQUAL';

  final public const string NOT_EQUAL_OPERATOR = 'NOT_EQUAL';

  final public const string GREATER_OPERATOR = 'GREATER_THAN';

  final public const string GREATER_EQUAL_OPERATOR = 'GREATER_OR_EQUAL';

  final public const string SMALLER_OPERATOR = 'SMALLER_THAN';

  final public const string SMALLER_EQUAL_OPERATOR = 'SMALLER_OR_EQUAL';

  final public const string NOT_OPERATOR = 'LOGICAL_NOT';

  final public const string AND_OPERATOR = 'LOGICAL_AND';

  final public const string OR_OPERATOR = 'LOGICAL_OR';

  final public const string POINTED_OBJECT_TAG = 'pointedObject';

  final public const string LOOK_INDEX = 'LOOK_INDEX';
}
