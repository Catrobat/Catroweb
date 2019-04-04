<?php

namespace App\Catrobat;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class StatusCode
 * @package Catrobat
 */
class StatusCode
{
  const UPLOAD_EXCEEDING_FILESIZE = Response::HTTP_REQUEST_ENTITY_TOO_LARGE;

  const MISSING_POST_DATA         = Response::HTTP_UNPROCESSABLE_ENTITY;
  const MISSING_CHECKSUM          = Response::HTTP_UNPROCESSABLE_ENTITY;
  const INVALID_CHECKSUM          = Response::HTTP_UNPROCESSABLE_ENTITY;
  const INVALID_FILE              = Response::HTTP_UNPROCESSABLE_ENTITY;
  const INVALID_PROGRAM           = Response::HTTP_UNPROCESSABLE_ENTITY;
  const PROJECT_XML_MISSING       = Response::HTTP_UNPROCESSABLE_ENTITY;
  const INVALID_XML               = Response::HTTP_UNPROCESSABLE_ENTITY;
  const MISSING_PROGRAM_TITLE     = Response::HTTP_UNPROCESSABLE_ENTITY;
  const IMAGE_MISSING             = Response::HTTP_UNPROCESSABLE_ENTITY;
  const UNEXPECTED_FILE           = Response::HTTP_UNPROCESSABLE_ENTITY;
  const RUDE_WORD_IN_PROGRAM_NAME = Response::HTTP_UNPROCESSABLE_ENTITY;
  const RUDE_WORD_IN_DESCRIPTION  = Response::HTTP_UNPROCESSABLE_ENTITY;
  const INVALID_PARAM             = Response::HTTP_UNPROCESSABLE_ENTITY;
  const FILE_UPLOAD_FAILED        = Response::HTTP_UNPROCESSABLE_ENTITY;

  const LOGIN_ERROR         = Response::HTTP_UNAUTHORIZED;
  const REGISTRATION_ERROR  = Response::HTTP_UNAUTHORIZED;
  const NOT_LOGGED_IN       = Response::HTTP_UNAUTHORIZED;
  const PASSWORD_INVALID    = Response::HTTP_UNAUTHORIZED;
  const NO_ADMIN_RIGHTS     = Response::HTTP_FORBIDDEN;

  const USER_PASSWORD_MISSING                 = 751;
  const USER_USERNAME_PASSWORD_EQUAL          = 752;
  const USER_PASSWORD_TOO_SHORT               = 753;
  const USER_PASSWORD_TOO_LONG                = 754;
  const USER_NEW_PASSWORD_BOARD_UPDATE_FAILED = 755;
  const USER_UPDATE_EMAIL_FAILED              = 756;
  const USER_ADD_EMAIL_EXISTS                 = 757;
  const USER_UPDATE_CITY_FAILED               = 758;
  const USER_UPDATE_COUNTRY_FAILED            = 759;
  const USER_UPDATE_GENDER_FAILED             = 760;
  const USER_UPDATE_BIRTHDAY_FAILED           = 761;
  const USER_USERNAME_MISSING                 = 762;
  const USER_USERNAME_INVALID_CHARACTER       = 763;
  const USER_USERNAME_INVALID                 = 764;
  const USER_EMAIL_INVALID                    = 765;
  const USER_COUNTRY_INVALID                  = 766;
  const USER_REGISTRATION_FAILED              = 767;
  const USER_UPDATE_LANGUAGE_FAILED           = 768;
  const USER_POST_DATA_MISSING                = 769;
  const USER_RECOVERY_NOT_FOUND               = 770;
  const USER_RECOVERY_HASH_CREATION_FAILED    = 771;
  const USER_RECOVERY_EXPIRED                 = 772;
  const USER_AVATER_CREATION_FAILED           = 773;
  const USER_PASSWORD_NOT_EQUAL_PASSWORD2     = 774;
  const USER_NAME_IS_EMAIL_ADDRESS            = 775;
  const USER_AVATAR_UPLOAD_ERROR              = 776;
  const USER_ADD_USERNAME_EXISTS              = 777;
  const USER_EMAIL_MISSING                    = 778;
  const USER_EMAIL_ALREADY_EXISTS             = 779;
  const FB_POST_ERROR                         = 790;
  const FB_DELETE_ERROR                       = 791;

  const NO_GAME_JAM                   = 900;

  const UPLOAD_UNSUPPORTED_MIME_TYPE  = 916;
  const UPLOAD_UNSUPPORTED_FILE_TYPE  = 917;
  const OLD_CATROBAT_LANGUAGE         = 918;
  const OLD_APPLICATION_VERSION       = 919;


  const MEDIA_LIB_CATEGORY_NOT_FOUND  = 922;
  const MEDIA_LIB_PACKAGE_NOT_FOUND   = 923;


  const PROGRAM_NAME_TOO_LONG         = 926;
  const DESCRIPTION_TOO_LONG          = 927;
  const INVALID_FILE_UPLOAD           = 928;  // upload failed but program still in DB

}
