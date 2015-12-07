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

    public function __construct(User $user, File $programfile, $ip = '127.0.0.1', $gamejam = null)
    {
        $this->user = $user;
        $this->programfile = $programfile;
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
}
