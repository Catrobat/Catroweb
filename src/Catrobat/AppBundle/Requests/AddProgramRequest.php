<?php

namespace Catrobat\AppBundle\Requests;

use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Entity\User;

class AddProgramRequest
{
    private $user;
    private $programfile;
    private $ip;
    private $gamejam;
    private $post_to_facebook;

    public function __construct(User $user, File $programfile, $post_to_facebook, $ip = '127.0.0.1', $gamejam = null)
    {
        $this->user = $user;
        $this->programfile = $programfile;
        $this->post_to_facebook = $post_to_facebook;
        $this->ip = $ip;
        $this->gamejam = $gamejam;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getProgramfile()
    {
        return $this->programfile;
    }

    public function setProgramfile(File $programfile)
    {
        $this->programfile = $programfile;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getGamejam()
    {
        return $this->gamejam;
    }


    public function shouldPostToFacebook()
    {
        return $this->post_to_facebook;
    }
}
