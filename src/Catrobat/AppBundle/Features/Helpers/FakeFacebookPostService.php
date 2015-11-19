<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\StatusCode;

class FakeFacebookPostService
{
    public function __construct()
    {
    }

    public function removeFbPost($post_id)
    {
        return StatusCode::OK;
    }

    public function postOnFacebook($program_id)
    {
        $fake_facebook_post_id = -1;
        return $fake_facebook_post_id;
    }
}
