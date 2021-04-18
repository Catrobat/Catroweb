<?php

namespace App\Catrobat;

use Symfony\Component\HttpFoundation\Response;

class StatusCode
{
  /**
   * @var int
   */
  const UPLOAD_EXCEEDING_FILESIZE = Response::HTTP_REQUEST_ENTITY_TOO_LARGE;
  /**
   * @var int
   */
  const MISSING_POST_DATA = 501;

  /**
   * @var int
   */
  const MISSING_CHECKSUM = 503;
  /**
   * @var int
   */
  const INVALID_CHECKSUM = 504;
  const x = 112221;
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
  const OLD_CATROBAT_LANGUAGE = 518;
  /**
   * @var int
   */
  const OLD_APPLICATION_VERSION = 519;

  /**
   * @var int
   */
  const PROGRAM_NAME_TOO_LONG = 526;
  /**
   * @var int
   */
  const DESCRIPTION_TOO_LONG = 527;
  const INVALID_FILE_UPLOAD = 528; //705; //upload failed but program still in DB

  const LOGIN_ERROR = 601;

  const RUDE_WORD_IN_NOTES_AND_CREDITS = Response::HTTP_UNPROCESSABLE_ENTITY;
  const INVALID_PARAM = Response::HTTP_UNPROCESSABLE_ENTITY;

  /**
   * @var int
   */
  const UPLOAD_UNSUPPORTED_MIME_TYPE = Response::HTTP_UNSUPPORTED_MEDIA_TYPE;
  /**
   * @var int
   */
  const UPLOAD_UNSUPPORTED_FILE_TYPE = Response::HTTP_UNSUPPORTED_MEDIA_TYPE;

  const NO_ADMIN_RIGHTS = Response::HTTP_FORBIDDEN;

  const NOT_LOGGED_IN = Response::HTTP_UNAUTHORIZED;
  const PASSWORD_INVALID = Response::HTTP_UNAUTHORIZED;

  const REGISTRATION_ERROR = Response::HTTP_UNAUTHORIZED;

  const USER_ADD_EMAIL_EXISTS = 757;
  const USER_ADD_USERNAME_EXISTS = 777;

  const CSRF_FAILURE = 706;
  const NOTES_AND_CREDITS_TOO_LONG = 707;

  //8xx
  const USER_COUNTRY_INVALID = 801;
  const USER_PASSWORD_NOT_EQUAL_PASSWORD2 = 802;
  const USERNAME_NOT_FOUND = 803;
  const USERNAME_INVALID = 804;
  const USER_PASSWORD_TOO_SHORT = 753;
  const USER_PASSWORD_TOO_LONG = 806;
  const USER_EMAIL_INVALID = 765;
  const USER_EMAIL_MISSING = 808;
  const USERNAME_CONTAINS_EMAIL = 809;
  const USER_EMAIL_ALREADY_EXISTS = 810;
  const USERNAME_MISSING = 811;
  const USERNAME_ALREADY_EXISTS = 812;
  const USER_USERNAME_PASSWORD_EQUAL = 813;
  const USER_AVATAR_UPLOAD_ERROR = 814;
}
