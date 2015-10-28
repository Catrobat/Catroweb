<?php

namespace Catrobat\AppBundle\Events;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\UserManager;
use Symfony\Component\EventDispatcher\Event;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;

class ProgramAfterInsertEvent extends Event
{
    protected $extracted_file;
    protected $program;
    protected $post_to_facebook;

    public function __construct(ExtractedCatrobatFile $extracted_file, Program $program, $post_to_facebook)
    {
        $this->extracted_file = $extracted_file;
        $this->program = $program;
        $this->post_to_facebook = $post_to_facebook;
    }

    public function getExtractedFile()
    {
        return $this->extracted_file;
    }

    public function getProgramEntity()
    {
        return $this->program;
    }

    public function shouldPostToFacebook()
    {
        return $this->post_to_facebook;
    }
}
