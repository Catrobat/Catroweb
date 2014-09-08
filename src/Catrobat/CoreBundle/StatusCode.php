<?php

namespace Catrobat\CoreBundle;

class StatusCode
{
  const OK = 200;
  const INTERNAL_SERVER_ERROR = 500;
  const MISSING_POST_DATA = 501;
  const MISSING_CHECKSUM = 503;
  const INVALID_CHECKSUM = 504;
  const INVALID_FILE = 505;
  const PROJECT_XML_MISSING = 507;
  const INVALID_XML = 508;
  const IMAGE_MISSING = 524;
  const UNEXPECTED_FILE = 525;
  const RUDE_WORD_IN_DESCRIPTION = 512;
  
  const LOGIN_ERROR = 601;
  const REGISTRATION_ERROR = 602;
  
  static private $messages = array(
    OK => "",
    PROJECT_XML_MISSING => "unknown error: project_xml_not_found!"
  );
  
  static function getMessage($code)
  {
    return StatusCode::$messages[$code];
  }
  
}