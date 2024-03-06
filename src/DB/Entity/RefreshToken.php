<?php

namespace App\DB\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ORM\Table('refresh_tokens')]
#[ORM\Entity]
class RefreshToken extends BaseRefreshToken
{
}
