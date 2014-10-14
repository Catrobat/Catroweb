<?php

namespace AppBundle;

class StatusCode
{
  const OK = 200;
  const EMAIL_ALREADY_EXISTS = 300;
  const INTERNAL_SERVER_ERROR = 500;
  const MISSING_POST_DATA = 501;
  const MISSING_CHECKSUM = 503;
  const INVALID_CHECKSUM = 504;
  const INVALID_FILE = 505;
  const PROJECT_XML_MISSING = 507;
  const INVALID_XML = 508;
  const IMAGE_MISSING = 524;
  const UNEXPECTED_FILE = 525;
  const RUDE_WORD_IN_PROGRAM_NAME = 511;
  const RUDE_WORD_IN_DESCRIPTION = 512;
  
  const LOGIN_ERROR = 601;
  const REGISTRATION_ERROR = 602;
  
}