<?php

namespace App\Catrobat;

class StatusCode
{
  /**
   * @var int
   */
  const OK = 200;
  /**
   * @var int
   */
  const INTERNAL_SERVER_ERROR = 500;
  /**
   * @var int
   */
  const MISSING_POST_DATA = 501;
  /**
   * @var int
   */
  const UPLOAD_EXCEEDING_FILESIZE = 502;
  /**
   * @var int
   */
  const MISSING_CHECKSUM = 503;
  /**
   * @var int
   */
  const INVALID_CHECKSUM = 504;
  /**
   * @var int
   */
  const INVALID_FILE = 505;
  /**
   * @var int
   */
  const INVALID_PROGRAM = 506;
  /**
   * @var int
   */
  const PROJECT_XML_MISSING = 507;
  /**
   * @var int
   */
  const INVALID_XML = 508;
  /**
   * @var int
   */
  const MISSING_PROGRAM_TITLE = 509;
  /**
   * @var int
   */
  const IMAGE_MISSING = 524;
  /**
   * @var int
   */
  const UNEXPECTED_FILE = 525;
  /**
   * @var int
   */
  const RUDE_WORD_IN_PROGRAM_NAME = 511;
  /**
   * @var int
   */
  const RUDE_WORD_IN_DESCRIPTION = 512;
  /**
   * @var int
   */
  const RUDE_WORD_IN_CREDITS = 813;
  /**
   * @var int
   */
  const UPLOAD_UNSUPPORTED_MIME_TYPE = 516;
  /**
   * @var int
   */
  const UPLOAD_UNSUPPORTED_FILE_TYPE = 517;
  /**
   * @var int
   */
  const OLD_CATROBAT_LANGUAGE = 518;
  /**
   * @var int
   */
  const OLD_APPLICATION_VERSION = 519;
  /**
   * @var int
   */
  const INVALID_PARAM = 520;
  /**
   * @var int
   */
  const FILE_UPLOAD_FAILED = 521;
  /**
   * @var int
   */
  const MEDIA_LIB_CATEGORY_NOT_FOUND = 522;
  /**
   * @var int
   */
  const MEDIA_LIB_PACKAGE_NOT_FOUND = 523;
  /**
   * @var int
   */
  const PROGRAM_NAME_TOO_LONG = 526;
  /**
   * @var int
   */
  const DESCRIPTION_TOO_LONG = 527;
  /**
   * @var int
   */
  const INVALID_FILE_UPLOAD = 528;  // upload failed but program still in DB
  const NOT_MY_PROGRAM = 529;
  const NO_ADMIN_RIGHTS = 530;
  const NOT_LOGGED_IN = 531;
  const PASSWORD_INVALID = 532;
  const CREDITS_TO_LONG = 833;

  const CSRF_FAILURE = 590;

  const LOGIN_ERROR = 601;
  const REGISTRATION_ERROR = 602;

  const USER_PASSWORD_MISSING = 751;
  const USER_USERNAME_PASSWORD_EQUAL = 752;
  const USER_PASSWORD_TOO_SHORT = 753;
  const USER_PASSWORD_TOO_LONG = 754;
  const USER_NEW_PASSWORD_BOARD_UPDATE_FAILED = 755;
  const USER_UPDATE_EMAIL_FAILED = 756;
  const USER_ADD_EMAIL_EXISTS = 757;
  const USER_UPDATE_CITY_FAILED = 758;
  const USER_UPDATE_COUNTRY_FAILED = 759;
  const USER_UPDATE_GENDER_FAILED = 760;
  const USER_UPDATE_BIRTHDAY_FAILED = 761;
  const USER_USERNAME_MISSING = 762;
  const USER_USERNAME_INVALID_CHARACTER = 763;
  const USER_USERNAME_INVALID = 764;
  const USER_EMAIL_INVALID = 765;
  const USER_COUNTRY_INVALID = 766;
  const USER_REGISTRATION_FAILED = 767;
  const USER_UPDATE_LANGUAGE_FAILED = 768;
  const USER_POST_DATA_MISSING = 769;
  const USER_RECOVERY_NOT_FOUND = 770;
  const USER_RECOVERY_HASH_CREATION_FAILED = 771;
  const USER_RECOVERY_EXPIRED = 772;
  const USER_AVATER_CREATION_FAILED = 773;
  const USER_PASSWORD_NOT_EQUAL_PASSWORD2 = 774;
  const USER_NAME_IS_EMAIL_ADDRESS = 775;
  const USER_AVATAR_UPLOAD_ERROR = 776;
  const USER_ADD_USERNAME_EXISTS = 777;
  const USER_EMAIL_MISSING = 778;
  const USER_EMAIL_ALREADY_EXISTS = 779;
  const USERNAME_MISSING = 800;
  const USERNAME_ALREADY_EXISTS = 801;
  const USERNAME_INVALID = 802;
  const USERNAME_NOT_FOUND = 803;
  const USERNAME_CONTAINS_EMAIL = 804;

  const NO_GAME_JAM = 900;
}
