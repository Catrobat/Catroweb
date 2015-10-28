<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\StatusCode;

class FakeFacebookPostService
{
    public function __construct()
    {
    }

    public function removeFbPost($program)
    {
        return StatusCode::OK;
    }

    public function postOnFacebook(Program $program)
    {
        $fake_facebook_post_id = -1;
        return $fake_facebook_post_id;
    }
}
